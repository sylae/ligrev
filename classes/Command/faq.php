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
 * Set and get frequently-asked questions
 */
class faq extends \Ligrev\command {

  function process() {
    $textParts = $this->_split($this->text);
    $set       = (array_key_exists(1, $textParts) ? $textParts[1] : false);

    if ($this->canDo("sylae/ligrev/faq-set") && ($set == "set" || $set == $this->t("set"))) {
      $sender = $this->fromJID->getData("affiliation");
      if (!($sender == "admin" || $sender == "owner")) {
        $this->_send($this->getDefaultResponse(),
          sprintf($this->t("Error: %s"), $this->t("Insufficient permissions")));
        return;
      }
      $code    = (array_key_exists(2, $textParts) ? $textParts[2] : false);
      $message = trim(implode(" ", array_slice($textParts, 3)));
      if (!$code) {
        $this->_send($this->getDefaultResponse(),
          sprintf($this->t("Error: %s"),
            $this->t("No keyword for FAQ.\nUsage `:faq set \$keyword \$message`")));
        return;
      } elseif (strlen($message) < 1) {
        $this->_send($this->getDefaultResponse(),
          sprintf($this->t("Error: %s"),
            $this->t("No FAQ Body.\nUsage: `:faq set \$keyword \$message`")));
        return;
      }
      // check if the key already exists, if so, don't do anything.
      $sql  = $this->db->prepare('SELECT * FROM faq WHERE room = ? AND keyword = ?',
        ["string", "string"]);
      $sql->bindValue(1, $this->room, "string");
      $sql->bindValue(2, $code, "string");
      $sql->execute();
      $faqs = $sql->fetchAll();
      foreach ($faqs as $a) {
        $this->_send($this->getDefaultResponse(),
          sprintf($this->t("FAQ %s already in use"), $code));
        return;
      }

      $sql = $this->db->prepare('INSERT INTO faq (author, room, message, keyword) VALUES(?, ?, ?, ?);',
        ['string', 'string', 'string', 'string']);
      $sql->bindValue(1, $this->fromJID->jid->bare, "string");
      $sql->bindValue(2, $this->room, "string");
      $sql->bindValue(3, $message, "string");
      $sql->bindValue(4, $code, "string");
      $sql->execute();
      $this->_send($this->getDefaultResponse(),
        sprintf($this->t("FAQ with keyword %s added."), $code));
      return;
    } elseif ($this->canDo("sylae/ligrev/faq")) {
      $sql  = $this->db->prepare('SELECT * FROM faq WHERE room = ? AND keyword = ?',
        ["string", "string"]);
      $sql->bindValue(1, $this->room, "string");
      $sql->bindValue(2, $set, "string");
      $sql->execute();
      $faqs = $sql->fetchAll();
      foreach ($faqs as $a) {
        $author = new \Ligrev\xmppEntity(new \XMPPJid($a['author']));

        $this->_send($this->getDefaultResponse(),
          sprintf($this->t("FAQ authored by %s: %s"), $author->generateHTML(),
            $a['message']));
        return;
      }
      $this->_send($this->getDefaultResponse(),
        sprintf($this->t("FAQ %s not found."), $set));
      return;
    }
  }

}
