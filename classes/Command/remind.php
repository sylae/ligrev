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

namespace Ligrev\Command;

/**
 * Leave a note for yourself, for Ligrev to send later
 */
class remind extends \Ligrev\command {

// this is stupid
  const MINUTE = 60;
  const HOUR   = 3600;
  const DAY    = 86400;
  const WEEK   = 604800;
  const YEAR   = 31536000;

  public function process() {
    if (!$this->canDo("sylae/ligrev/remind")) {
      return false;
    }
    $textParts = $this->_split($this->text);
    $r         = \Ligrev\return_ake(1, $textParts, null);

    $message = trim(implode(" ", array_slice($textParts, 2)));
    if (strlen($message) < 1) {
      $this->help(sprintf($this->t("Error: %s"), $this->t("No message.")));
      return;
    }

    if ($r == "login") {
      $this->addReminder(null, $message);
    } elseif ($this->isRelativeTime($r)) {
      $time = $this->timeRelative($r);
      $this->addReminder($time, $message);
    } else {
      $time = $this->timeAbsolute($r);
      if ($time) {
        $this->addReminder($time, $message);
      } else {
        $this->help(sprintf($this->t("Error: %s"),
            $this->t("Could not parse reminder time")));
      }
    }
  }

  private function addReminder($time, $message) {
    if ($time == null) {
      $time = 0;
    }
    if ($time != 0 && $time < time()) {
      $this->help(sprintf($this->t("Error: %s"),
          $this->t("Hindsight is 20/20, but this doesn't work like that.")));
      return;
    }
    $this->_insertReminder($this->fromJID->jid->bare, $time, $message);
    $timeLabel = $this->_getLabel($time);
    $this->_send($this->getDefaultResponse(),
      sprintf($this->t("Reminder has been processed for %s"), $timeLabel));
  }

  private function isRelativeTime($r) {
    $matches  = [];
    $nmatches = 0;
    if (preg_match_all("/((\\d+)([ywdhm]))/i", $r, $matches, PREG_SET_ORDER)) {
      foreach ($matches as $match) {
        $nmatches++;
      }
    }
    $m = preg_replace("/((\\d+)([ywdhm]))/i", "", $r);
    return ($nmatches > 0 && mb_strlen(trim($m)) == 0);
  }

  private function TimeRelative($r) {
    $matches = [];
    if (preg_match_all("/((\\d+)([ywdhm]))/i", $r, $matches, PREG_SET_ORDER)) {
      $time = 0;
      foreach ($matches as $m) {
        $num = $m[2] ?? 1;
        $typ = mb_strtolower($m[3] ?? "m");
        switch ($typ) {
          case "y":
            $time += $num * self::YEAR;
            break;
          case "w":
            $time += $num * self::WEEK;
            break;
          case "d":
            $time += $num * self::DAY;
            break;
          case "h":
            $time += $num * self::HOUR;
            break;
          case "m":
            $time += $num * self::MINUTE;
            break;
        }
      }
      return $time + time();
    }
  }

  private function timeAbsolute($r) {
    $str = $r;
    $tzo = $this->fromJID->getData('tzo');
    if (is_string($tzo)) {
      return \Ligrev\userTimeReverse($str, $tzo);
    } else {
      return \Ligrev\userTimeReverse($str);
    }
  }

  public function help($prefix = null) {
    if (is_string($prefix)) {
      $prefix = $prefix . "\n";
    }
    $help_lines = [
      $this->t("Usage help for Ligrev command :remind:"),
      $this->t("`:remind \$time \$message` - To send a reminder."),
      $this->t("`\$time` can be `login` for an alert next login, or a [time](http://php.net/manual/en/datetime.formats.php) such as `12/08/16`, `tomorrow`, `next\ Tuesday`, or `8h30m`"),
    ];
    $this->_send($this->getDefaultResponse(),
      $prefix . implode("\n", $help_lines));
  }

  private function _insertReminder($recipient, $time, $message) {
    $sql = $this->db->prepare('INSERT INTO remind (recipient, due, private, message) VALUES(?, ?, ?, ?);',
      ['string', 'integer', 'boolean', 'string']);
    $sql->bindValue(1, $recipient, "string");
    $sql->bindValue(2, $time, "integer");
    $sql->bindValue(3, ($this->origin == "groupchat" ? false : true), "boolean");
    $sql->bindValue(4, $message, "string");
    $sql->execute();
  }

  private function _getLabel($time) {
    if ($time == 0) {
      return $this->t("next login");
    } else {
      return $this->fromJID->formatUserTime($time);
    }
  }

}
