<?php

/*
 * Copyright (C) 2017 Keira Sylae Aro <sylae@calref.net>
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

namespace Ligrev\Parser;

/**
 * we all know who the best is
 *
 * @author Keira Sylae Aro <sylae@calref.net>
 */
class rwbyHandler extends \Ligrev\parser {

  const RWBY_MATCH = "/best\\srwby\\scharacter/i";

  function __construct(\XMPPStanza $stanza, $origin) {
    static $rwbyTimer;

    parent::__construct($stanza, $origin);

    if (!$this->canDo("sylae/ligrev/fun/rwby")) { // is allowed to learn about best character
      return false;
    }
    if (time() - $rwbyTimer < 300) { // five minute timeout
      return false;
    }
    $rwbyTimer = time();
    $this->_send($this->getDefaultResponse(), "https://syl.ae/l_img/ruby.png");
  }

  public static function trigger(\XMPPStanza $stanza, \QueryPath\DOMQuery $qp) {
    $body = $stanza->body;
    if (preg_match(self::RWBY_MATCH, $body, $match)) {
      return true;
    }
    return false;
  }

}
