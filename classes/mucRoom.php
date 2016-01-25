<?php

namespace Ligrev;

/**
 * class representing an MUC room.
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
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

}
