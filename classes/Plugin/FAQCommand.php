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
 * frequently asked questions. Helpful for a tech support channel
 *
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
class FAQCommand implements Ligrev\iLigrevCommand {

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
      case "set":
        if (count($message->textParts) < 4) {
          throw new \Ligrev\MalformedCommandException();
        }
        return $this->set($ligrev, $message);
      default:
        return $this->query($ligrev, $message);
    }
  }

  private function set(\Ligrev $ligrev, \Ligrev\Message $message) {
    if ($ligrev->hasPermission("Ligrev/FAQ/Set", $message)) {
      $code = $message->textParts[3];
      $answer = $message->textAllAfter(3);


      if ($this->getFAQ($ligrev, $message, $code)['found'] == true) {
        // ALREADY EXISTS.
        $message->reply(sprintf($message->t("%s, the FAQ %s is already taken!"), $message->author->HTML(), $code));
        return true;
      } else {
        // doesn't exist.
        $sql = $this->db->prepare('INSERT INTO faq (author, room, message, keyword) VALUES(?, ?, ?, ?);', ['string', 'string', 'string', 'string']);
        $sql->bindValue(1, $message->author, "string");
        $sql->bindValue(2, $message->room, "string");
        $sql->bindValue(3, $answer, "string");
        $sql->bindValue(4, $code, "string");
        $sql->execute();
        $message->reply(sprintf($message->t("FAQ with keyword %s added."), $code));
        return true;
      }
    } else {
      throw new \Ligrev\noPermissionsException("Ligrev/FAQ/Set");
    }
  }

  private function query(\Ligrev $ligrev, \Ligrev\Message $message) {
    if ($ligrev->hasPermission("Ligrev/FAQ/Ask", $message)) {
      $code = $message->textParts[2];

      $query = $this->getFAQ($ligrev, $message, $code);
      if ($query['found'] == true) {
        // EXISTS.
        $message->reply(sprintf($message->t("FAQ authored by %s: %s"), $query['author']->HTML(), $query['message']));
        return true;
      } else {
        // doesn't exist.
        $message->reply(sprintf($message->t("%s, the FAQ %s does not exist!"), $message->author->HTML(), $code));
        return true;
      }
    } else {
      throw new \Ligrev\noPermissionsException("Ligrev/FAQ/Ask");
    }
  }

  /**
   * Get the answer to a FAQ
   * @param string $keyword
   * @return array the answer
   */
  private function getFAQ(\Ligrev $ligrev, \Ligrev\Message $message, string $keyword) {
    $sql = $ligrev->db->prepare('SELECT * FROM faq WHERE room = ? AND keyword = ?', ["string", "string"]);
    $sql->bindValue(1, $message->room, "string");
    $sql->bindValue(2, $keyword, "string");
    $sql->execute();
    $faqs = $sql->fetchAll();
    foreach ($faqs as $a) {
      return [
        'found' => true,
        'author' => $ligrev->roster->getUser($a['author']),
        'message' => $a['message'],
      ];
    }
    return ['found' => false];
  }

  /**
   *
   * @return array
   */
  public static function help() {
    return [
      "faq" => [
        'type' => 'subcommand',
        'help' => 'Provide answers for frequently asked questions',
        'subcommands' => [
          "set" => [
            'type' => 'args',
            'permission' => 'Ligrev/FAQ/Set',
            'help' => 'Set a FAQ',
            'args' => [
              [1] => [
                'type' => 'string',
                'required' => true,
                'help' => 'Keyword for the FAQ',
              ],
              [2] => [
                'type' => 'string...',
                'required' => true,
                'help' => 'Answer of the FAQ',
              ],
            ],
          ],
          '(default)' => [
            'type' => 'args',
            'permission' => 'Ligrev/FAQ/Ask',
            'help' => 'Get an answer for the keyword, if it exists', 'args' => [
              [1] => [
                'type' => 'string',
                'required' => true,
                'help' => 'Keyword for the FAQ',
              ],
            ],
          ],
        ],
      ]
    ];
  }

}
