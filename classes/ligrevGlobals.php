<?php

/**
 * A simple class to hold common stuff
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */

namespace Ligrev;

class ligrevGlobals {

  protected $client;
  protected $db;
  protected $config;
  protected $data;
  protected $roster;
  protected $lang;

  function __construct() {

    global $client, $db, $config, $roster;
    $this->client = &$client;
    $this->db = &$db;
    $this->config = &$config;
    $r = &$roster;
    $this->roster = (isset($roster) && $roster instanceof roster) ? $r : null;

    $this->lang = "en";
  }

  public function getData($key) {
    if (array_key_exists($key, $this->data)) {
      return $this->data[$key];
    } else {
      return false;
    }
  }

  public function setData($key, $data) {
    $this->data[$key] = $data;
    return true;
  }

  public static function sendMessage($to, $text, $isMarkdown = true, $origin = "groupchat") {
    global $client;
    if ($isMarkdown) {
      // TODO: fuck all this, do it properly
      $Pd = new \Parsedown();
      $html = trim($Pd->text($text));
      $md = strip_tags($text);
      $qp = "<body>$md</body><html xmlns=\"http://jabber.org/protocol/xhtml-im\"><body xmlns=\"http://www.w3.org/1999/xhtml\">$html</body></html>";
    } else {
      $qp = '<body>' . strip_tags($text) . '</body>';
    }
    $body = new rawXML($qp);
    $msg = new \XMPPMsg(
      array(
      'type' => (($origin == "groupchat") ? "groupchat" : "chat"),
      'to' => (($to instanceof \XMPPJid) ? $to->to_string() : $to),
      'from' => $client->full_jid->to_string(),
      )
    );
    $msg->cnode($body);
    $client->send($msg);
  }

  public function t($string) {
    return t($string, $this->lang);
  }

}
