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

$disco = [];

$disco['identity']   = [];
$disco['identity'][] = ['client', 'bot', 'en', 'Ligrev'];

// This will get turned into 'features' later
$disco['f']   = [];
$disco['f'][] = 'dnsserv';
$disco['f'][] = 'fullunicode';
$disco['f'][] = NS_CAPS;
$disco['f'][] = NS_MUC;
$disco['f'][] = NS_MUC . "#user";
$disco['f'][] = 'http://jabber.org/protocol/xhtml-im';
$disco['f'][] = NS_COMPRESSION_FEATURE;
$disco['f'][] = NS_XMPP_PING;

foreach ($iq_classes as $iq) {
  $new = $iq::disco();
  foreach ($new as $ns) {
    $disco['f'][] = $ns;
  }
}

/**
 * As disco[f] can take outside input from user supplied classes, lets remove
 * the duplicates for the final array
 */
$disco['features'] = [];
foreach ($disco['f'] as $f) {
  if (!array_key_exists($f, $disco['features'])) {
    $disco['features'][] = $f;
  }
}
sort($disco['features']);
unset($disco['f']);
