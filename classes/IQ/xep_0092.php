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

namespace Ligrev\IQ;

define("NS_IQ_VERSION", 'jabber:iq:version');

/**
 * Support for XEP-0092, Software version reporting
 *
 * @link http://xmpp.org/extensions/xep-0092.html
 */
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
