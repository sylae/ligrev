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

  define("NS_EVENT_LOGGING", "urn:xmpp:eventlog");

  function rss_init() {
    global $config;
    $rss = $config['rss'];
    $feeds = [];
    foreach ($rss as $feed) {
      $feeds[] = new RSS($feed['url'], $feed['rooms'], $feed['ttl']);
    }
  }

  function remoteLog_init() {
    global $config;
    \JAXLLoop::$clock->call_fun_periodic($config['remoteLogThrottle'] * 1000000, function () {
      global $_xmppLogHandler_messageQueue, $client, $config;

      if (count($_xmppLogHandler_messageQueue) < 1) {
        return;
      }

      foreach ($config['remoteLogRecipients'] as $recipient) {
        $msg = new \XMPPMsg(
          array(
          'type' => "chat",
          'to' => $recipient,
          'from' => $client->full_jid->to_string(),
          )
        );
        foreach ($_xmppLogHandler_messageQueue as $item) {
          switch ($item['level']) {
            case \Monolog\Logger::DEBUG:
              $type = "Debug";
              break;
            case \Monolog\Logger::INFO:
              $type = "Informational";
              break;
            case \Monolog\Logger::NOTICE:
              $type = "Notice";
              break;
            case \Monolog\Logger::WARNING:
              $type = "Warning";
              break;
            case \Monolog\Logger::ERROR:
              $type = "Error";
              break;
            case \Monolog\Logger::CRITICAL:
              $type = "Critical";
              break;
            case \Monolog\Logger::ALERT:
              $type = "Alert";
              break;
            case \Monolog\Logger::EMERGENCY:
              $type = "Emergency";
              break;
          }
          $msg->c('log', NS_EVENT_LOGGING, [
            'timestamp' => $xmpptime = $item['datetime']->format(\DateTime::ATOM),
            "type" => $type,
            "module" => $item['channel'],
          ]);
          $msg->c("message", null, [], $item['message'])->up();
          foreach ($item['context'] as $tag => $value) {
            switch (gettype($value)) {
              case "boolean":
                $type = "xs:boolean";
                break;
              case "integer":
                $type = "xs:integer";
                break;
              case "double":
                $type = "xs:double";
                break;
              case "string":
                $type = "xs:string";
                break;
              default:
                $type = NULL;
                break;
            }
            $attrs = [
              'name' => $tag,
              'value' => $value,
            ];
            if (!is_null($type)) {
              $attrs['type'] = $type;
            }

            $msg->c("tag", null, $attrs)->up();
          }
          $msg->c("tag", null, [
            'name' => "git",
            'value' => $item['extra']['git']['commit'],
          ])->up();
          $msg->c("tag", null, [
            'name' => "file",
            'value' => $item['extra']['file'],
          ])->up();
          $msg->c("tag", null, [
            'name' => "line",
            'value' => $item['extra']['line'],
          ])->up();
          $msg->c("tag", null, [
            'name' => "class",
            'value' => $item['extra']['class'],
          ])->up();
          $msg->c("tag", null, [
            'name' => "function",
            'value' => $item['extra']['function'],
          ])->up();
          $msg->up();
        }
        $_xmppLogHandler_messageQueue = [];
        $client->send($msg);
      }
    });
  }

  function userTime($epoch, $tzo = "+00:00", $locale = null, $html = true) {

    // first, parse our tzo into seconds
    preg_match_all('/([+-]?)(\\d{2}):(\\d{2})/', $tzo, $matches);
    if (array_key_exists(0, $matches[1])) {
      $sign = $matches[1][0];
      $h = $matches[2][0] * 3600;
      $m = $matches[3][0] * 60;
      $offset = ($sign == "-") ? -1 * $h + $m : $h + $m;
    } else {
      $offset = 0;
    }

    // good god what have i done with my life.
    $str = "Etc/GMT" . sprintf("%+d", ($offset / 60 / 60) * -1);
    $tz = new \DateTimeZone($str);

    $intl_soon = new \IntlDateFormatter($locale, \IntlDateFormatter::NONE, \IntlDateFormatter::LONG, $tz);
    $intl_past = new \IntlDateFormatter($locale, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::LONG, $tz);
    $date = new \DateTime(date('c', $epoch));
    $date->setTimezone($tz);
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

  /**
   * Shorthand for array_key_exists spam
   * @param string $key
   * @param array $array
   * @param mixed $not_exists
   * @return mixed If key exists, the value of the key, otherwise $not_exists
   */
  function return_ake($key, $array, $not_exists = false) {
    return array_key_exists($key, $array) ? $array[$key] : $not_exists;
  }

}