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
 * stop being mean :(
 *
 * @author Keira Sylae Aro <sylae@calref.net>
 */
class talkingShitHandler extends \Ligrev\parser {

  const LIGREV_MATCH  = "/ligrev\\s(?:(?:is|has|has\\sa|has\\san))\\s(?:(?:|pretty|quite|rather)\\s)*(?:bug|buggy|broken|issue|issues|bugs|trash|bad|terrible|awful|shit|garbage|feces)/i";

  function __construct(\XMPPStanza $stanza, $origin) {
    static $talkingShitTimer;
    
    parent::__construct($stanza, $origin);

    if ($this->canDo("sylae/ligrev/fun/talkShit")) { // is allowed to be mean to me
      return false;
    }
    if (time() - $talkingShitTimer < 300) { // five minute timeout
      return false;
    }
    $talkingShitTimer = time();
    $this->_send($this->getDefaultResponse(), "https://gfycat.com/GiganticHopefulCanary");
  }

  public static function trigger(\XMPPStanza $stanza, \QueryPath\DOMQuery $qp) {
    $body = $stanza->body;
    if (preg_match(self::LIGREV_MATCH, $body, $match)) {
      return true;
    }
    return false;
  }

}
