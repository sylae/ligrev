<?php

/**
 * Support for XEP-0202, Entity Time
 *
 * @link http://xmpp.org/extensions/xep-0202.html
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */

namespace Ligrev\IQ;

define("NS_TIME", 'urn:xmpp:time');

class xep_0202 extends \Ligrev\iq {

  static function canUse(\XMPPStanza $stanza, \QueryPath\DOMQuery $qp) {
    return (\qp($qp, 'time')->attr('xmlns') == NS_TIME && $stanza->type == "get");
  }

  function process(\XMPPStanza $stanza) {

    $resp = new \XMPPIq(array('from' => $this->client->full_jid->to_string(), 'to' => $stanza->from, 'type' => 'result', 'id' => $stanza->id));
    $resp->c('time', NS_TIME);
    $resp->c('utc', null, array(), date(DATE_ATOM))->up();
    $resp->c('tzo', null, array(), date("P"));

    $this->client->send($resp);
  }

}
