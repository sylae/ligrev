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

/**
 * DO NOT EDIT THIS FILE.
 *
 * Default configuration for ligrev. To make changes, copy config.sample.php to config.php
 * and make your edits there.
 *
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
$config = [];

/**
 * Default language for Ligrev.
 * @var string
 */
$config['lang'] = 'en';

/**
 * Passed on to DBAL to connect to the database.
 * @var string
 */
$config['db'] = 'mysqli://user:password@localhost/database';

/**
 * Passed on to JAXL to configure basic XMPP stuff.
 * @var array
 */
$config['jaxl'] = [
  'jid'  => 'ligrev@example.net',
  'pass' => 'rainbowdashisactuallybestpony',
  'host' => 'example.net',
];

/**
 * The default nick to join MUC chats with.
 * @var string
 */
$config['botname'] = "Ligrev";

/**
 * A list of rooms to join. In addition, defaults may be overridden here.
 * @var array
 */
$config['rooms'] = [];

/**
 * A list of RSS feeds to crawl.
 * @var array
 */
$config['rss'] = [];

/**
 * Send a message on room join; great if you have five bots sharing a common bot name
 * @var boolean
 */
$config['announceOnStart'] = true;

/**
 * defaultTellDomain automatically provides a domain if not provided in the :tell command
 * (ie ':tell sylae message' would become ':tell sylae@example.net message')
 *
 * If false, will default to the domain of the room (minus 'conference.' if applicable)
 * @var string|boolean
 * */
$config['defaultTellDomain'] = false;

/**
 * Whether or not to disclose the system OS via XEP-0092.
 * @var boolean
 */
$config['discloseOSwithXEP0092'] = true;

/**
 * Time, in seconds, before a user is considered AFK by ligrev
 * @var integer
 */
$config['afkThreshold'] = 1800;

/**
 * List of JIDs to send log messages to
 * @var array
 */
$config['remoteLogRecipients'] = [];

/**
 * Send messages no faster than this rate (in seconds).
 * For example, "5" will send one message packet every five seconds.
 * Note that a packet can contain multiple messages.
 */
$config['remoteLogThrottle'] = 5;

/**
 * Array holding any credentials needed to access third-party APIs
 * @link https://console.developers.google.com/ key 'google'
 */
$config['api'] = [];

/**
 * Default permissions array.
 */
$config['permissions'] = [
  'sylae@calref.net'            => [// example of a user-only permission
    'sylae/ligrev/fun/sybeam' => true,
  ],
  'owner'                       => [// example of an affiliation permission
    'sylae/ligrev/restart' => true,
    'sylae/ligrev/alias'   => true,
  ],
  'admin'                       => [// example of an affiliation permission
    'sylae/ligrev/restart' => true,
    'sylae/ligrev/alias'   => true,
  ],
  'sylae/ligrev/card-draw'      => true,
  'sylae/ligrev/card-shuffle'   => true,
  'sylae/ligrev/diag'           => true,
  'sylae/ligrev/faq'            => true,
  'sylae/ligrev/faq-set'        => true,
  'sylae/ligrev/permissions'    => true,
  'sylae/ligrev/roll'           => true,
  'sylae/ligrev/slap'           => true,
  'sylae/ligrev/tell'           => true,
  'sylae/ligrev/remind'         => true,
  'sylae/ligrev/youtube'        => true,
  'sylae/ligrev/fun/chanquotes' => true,
];
