<?php

/**
 * DO NOT EDIT THIS FILE.
 *
 * Default configuration for ligrev. To make changes, copy config.sample.php to config.php
 * and make your edits there.
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
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
  'jid' => 'ligrev@example.net',
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
