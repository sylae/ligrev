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

    $decks[$this->room] = array();
    $suits = array(
      'Hearts',
      'Diamonds',
      'Clubs',
      'Spades',
    );
    $nums = array(
      'Ace',
      '2',
      '3',
      '4',
      '5',
      '6',
      '7',
      '8',
      '9',
      '10',
      'Jack',
      'Queen',
      'King',
    );
    $c = 1;
    while ($c <= 54) {
      if ($c >= 53) {
        $decks[$this->room][] = "Joker";
      } else {
        $decks[$this->room][] = $nums[($c - 1) % 13] . " of " . $suits[($c - 1) % 4];
      }
      $c++;
    }
    shuffle($decks[$this->room]);
    l("[CARD] Reset " . $this->room, L_DEBUG);
    $this->_send($this->room, "Deck shuffled.");
  }

}
