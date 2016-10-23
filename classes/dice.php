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

namespace Ligrev;

/**
 * Dice rolling class
 */
class dice {

  /**
   * Result of the dice roll.
   * @var int
   */
  public $result;

  /**
   * Constructor function. Handles the entire dice roll and returns the result
   * @param int $n Number of dice
   * @param type $d Number of sides per die
   * @param string $method Adding method. "sum" or "array"
   * @param boolean $savage If true, "explode" the dice
   * @return int|string the result of the roll
   */
  function __construct($n, $d, $method = "sum", $savage = false) {
    $n = (int) $n;
    if (!is_int($n) || $n < 1 || $n > 128) {
      $n = 1;
    }
    $d = (int) $d;
    if (!is_int($d) || $d < 0) {
      $result = 0;
    }

    $die = [];

    for ($i = 0; $i < $n; $i++) {
      if ($savage) {
        $die[] = $this->_rollSavageDice($d);
      } else {
        $die[] = $this->_dice(1, $d);
      }
    }
    if ($method == "array") {
      $result = implode(", ", $die);
    } else {
      $result = array_sum($die);
    }
    $this->result = $result;
  }

  /**
   * get a random dice roll.
   * @todo figure out why the openssl code sucks.
   * @param int $min Smallest allowed value
   * @param int $max Largest allowed value
   * @return int Result of die roll
   */
  private function _dice($min, $max) {
    return rand($min, $max);
  }

  /**
   * Roll a savage die. If result==maximum, roll again and add them together. This is recursive
   * @param int $die Sides
   * @param int $nest Any nested prior dice to add
   * @return int Result of die roll
   */
  private function _rollSavageDice($die, $nest = 0) {
    if ($die == 1) {
      return 1;
    }
    $roll = $this->_dice(1, $die);
    if ($roll == $die) {
      // Ace
      \Monolog\Registry::COMMAND()->debug("Savage dice aced");
      return $this->_rollSavageDice($die, $roll + $nest);
    } else {
      return $nest + $roll;
    }
  }

}
