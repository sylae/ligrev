<?php

namespace Ligrev;

require_once 'functions.php';

set_error_handler("Ligrev\\php_error_handler");
set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . DIRECTORY_SEPARATOR . 'lib');

// Hey, let's load some things
l(_("Reading config.php..."));
require_once 'config.default.php';
require_once 'config.php';

l(_("Loading libraries..."));
require __DIR__ . '/vendor/autoload.php';
require_once 'JAXL/jaxl.php';

l(_("Loading core classes"));
foreach (glob("classes/*.php") as $file) {
  require_once $file;
}

l(_("Registering SPL command autoloader"));
spl_autoload_register(function ($class) {
  $class = str_replace("Ligrev\\Command\\", "", $class);
  if (file_exists("commands/$class.php")) {
    require_once "commands/$class.php";
  }
});

// Database stuff is a bit heavy, so it's thrown in an include to keep this area tidy.
require_once 'includes/schema.php';

l(_("Loading JAXL and connecting..."), "JAXL");
$client = new \JAXL($config['jaxl']);

$client->require_xep(array(
  '0045', // MUC
  '0203', // Delayed Delivery
  '0199'  // XMPP Ping
));


$client->add_cb('on_auth_success', function() {
  global $client, $config;
  l(sprintf(_("Connected with jid %s"), $client->full_jid->to_string()), "JAXL");
  $client->set_status("", "chat", 10);

  foreach ($config['rooms'] as $jid => $conf) {
    $c = array_merge($config, $conf);
    $room = new \XMPPJid($jid . '/' . $c['botname']);
    l(sprintf(_("Joining room %s"), $room->to_string()), "JAXL");
    $client->xeps['0045']->join_room($room);
    l(sprintf(_("Joined room %s"), $room->to_string()), "JAXL");
    if ($c['announceOnStart']) {
      $lv = V_LIGREV;
      _send($room->bare, sprintf(_("Ligrev version %s now online."), "[$lv](https://github.com/sylae/ligrev/commit/$lv)"));
    }
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
  new messageHandler($stanza, "groupchat");
});
$client->add_cb('on_chat_message', function($stanza) {
  new messageHandler($stanza, "chat");
});
$client->add_cb('on_presence_stanza', function($stanza) {
  global $roster;
  $roster->ingest($stanza);
});

/**
 * This is way down here to make sure our shutdown handler is the last one in
 * the stack.
 */
require_once 'includes/shutdown.php';

$client->start();
