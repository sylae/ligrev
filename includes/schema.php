<?php

namespace Ligrev;

// table rss
$tables['rss'] = $schema->createTable("rss");
$tables['rss']->addColumn("url", "string", ["length" => 255]);
$tables['rss']->addColumn("request", "integer", ["notnull" => false]);
$tables['rss']->addColumn("latest", "integer", ["notnull" => false]);
$tables['rss']->setPrimaryKey(["url"]);
