<?php

/**
 * Support for XEP-0092, Software version reporting
 *
 * @link http://xmpp.org/extensions/xep-0092.html
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */

namespace Ligrev\IQ;

define("NS_IQ_VERSION", 'jabber:iq:version');

class xep_0092 extends \Ligrev\iq {

  static function canUse(\XMPPStanza $stanza, \QueryPath\DOMQuery $qp) {
    return (\qp($qp, 'query')->attr('xmlns') == NS_IQ_VERSION && $stanza->type == "get");
  }

  static function disco() {
    return [NS_IQ_VERSION];
  }

  function process(\XMPPStanza $stanza) {

    $resp = new \XMPPIq(array('from' => $this->client->full_jid->to_string(), 'to' => $stanza->from, 'type' => 'result', 'id' => $stanza->id));
    $resp->c('query', NS_IQ_VERSION);
    $resp->c('name', null, array(), "sylae/ligrev")->up();
    $resp->c('version', null, array(), V_LIGREV)->up();
    if ($this->config['discloseOSwithXEP0092']) {
      $resp->c('os', null, array(), php_uname("s") . " " . php_uname("r"));
    }

    $this->client->send($resp);
  }

}
