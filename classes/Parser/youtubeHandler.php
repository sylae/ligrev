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
        $id   = $this->_getID($match[0], $type);
        $info = $this->_getYTInfo($id);
      }
    }
    if (is_object($info)) {
      $title  = $info->snippet->title;
      $author = $info->snippet->channelTitle;
      $length = $this->_formatDuration($info->contentDetails->duration);
      $this->_send($this->getDefaultResponse(),
        sprintf("YouTube video: %s posted by %s (%s)", $title, $author, $length));
    }
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
    global $api_google;

    if (!isset($api_google)) {
      $api_google = new \Google_Client();
      $api_google->setApplicationName("ligrev/" . V_LIGREV . " (https://github.com/sylae/ligrev)");
      $api_google->setDeveloperKey($this->config['api']['google']);
    }
    $yt   = new \Google_Service_YouTube($api_google);
    $info = $yt->videos->listVideos("snippet,contentDetails", ['id' => $id]);

    foreach ($info as $item) {
      return $item;
    }
    return false;
  }

  private function _getID($string, $matchType) {
    switch ($matchType) {
      case "reg":
        parse_str(parse_url($string, PHP_URL_QUERY), $urls);
        return substr($urls["v"], 0, 11);
      case "short":
        preg_match("/\\/([^\\/]+)$/", $string, $match);
        return substr($match[1], 0, 11);
    }
  }

  private function _formatDuration($pt) {

    $t = new \DateInterval($pt);
    $i = [];
    if (strstr($pt, "D")) {
      $i[] = "%d days";
    }
    if (strstr($pt, "H")) {
      $i[] = "%h hours";
    }
    if (strstr($pt, "M")) {
      $i[] = "%i minutes";
    }
    if (strstr($pt, "S")) {
      $i[] = "%s seconds";
    }
    $fmt = implode(", ", $i);
    return $t->format($fmt);
  }

}
