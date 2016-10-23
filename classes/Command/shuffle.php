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

namespace Ligrev\Command;

/**
 * Initializes and/or resets the deck of cards. Right now, one deck per room.
 */
class shuffle extends \Ligrev\command {

  function process() {
    global $decks;

    if (!$this->canDo("sylae/ligrev/card-shuffle")) {
      return false;
    }

    if ($this->origin == "chat") {
      $this->_send($this->getDefaultResponse(),
        $this->t("Cannot use cards in private context."));
      return;
    }
    $decks[$this->room] = [];
    $suits              = [
      $this->t('Hearts'),
      $this->t('Diamonds'),
      $this->t('Clubs'),
      $this->t('Spades'),
    ];
    $nums               = [
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
    ];
    $c                  = 1;
    while ($c <= 54) {
      if ($c >= 53) {
        $decks[$this->room][] = $this->t("Joker");
      } else {
        $decks[$this->room][] = sprintf($this->t("%s of %s"),
          $nums[($c - 1) % 13], $suits[($c - 1) % 4]);
      }
      $c++;
    }
    shuffle($decks[$this->room]);
    $this->logger()->debug("Reset deck", ['room' => $this->room]);
    $this->_send($this->getDefaultResponse(), $this->t("Deck shuffled."));
  }

}
