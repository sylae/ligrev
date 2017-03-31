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
 * appropriate response to filth
 *
 * @author Keira Sylae Aro <sylae@calref.net>
 */
class filthHandler extends \Ligrev\parser {

  const URL_MATCH  = "/https*:\\/\\/[^\\s]*\\.[^\\s]*\\/?/i";
  const NSFW_MATCH = "/nsfw|porn|tits|titties|penis|pussy|vagina|pron/i";
  const DBOT_MATCH = "/Ligrev/i";

  function __construct(\XMPPStanza $stanza, $origin) {
    static $filthTimer;

    parent::__construct($stanza, $origin);

    if ($this->canDo("sylae/ligrev/fun/filth")) { // is allowed to post filth
      return false;
    }
    if (time() - $filthTimer < 300) { // five minute timeout
      return false;
    }
    $filthTimer = time();
    $this->_send($this->getDefaultResponse(), "https://syl.ae/l_img/tif.gif");
  }

  public static function trigger(\XMPPStanza $stanza, \QueryPath\DOMQuery $qp) {
    $body = $stanza->body;
    if (preg_match(self::URL_MATCH, $body, $match) && preg_match(self::NSFW_MATCH,
        $body, $match) && !preg_match(self::DBOT_MATCH, $body, $match)) {
      return true;
    }
    return false;
  }

}
