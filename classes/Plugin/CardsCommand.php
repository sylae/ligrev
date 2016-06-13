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
 * card/deck handling commands
 *
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
class CardsCommand implements Ligrev\iLigrevCommand {

  /**
   *
   * @param \Ligrev $ligrev
   * @param \Ligrev\Message $message
   * @return boolean If true, do not bubble to other handlers
   * @throws \Ligrev\MalformedCommandException
   */
  function __construct(\Ligrev $ligrev, \Ligrev\Message $message) {
    switch ($message->command) {
      case "shuffle":
        return $this->shuffle($ligrev, $message);
      case "card":
      case "draw":
        return $this->card($ligrev, $message);
      default:
        throw new \Ligrev\MalformedCommandException();
    }
  }

  /**
   * Draw a card
   * @param \Ligrev $ligrev
   * @param \Ligrev\Message $message
   * @return boolean
   * @throws \Ligrev\noPermissionsException
   */
  private function card(\Ligrev $ligrev, \Ligrev\Message $message) {
    if ($ligrev->hasPermission("Ligrev/Cards/Draw", $message)) {
      $deck = $ligrev->getRoomData($message->room, "Ligrev/Cards/Deck");
      if (!is_array($deck)) {
        $message->reply(sprintf($message->t("%s starts a deck of cards."), $message->author->HTML()));
        $deck = $this->newDeck();
      } elseif (count($deck) == 0) {
        $message->reply(sprintf($message->t("%s tries to draw but the deck is empty!"), $message->author->HTML()));
        return true;
      }
      $c = array_pop($deck);
      $ligrev->setRoomData($message->room, "Ligrev/Cards/Deck", $deck);
      $message->reply(sprintf($message->t("%s draws a %s"), $message->author->HTML(), $c));

      return true;
    } else {
      throw new \Ligrev\noPermissionsException("Ligrev/Cards/Draw");
    }
  }

  /**
   * Shuffle the deck
   * @param \Ligrev $ligrev
   * @param \Ligrev\Message $message
   * @return boolean
   * @throws \Ligrev\noPermissionsException
   */
  private function shuffle(\Ligrev $ligrev, \Ligrev\Message $message) {
    if ($ligrev->hasPermission("Ligrev/Cards/Draw", $message)) {
      $ligrev->setRoomData($message->room, "Ligrev/Cards/Deck", $this->newDeck());
      $message->reply(sprintf($message->t("%s shuffles the deck."), $message->author->HTML()));
      return true;
    } else {
      throw new \Ligrev\noPermissionsException("Ligrev/Cards/Draw");
    }
  }

  /**
   * Get a fresh, shuffled deck of cards
   * @return array
   */
  private function newDeck() {
    $suits = [
      'Hearts', 'Diamonds', 'Clubs', 'Spades',
    ];
    $nums = [
      'Ace', 'Two', 'Three', 'Four', 'Five',
      'Six', 'Seven', 'Eight', 'Nine', 'Ten',
      'Jack', 'Queen', 'King',
    ];
    $c = 1;
    while ($c <= 54) {
      if ($c >= 53) {
        $deck[] = "Joker";
      } else {
        $deck[] = sprintf("%s of %s", $nums[($c - 1) % 13], $suits[($c - 1) % 4]);
      }
      $c++;
    }
    shuffle($deck);
    return $deck;
  }

  /**
   *
   * @return array
   */
  public static function help() {
    return [
      'card' => [
        'type' => 'bare',
        'permission' => 'Ligrev/Cards/Draw',
        'help' => 'Draw a card',
      ],
      'draw' => [
        'type' => 'alias',
        'alias' => 'card'
      ],
      'shuffle' => [
        'type' => 'bare',
        'permission' => 'Ligrev/Cards/Shuffle',
        'help' => 'Shuffle this room\'s deck of cards',
      ],
    ];
  }

}
