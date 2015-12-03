<?php

/**
 * Template class for any ligrev :commands
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */

namespace Ligrev;

class ligrevCommand {

  protected $client;
  protected $rooms;
  protected $origin;
  protected $stanza;
  protected $from;
  protected $text;
  protected $author;
  protected $room;

  function __construct(\XMPPStanza $stanza, $origin) {
    global $client, $rooms, $roster, $config;
    $this->client = $client;
    $this->rooms = $rooms;

    $this->origin = $origin;
    $this->stanza = $stanza;

    $this->from = new \XMPPJid($stanza->from);
    if ($this->from->resource) {
      if (!$this->stanza->exists('delay', NS_DELAYED_DELIVERY)) {
        l("[" . $this->from->node . "] " . $this->from->resource . (($this->origin == "chat") ? " (" . _("PM") . ")" : "") . ": " . $this->stanza->body);
        $this->text = $this->stanza->body;
        $this->room = $this->from->bare;
        $this->author = $this->from->resource;
      }
      $preg = "/^[\/:!](\w+)(\s|$)/";
      if (!in_array($this->author[0], array(':', '!', '/')) && preg_match($preg, $this->text, $match) && class_exists("Ligrev\\Command\\" . $match[1])) {
        $class = "Ligrev\\Command\\" . $match[1];
        $command = new $class($stanza, $this->origin);
        $command->process();
      }
    }
  }

  function kickOccupant($nick, $roomJid, $reason = false, $callback = false) {
    global $client;
    $payload = "<iq from='" . $client->jid->to_string() . "'id='ligrev_" . time() . "'to='" . $roomJid . "'type='set'>
  <query xmlns='http://jabber.org/protocol/muc#admin'>
    <item nick='" . $nick . "' role='none'>
      <reason>" . $reason . "</reason>
    </item>
  </query>
</iq>";
    $client->send_raw($payload);
  }

}
