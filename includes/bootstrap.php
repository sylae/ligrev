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

// initialize all the shit
require __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/logger.php';

// now load all the other basic stuff
require_once __DIR__ . '/../functions.php';

// Hey, let's load some things
require_once __DIR__ . '/../config.default.php';
require_once __DIR__ . '/../config.php';

$i18n = [];
foreach (glob(__DIR__ . "/../i18n/*.po") as $file) {
  $lang = preg_replace_callback('/i18n\\/(.+?)\\.po/',
    function ($m) {
    return $m[1];
  }, $file);
  $i18n[$lang] = \Sepia\PoParser::parseFile($file)->getEntries();
}

$iq_classes = [];
foreach (get_declared_classes() as $class) {
  $c = new \ReflectionClass($class);
  if ($c->getNameSpaceName() == "Ligrev\IQ") {
    $iq_classes[] = $class;
  }
}
