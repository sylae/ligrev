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
 * :ligrev command
 *
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
class LigrevManagementCommand implements Ligrev\iLigrevCommand {

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
    switch ($message->textParts[1]) {
      case "restart":
        return $this->restart($ligrev, $message);
      case "diag":
      case "debug":
        return $this->diag($ligrev, $message);
      default:
        throw new \Ligrev\MalformedCommandException();
    }
  }

  /**
   * Restart Ligrev
   * @param \Ligrev $ligrev
   * @param \Ligrev\Message $message
   * @return boolean
   * @throws \Ligrev\noPermissionsException
   */
  private function restart(\Ligrev $ligrev, \Ligrev\Message $message) {
    if ($ligrev->hasPermission("Ligrev/Management/Restart", $message)) {
      \JAXLLoop::$clock->call_fun_after(5000000, function () {
        die();
      });
      return true;
    } else {
      throw new \Ligrev\noPermissionsException("Ligrev/Management/Restart");
    }
  }

  /**
   * Dump version debug info via xmpp
   * @param \Ligrev $ligrev
   * @param \Ligrev\Message $message
   * @return boolean
   * @throws \Ligrev\noPermissionsException
   */
  private function diag(\Ligrev $ligrev, \Ligrev\Message $message) {
    if ($ligrev->hasPermission("Ligrev/Management/Diag", $message)) {
      $lv = V_LIGREV;
      $string = $message->t('Ligrev Diagnostic Information') . PHP_EOL .
        sprintf($message->t('Ligrev Version: %s'), "[$lv](https://github.com/sylae/ligrev/commit/$lv)") . PHP_EOL .
        sprintf($message->t('PHP Version: %s'), phpversion()) . PHP_EOL .
        sprintf($message->t('Process ID %s as %s'), getmypid(), get_current_user()) . PHP_EOL .
        sprintf($message->t('System: %s'), php_uname());
      $message->reply($string);
      return true;
    } else {
      throw new \Ligrev\noPermissionsException("Ligrev/Management/Diag");
    }
  }

  /**
   *
   * @return array
   */
  public static function help() {
    return [
      "ligrev" => [
        'type' => 'subcommand',
        'help' => 'Commands for handling Ligrev itself',
        'subcommands' => [
          'diag' => [
            'type' => 'bare',
            'permission' => 'Ligrev/Management/Diag',
            'help' => 'Print helpful diagnostic information',
          ],
          'debug' => [
            'type' => 'alias',
            'alias' => 'diag'
          ],
          'restart' => [
            'type' => 'bare',
            'permission' => 'Ligrev/Management/Restart',
            'help' => 'Force Ligrev to update codebase and restart',
          ],
        ],
      ]
    ];
  }

}
