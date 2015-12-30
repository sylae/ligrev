<?php

/**
 * Populate our service discovery information
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */

namespace Ligrev;

$disco = [];

$disco['identity'] = [];
$disco['identity'][] = ['client', 'bot', 'en', 'Ligrev'];

// This will get turned into 'features' later
$disco['f'] = [];
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
