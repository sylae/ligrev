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
 * Remap tough usernames to simpler ones for commands like :tell
 */
class alias extends \Ligrev\command {

  public function process() {
    if (!$this->canDo("sylae/ligrev/alias")) {
      return false;
    }

    $textParts = $this->_split($this->text);
    $from      = \Ligrev\return_ake(1, $textParts, null);
    $to        = \Ligrev\return_ake(2, $textParts, null);

    if (strlen($from) == 0 || strlen($to) == 0) {
      $this->help(sprintf($this->t("Error: %s"), $this->t("Must provide names.")));
      return;
    }

    $fromR = $this->_appendPrefix($from);
    $toR   = $this->_appendPrefix($to);
    $fromJ = new \Ligrev\xmppEntity(new \XMPPJid($fromR));
    $toJ   = new \Ligrev\xmppEntity(new \XMPPJid($toR));

    $exists = $this->_checkExists($fromR);
    if (is_array($exists)) {
      $this->_send($this->getDefaultResponse(),
        sprintf($this->t("Error: %s"),
          sprintf($this->t("%s already mapped to: %s"), $exists['fromName'],
            $exists['toName'])));
      return;
    }

    $this->_insertAlias($fromR, $toR);
    $this->_send($this->getDefaultResponse(),
      sprintf($this->t("Now mapping %s to %s."), $fromJ->generateHTML(),
        $toJ->generateHTML()));
  }

  public function help($prefix = null) {
    if (is_string($prefix)) {
      $prefix = $prefix . "\n";
    }
    $help_lines = [
      $this->t("Usage help for Ligrev command :alias:"),
      $this->t("`:alias \$original \$replacement` - Create a JID remap in ligrev."),
      $this->t("Arguments that take a username will map \$original to \$replacement."),
    ];
    $this->_send($this->getDefaultResponse(),
      $prefix . implode("\n", $help_lines));
  }

  private function _appendPrefix($recipient) {
    if (!preg_match("/@+/", $recipient)) {
      // If there's no domain, assume it's for the default
      if ($this->config['defaultTellDomain'] === false) {
        $domain = str_replace("conference.", "", $this->from->domain);
      } else {
        $domain = $this->config['defaultTellDomain'];
      }
      $recipient = $recipient . "@" . $domain;
    }
    return $recipient;
  }

  private function _insertAlias($from, $to) {
    $sql = $this->db->prepare('INSERT INTO user_alias (fromName, toName, author, submitTime) VALUES(?, ?, ?, ?);',
      ['string', 'string', 'string', 'integer']);
    $sql->bindValue(1, $from, "string");
    $sql->bindValue(2, $to, "string");
    $sql->bindValue(3, $this->fromJID->jid->bare, "string");
    $sql->bindValue(4, time(), "integer");
    $sql->execute();
  }

  private function _checkExists($from) {
    $sql   = $this->db->prepare('SELECT * FROM user_alias WHERE fromName = ?',
      ["string"]);
    $sql->bindValue(1, str_replace("\\20", " ", $from), "string");
    $sql->execute();
    $tells = $sql->fetchAll();
    foreach ($tells as $tell) {
      return $tell;
    }
    return false;
  }

}
