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
 * class representing an MUC room.
 */
class mucRoom extends ligrevGlobals {

  /**
   * An array of xmppEntities, key is room nick
   * @var array
   */
  public $members = [];

  /**
   * The name/string JID of the room
   * @var string
   */
  public $name;

  /**
   * Constructor
   * @param string $name The to_string()'ed form of the room JID
   */
  public function __construct($name) {
    parent::__construct();
    $this->name = $name;

    if (array_key_exists("lang", $this->config['rooms'][$this->name])) {
      $this->lang = $this->config['rooms'][$this->name]['lang'];
    }
  }

  /**
   * Add a new member to the room
   * @param string $nick The member's nick
   * @param \XMPPJid $jid JID of the member
   * @todo return value
   * @todo Use the $rosters->jids array and reference here instead
   */
  public function addMember($nick, $jid) {
    $this->members[$nick] = new xmppEntity($jid);
  }

  /**
   * Remove a member from the room
   * @param string $nick The nick to remove
   * @todo return value
   * @todo Use the $rosters->jids array and reference here instead
   */
  public function removeMember($nick) {
    unset($this->members[$nick]);
  }

  /**
   *
   * @param string $oldnick The old nick
   * @param string $newnick The new nick
   * @todo return value
   * @todo Use the $rosters->jids array and reference here instead
   */
  public function renameMember($oldnick, $newnick) {
    $this->members[$newnick] = $this->members[$oldnick];
    $this->removeMember($oldnick);

    $this->members[$newnick]->active();
  }

  /**
   * Look up a JID and return the nick of the matching user
   * @param \XMPPJid $jid The JID to search for
   * @param type $resource_sensitive If true, match the exact resource, otherwise return the first matching bare JID
   * @return string|boolean The nick, or false if no matches were found
   */
  public function jidToNick(\XMPPJid $jid, $resource_sensitive = true) {
    foreach ($this->members as $nick => $member) {
      if ($resource_sensitive && mb_strtolower($jid->to_string()) == mb_strtolower($member->jid->to_string())) {
        return $nick;
      } elseif (!$resource_sensitive && mb_strtolower($jid->bare) == mb_strtolower($member->jid->bare)) {
        return $nick;
      }
    }
    return false;
  }

  /**
   * Given a nick, returns the matching xmppEntity.
   * @param string $nick The nick to search for
   * @return xmppEntity|boolean The entity object, or false if no match was found
   */
  public function nickToEntity($nick) {
    if (array_key_exists($nick, $this->members)) {
      return $this->members[$nick];
    } else {
      return false;
    }
  }

  /**
   * Check if a room permission is enabled.
   * @global array $config
   * @param string $permission
   * @return boolean Whether or not the room has the permission
   */
  public function canDo($permission) {
    global $config;

    $value = false;

    // global
    $value = return_ake($permission, $config['permissions'], $value);

    // room
    if (array_key_exists("permissions", $config['rooms'][$this->name])) {
      $value = return_ake($permission, $config['rooms'][$this->name]['permissions'], $value);
    }

    return $value;
  }

}
