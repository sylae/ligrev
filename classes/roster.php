<?php

namespace Ligrev;

use Monolog\Registry;

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
class roster extends ligrevGlobals {

  /**
   * An array of rooms. Key is room jid, value is a \Ligrev\mucRoom object
   * @var array
   */
  public $rooms = [];

  /**
   * Where we keep all of the JIDs in use by Ligrev
   * @var array
   */
  public $jids = [];

  /**
   * Used internally to store nick changing state (otherwise duped messages appear)
   * @var boolean
   */
  private $isNickChange = false;

  /**
   * Constructor
   */
  function __construct() {
    parent::__construct();
  }

  /**
   * Read a stanza and parse its presence information
   * @global array $codes
   * @param \XMPPStanza $stanza The stanza to parse
   * @return boolean True if everything went well
   * @todo replace the $codes global with $this->codes
   */
  function ingest(\XMPPStanza $stanza) {

    $xml = \qp('<?xml version="1.0"?>' . $stanza->to_string());


    $from = new \XMPPJid($stanza->from);
    $room = $from->bare;
    $nick = $from->resource;
    $type = \qp($xml)->attr('type');

    // Don't add self to roster, or if its an error
    if ($room == $this->config['jaxl']['jid'] || $type == 'error') {
      return true;
    }
    // Initialize the room if it doesn't exist yet.
    if (!array_key_exists($room, $this->rooms)) {
      $this->rooms[$room] = new mucRoom($room);
    }

    // Find the status codes.
    $item = \qp($xml)->find('item');
    global $codes;
    $codes = [];
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

  /**
   * Handle nick changes, logouts, kicks
   * @param sting $room
   * @param string $nick
   * @param array $codes
   * @param \DOMQuery $item
   */
  private function eventPresenceUnavailable($room, $nick, $codes, $item) {

    if (array_key_exists(303, $codes) && $codes[303] >= 0) { // An `unavailable` 303 is a nick change to <item nick="{new}"/>
      $newNick = \qp($item)->attr('nick');

      // Move the roster entry to the new nick, so the new presence
      // won't trigger a notification.
      $this->rooms[$room]->renameMember($nick, $newNick);
      Registry::ROSTER()->info("Username change", ['nick' => $newNick, 'old_nick' => $nick, 'room' => $room]);
      $this->isNickChange = true;
    } elseif ((array_key_exists(301, $codes) && $codes[301] >= 0) || (array_key_exists(307, $codes) && $codes[307] >= 0)) { // An `unavailable` 301 is a ban; a 307 is a kick.
      $type = (array_key_exists(301, $codes) && $codes[301] >= 0) ? 'banned' : 'kicked';
      $actor = \qp($item, 'actor')->attr('nick');
      $reason = \qp($item, 'reason')->text();
      Registry::ROSTER()->info("User booted from chat", ['nick' => $nick, 'type' => $type, 'actor' => $actor, 'reason' => $reason, 'room' => $room]);
    } else { // Any other `unavailable` presence indicates a logout.
      Registry::ROSTER()->info("User left room", ['nick' => $nick, 'room' => $room]);
      $this->rooms[$room]->removeMember($nick);
    }
  }

  /**
   * Handle logins
   * @param string $room
   * @param string $nick
   * @param \DOMQuery $item
   * @param \DOMQuery $stanza
   */
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
      Registry::ROSTER()->info("User joined room", ['nick' => $nick, 'room' => $room]);
      $user->getUserTime();
    }

    // TODO: get XEP-0256 info if possible, use it to update user activity timer
    $user->processTells($room, $nick);
  }

  /**
   * Check the rooms to see if a given JID is online
   * @param \XMPPJid $id The JID to check for
   * @param string $room If given, only search this room in particular
   * @param bool $return_obj If true, return the object itself instead of a bool
   * @return boolean|xmppEntity True/object if found, false otherwise
   */
  function onlineByJID($id, $room = null, $return_obj = false) {
    $id = new \XMPPJid(str_replace(" ", "\\20", $id));
    foreach ($this->rooms as $name => $mucRoomObj) {
      $found = $mucRoomObj->jidToNick($id, false);
      if ($found && is_null($room)) {
        return $return_obj ? $mucRoomObj->nickToEntity($found) : true;
      } elseif ($found && is_string($room) && $room == $name) {
        return $return_obj ? $mucRoomObj->nickToEntity($found) : true;
      }
    }
    return false;
  }

}
