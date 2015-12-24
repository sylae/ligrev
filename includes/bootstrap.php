<?php

/**
 * Do all of the important loading stuff here
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */

namespace Ligrev;

require_once 'functions.php';

set_error_handler("Ligrev\\php_error_handler");

// Hey, let's load some things
l("Reading config.php...");
require_once 'config.default.php';
require_once 'config.php';

l("Loading libraries...");
require 'vendor/autoload.php';

l("Loading i18n...");
$i18n = array();
foreach (glob("i18n/*.po") as $file) {
  $lang = preg_replace_callback('/i18n\\/(.+?)\\.po/', function ($m) {
    return $m[1];
  }, $file);
  $i18n[$lang] = \Sepia\PoParser::parseFile($file)->getEntries();
}

l("Loading core classes");
require_once 'classes/ligrevGlobals.php';
foreach (glob("classes/*.php") as $file) {
  require_once $file;
}

l("Registering SPL command autoloader");
spl_autoload_register(function ($class) {
  $class = str_replace("Ligrev\\Command\\", "", $class);
  if (file_exists("commands/$class.php")) {
    require_once "commands/$class.php";
  }
});

l("Scanning IQ parsers");
foreach (glob("iq/*.php") as $file) {
  require_once $file;
}
$iq_classes = array();
foreach (get_declared_classes() as $class) {
  $c = new \ReflectionClass($class);
  if ($c->getNameSpaceName() == "Ligrev\IQ") {
    $iq_classes[] = $class;
  }
}