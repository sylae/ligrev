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
 * :slap command
 *
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
class SlapCommand implements Ligrev\iLigrevCommand {

  /**
   *
   * @param \Ligrev $ligrev
   * @param \Ligrev\Message $message
   * @return boolean If true, do not bubble to other handlers
   * @throws \Ligrev\MalformedCommandException
   */
  function __construct(\Ligrev $ligrev, \Ligrev\Message $message) {
    if (count($message->textParts) < 1) {
      throw new \Ligrev\MalformedCommandException();
    }
    return $this->slap($ligrev, $message);
  }

  /**
   * Slap a user
   * @param \Ligrev $ligrev
   * @param \Ligrev\Message $message
   * @return boolean
   * @throws \Ligrev\noPermissionsException
   */
  private function slap(\Ligrev $ligrev, \Ligrev\Message $message) {
    if ($ligrev->hasPermission("Ligrev/Slap", $message)) {
      if (array_key_exists(1, $message->textParts)) {
        $vic = $ligrev->roster->getOnlineUser($message->textParts[1]);
      } else {
        $vic = $ligrev->getSelfUser();
      }
      $fish = [$this->t('poach'), $this->t('salmon'), $this->t('greyling'), $this->t('coelecanth'), $this->t('trout')];
      $wep = (array_key_exists(2, $message->textParts) ? $message->textParts[2] : array_rand(array_flip($fish)));
      $message->reply(sprintf($this->t("%s slaps %s with a large %s"), $message->author->HTML(), $vic->HTML(), $wep));
      return true;
    } else {
      throw new \Ligrev\noPermissionsException("Ligrev/Slap");
    }
  }

  /**
   *
   * @return array
   */
  public static function help() {
    return [
      "slap" => [
        'type' => 'args',
        'permission' => 'Ligrev/Slap',
        'help' => 'Slap another user',
        'args' => [
          [1] => [
            'type' => 'User',
            'required' => false,
            'help' => 'Who to slap',
            'default' => 'Ligrev',
          ],
          [2] => [
            'type' => 'string',
            'required' => false,
            'help' => 'What to slap the user with',
            'default' => 'fish',
          ],
        ],
      ],
    ];
  }

}
