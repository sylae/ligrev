<?php

/**
 * Represents one xmpp resource.
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */

namespace Ligrev;

class xmppEntity extends ligrevGlobals {

  public $jid;

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

  public function setUserTime($tzo) {
    $this->setData('tzo', $tzo);
    return true;
  }

  public function formatUserTime($epoch) {
    $tzo = $this->getData('tzo');
    if (is_string($tzo)) {
      return userTime($epoch, $tzo);
    } else {
      return userTime($epoch);
    }
  }

  protected function escape_class($string) {
    return $string ? preg_replace_callback('/[\\s\0\\\\]/', function ($x) {
              return '\\' . ord($x[0]);
            }, $string) : '';
  }

  protected function jid_classes() {
    return 'user jid-node-' . $this->escape_class(strtolower($this->jid->node))
            . ' jid-domain-' . $this->escape_class($this->jid->domain)
            . ' jid-resource-' . $this->escape_class($this->jid->resource);
  }

  public function generateHTML($nick = null) {

    $display = str_replace('\\20', ' ', (is_string($nick) ? $nick : $this->jid->bare));
    $classes = $this->jid_classes();
    $html = "<span class=\"$classes\" data-jid=\"{$this->jid->to_string()}\""
            . (is_string($nick) ? " data-nick=\"$nick\"" : '')
            . ">{$display}</span>";
    return $html;
  }

  function processTells($room) {
    global $roster;
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
