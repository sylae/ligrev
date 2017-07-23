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
 * inline dice
 *
 * @author Keira Sylae Aro <sylae@calref.net>
 */
class inlineDiceHandler extends \Ligrev\parser {

  const DICE_MATCH = "/d{(.+?)}/i";

  function __construct(\XMPPStanza $stanza, $origin) {
    parent::__construct($stanza, $origin);

    if (!$this->canDo("sylae/ligrev/roll")) {
      return false;
    }

    $sa = preg_replace_callback(self::DICE_MATCH,
      function ($m) {
      return "{" . \Ligrev\dice::parseString($m[1]) . "}";
    }, $this->text
    );
    $snd = sprintf($this->t("Results for %s:" . PHP_EOL . "%s"),
      $this->authorHTML, implode(", ", $sa));
    $this->_send($this->getDefaultResponse(), $snd);
  }

  public static function trigger(\XMPPStanza $stanza, \QueryPath\DOMQuery $qp) {
    $body = $stanza->body;
    if (preg_match(self::DICE_MATCH, $body, $match)) {
      return true;
    }
    return false;
  }

}
