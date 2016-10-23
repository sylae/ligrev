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

/**
 * Handler for any messages incoming. Calls a Ligrev\command class if needed
 */
class messageHandler {

  protected $client;
  protected $origin;
  protected $stanza;
  protected $from;
  protected $text;
  protected $author;
  protected $room;
  protected $config;

  function __construct(\XMPPStanza $stanza, $origin) {
    global $client, $roster, $config, $_ligrevStartupInhibitTell;
    $this->client = &$client;
    $this->roster = &$roster;
    $this->origin = $origin;
    $this->stanza = $stanza;
    $this->from = new \XMPPJid($stanza->from);

    if ($origin == "groupchat" && array_key_exists($this->from->bare, $config['rooms'])) {
      $this->config = array_merge($config, $config['rooms'][$this->from->bare]);
    } else {
      $this->config = $config;
    }

    if ($this->from->resource && !$this->stanza->exists('delay', NS_DELAYED_DELIVERY)) {
      \Monolog\Registry::MESSAGE()->info("Message received", ['body' => $this->stanza->body, 'room' => $this->from->node, 'nick' => $this->from->resource, 'isPM' => ($this->origin == "chat")]);
      $this->text = $this->stanza->body;
      $this->room = $this->from->bare;
      $this->author = $this->from->resource;

      $real_jid = $roster->rooms[$this->room]->nickToEntity($this->author);
      if ($real_jid instanceof xmppEntity) {
        $_ligrevStartupInhibitTell = false; // now that we've received a non-delayed msg, we know we're realtime.
        $real_jid->active();
        $real_jid->processTells($this->room);
      }

      $preg = "/^[\/:!](\w+)(\s|$)/";
      if (!in_array($this->author[0], [':', '!', '/']) && preg_match($preg, $this->text, $match) && class_exists("Ligrev\\Command\\" . $match[1])) {
        $class = "Ligrev\\Command\\" . $match[1];
        $command = new $class($stanza, $this->origin);
        $command->process();
      }
    }
  }

  function kickOccupant($nick, $roomJid, $reason = false, $callback = false) {
    global $client;
    $payload = "<iq from='" . $client->jid->to_string() . "'id='ligrev_" . time() . "'to='" . $roomJid . "'type='set'>
  <query xmlns='http://jabber.org/protocol/muc#admin'>
    <item nick='" . $nick . "' role='none'>
      <reason>" . $reason . "</reason>
    </item>
  </query>
</iq>";
    $client->send_raw($payload);
  }

}
