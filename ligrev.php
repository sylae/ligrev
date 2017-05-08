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

use Monolog\Registry;

require_once 'includes/bootstrap.php';

// Database stuff is a bit heavy, so it's thrown in an include to keep this area tidy.
require_once 'includes/schema.php';

Registry::JAXL()->debug("Initializing JAXL");
$client = new \JAXL($config['jaxl']);

$client->require_xep([
  '0045', // MUC
  '0203', // Delayed Delivery
  '0199'  // XMPP Ping
]);

require_once __DIR__ . '/includes/disco_id.php';

// ejabberd (possibly other xmpp servers) doesn't tell us how long a user has been logged in
// when we login. So we inhibit processing tell messages until we get a message that isn't
// Delayed Delivery (real-time).
$_ligrevStartupInhibitTell = true;

$client->add_cb('on_auth_success',
  function() {
  global $client, $config, $disco;
  Registry::JAXL()->debug("Connected to XMPP Server",
    ['jid' => $client->full_jid->to_string()]);

  // Attach the Promises handler to the JAXL event loop, now that we are connected and ready to use it...
  \JAXLLoop::$clock->call_fun_periodic(1,
    function() {
    $queue = \GuzzleHttp\Promise\queue();
    $queue->run();
  });

  /**
   * Why not use $client->set_status()? Well, a very popular XMPP messenger
   * buggily does not send a service disco (0030) if the client it is interested
   * in doesn't support XEP-0115. So we've basically had to rewrite JAXL's
   * presence info here, to bodge in XEP-0115 saupport.
   *
   * Also, JAXL's XEP-0115 xep/ class is...lacking.
   */
  $S    = "";
  $S_id = [];
  foreach ($disco['identity'] as $id) {
    $S_id[] = "{$id[0]}/{$id[1]}/{$id[2]}/{$id[3]}<";
  }
  sort($S_id);
  foreach ($S_id as $id) {
    $S .= $id;
  }
  foreach ($disco['features'] as $feature) {
    $S .= $feature . "<";
  }

  $pres     = new \XMPPPres(['from' => $client->full_jid->to_string()], '',
    'chat', 10);
  $pres->id = $client->get_id();
  $pres->c("c", NS_CAPS,
    ['hash' => 'sha-1', 'node' => 'https://github.com/sylae/ligrev', 'ver'  => base64_encode(sha1($S,
        true))]);
  $client->send($pres);

  foreach ($config['rooms'] as $jid => $conf) {
    $c    = array_merge($config, $conf);
    $room = new \XMPPJid($jid . '/' . $c['botname']);
    Registry::JAXL()->debug("Joining room", ['room' => $room->to_string()]);
    $client->xeps['0045']->join_room($room);
    Registry::JAXL()->info("Joined room", ['room' => $room->to_string()]);
    if ($c['announceOnStart']) {
      // $lv = V_LIGREV;
      // ligrevGlobals::sendMessage($room->bare,
      //   sprintf(_("Ligrev version %s now online."),
      //     "[$lv](https://github.com/sylae/ligrev/commit/$lv)"));
      ligrevGlobals::sendMessage($room->bare,
        "Did I just crash? https://calref.net/index.php?topic=1456.msg26296#msg26296");
    }
  }
  rss_init();
  remoteLog_init();
  remind_init();
});

$roster = new roster();
$decks  = [];

$client->add_cb('on_auth_failure',
  function($reason) {
  global $client;
  $client->send_end_stream();
  Registry::JAXL()->error("Authentication failure", ['reason' => $reason]);
});

// Where the magic happens. "Magic" "Happens". I dunno why I type this either.
$client->add_cb('on_groupchat_message',
  function($stanza) {
  Registry::STREAM()->debug("Stanza received",
    ['callback' => 'on_groupchat_message', 'stanza' => $stanza->to_string()]);
  new messageHandler($stanza, "groupchat");
});
$client->add_cb('on_chat_message',
  function($stanza) {
  Registry::STREAM()->debug("Stanza received",
    ['callback' => 'on_chat_message', 'stanza' => $stanza->to_string()]);
  new messageHandler($stanza, "chat");
});
$client->add_cb('on_presence_stanza',
  function($stanza) {
  Registry::STREAM()->debug("Stanza received",
    ['callback' => 'on_presence_stanza', 'stanza' => $stanza->to_string()]);
  global $roster;
  $roster->ingest($stanza);
});
$client->add_cb('on_result_iq',
  function($stanza) {
  Registry::STREAM()->debug("Stanza received",
    ['callback' => 'on_result_iq', 'stanza' => $stanza->to_string()]);
  new iqHandler($stanza);
});
$client->add_cb('on_get_iq',
  function($stanza) {
  Registry::STREAM()->debug("Stanza received",
    ['callback' => 'on_get_iq', 'stanza' => $stanza->to_string()]);
  new iqHandler($stanza);
});
$client->add_cb('on_error_iq',
  function($stanza) {
  Registry::STREAM()->debug("Stanza received",
    ['callback' => 'on_error_iq', 'stanza' => $stanza->to_string()]);
  new iqHandler($stanza);
});

/**
 * This is way down here to make sure our shutdown handler is the last one in
 * the stack.
 */
require_once 'includes/shutdown.php';

$client->start();
