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
 * Ligrev plugin allowing users to leave each other messages
 *
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
class Tell implements \Ligrev\iLigrevPlugin {

  public static function register(\Ligrev $ligrev) {
    $ligrev->register_command("tell", "TellCommand");

    $ligrev->register_hook("on_db_schema", "Tell::db");
    $ligrev->register_hook("on_muc_join", "Tell::join");
    $ligrev->register_hook("on_message", "Tell::message");
  }

  public static function db(\Ligrev $ligrev, \Doctrine\DBAL\Schema\Schema $schema) {
    $tables = [];
    // table tell
    $tables['tell'] = $schema->createTable("tell");
    $tables['tell']->addColumn("id", "integer", ["unsigned" => true, "autoincrement" => true]);
    $tables['tell']->addColumn("sender", "text");
    $tables['tell']->addColumn("recipient", "text");
    $tables['tell']->addColumn("sent", "integer", ["unsigned" => true]);
    $tables['tell']->addColumn("private", "boolean");
    $tables['tell']->addColumn("message", "text");
    $tables['tell']->setPrimaryKey(["id"]);

    return $schema;
  }

  public static function join(\Ligrev $ligrev, \Ligrev\PresenceHook $hook) {
    self::processTells($ligrev, $hook->from, $hook->room);
  }

  public static function message(\Ligrev $ligrev, \Ligrev\Message $message) {
    self::processTells($ligrev, $message->from, $message->room);
  }

  public function processTells(\Ligrev $ligrev, $recipient, $roomContext = null) {
    $sql = $ligrev->db->prepare('SELECT * FROM tell WHERE recipient = ? ORDER BY sent ASC', array("string"));
    $sql->bindValue(1, $recipient, "string");
    $sql->execute();
    $tells = $sql->fetchAll();
    foreach ($tells as $tell) {
      $sender = $ligrev->roster->getUser($tell['sender']);
      $time = $recipient->formatTime($tell['sent']);
      $intro = sprintf($this->t("Hello %s, %s left a message for you at %s:"), $recipient->HTML(), $sender->HTML(), $time);
      $message = $intro . PHP_EOL . $tell['message'];

      if ($tell['private']) {
        $ligrev->sendPrivateMessage($recipient, $roomContext, $message);
      } else {
        $ligrev->sendMessage($recipient, $roomContext, $message);
      }
      $ligrev->db->delete('tell', ['id' => $tell['id']]);
    }
  }

}
