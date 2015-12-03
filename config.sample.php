<?php
$config = array();

// DSN sent to DBAL
$config['db'] = 'mysqli://user:password@localhost/database';

// Bot credentials
$config['jaxl'] = array(
  'jid' => 'ligrev@example.net',
  'pass' => 'rainbowdashisactuallybestpony',
  'host' => 'example.net',
);
$config['botname'] = "Ligrev";

// List of rooms to join
$config['rooms'] = array(
  'lounge' => 'lounge@conference.example.net',
  'test'   => 'test@conference.example.org',
);

// :tell settings
// defaultTellDomain automatically provides a domain if not provided in the :tell command
// (ie ':tell sylae message' would become ':tell sylae@example.net message')
$config['defaultTellDomain'] = 'example.net';
$config['tellCaseSensitive'] = true;

