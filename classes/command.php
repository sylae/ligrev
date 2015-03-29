<?php

/**
 * Template class for a command
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */

namespace Ligrev;

class command {

  protected $text;
  protected $author;
  protected $room;
  protected $origin;

  function __construct(\XMPPStanza $stanza, $origin) {
    $this->text = $stanza->body;
    $from = new \XMPPJid($stanza->from);
    $this->room = $from->bare;
    $this->author = $from->resource;
    $this->origin = $origin;
  }

  function _send($to, $text) {
    global $client;
    if ($this->origin == "groupchat") {
      return $client->xeps['0045']->send_groupchat($to, $text);
    } else {
      return $client->send_chat_msg($to, $text);
    }
  }

  function _split($string) {
    return explode(" ", $string);
  }

}