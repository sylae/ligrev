<?php

/**
 * Initializes and/or resets the deck of cards. Right now, one deck per room.
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */

namespace Ligrev\Command;

class shuffle extends \Ligrev\command {

  function process() {
    global $decks;

    if ($this->origin == "chat") {
      $this->_send($this->getDefaultResponse(), _("Cannot use cards in private context."));
      return;
    }
    $decks[$this->room] = array();
    $suits = array(
        _('Hearts'),
        _('Diamonds'),
        _('Clubs'),
        _('Spades'),
    );
    $nums = array(
        _('Ace'),
        _('Two'),
        _('Three'),
        _('Four'),
        _('Five'),
        _('Six'),
        _('Seven'),
        _('Eight'),
        _('Nine'),
        _('Ten'),
        _('Jack'),
        _('Queen'),
        _('King'),
    );
    $c = 1;
    while ($c <= 54) {
      if ($c >= 53) {
        $decks[$this->room][] = _("Joker");
      } else {
        $decks[$this->room][] = sprintf(_("%s of %s"), $nums[($c - 1) % 13], $suits[($c - 1) % 4]);
      }
      $c++;
    }
    shuffle($decks[$this->room]);
    l(sprintf(_("Reset %s"), $this->room), "CARD", L_DEBUG);
    $this->_send($this->getDefaultResponse(), _("Deck shuffled."));
  }

}
