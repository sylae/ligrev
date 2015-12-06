<?php

/**
 * Handles our entire user roster management. Uses querypath because
 * sweet christ, JAXL has a shitty XML parser...
 * 
 * Blatantly copies code from cadence
 *
 * @link https://github.com/cburschka/cadence/blob/master/js/core/xmpp.js
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */

namespace Ligrev;

class roster {

  public $roster = array();

  function ingest(\XMPPStanza $stanza) {
    global $config;

    $xml = '<?xml version="1.0"?>' . $stanza->to_string();


    $from = new \XMPPJid($stanza->from);
    $room = $from->bare;
    $nick = $from->resource;
    $type = \qp($xml)->attr('type');
    if ($room == $config['jaxl']['jid'])
      return true;
    // Initialize the room roster if it doesn't exist yet.
    if (!array_key_exists($room, $this->roster)) {
      $this->roster[$room] = array();
    }

    if ($type == 'error') {
      return true;
    }

    // Find the status codes.
    $item = \qp($xml)->find('item');
    global $codes;
    $codes = array();
    \qp($xml, 'status')->map(function($index, $item) {
      global $codes;
      $c = (int) qp($item)->attr('code');
      if (!array_key_exists($c, $codes)) {
        $codes[$c] = 0;
      }
      $codes[(int) qp($item)->attr('code')] ++;
    });

    if ($type == 'unavailable') {
      $this->eventPresenceUnavailable($room, $nick, $codes, $item);
    } else {
      $this->eventPresenceDefault($room, $nick, $item, $xml);
    }
    return true;
  }

  private function eventPresenceUnavailable($room, $nick, $codes, $item) {

    if (array_key_exists(303, $codes) && $codes[303] >= 0) { // An `unavailable` 303 is a nick change to <item nick="{new}"/>
      $newNick = \qp($item)->attr('nick');

      // Move the roster entry to the new nick, so the new presence
      // won't trigger a notification.
      $this->roster[$room][$newNick] = $this->roster[$room][$nick];
      l("[" . $room . "] " . sprintf(_("%s is now %s"), $nick, $newNick));
    } elseif ((array_key_exists(301, $codes) && $codes[301] >= 0) || (array_key_exists(307, $codes) && $codes[307] >= 0)) { // An `unavailable` 301 is a ban; a 307 is a kick.
      $type = (array_key_exists(301, $codes) && $codes[301] >= 0) ? 'banned' : 'kicked';
      $actor = \qp($item, 'actor')->attr('nick');
      $reason = \qp($item, 'reason')->text();
      l("[" . $room . "] " . sprintf(_("%s %s by %s"), $nick,  $type,  $actor));
    } else { // Any other `unavailable` presence indicates a logout.
      l("[" . $room . "] " . sprintf(_("%s left room"), $nick));
    }
    // In either case, the old nick must be removed and destroyed.
    unset($this->roster[$room][$nick]);
  }

  private function eventPresenceDefault($room, $nick, $item, $stanza) {
    // away, dnd, xa, chat, [default].
    $show = \qp($stanza, 'show')->text() || 'default';
    $status = \qp($stanza, 'status')->text() || '';

    // Create the user object.
    $user = array(
      'jid' => new \XMPPJid($item->attr('jid')), // if not anonymous.
      'role' => $item->attr('role'),
      'affiliation' => $item->attr('affiliation'),
      'show' => $show,
      'status' => $status,
    );
    $this->roster[$room][$nick] = $user;
    l("[" . $room . "] " . sprintf(_("%s joined room"), $nick));
    $this->processTells($user['jid']->bare, $room);
  }
  
  function nickToJid($room, $nick) {
    return $this->roster[$room][$nick]['jid'];
  }

  function onlineByJID($id) {
    global $config;
    foreach ($this->roster as $room) {
      foreach ($room as $nick => $info) {
        if ($info['jid']->bare == $id && $config['tellCaseSensitive']) {
          return true;
        } elseif (strtolower($info['jid']->bare) == strtolower($id) && !$config['tellCaseSensitive']) {
          return true;
        }
      }
    }
    return false;
  }
  
  function processTells($user, $room) {
    global $db, $client;
    $sql = $db->prepare('SELECT * FROM tell WHERE recipient = ?', array("string"));
    $sql->bindValue(1, $user, "string");
    $sql->execute();
    $tells = $sql->fetchAll();
    foreach($tells as $tell) {
      $time = ($tell['sent'] > time()-(60*60*24)) ? strftime('%X', $tell['sent']) : strftime('%c', $tell['sent']);
      $message = sprintf(_("Message from %s for %s at %s:").PHP_EOL.$tell['message'], $tell['sender'], $tell['recipient'], $time);
      if ($tell['private']) {
        \Ligrev\_send($user, $message, true, "chat");
      } else {
        \Ligrev\_send($room, $message, true, "groupchat");
      }
      $db->delete('tell', array('id' => $tell['id']));
    }
  }
}
