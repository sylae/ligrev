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

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\ErrorHandler;
use Monolog\Registry;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\GitProcessor;

$l_console = new StreamHandler(STDOUT, Logger::INFO); //@todo: configure option
$l_console->setFormatter(new stdOutFormatter(null, null, true, true));

$_xmppLogHandler_messageQueue = [];
$l_remote                     = new xmppLogHandler(Logger::DEBUG); //@todo: configure option
$l_remote->setFormatter(new \Monolog\Formatter\LineFormatter(null, null, true,
  true));

$l_template = new Logger("template");
$l_template->pushHandler($l_console);
$l_template->pushHandler($l_remote);
$l_template->pushProcessor(new IntrospectionProcessor());
$l_template->pushProcessor(new GitProcessor());
$loggers    = [// todo: const?
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

// Let's not resend everything back that the server sends to us.
Registry::STREAM()->popHandler($l_remote);
