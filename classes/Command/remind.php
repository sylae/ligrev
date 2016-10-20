<?php

/**
 * Leave a note for yourself, for Ligrev to send later
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */

namespace Ligrev\Command;

class remind extends \Ligrev\command {

  public function process() {
    if (!$this->canDo("sylae/ligrev/remind")) {
      return false;
    }
    $textParts = $this->_split($this->text);
    $r = \Ligrev\return_ake(1, $textParts, null);

    $message = trim(implode(" ", array_slice($textParts, 2)));
    if (strlen($message) < 1) {
      $this->help(sprintf($this->t("Error: %s"), $this->t("No message.")));
      return;
    }

    if ($r == "login") {
      $this->addReminder(null, $message);
    } elseif ($r[0] == "@") {
      $time = $this->timeAbsolute($r);
      $this->addReminder($time, $message);
    } elseif ($this->isRelativeTime($r)) {
      $time = $this->timeRelative($r);
      $this->addReminder($time, $message);
    } else {
      $this->help(sprintf($this->t("Error: %s"), $this->t("Could not parse reminder time")));
    }
  }

  private function addReminder($time, $message) {
    if ($time == null) {
      $time = 0;
    }
    $this->_insertReminder($this->fromJID->jid->bare, $time, $message);
    $timeLabel = $this->_getLabel($time);
    $this->_send($this->getDefaultResponse(), sprintf($this->t("Reminder has been processed for %s"), $timeLabel));
  }

  private function timeRelative($r) {
    // TODO
    // /(\d+[ywdhms]?)/ig should do the trick
  }

  private function isRelativeTime($r) {
    // TODO
    // /(\d+[ywdhms]?)/ig should do the trick
  }

  private function timeAbsolute($r) {
    $str = substr($r, 1);
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
      $this->t("`\$time can be 'login' for an alert next login, an absolute date/time prefixed with @, or a relative time such as '8h30m'"),
    ];
    $this->_send($this->getDefaultResponse(), $prefix . implode("\n", $help_lines));
  }

  private function _insertReminder($recipient, $time, $message) {
    $sql = $this->db->prepare('INSERT INTO remind (recipient, due, private, message) VALUES(?, ?, ?, ?);', ['string', 'integer', 'boolean', 'string']);
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
