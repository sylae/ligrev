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
   * Last time the user was seen active
   * @var int Unix timestamp.
   */
  public $lastActivity;

  /**
   * Constructor
   * @param \XMPPJid $jid The JID of the user in question
   */
  public function __construct(\XMPPJid $jid) {
    parent::__construct();
    $this->jid = $jid;
  }

  /**
   * Magic method
   * @return string JID as a string
   */
  public function __toString() {
    return $this->jid->to_string();
  }

  /**
   * Update the activity counter
   * @return boolean true
   */
  public function active() {
    $this->lastActivity = time();
    return true;
  }

  /**
   * Send an IQ to get a user's timezone
   */
  public function getUserTime() {
    $id = $this->client->get_id();
    $resp = new \XMPPIq([
      'from' => $this->client->full_jid->to_string(),
      'to' => $this->jid->to_string(),
      'type' => 'get',
      'id' => $id
    ]);
    $resp->c('time', IQ\xep_0202::NS_TIME);

    $this->client->send($resp);
    $this->client->add_cb('on_stanza_id_' . $id, function($stanza) {
      global $roster;
      $qp = \qp('<?xml version="1.0"?>' . $stanza->to_string());
      if (\qp($qp, 'time')->attr('xmlns') == IQ\xep_0202::NS_TIME && $stanza->type == "result" && array_key_exists($stanza->from, $roster->jids)) {
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
   * @param string $nick The user's nick, for private :tells
   */
  function processTells($room, $nick = null) {
    $sql = $this->db->prepare('SELECT * FROM tell WHERE recipient = ? ORDER BY sent ASC', array("string"));
    $sql->bindValue(1, str_replace("\\20", " ", $this->jid->bare), "string");
    $sql->execute();
    $tells = $sql->fetchAll();
    foreach ($tells as $tell) {
      $sender = new xmppEntity(new \XMPPJid($tell['sender']));
      $senderHTML = $sender->generateHTML($this->roster->onlineByJID($sender, $room) ? $this->roster->rooms[$room]->jidToNick($sender->jid, false) : null);
      $recipientHTML = $this->generateHTML(is_string($nick) ? $nick : null);

      $time = $this->formatUserTime($tell['sent']);
      $message = sprintf($this->t("Message from %s for %s at %s:"), $senderHTML, $recipientHTML, $time) . PHP_EOL . $tell['message'];
      if ($tell['private']) {
        $jid = new \XMPPJid($room);
        $jid->resource = $nick;
        $this->sendMessage($jid, $message, true, "chat");
      } else {
        $this->sendMessage($room, $message, true, "groupchat");
      }
      $this->db->delete('tell', ['id' => $tell['id']]);
    }
  }

  /**
   * Determine if a user has permissions to do something. Optionally filter by room.
   * @param string $permission
   * @param string $room
   * @return bool true if the user has permission, false otherwise
   */
  public function canDo($permission, $room = null) {
    global $config;

    $value = false;

    // global
    $value = return_ake($permission, $config['permissions'], $value);

    // room
    if (is_string($room) && array_key_exists("permissions", $config['rooms'][$room])) {
      $value = return_ake($permission, $config['rooms'][$room]['permissions'], $value);
    }

    // affiliation
    if (array_key_exists($this->getData("affiliation"), $config['permissions'])) {
      $value = return_ake($permission, $config['permissions'][$this->getData("affiliation")], $value);
    }

    // user
    if (array_key_exists($this->jid->bare, $config['permissions'])) {
      $value = return_ake($permission, $config['permissions'][$this->jid->bare], $value);
    }

    if (is_string($room) && array_key_exists("permissions", $config['rooms'][$room])) {

      // room-> affiliation
      if (array_key_exists($this->getData("affiliation"), $config['rooms'][$room]['permissions'])) {
        $value = return_ake($permission, $config['rooms'][$room]['permissions'][$this->getData("affiliation")], $value);
      }

      // room->user
      if (array_key_exists($this->getData("affiliation"), $config['rooms'][$room]['permissions'])) {
        $value = return_ake($permission, $config['rooms'][$room]['permissions'][$this->getData("affiliation")], $value);
      }
    }

    return $value;
  }

}
