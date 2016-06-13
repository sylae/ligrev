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
 * :sybeam command
 *
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
class SybeamCommand implements Ligrev\iLigrevCommand {

  /**
   *
   * @param \Ligrev $ligrev
   * @param \Ligrev\Message $message
   * @return boolean If true, do not bubble to other handlers
   * @throws \Ligrev\MalformedCommandException
   */
  function __construct(\Ligrev $ligrev, \Ligrev\Message $message) {
    if (count($message->textParts) < 2) {
      throw new \Ligrev\MalformedCommandException();
    }
    return $this->sybeam($ligrev, $message);
  }

  /**
   * Print some sybeams
   * @param \Ligrev $ligrev
   * @param \Ligrev\Message $message
   * @return boolean
   * @throws \Ligrev\noPermissionsException
   */
  private function sybeam(\Ligrev $ligrev, \Ligrev\Message $message) {
    if ($ligrev->hasPermission("Ligrev/Sybeam", $message)) {
      try {
        $sybeams = new \Ligrev\SimpleMath($message->textParts[1]);
      } catch (\Ligrev\MathException $e) {
        $message->reply($e->getMessage());
        return true;
      }
      $string = str_repeat(':sybeam:', max(1, min(100, $sybeams)));
      $message->reply($string);
      return true;
    } else {
      throw new \Ligrev\noPermissionsException("Ligrev/Sybeam");
    }
  }

  /**
   *
   * @return array
   */
  public static function help() {
    return [
      "ligrev" => [
        'type' => 'args',
        'permission' => 'Ligrev/Sybeam',
        'help' => 'Print multiple sybeams',
        'args' => [
          [1] => [
            'type' => 'Math',
            'required' => true,
            'help' => 'How many sybeams to print',
          ]
        ],
      ],
    ];
  }

}
