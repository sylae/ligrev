<?php

namespace Ligrev;

use Monolog\Registry;

require_once 'includes/bootstrap.php';

// TODO: Register hook on_plugin_load
// Also load plugins here

Registry::JAXL()->debug("Initializing JAXL");
$ligrev = new Ligrev($config);

require_once __DIR__ . '/includes/disco_id.php';

// ejabberd (possibly other xmpp servers) doesn't tell us how long a user has been logged in
// when we login. So we inhibit processing tell messages until we get a message that isn't
// Delayed Delivery (real-time).
$_ligrevStartupInhibitTell = true;

$client->add_cb('on_auth_success', function() {
  global $client, $config, $disco;
  Registry::JAXL()->debug("Connected to XMPP Server", ['jid' => $client->full_jid->to_string()]);

  foreach ($config['rooms'] as $jid => $conf) {
    $c = array_merge($config, $conf);
    $room = new \XMPPJid($jid . '/' . $c['botname']);
    Registry::JAXL()->debug("Joining room", ['room' => $room->to_string()]);
    $client->xeps['0045']->join_room($room);
    Registry::JAXL()->info("Joined room", ['room' => $room->to_string()]);
    if ($c['announceOnStart']) {
      $lv = V_LIGREV;
      ligrevGlobals::sendMessage($room->bare, sprintf(_("Ligrev version %s now online."), "[$lv](https://github.com/sylae/ligrev/commit/$lv)"));
    }
  }
  rss_init();
  remoteLog_init();
});

$roster = new roster();
$decks = [];

$client->add_cb('on_auth_failure', function($reason) {
  global $client;
  $client->send_end_stream();
  Registry::JAXL()->error("Authentication failure", ['reason' => $reason]);
});

// Where the magic happens. "Magic" "Happens". I dunno why I type this either.
$client->add_cb('on_groupchat_message', function($stanza) {
  Registry::STREAM()->debug("Stanza received", ['callback' => 'on_groupchat_message', 'stanza' => $stanza->to_string()]);
  new messageHandler($stanza, "groupchat");
});
$client->add_cb('on_chat_message', function($stanza) {
  Registry::STREAM()->debug("Stanza received", ['callback' => 'on_chat_message', 'stanza' => $stanza->to_string()]);
  new messageHandler($stanza, "chat");
});
$client->add_cb('on_presence_stanza', function($stanza) {
  Registry::STREAM()->debug("Stanza received", ['callback' => 'on_presence_stanza', 'stanza' => $stanza->to_string()]);
  global $roster;
  $roster->ingest($stanza);
});
$client->add_cb('on_result_iq', function($stanza) {
  Registry::STREAM()->debug("Stanza received", ['callback' => 'on_result_iq', 'stanza' => $stanza->to_string()]);
  new iqHandler($stanza);
});
$client->add_cb('on_get_iq', function($stanza) {
  Registry::STREAM()->debug("Stanza received", ['callback' => 'on_get_iq', 'stanza' => $stanza->to_string()]);
  new iqHandler($stanza);
});
$client->add_cb('on_error_iq', function($stanza) {
  Registry::STREAM()->debug("Stanza received", ['callback' => 'on_error_iq', 'stanza' => $stanza->to_string()]);
  new iqHandler($stanza);
});

/**
 * This is way down here to make sure our shutdown handler is the last one in
 * the stack.
 */
require_once 'includes/shutdown.php';

$client->start();
