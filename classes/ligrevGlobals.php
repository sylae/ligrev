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

namespace Ligrev;

/**
 * The ligrevGlobals class is used as a template to hold common stuff
 * used by other classes. It should just be called directly, just extended.
 */
class ligrevGlobals {

  /**
   * A reference to the main JAXL object
   * @var JAXL
   */
  protected $client;

  /**
   * A reference to the main database object
   * @var Doctrine\DBAL\Connection
   */
  protected $db;

  /**
   * A reference to the main configuration array
   * @var array
   */
  protected $config;

  /**
   * An array to store object data
   * @var array
   */
  protected $data = [];

  /**
   * If available, a reference to the main roster object
   * @var roster|null
   */
  protected $roster;

  /**
   * A string representing the desired language of the object
   * @var string
   */
  protected $lang;

  /**
   * Constructor
   * @global JAXL $client
   * @global Doctrine\DBAL\Connection $db
   * @global array $config
   * @global \Ligrev\roster $roster
   */
  function __construct() {

    global $client, $db, $config, $roster;
    $this->client = &$client;
    $this->db     = &$db;
    $this->config = &$config;
    $r            = &$roster;
    $this->roster = (isset($roster) && $roster instanceof roster) ? $r : null;

    $this->lang = $this->config['lang'];
  }

  /**
   * Get a data value from the internal storage
   * @param string $key The key to get
   * @return boolean|mixed Returns the data, or false if the key has not been populated
   */
  public function getData($key) {
    if (array_key_exists($key, $this->data)) {
      return $this->data[$key];
    } else {
      return false;
    }
  }

  /**
   * Store some data in the internal storage
   * @param string $key The key to store data in
   * @param mixed $data The data in question
   * @return boolean True if everything was set fine
   * @todo check to make sure everything was set fine
   */
  public function setData($key, $data) {
    $this->data[$key] = $data;
    return true;
  }

  /**
   * Send a chat message.
   * @global \Ligrev\JAXL $client
   * @param \XMPPJid $to The JID to send it to
   * @param string $text The message body to send
   * @param boolean $isMarkdown If true, $text will be interpreted as Markdown and a multipart message will be sent
   * @param string $origin "groupchat" or "chat", determines if the server will parse it as MUC or PM.
   */
  public static function sendMessage($to, $text, $isMarkdown = true, $origin = "groupchat") {
    global $client;
    $msg       = new \XMPPMsg(
      array(
      'type' => (($origin == "groupchat") ? "groupchat" : "chat"),
      'to'   => (($to instanceof \XMPPJid) ? $to->to_string() : $to),
      'from' => $client->full_jid->to_string(),
      )
    );
    $plaintext = new rawXML(strip_tags($text));
    $msg->c('body')->cnode($plaintext)->up()->up();
    if ($isMarkdown) {
      $md   = new xmppMarkdown();
      $html = trim($md->text($text));
      $msg->c('html', 'http://jabber.org/protocol/xhtml-im');
      $msg->c('body', 'http://www.w3.org/1999/xhtml');
      $msg->cnode(new RawXML($html));
    }
    $client->send($msg);
  }

  /**
   * A wrapper for t(), which pre-fills in the lang value
   * @see Ligrev\t()
   * @param string $string The string to translate
   * @return string The translated string
   */
  public function t($string) {
    return t($string, $this->lang);
  }

}
