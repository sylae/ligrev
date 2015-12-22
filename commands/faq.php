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

    if ($set == "set" || $set == _("set")) {
      $sender = $this->roster->roster[$this->room][$this->author]["affiliation"];
      if (!($sender == "admin" || $sender == "owner")) {
        $this->_send($this->from, sprintf(_("Error: %s"), _("Insufficient permissions")));
        return;
      }
      $code = (array_key_exists(2, $textParts) ? $textParts[2] : false);
      $message = trim(implode(" ", array_slice($textParts, 3)));
      if (!$code) {
        $this->_send($this->from, sprintf(_("Error: %s"), _("No keyword for FAQ.\nUsage `:faq set \$keyword \$message`")));
        return;
      } elseif (strlen($message) < 1) {
        $this->_send($this->from, sprintf(_("Error: %s"), _("No FAQ Body.\nUsage: `:faq set \$keyword \$message`")));
        return;
      }
      // check if the key already exists, if so, don't do anything.
      $sql = $this->db->prepare('SELECT * FROM faq WHERE room = ? AND keyword = ?', array("string", "string"));
      $sql->bindValue(1, $this->room, "string");
      $sql->bindValue(2, $code, "string");
      $sql->execute();
      $faqs = $sql->fetchAll();
      foreach ($faqs as $a) {
        // $author = $this->$roster->generateHTML()
        $this->_send($this->from, sprintf(_("FAQ %s already in use"), $code));
        return;
      }

      $sql = $this->db->prepare('INSERT INTO faq (author, room, message, keyword) VALUES(?, ?, ?, ?);', array('string', 'string', 'string', 'string'));
      $sql->bindValue(1, $this->roster->nickToJID($this->room, $this->author)->bare, "string");
      $sql->bindValue(2, $this->room, "string");
      $sql->bindValue(3, $message, "string");
      $sql->bindValue(4, $code, "string");
      $sql->execute();
      $this->_send($this->from, sprintf(_("FAQ with keyword %s added."), $code));
      return;
    } else {
      $sql = $this->db->prepare('SELECT * FROM faq WHERE room = ? AND keyword = ?', array("string", "string"));
      $sql->bindValue(1, $this->room, "string");
      $sql->bindValue(2, $set, "string");
      $sql->execute();
      $faqs = $sql->fetchAll();
      foreach ($faqs as $a) {
        // $author = $this->$roster->generateHTML()
        $this->_send($this->from, sprintf(_("FAQ authored by %s: %s"), $a['author'], $a['message']));
        return;
      }
      $this->_send($this->from, sprintf(_("FAQ %s not found."), $set));
      return;
    }
  }

}
