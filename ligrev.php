<?php

namespace Ligrev;

require_once 'functions.php';

set_error_handler("Ligrev\\php_error_handler");
set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . DIRECTORY_SEPARATOR . 'lib');

// Hey, let's load some things
l(_("Reading config.php..."));
require_once 'config.php';

l(_("Loading libraries..."));
require __DIR__ . '/vendor/autoload.php';
require_once 'JAXL/jaxl.php';

require_once 'classes/ligrevCommand.php';
require_once 'classes/bc.php';
require_once 'classes/dice.php';
require_once 'classes/command.php';
require_once 'classes/roster.php';
require_once 'classes/RSS.php';

require_once 'commands/roll.php';
require_once 'commands/slap.php';
require_once 'commands/diag.php';
require_once 'commands/card.php';
require_once 'commands/shuffle.php';
require_once 'commands/sybeam.php';
require_once 'commands/tell.php';

l(_("Initializing database..."), "DBAL");
$db = \Doctrine\DBAL\DriverManager::getConnection(array('url' => $config['db']), new \Doctrine\DBAL\Configuration());

// TODO: Schema validation/installation/update

l(_("Loading JAXL and connecting..."), "JAXL");
$client = new \JAXL($config['jaxl']);

$client->require_xep(array(
  '0045', // MUC
  '0203', // Delayed Delivery
  '0199'  // XMPP Ping
));

$rooms = array();

$client->add_cb('on_auth_success', function() {
  global $client, $config, $rooms;
  l(sprintf(_("Connected with jid %s"), $client->full_jid->to_string()), "JAXL");
  $client->get_vcard();
  $client->get_roster();
  $client->set_status("", "chat", 10);

  foreach ($config['rooms'] as $id => $jid) {
    $rooms[$id] = new \XMPPJid($jid . '/' . $config['botname']);
    l(sprintf(_("Joining room %s"), $rooms[$id]->to_string()), "JAXL");
    $client->xeps['0045']->join_room($rooms[$id]);
    l(sprintf(_("Joined room %s"),$rooms[$id]->to_string()), "JAXL");
  }
  rss_init();
});

$roster = new roster();
$decks = array();

$client->add_cb('on_auth_failure', function($reason) {
  global $client;
  $client->send_end_stream();
  l(sprintf(_("Auth failure: %s"), $reason), "JAXL", L_WARN);
});

// Where the magic happens. "Magic" "Happens". I dunno why I type this either.
$client->add_cb('on_groupchat_message', function($stanza) {
  new ligrevCommand($stanza, "groupchat");
});
$client->add_cb('on_chat_message', function($stanza) {
  new ligrevCommand($stanza, "chat");
});
$client->add_cb('on_presence_stanza', function($stanza) {
  global $roster;
  $roster->ingest($stanza);
});

$client->start();
