<?php

/*
 * Copyright (C) 2016 Sylae Jiendra Corell <sylae@calref.net>
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

namespace Ligrev\Plugin;

/**
 * dice handling commands
 *
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
class DiceCommand implements Ligrev\iLigrevCommand {

  const dice = "/(\d*)d(\d+)/";
  const savdice = "/(\d*)d(\d+)e/";
  const dlist = "/(\d*)a(\d+)/";
  const savdlist = "/(\d*)a(\d+)e/";

  /**
   *
   * @param \Ligrev $ligrev
   * @param \Ligrev\Message $message
   * @return boolean If true, do not bubble to other handlers
   * @throws \Ligrev\MalformedCommandException
   */
  function __construct(\Ligrev $ligrev, \Ligrev\Message $message) {
    switch ($message->command) {
      case "dice":
      case "roll":
        return $this->roll($ligrev, $message);
      default:
        throw new \Ligrev\MalformedCommandException();
    }
  }

  /**
   * Roll some dice
   * @param \Ligrev $ligrev
   * @param \Ligrev\Message $message
   * @return boolean
   * @throws \Ligrev\noPermissionsException
   */
  private function roll(\Ligrev $ligrev, \Ligrev\Message $message) {
    if ($ligrev->hasPermission("Ligrev/Dice", $message)) {
      $text = $message->textAllAfter(0);
      $strings = explode(":", $text, $ligrev->getConfig('Ligrev/Dice/MaxDicePerCommand', $message));

      $st = [];
      foreach ($strings as $i => $s) {
        $sa = $this->cup($sa);
        try {
          $sa = new \Ligrev\SimpleMath($sa);
        } catch (\Ligrev\MathException $e) {
          $sa = $e->getMessage();
        }
        $st[] = $sa;
      }
      $snd = sprintf($message->t("%s rolls %s"), $message->author->HTML(), implode(", ", $st));
      $message->reply($snd);
      return true;
    } else {
      throw new \Ligrev\noPermissionsException("Ligrev/Dice");
    }
  }

  /**
   * Scan for dice and roll them
   * @param string $sa
   * @return string
   */
  private function cup(string $sa) {
    $sa = preg_replace_callback(self::savdice, function ($m) {
      $m[2] = (($m[2] == 0) ? 1 : $m[2]);
      $m[1] = (($m[1] == 0) ? 1 : $m[1]);
      $d = $this->_roll($m[1], $m[2], "sum", true);
      return "(" . $d . ")";
    }, $s
    );
    $sa = preg_replace_callback(self::dice, function ($m) {
      $m[2] = (($m[2] == 0) ? 1 : $m[2]);
      $m[1] = (($m[1] == 0) ? 1 : $m[1]);
      $d = $this->_roll($m[1], $m[2], "sum", false);
      return "(" . $d . ")";
    }, $sa
    );
    $sa = preg_replace_callback(self::savdlist, function ($m) {
      $m[2] = (($m[2] == 0) ? 1 : $m[2]);
      $m[1] = (($m[1] == 0) ? 1 : $m[1]);
      $d = $this->_roll($m[1], $m[2], "array", true);
      return 'print "' . $d . '"';
    }, $sa
    );
    $sa = preg_replace_callback(self::dlist, function ($m) {
      $m[2] = (($m[2] == 0) ? 1 : $m[2]);
      $m[1] = (($m[1] == 0) ? 1 : $m[1]);
      $d = $this->_roll($m[1], $m[2], "array", false);
      return 'print "' . $d . '"';
    }, $sa
    );
    return $sa;
  }

  /**
   * Handles the entire dice roll and returns the result
   * @param int $n Number of dice
   * @param type $d Number of sides per die
   * @param string $method Adding method. "sum" or "array"
   * @param boolean $savage If true, "explode" the dice
   * @return int|string the result of the roll
   */
  function _roll($n, $d, $method = "sum", $savage = false) {
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
      return $this->_rollSavageDice($die, $roll + $nest);
    } else {
      return $nest + $roll;
    }
  }

  /**
   * @return array
   */
  public static function help() {
    return [
      'roll' => [
        'type' => 'args',
        'permission' => 'Ligrev/Dice',
        'help' => 'Roll Dice',
        'args' => [
          [1] => [
            'type' => 'string...',
            'required' => true,
            'help' => 'Dice to roll',
            'help_extended' => [
              'Seperate multiple dice with colons (":").',
            ],
          ],
        ],
      ],
      'dice' => [
        'type' => 'alias',
        'alias' => 'roll'
      ],
    ];
  }

}
