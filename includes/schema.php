<?php

namespace Ligrev;

l(_("Initializing database..."), "DBAL");
$db = \Doctrine\DBAL\DriverManager::getConnection(array('url' => $config['db']), new \Doctrine\DBAL\Configuration());
$sm = $db->getSchemaManager();
$fromSchema = $sm->createSchema();

// Initialize existing schema database.
$schema = new \Doctrine\DBAL\Schema\Schema();
$tables = array();

// table faq
$tables['faq'] = $schema->createTable("faq");
$tables['faq']->addColumn("id", "integer", array("unsigned" => true, "autoincrement" => true));
$tables['faq']->addColumn("room", "text");
$tables['faq']->addColumn("keyword", "text");
$tables['faq']->addColumn("author", "text");
$tables['faq']->addColumn("message", "text");
$tables['faq']->setPrimaryKey(array("id"));

// table rss
$tables['rss'] = $schema->createTable("rss");
$tables['rss']->addColumn("url", "string", array("length" => 255));
$tables['rss']->addColumn("request", "integer", array("notnull" => false));
$tables['rss']->addColumn("latest", "integer", array("notnull" => false));
$tables['rss']->setPrimaryKey(array("url"));

// table tell
$tables['tell'] = $schema->createTable("tell");
$tables['tell']->addColumn("id", "integer", array("unsigned" => true, "autoincrement" => true));
$tables['tell']->addColumn("sender", "text");
$tables['tell']->addColumn("recipient", "text");
$tables['tell']->addColumn("sent", "integer", array("unsigned" => true));
$tables['tell']->addColumn("private", "boolean");
$tables['tell']->addColumn("message", "text");
$tables['tell']->setPrimaryKey(array("id"));

$comparator = new \Doctrine\DBAL\Schema\Comparator();
$schemaDiff = $comparator->compare($fromSchema, $schema);
$sql = $schemaDiff->toSaveSql($db->getDatabasePlatform());
$total_changes = count($sql);

if ($total_changes > 0) {
  l(sprintf(_("Schema needs initialization or upgrade, executing %s SQL statement(s) to correct..."), $total_changes), "DBAL");
  foreach ($sql as $s) {
    $db->exec($s);
  }
} else {
  l(_("Schema up to date."), "DBAL");
}
