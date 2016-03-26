<?php

/**
 * Do all of the important loading stuff here
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
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
  $lang = preg_replace_callback('/i18n\\/(.+?)\\.po/', function ($m) {
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
