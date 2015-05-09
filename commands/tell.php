<?php

/**
 * Leave a message for an offline user, for Ligrev to send later
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */

namespace Ligrev;

class tell extends command {

  function process() {
    global $config, $roster;
    $textParts = $this->_split($this->text);
    $r = (array_key_exists(1, $textParts) ? $textParts[1] : null);
    $message = trim(str_replace($textParts[0] . " " . $textParts[1], "", $this->text));
    if ($r) {
      $recipient = $r;
    } else {
      $this->_send($this->room, "Error: No recipient.");
      return;
    }
    $private = ($this->origin == "groupchat" ? false : true);

    if (!preg_match("/@+/", $recipient)) {
      // If there's no domain, assume it's for the default
      $recipient = $r . "@" . $config['defaultTellDomain'];
    }

    // Let's make sure the user isn't already online.
    if ($roster->onlineByJID($recipient)) {
      l("User already online");
    }
  }

}
