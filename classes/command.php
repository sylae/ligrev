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

  function _send($to, $text, $isMarkdown = true) {
    global $client;
    if ($isMarkdown) {
      // TODO: fuck all this, do it properly
      $html = trim(\Michelf\Markdown::defaultTransform($text));
      $md = htmlspecialchars($text);
      $qp = "<body>$md</body><html xmlns=\"http://jabber.org/protocol/xhtml-im\"><body xmlns=\"http://www.w3.org/1999/xhtml\">$html</body></html>";
    } else {
      $qp = '<body>' . htmlspecialchars($text) . '</body>';
    }
    $body = new rawXML($qp);
    $msg = new \XMPPMsg(
      array(
        'type'=>(($this->origin == "groupchat") ? "groupchat" : "chat"),
        'to'=>(($to instanceof \XMPPJid) ? $to->to_string() : $to),
        'from'=>$client->full_jid->to_string(),
      )
    );
    $msg->cnode($body);
    $client->send($msg);
  }

  function _split($string) {
    return explode(" ", $string);
  }

}
