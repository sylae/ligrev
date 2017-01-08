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
    $this->from   = new \XMPPJid($stanza->from);

    if ($origin == "groupchat" && array_key_exists($this->from->bare,
        $config['rooms'])) {
      $this->config = array_merge($config, $config['rooms'][$this->from->bare]);
    } else {
      $this->config = $config;
    }

    if ($this->from->resource && !$this->stanza->exists('delay',
        NS_DELAYED_DELIVERY)) {
      \Monolog\Registry::MESSAGE()->info("Message received",
        ['body' => $this->stanza->body, 'room' => $this->from->node, 'nick' => $this->from->resource, 'isPM' => ($this->origin == "chat")]);
      $this->text   = $this->stanza->body;
      $this->room   = $this->from->bare;
      $this->author = $this->from->resource;

      $real_jid = $roster->rooms[$this->room]->nickToEntity($this->author);
      if ($real_jid instanceof xmppEntity) {
        $_ligrevStartupInhibitTell = false; // now that we've received a non-delayed msg, we know we're realtime.
        $real_jid->active();
        $real_jid->processTells($this->room);
      }

      $preg = "/^[\/:!](\w+)(\s|$)/";
      if (!in_array($this->author[0], [':', '!', '/']) && preg_match($preg,
          $this->text, $match) && class_exists("Ligrev\\Command\\" . $match[1])) {
        $class   = "Ligrev\\Command\\" . $match[1];
        $command = new $class($stanza, $this->origin);
        $command->process();
      }

      $qp = \qp('<?xml version="1.0"?>' . $this->stanza->to_string());
      foreach (get_declared_classes() as $class) {
        $c = new \ReflectionClass($class);
        if ($c->getNameSpaceName() == "Ligrev\Parser") {
          $p_n = $c->name;
          if ($p_n::trigger($this->stanza, $qp)) {
            \Monolog\Registry::MESSAGE()->info("Fired parser",
              ['parser' => $p_n]);
            $p = new $p_n($this->stanza, $this->origin);
          }
        }
      }
    }
  }

}
