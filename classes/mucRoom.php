<?php

/**
 * class representing an MUC room.
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */

namespace Ligrev;

class mucRoom extends ligrevGlobals {

  public $members = array();

  public function __construct() {
    parent::__construct();
  }

  public function addMember($nick, $jid) {
    $this->members[$nick] = new xmppEntity($jid);
  }

  public function removeMember($nick) {
    unset($this->members[$nick]);
  }

  public function renameMember($oldnick, $newnick) {
    $this->members[$newnick] = $this->members[$oldnick];
    $this->removeMember($oldnick);
  }

  public function jidToNick(\XMPPJid $jid, $resource_sensitive = true) {
    foreach ($this->members as $nick => $member) {
      var_dump($member->jid->to_string(), $jid->to_string());
      if ($resource_sensitive && $jid->to_string() == $member->jid->to_string()) {
        return $nick;
      } elseif (!$resource_sensitive && $jid->bare == $member->jid->bare) {
        return $nick;
      }
    }
    return false;
  }

  public function nickToEntity($nick) {
    if (array_key_exists($nick, $this->members)) {
      return $this->members[$nick];
    } else {
      return false;
    }
  }

}
