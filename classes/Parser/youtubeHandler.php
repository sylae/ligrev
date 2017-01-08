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
 * Get video information about a posted Youtube link
 *
 * @author Keira Sylae Aro <sylae@calref.net>
 */
class youtubeHandler extends \Ligrev\parser {

  const YT_MATCH = [
    "reg"   => "/https?:\\/\\/www\\.youtube\\.com\\/watch\\?[^\\s]+/i",
    "short" => "/https?:\\/\\/youtu\\.be\\/[^\\s]+/i"
  ];

  function __construct(\XMPPStanza $stanza, $origin) {
    parent::__construct($stanza, $origin);

    if (!$this->canDo("sylae/ligrev/youtube")) {
      return false;
    }

    foreach (self::YT_MATCH as $type => $preg) {
      if (preg_match($preg, $this->text, $match)) {
        $id = $this->_getID($match[0], $type);

        //$info = $this->_getYTInfo($id);
      }
    }

    //$this->_send($this->getDefaultResponse(),
    //  "that's a youtube link. id is $id.");
  }

  public static function trigger(\XMPPStanza $stanza, \QueryPath\DOMQuery $qp) {
    $body = $stanza->body;
    foreach (self::YT_MATCH as $preg) {
      if (preg_match($preg, $body, $match)) {
        return true;
      }
    }
    return false;
  }

  private function _getYTInfo($id) {

  }

  private function _getID($string, $matchType) {
    switch ($matchType) {
      case "reg":
        parse_str(parse_url($string, PHP_URL_QUERY), $urls);
        return $urls["v"];
      case "short":
        preg_match("/\\/([^\\/]+)$/", $string, $match);
        return $match[1];
    }
  }

}
