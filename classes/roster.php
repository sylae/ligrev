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

class roster extends ligrevGlobals {

  public $rooms = array();
  public $jids = array();
  private $isNickChange = false;

  function __construct() {
    parent::__construct();
  }

  function ingest(\XMPPStanza $stanza) {
    global $config;

    $xml = \qp('<?xml version="1.0"?>' . $stanza->to_string());


    $from = new \XMPPJid($stanza->from);
    $room = $from->bare;
    $nick = $from->resource;
    $type = \qp($xml)->attr('type');

    // Don't add self to roster, or if its an error
    if ($room == $config['jaxl']['jid'] || $type == 'error') {
      return true;
    }
    // Initialize the room if it doesn't exist yet.
    if (!array_key_exists($room, $this->rooms)) {
      $this->rooms[$room] = new mucRoom();
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
      $this->rooms[$room]->renameMember($nick, $newNick);
      l(sprintf(_("%s is now known as %s"), $nick, $newNick), $room);
      $this->isNickChange = true;
    } elseif ((array_key_exists(301, $codes) && $codes[301] >= 0) || (array_key_exists(307, $codes) && $codes[307] >= 0)) { // An `unavailable` 301 is a ban; a 307 is a kick.
      $type = (array_key_exists(301, $codes) && $codes[301] >= 0) ? 'banned' : 'kicked';
      $actor = \qp($item, 'actor')->attr('nick');
      $reason = \qp($item, 'reason')->text();
      l(sprintf("%s %s by %s", $nick, $type, $actor), $room);
    } else { // Any other `unavailable` presence indicates a logout.
      l(sprintf("%s left room", $nick), $room);
      $this->rooms[$room]->removeMember($nick);
    }
  }

  private function eventPresenceDefault($room, $nick, $item, $stanza) {
    // away, dnd, xa, chat, [default].
    $show = \qp($stanza, 'show')->text() || 'default';
    $status = \qp($stanza, 'status')->text() || '';

    $this->rooms[$room]->addMember($nick, new \XMPPJid($item->attr('jid')));
    $user = & $this->rooms[$room]->members[$nick];
    $user->setData('role', $item->attr('role'));
    $user->setData('affiliation', $item->attr('affiliation'));
    $user->setData('show', $item->attr('show'));
    $user->setData('status', $item->attr('status'));

    if ($this->isNickChange) {
      $this->isNickChange = false;
    } else {
      l(sprintf("%s joined room", $nick), $room);
      $user->getUserTime();
    }
    $user->processTells($room);
  }

  function onlineByJID($id) {
    global $config;
    $id = new \XMPPJid(str_replace(" ", "\\20", $id));
    foreach ($this->rooms as $name => $mucRoomObj) {
      $found = $mucRoomObj->jidToNick($id, false);
      var_dump($found);
      if ($found) {
        return true;
      }
    }
    return false;
  }

}
