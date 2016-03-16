<?php

/**
 * Miscellaneous functions and consts not in a class
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */

namespace Ligrev {

// Check if Ligrev has been modified locally.
  exec("git diff --quiet HEAD", $null, $rv);
  define("V_LIGREV", trim(`git rev-parse HEAD`) . ($rv == 1 ? "-modified" : ""));

  function rss_init() {
    global $config;
    $rss = $config['rss'];
    $feeds = [];
    foreach ($rss as $feed) {
      $feeds[] = new RSS($feed['url'], $feed['rooms'], $feed['ttl']);
    }
  }

  function userTime($epoch, $tzo = "+00:00", $locale = null, $html = true) {

    // first, parse our tzo into seconds
    preg_match_all('/([+-]?)(\\d{2}):(\\d{2})/', $tzo, $matches);
    if (array_key_exists(0, $matches[1])) {
      $sign = $matches[1][0];
      $h = $matches[2][0] * 3600;
      $m = $matches[3][0] * 60;
      $offset = ($sign == "-") ? -1 * $h + $m : $h + $m;
      ;
    } else {
      $offset = 0;
    }
    $intl_soon = new \IntlDateFormatter($locale, \IntlDateFormatter::NONE, \IntlDateFormatter::LONG, timezone_name_from_abbr("", $offset, false));
    $intl_past = new \IntlDateFormatter($locale, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::LONG, timezone_name_from_abbr("", $offset, false));
    $date = new \DateTime(date('c', $epoch));
    $date->setTimezone(new \DateTimezone(timezone_name_from_abbr("", $offset, false)));
    $time = ($epoch > time() - (60 * 60 * 24)) ? $intl_soon->format($date) : $intl_past->format($date);
    $xmpptime = date(DATE_ATOM, $epoch);
    if ($html) {
      return "<span data-timestamp=\"$xmpptime\">$time</span>";
    } else {
      return $time;
    }
  }

  function t($string, $lang = null) {
    global $i18n, $config;

    if (is_null($lang)) {
      $lang = $config['lang'];
    }

    $opts = [];
    foreach ($i18n as $ilang => $strings) {
      if (array_key_exists($string, $strings) && strlen($strings[$string]['msgstr'][0]) > 0) {
        $opts[$ilang] = $strings[$string]['msgstr'][0];
      }
    }

    // the "en" lang file won't show up, because it's empty, but that's okay.
    $opts['en'] = $string;

    $best = \Locale::lookup(array_keys($opts), $lang, true, "en");
    if (count($opts) == 0) {
      return $string;
    }
    return $opts[$best];
  }

}