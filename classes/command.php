<?php

/**
 * Template class for a command
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */

namespace Ligrev;

class command extends ligrevGlobals {

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

  /**
   * Cadence HTML represenation of user
   * @var string
   */
  protected $authorHTML;

  /**
   * Config array for this room.
   * @var array
   */
  protected $config;

  function __construct(\XMPPStanza $stanza, $origin) {
    global $client, $db, $config, $roster;

    parent::__construct();

    $this->text = $stanza->body;
    $this->from = new \XMPPJid($stanza->from);
    $this->room = $this->from->bare;
    $this->nick = $this->from->resource;
    $this->origin = $origin;
    $this->fromJID = $roster->rooms[$this->room]->nickToEntity($this->nick);
    $this->authorHTML = $this->fromJID->generateHTML($this->nick);

    if ($this->origin == "groupchat" && array_key_exists($this->room, $config['rooms'])) {
      $this->config = array_merge($config, $config['rooms'][$this->room]);
    } else {
      $this->config = $config;
    }
  }

  function _send($to, $text, $isMarkdown = true) {
    $this->sendMessage($to, $text, $isMarkdown, $this->origin);
  }

  public static function _split($string) {
    $regex = '/(.*?[^\\\\](\\\\\\\\)*?)\\s/';
    preg_match_all($regex, $string . ' ', $matches);
    $m = array_map(function($s) {
      return str_replace("\\ ", " ", $s);
    }, $matches[1]);
    return $m;
  }

  protected function getDefaultResponse() {
    if ($this->origin == "chat") {
      return $this->from;
    } elseif ($this->origin == "groupchat") {
      return $this->room;
    } else {
      return false;
    }
  }

}
