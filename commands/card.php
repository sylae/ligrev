<?php

/**
 * Pick a random card from the $deck global variable.
 * @see shuffle
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 *
 * @todo Translate cards
 */

namespace Ligrev\Command;

class card extends \Ligrev\command {

  function process() {
    global $decks;
    if (!array_key_exists($this->room, $decks)) {
      $this->_send($this->from, _("Deck uninitialized, use :shuffle."));
    } elseif (count($decks[$this->room]) == 0) {
      $this->_send($this->from, _("Deck depleted, use :shuffle."));
    } else {
      $c = array_pop($decks[$this->room]);
      $this->_send($this->from, sprintf(_("%s draws a %s"), $this->authorHTML, $c));
    }
  }

}
