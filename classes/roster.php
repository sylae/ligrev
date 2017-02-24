<?php

/*
 * Copyright (C) 2016 Keira Sylae Aro <sylae@calref.net>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Ligrev;

use Monolog\Registry;

/**
 * Handles our entire user roster management. Uses querypath because
 * sweet christ, JAXL has a shitty XML parser...
 *
 * Blatantly copies code from cadence
 *
 * @link https://github.com/cburschka/cadence/blob/master/js/core/xmpp.js
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

    // Make sure we aren't doing anything for buddies (todo: fix this better)
    if (!array_key_exists($room, $this->config['rooms'])) {
      return true;
    }

    // Initialize the room if it doesn't exist yet.
    if (!array_key_exists($room, $this->rooms)) {
      $this->rooms[$room] = new mucRoom($room);
    }

    // Find the status codes.
    $item  = \qp($xml)->find('item');
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
      Registry::ROSTER()->info("Username change",
        ['nick' => $newNick, 'old_nick' => $nick, 'room' => $room]);
      $this->isNickChange = true;
    } elseif ((array_key_exists(301, $codes) && $codes[301] >= 0) || (array_key_exists(307,
        $codes) && $codes[307] >= 0)) { // An `unavailable` 301 is a ban; a 307 is a kick.
      $type   = (array_key_exists(301, $codes) && $codes[301] >= 0) ? 'banned' : 'kicked';
      $actor  = \qp($item, 'actor')->attr('nick');
      $reason = \qp($item, 'reason')->text();
      Registry::ROSTER()->info("User booted from chat",
        ['nick' => $nick, 'type' => $type, 'actor' => $actor, 'reason' => $reason, 'room' => $room]);
      $this->rooms[$room]->removeMember($nick);
    } else { // Any other `unavailable` presence indicates a logout.
      Registry::ROSTER()->info("User left room",
        ['nick' => $nick, 'room' => $room]);
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
    $show   = \qp($stanza, 'show')->text() || 'default';
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
      Registry::ROSTER()->info("User joined room",
        ['nick' => $nick, 'room' => $room]);
      $user->getUserTime()->then(function () use ($user, $room, $nick) {
        if ($this->onlineByJID($user, $room)) {
          $user->processTells($room, $nick);
        }
      });
    }

    // TODO: get XEP-0256 info if possible, use it to update user activity timer
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

  /**
   * Check the room(s) a user is active in
   * @param \XMPPJid $id The JID to check for
   * @return array array of rooms user is in
   */
  function onlineRoom($id) {
    $id    = new \XMPPJid(str_replace(" ", "\\20", $id));
    $rooms = [];
    foreach ($this->rooms as $name => $mucRoomObj) {
      $found = $mucRoomObj->jidToNick($id, false);
      if ($found) {
        $rooms[] = $mucRoomObj;
      }
    }
    return $rooms;
  }

}
