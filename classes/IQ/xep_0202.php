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

/**
 * Support for XEP-0202, Entity Time
 *
 * @link http://xmpp.org/extensions/xep-0202.html
 */
class xep_0202 extends \Ligrev\iq {

  const NS_TIME = 'urn:xmpp:time';

  static function canUse(\XMPPStanza $stanza, \QueryPath\DOMQuery $qp) {
    return (\qp($qp, 'time')->attr('xmlns') == self::NS_TIME && $stanza->type == "get");
  }

  static function disco() {
    return [self::NS_TIME];
  }

  function process(\XMPPStanza $stanza) {

    $resp = new \XMPPIq(array('from' => $this->client->full_jid->to_string(), 'to' => $stanza->from, 'type' => 'result', 'id' => $stanza->id));
    $resp->c('time', self::NS_TIME);
    $resp->c('utc', null, array(), date(DATE_ATOM))->up();
    $resp->c('tzo', null, array(), date("P"));

    $this->client->send($resp);
  }

}
