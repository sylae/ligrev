<?php

/**
 * Pick a random card
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */

namespace Ligrev;

class card extends command {

  function process() {

    $dice = new dice(1, 54);
    $c = $dice->result;
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
    if ($c > 52) {
      $card = "Joker";
    } else {
      $card = $nums[($c - 1) % 13] . " of " . $suits[($c - 1) % 4];
    }
    l("[CARD] Dice rolled a " . $c, L_DEBUG);
    $snd = $this->author . ' draws a ' . $card;
    $this->_send($this->room, $snd);
  }

}
