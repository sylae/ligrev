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
      $this->_send($this->getDefaultResponse(), $this->t("Cannot use cards in private context."));
      return;
    }
    $decks[$this->room] = array();
    $suits = array(
      $this->t('Hearts'),
      $this->t('Diamonds'),
      $this->t('Clubs'),
      $this->t('Spades'),
    );
    $nums = array(
      $this->t('Ace'),
      $this->t('Two'),
      $this->t('Three'),
      $this->t('Four'),
      $this->t('Five'),
      $this->t('Six'),
      $this->t('Seven'),
      $this->t('Eight'),
      $this->t('Nine'),
      $this->t('Ten'),
      $this->t('Jack'),
      $this->t('Queen'),
      $this->t('King'),
    );
    $c = 1;
    while ($c <= 54) {
      if ($c >= 53) {
        $decks[$this->room][] = $this->t("Joker");
      } else {
        $decks[$this->room][] = sprintf($this->t("%s of %s"), $nums[($c - 1) % 13], $suits[($c - 1) % 4]);
      }
      $c++;
    }
    shuffle($decks[$this->room]);
    l(sprintf($this->t("Reset %s"), $this->room), "CARD", L_DEBUG);
    $this->_send($this->getDefaultResponse(), $this->t("Deck shuffled."));
  }

}
