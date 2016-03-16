<?php

/**
 * Initialize the logging mechanism
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */

namespace Ligrev;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\ErrorHandler;
use Monolog\Registry;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\GitProcessor;

$l_console = new StreamHandler(STDOUT, Logger::INFO); //@todo: configure option
$l_console->setFormatter(new \Monolog\Formatter\LineFormatter(null, null, true, true));

$l_template = new Logger("template");
$l_template->pushHandler($l_console);
$l_template->pushProcessor(new IntrospectionProcessor());
$l_template->pushProcessor(new GitProcessor());

$loggers = [ // todo: const?
  'JAXL',
  'STREAM',
  'DB',
  'COMMAND',
  'MESSAGE',
  'IQ',
  'CORE',
  'ROSTER',
  'PHP',
  'MATH',
];

foreach ($loggers as $log) {
  Registry::addLogger($l_template->withName($log));
}
ErrorHandler::register(Registry::PHP());
