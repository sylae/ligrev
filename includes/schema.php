<?php

/*
 * Copyright (C) 2016 Keira Sylae Aro <sylae@calref.net>
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

namespace Ligrev;

$db_config  = new \Doctrine\DBAL\Configuration();
$db         = \Doctrine\DBAL\DriverManager::getConnection(['url' => $config['db']],
    $db_config);
$sm         = $db->getSchemaManager();
$fromSchema = $sm->createSchema();

// Initialize existing schema database.
$schema = new \Doctrine\DBAL\Schema\Schema();
$tables = [];

// table faq
$tables['faq'] = $schema->createTable("faq");
$tables['faq']->addColumn("id", "integer",
  ["unsigned" => true, "autoincrement" => true]);
$tables['faq']->addColumn("room", "text");
$tables['faq']->addColumn("keyword", "text");
$tables['faq']->addColumn("author", "text");
$tables['faq']->addColumn("message", "text");
$tables['faq']->setPrimaryKey(["id"]);

// table rss
$tables['rss'] = $schema->createTable("rss");
$tables['rss']->addColumn("url", "string", ["length" => 255]);
$tables['rss']->addColumn("request", "integer", ["notnull" => false]);
$tables['rss']->addColumn("latest", "integer", ["notnull" => false]);
$tables['rss']->setPrimaryKey(["url"]);

// table remind
$tables['remind'] = $schema->createTable("remind");
$tables['remind']->addColumn("id", "integer",
  ["unsigned" => true, "autoincrement" => true]);
$tables['remind']->addColumn("recipient", "text");
$tables['remind']->addColumn("due", "integer", ["unsigned" => true]);
$tables['remind']->addColumn("private", "boolean");
$tables['remind']->addColumn("message", "text");
$tables['remind']->setPrimaryKey(["id"]);

// table tell
$tables['tell'] = $schema->createTable("tell");
$tables['tell']->addColumn("id", "integer",
  ["unsigned" => true, "autoincrement" => true]);
$tables['tell']->addColumn("sender", "text");
$tables['tell']->addColumn("recipient", "text");
$tables['tell']->addColumn("sent", "integer", ["unsigned" => true]);
$tables['tell']->addColumn("private", "boolean");
$tables['tell']->addColumn("message", "text");
$tables['tell']->setPrimaryKey(["id"]);

// table tell_block
$tables['tell'] = $schema->createTable("tell_block");
$tables['tell']->addColumn("sender", "text");
$tables['tell']->addColumn("recipient", "text");

$comparator    = new \Doctrine\DBAL\Schema\Comparator();
$schemaDiff    = $comparator->compare($fromSchema, $schema);
$sql           = $schemaDiff->toSaveSql($db->getDatabasePlatform());
$total_changes = count($sql);

if ($total_changes > 0) {
  \Monolog\Registry::DB()->info("Schema needs initialization or upgrade",
    ["statements_to_execute" => $total_changes]);
  foreach ($sql as $s) {
    $db->exec($s);
  }
} else {
  \Monolog\Registry::DB()->info("Schema up to date",
    ["statements_to_execute" => $total_changes]);
}
