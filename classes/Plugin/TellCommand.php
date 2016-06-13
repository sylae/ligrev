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
 * :tell messages for another use
 *
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
class TellCommand implements Ligrev\iLigrevCommand {

  /**
   *
   * @param \Ligrev $ligrev
   * @param \Ligrev\Message $message
   * @return boolean If true, do not bubble to other handlers
   * @throws \Ligrev\MalformedCommandException
   */
  function __construct(\Ligrev $ligrev, \Ligrev\Message $message) {
    if (count($message->textParts) < 3) {
      throw new \Ligrev\MalformedCommandException();
    }
    switch ($message->command) {
      case "tell":
        return $this->tell($ligrev, $message);
      default:
        throw new \Ligrev\MalformedCommandException();
    }
  }

  public function tell(\Ligrev $ligrev, \Ligrev\Message $message) {
    $recipient = $ligrev->roster->getUser($message->textParts[1]);
    $message->textAllAfter(2);
    $sender = $message->from;
    $private = $message->isWhisper();

    // Let's make sure the user isn't already online.
    if ($ligrev->roster->isOfflineOrAFK($recipient)) {
      $message->reply(sprintf($message->t("%s already online. Contact user directly."), $recipient->HTML()));
      return;
    }

    $this->_insertTell($ligrev, $sender, $recipient, $private, $message);
    $this->_send($this->getDefaultResponse(), sprintf($this->t("Message for %s processed."), $recipientHTML));
  }

  private function _insertTell(\Ligrev $ligrev, $from, $recipient, $private, $message) {
    $sql = $ligrev->db->prepare('INSERT INTO tell (sender, recipient, sent, private, message) VALUES(?, ?, ?, ?, ?);', ['string', 'string', 'integer', 'boolean', 'string']);
    $sql->bindValue(1, $from, "string");
    $sql->bindValue(2, $recipient, "string");
    $sql->bindValue(3, time(), "integer");
    $sql->bindValue(4, $private, "boolean");
    $sql->bindValue(5, $message, "string");
    $sql->execute();
  }

  /**
   *
   * @return array
   */
  public static function help() {
    return [
      "tell" => [
        'type' => 'args',
        'permission' => 'Ligrev/Tell',
        'help' => 'Send a message to another user',
        'args' => [
          [1] => [
            'type' => 'User',
            'required' => true,
            'help' => 'Who to send the message to',
          ],
          [2] => [
            'type' => 'string...',
            'required' => true,
            'help' => 'The message',
          ],
        ],
      ],
    ];
  }

}
