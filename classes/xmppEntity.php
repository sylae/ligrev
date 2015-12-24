<?php

namespace Ligrev;

/**
 * The xmppEntity class is a representation of an xmpp "resource". It provides
 * any functions that relate to a specific user
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
class xmppEntity extends ligrevGlobals {

  /**
   * The JID of this user
   * @var \XMPPJid
   */
  public $jid;

  /**
   * Constructor
   * @param \XMPPJid $jid The JID of the user in question
   */
  public function __construct(\XMPPJid $jid) {
    parent::__construct();
    $this->jid = $jid;
  }

  /**
   * Send an IQ to get a user's timezone
   */
  public function getUserTime() {
    $id = $this->client->get_id();
    $resp = new \XMPPIq(array('from' => $this->client->full_jid->to_string(), 'to' => $this->jid->to_string(), 'type' => 'get', 'id' => $id));
    $resp->c('time', NS_TIME);

    $this->client->send($resp);
    $this->client->add_cb('on_stanza_id_' . $id, function($stanza) {
      global $roster;
      $qp = \qp('<?xml version="1.0"?>' . $stanza->to_string());
      if (\qp($qp, 'time')->attr('xmlns') == NS_TIME && $stanza->type == "result" && array_key_exists($stanza->from, $roster->jids)) {
        $tzo = \qp($qp, 'tzo')->text();
        $roster->jids[$stanza->from]->setUserTime($tzo);
      }
    });
  }

  /**
   * Set the user's time offset
   * @param string $tzo Offset in "[+/-]HH:MM" notation or "Z".
   * @return boolean True if everything went well
   */
  public function setUserTime($tzo) {
    $this->setData('tzo', $tzo);
    return true;
  }

  /**
   * Format a timestamp for presentation to a user
   * @param int $epoch a unix timestamp
   * @return string The localized representation of the time
   */
  public function formatUserTime($epoch) {
    $tzo = $this->getData('tzo');
    if (is_string($tzo)) {
      return userTime($epoch, $tzo);
    } else {
      return userTime($epoch);
    }
  }

  /**
   * Escape whitespace for use in JID classes
   * @param string $string The string to esape
   * @return string The escaped string
   * @todo Option to switch between decimal and hex
   * @link https://github.com/cburschka/cadence/issues/298
   */
  protected function escape_class($string) {
    return $string ? preg_replace_callback('/[\\s\0\\\\]/', function ($x) {
        return '\\' . ord($x[0]);
      }, $string) : '';
  }

  /**
   * Generate JID classes for use by Cadence-compatible chats.
   * @return string A string of CSS classes
   */
  protected function jid_classes() {
    return 'user jid-node-' . $this->escape_class(strtolower($this->jid->node))
      . ' jid-domain-' . $this->escape_class($this->jid->domain)
      . ' jid-resource-' . $this->escape_class($this->jid->resource);
  }

  /**
   * Wrap a username in a Cadence-compatible span element.
   * @param string $nick Optionally include a nick to display as
   * @return string The wrapped string
   */
  public function generateHTML($nick = null) {

    $display = str_replace('\\20', ' ', (is_string($nick) ? $nick : $this->jid->bare));
    $classes = $this->jid_classes();
    $html = "<span class=\"$classes\" data-jid=\"{$this->jid->to_string()}\""
      . (is_string($nick) ? " data-nick=\"$nick\"" : '')
      . ">{$display}</span>";
    return $html;
  }

  /**
   * Check if the user has any pending :tell messages
   * @param string $room The room the user has joined, for public :tells
   */
  function processTells($room) {
    $sql = $this->db->prepare('SELECT * FROM tell WHERE recipient = ? ORDER BY sent ASC', array("string"));
    $sql->bindValue(1, str_replace("\\20", " ", $this->jid->bare), "string");
    $sql->execute();
    $tells = $sql->fetchAll();
    foreach ($tells as $tell) {
      $sender = new xmppEntity(new \XMPPJid($tell['sender']));
      $senderHTML = $sender->generateHTML();
      $recipientHTML = $this->generateHTML();

      $time = $this->formatUserTime($tell['sent']);
      $message = sprintf($this->t("Message from %s for %s at %s:") . PHP_EOL . $tell['message'], $senderHTML, $recipientHTML, $time);
      if ($tell['private']) {
        $this->sendMessage($user, $message, true, "chat");
      } else {
        $this->sendMessage($room, $message, true, "groupchat");
      }
      $this->db->delete('tell', array('id' => $tell['id']));
    }
  }

}
