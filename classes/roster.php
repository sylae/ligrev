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

  protected $roster = array();

  function ingest(\XMPPStanza $stanza) {
    global $config;
    
    $xml = '<?xml version="1.0"?>'.$stanza->to_string();
    

    $from = new \XMPPJid($stanza->from);
    $room = $from->bare;
    $nick = $from->resource;
    $type = \qp($xml)->attr('type');
    if ($room == $config['jaxl']['jid']) return true;

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
      if(!array_key_exists($c, $codes)) { $codes[$c] = 0; }
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
      l("[" . $room . "] " . $nick . " is now " . $newNick);
    } elseif ((array_key_exists(301, $codes) && $codes[301] >= 0) || (array_key_exists(307, $codes) && $codes[307] >= 0)) { // An `unavailable` 301 is a ban; a 307 is a kick.
      $type = (array_key_exists(301, $codes) && $codes[301] >= 0) ? 'banned' : 'kicked';
      $actor = \qp($item, 'actor')->attr('nick');
      $reason = \qp($item, 'reason')->text();
      l("[" . $room . "] " . $nick . " " . $type . " by " . $actor . (strlen($reason) > 0 ? " (" . $reason . ")" : ""));
    } else { // Any other `unavailable` presence indicates a logout.
      l("[" . $room . "] " . $nick . " left room");
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
      'nick' => $nick,
      'jid' => $item->attr('jid'), // if not anonymous.
      'role' => $item->attr('role'),
      'affiliation' => $item->attr('affiliation'),
      'show' => $show,
      'status' => $status,
    );
    $this->roster[$room][$nick] = $user;
    l("[" . $room . "] " . $user['nick'] . " joined room");
  }

}
