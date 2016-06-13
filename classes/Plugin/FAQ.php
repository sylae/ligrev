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
 * Ligrev plugin providing a deck of cards
 *
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
class FAQ implements \Ligrev\iLigrevPlugin {

  public static function register(\Ligrev $ligrev) {
    $ligrev->register_command("faq", "FAQCommand");

    $ligrev->register_hook("on_db_schema", "FAQ::db");
  }

  public static function db(\Ligrev $ligrev, \Doctrine\DBAL\Schema\Schema $schema) {
    $tables = [];
    $tables['faq'] = $schema->createTable("faq");
    $tables['faq']->addColumn("id", "integer", ["unsigned" => true, "autoincrement" => true]);
    $tables['faq']->addColumn("room", "text");
    $tables['faq']->addColumn("keyword", "text");
    $tables['faq']->addColumn("author", "text");
    $tables['faq']->addColumn("message", "text");
    $tables['faq']->setPrimaryKey(["id"]);

    return $schema;
  }

}
