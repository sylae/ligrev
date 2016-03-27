<?php

/**
 * Leave a message for an offline user, for Ligrev to send later
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */

namespace Ligrev\Command;

class tell extends \Ligrev\command {

  public function process() {
    if (!$this->canDo("sylae/ligrev/tell")) {
      return false;
    }
    $textParts = $this->_split($this->text);
    $r = \Ligrev\return_ake(1, $textParts, null);
    switch ($r) {
      case "block":
        $this->addBlock();
        break;
      case "unblock":
        $this->removeBlock();
        break;
      case "help":
        $this->help();
        break;
      default:
        $this->addTell();
        break;
    }
  }

  private function addTell() {
    global $roster;

    $textParts = $this->_split($this->text);

    $recipient = \Ligrev\return_ake(1, $textParts, null);
    if (strlen($recipient) == 0) {
      $this->help(sprintf($this->t("Error: %s"), $this->t("No recipient.")));
      return;
    }

    $message = trim(implode(" ", array_slice($textParts, 2)));
    if (strlen($message) < 1) {
      $this->help(sprintf($this->t("Error: %s"), $this->t("No message.")));
      return;
    }

    $recipient = $this->_appendPrefix($recipient);
    $rec = new \Ligrev\xmppEntity(new \XMPPJid($recipient));
    $recipientHTML = $rec->generateHTML();

    // check for blockages
    if ($this->_amIBlocked($recipient)) {
      $this->_send($this->getDefaultResponse(), sprintf($this->t("Error: %s"), sprintf($this->t("%s has blocked you from sending :tell messages."), $recipientHTML)));
      return;
    }

    // Let's make sure the user isn't already online.
    if ($roster->onlineByJID($recipient) && $roster->onlineByJID($recipient, null, true)->isAFK()) {
      $this->help(sprintf($this->t("Error: %s"), sprintf($this->t("%s already online. Contact user directly."), $recipientHTML)));
      return;
    }

    $this->_insertTell($recipient, $recipientHTML, $message);
    $this->_send($this->getDefaultResponse(), sprintf($this->t("Message for %s processed."), $recipientHTML));
  }

  private function addBlock() {
    $textParts = $this->_split($this->text);
    $sender = \Ligrev\return_ake(2, $textParts, null);
    $sender = $this->_appendPrefix($sender);
    $snd = new \Ligrev\xmppEntity(new \XMPPJid($sender));
    $senderHTML = $snd->generateHTML();

    // first make sure they aren't already blocked
    if ($this->_areTheyBlocked($sender)) {
      $this->_send($this->getDefaultResponse(), sprintf($this->t("%s is already blocked."), $senderHTML));
      return;
    }
    $this->_insertBlock($sender);
    $this->_send($this->getDefaultResponse(), sprintf($this->t("%s has been blocked."), $senderHTML));
  }

  private function removeBlock() {
    $textParts = $this->_split($this->text);
    $sender = \Ligrev\return_ake(2, $textParts, null);
    $sender = $this->_appendPrefix($sender);
    $snd = new \Ligrev\xmppEntity(new \XMPPJid($sender));
    $senderHTML = $snd->generateHTML();

    // first make sure they aren't already blocked
    if (!$this->_areTheyBlocked($sender)) {
      $this->_send($this->getDefaultResponse(), sprintf($this->t("%s is not blocked."), $senderHTML));
      return;
    }
    $this->db->delete('tell_block', ['sender' => $sender, 'recipient' => $this->fromJID->jid->bare]);
    $this->_send($this->getDefaultResponse(), sprintf($this->t("%s has been unblocked."), $senderHTML));
  }

  public function help($prefix = null) {
    if (is_string($prefix)) {
      $prefix = $prefix . "\n";
    }
    $help_lines = [
      $this->t("Usage help for Ligrev command :tell:"),
      $this->t("`:tell \$recipient \$message` - To send a message."),
      $this->t("`:tell block \$sender` - To block a user from sending messages to you."),
      $this->t("`:tell unblock \$sender` - To unblock a user from sending messages to you."),
    ];
    $this->_send($this->getDefaultResponse(), $prefix . implode("\n", $help_lines));
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

  private function _areTheyBlocked($sender) {
    $sql = $this->db->prepare('SELECT * FROM tell_block WHERE sender = ? AND recipient = ?', ["string", "string"]);
    $sql->bindValue(1, str_replace("\\20", " ", $sender), "string");
    $sql->bindValue(2, str_replace("\\20", " ", $this->fromJID->jid->bare), "string");
    $sql->execute();
    $tells = $sql->fetchAll();
    foreach ($tells as $tell) {
      return true;
    }
    return false;
  }

  private function _amIBlocked($recipient) {
    $sql = $this->db->prepare('SELECT * FROM tell_block WHERE recipient = ? AND sender = ?', ["string", "string"]);
    $sql->bindValue(1, str_replace("\\20", " ", $recipient), "string");
    $sql->bindValue(2, str_replace("\\20", " ", $this->fromJID->jid->bare), "string");
    $sql->execute();
    $tells = $sql->fetchAll();
    foreach ($tells as $tell) {
      return true;
    }
    return false;
  }

  private function _insertTell($recipient, $message) {
    $sql = $this->db->prepare('INSERT INTO tell (sender, recipient, sent, private, message) VALUES(?, ?, ?, ?, ?);', ['string', 'string', 'integer', 'boolean', 'string']);
    $sql->bindValue(1, $this->fromJID->jid->bare, "string");
    $sql->bindValue(2, $recipient, "string");
    $sql->bindValue(3, time(), "integer");
    $sql->bindValue(4, ($this->origin == "groupchat" ? false : true), "boolean");
    $sql->bindValue(5, $message, "string");
    $sql->execute();
  }

  private function _insertBlock($sender) {
    $sql = $this->db->prepare('INSERT INTO tell_block (sender, recipient) VALUES(?, ?);', ['string', 'string']);
    $sql->bindValue(1, str_replace("\\20", " ", $sender), "string");
    $sql->bindValue(2, str_replace("\\20", " ", $this->fromJID->jid->bare), "string");
    $sql->execute();
  }

}
