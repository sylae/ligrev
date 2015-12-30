<?php

namespace Ligrev;

require_once 'includes/bootstrap.php';

// Database stuff is a bit heavy, so it's thrown in an include to keep this area tidy.
require_once 'includes/schema.php';

l("Loading JAXL and connecting...", "JAXL");
$client = new \JAXL($config['jaxl']);

$client->require_xep([
  '0045', // MUC
  '0203', // Delayed Delivery
  '0199'  // XMPP Ping
]);


$client->add_cb('on_auth_success', function() {
  global $client, $config;
  l(sprintf("Connected with jid %s", $client->full_jid->to_string()), "JAXL");
  $client->set_status("", "chat", 10);

  foreach ($config['rooms'] as $jid => $conf) {
    $c = array_merge($config, $conf);
    $room = new \XMPPJid($jid . '/' . $c['botname']);
    l(sprintf("Joining room %s", $room->to_string()), "JAXL");
    $client->xeps['0045']->join_room($room);
    l(sprintf("Joined room %s", $room->to_string()), "JAXL");
    if ($c['announceOnStart']) {
      $lv = V_LIGREV;
      ligrevGlobals::sendMessage($room->bare, sprintf(_("Ligrev version %s now online."), "[$lv](https://github.com/sylae/ligrev/commit/$lv)"));
    }
  }
  rss_init();
});

$roster = new roster();
$decks = [];

$client->add_cb('on_auth_failure', function($reason) {
  global $client;
  $client->send_end_stream();
  l(sprintf("Auth failure: %s", $reason), "JAXL", L_WARN);
});

// Where the magic happens. "Magic" "Happens". I dunno why I type this either.
$client->add_cb('on_groupchat_message', function($stanza) {
  l($stanza->to_string(), "RECV", L_DEBUG);
  new messageHandler($stanza, "groupchat");
});
$client->add_cb('on_chat_message', function($stanza) {
  l($stanza->to_string(), "RECV", L_DEBUG);
  new messageHandler($stanza, "chat");
});
$client->add_cb('on_presence_stanza', function($stanza) {
  l($stanza->to_string(), "RECV", L_DEBUG);
  global $roster;
  $roster->ingest($stanza);
});
$client->add_cb('on_result_iq', function($stanza) {
  l($stanza->to_string(), "RECV", L_DEBUG);
  new iqHandler($stanza);
});
$client->add_cb('on_get_iq', function($stanza) {
  l($stanza->to_string(), "RECV", L_DEBUG);
  new iqHandler($stanza);
});
$client->add_cb('on_error_iq', function($stanza) {
  l($stanza->to_string(), "RECV", L_DEBUG);
  new iqHandler($stanza);
});

/**
 * This is way down here to make sure our shutdown handler is the last one in
 * the stack.
 */
require_once 'includes/shutdown.php';

$client->start();
