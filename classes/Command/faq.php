<?php

/**
 * Set and get frequently-asked questions
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */

namespace Ligrev\Command;

class faq extends \Ligrev\command {

  function process() {
    $textParts = $this->_split($this->text);
    $set = (array_key_exists(1, $textParts) ? $textParts[1] : false);

    if ($set == "set" || $set == $this->t("set")) {
      $sender = $this->fromJID->getData("affiliation");
      if (!($sender == "admin" || $sender == "owner")) {
        $this->_send($this->getDefaultResponse(), sprintf($this->t("Error: %s"), $this->t("Insufficient permissions")));
        return;
      }
      $code = (array_key_exists(2, $textParts) ? $textParts[2] : false);
      $message = trim(implode(" ", array_slice($textParts, 3)));
      if (!$code) {
        $this->_send($this->getDefaultResponse(), sprintf($this->t("Error: %s"), $this->t("No keyword for FAQ.\nUsage `:faq set \$keyword \$message`")));
        return;
      } elseif (strlen($message) < 1) {
        $this->_send($this->getDefaultResponse(), sprintf($this->t("Error: %s"), $this->t("No FAQ Body.\nUsage: `:faq set \$keyword \$message`")));
        return;
      }
      // check if the key already exists, if so, don't do anything.
      $sql = $this->db->prepare('SELECT * FROM faq WHERE room = ? AND keyword = ?', ["string", "string"]);
      $sql->bindValue(1, $this->room, "string");
      $sql->bindValue(2, $code, "string");
      $sql->execute();
      $faqs = $sql->fetchAll();
      foreach ($faqs as $a) {
        $this->_send($this->getDefaultResponse(), sprintf($this->t("FAQ %s already in use"), $code));
        return;
      }

      $sql = $this->db->prepare('INSERT INTO faq (author, room, message, keyword) VALUES(?, ?, ?, ?);', ['string', 'string', 'string', 'string']);
      $sql->bindValue(1, $this->fromJID->jid->bare, "string");
      $sql->bindValue(2, $this->room, "string");
      $sql->bindValue(3, $message, "string");
      $sql->bindValue(4, $code, "string");
      $sql->execute();
      $this->_send($this->getDefaultResponse(), sprintf($this->t("FAQ with keyword %s added."), $code));
      return;
    } else {
      $sql = $this->db->prepare('SELECT * FROM faq WHERE room = ? AND keyword = ?', ["string", "string"]);
      $sql->bindValue(1, $this->room, "string");
      $sql->bindValue(2, $set, "string");
      $sql->execute();
      $faqs = $sql->fetchAll();
      foreach ($faqs as $a) {
        $author = new \Ligrev\xmppEntity(new \XMPPJid($a['author']));

        $this->_send($this->getDefaultResponse(), sprintf($this->t("FAQ authored by %s: %s"), $author->generateHTML(), $a['message']));
        return;
      }
      $this->_send($this->getDefaultResponse(), sprintf($this->t("FAQ %s not found."), $set));
      return;
    }
  }

}
