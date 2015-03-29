<?php

/**
 * Dice rolling class
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */

namespace Ligrev;

class dice {

  public $result;

  /**
   * Constructor function. Handles the entire dice roll and returns the result
   * @param int $n Number of dice
   * @param type $d Number of sides per die
   * @param string $method Adding method. "sum", "savage", or "array"
   * @return int|string the result of the roll
   */
  function __construct($n, $d, $method = "sum") {
    $n = (int) $n;
    if (!is_int($n) || $n < 1 || $n > 128) {
      $n = 1;
    }
    $d = (int) $d;
    if (!is_int($d) || $d < 0) {
      $result = 0;
    }

    $die = array();

    for ($i = 0; $i < $n; $i++) {
      if ($method == "sum") {
        $die[] = $this->_dice(1, $d);
      } elseif ($method == "savage") {
        $die[] = $this->_rollSavageDice($d);
      }
    }
    if ($method == "sum" || $method == "savage") {
      $result = array_sum($die);
    } elseif ($method == "array") {
      $result = implode(", ", $die);
    } else {
      $result = 0;
    }
    $this->result = $result;
  }

  /**
   * Uses OpenSSL to get a securely random dice roll. Overkill as fuck for our purposes
   * If the function doesn't exist (thanks windows?), use regular rand() to bullshit a number
   * @link http://us3.php.net/manual/en/function.openssl-random-pseudo-bytes.php#104322
   * @param int $min Smallest allowed value
   * @param int $max Largest allowed value
   * @return int Result of die roll
   */
  private function _dice($min, $max) {
    if (function_exists("openssl_random_pseudo_bytes")) {
      $range = $max - $min;
      if ($range == 0) {
        return $min;
      } // not so random...
      $log = log($range, 2);
      $bytes = (int) ($log / 8) + 1; // length in bytes
      $bits = (int) $log + 1; // length in bits
      $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
      do {
        $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes, $s))) & $filter;
      } while ($rnd >= $range);
      return $min + $rnd;
    } else {
      l("[DICE] Warning: openssl not used!");
      return rand($min, $max);
    }
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
      l("[DICE] Savage dice aced", L_DEBUG);
      return $this->_rollSavageDice($die, $roll);
    } else {
      return $nest + $roll;
    }
  }

}
