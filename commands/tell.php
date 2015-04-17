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
    $textParts = $this->_split($this->text);
    $r = (array_key_exists(1, $textParts) ? $textParts[1] : null);
    $message = trim(str_replace($textParts[0] . " " . $textParts[1], "", $this->text));
    if ($r) {
      $recipient = $this->room . "/" . $r;
    } else {
      $this->_send($this->room, "Error: No recipient.");
    }
    $private = ($this->origin == "groupchat" ? false : true);
    
    // TODO: The rest of this
  }

}
