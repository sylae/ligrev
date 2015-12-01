<?php

/**
 * Template class for a command
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */

namespace Ligrev;

class command {

  /**
   * Body of the message. Probably want to explode() this.
   * @var string 
   */
  protected $text;

  /**
   * Who sent the message.
   * @var string 
   */
  protected $author;

  /**
   * MUC room this message originated from
   * @var string 
   */
  protected $room;

  /**
   * Where the message came from (will be used to differentiate non-MUC messages)
   * Currently not used
   * @var string 
   */
  protected $origin;

  function __construct(\XMPPStanza $stanza, $origin) {
    $this->text = $stanza->body;
    $this->from = new \XMPPJid($stanza->from);
    $this->room = $this->from->bare;
    $this->author = $this->from->resource;
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
