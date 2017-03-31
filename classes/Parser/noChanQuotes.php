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
 * Encourages certain people to not do 4chan quotes (fun!)
 *
 * @author Keira Sylae Aro <sylae@calref.net>
 */
class noChanQuotes extends \Ligrev\parser {

  const WARNTIME = 300;

  function __construct(\XMPPStanza $stanza, $origin) {
    parent::__construct($stanza, $origin);

    if ($this->canDo("sylae/ligrev/fun/chanquotes")) {
      return false;
    }
    $body = trim($this->text);
    if (substr($body, 0, 4) === "&gt;" && !strstr(PHP_EOL, $body)) {
      $warns = $this->timeoutRefresh();
      $text  = $this->getWarnText($warns);
      $this->_send($this->getDefaultResponse(), $text);
      if ($warns >= 3) {
        \JAXLLoop::$clock->call_fun_after(2500000,
          function () {
          $this->roster->rooms[$this->room]->kickOccupant($this->nick,
            "This isnt 4chan");
        });
      }
    }
  }

  public static function trigger(\XMPPStanza $stanza, \QueryPath\DOMQuery $qp) {
    $body = trim($stanza->body);
    if (substr($body, 0, 4) === "&gt;" && !strstr(PHP_EOL, $body)) {
      return true;
    }
    return false;
  }

  /**
   * Adds one to the user warnings counter, tossing old ones as needed.
   */
  private function timeoutRefresh() {
    $warn = $this->roster->rooms[$this->room]->getData("noChanWarns");
    if (is_bool($warn)) {
      $warn = [];
    }
    $warns  = $warn[(string) $this->fromJID] ?? [];
    $w_time = time() - (self::WARNTIME + (pow(10, count($warns))));
    foreach ($warns as $w) {
      if ($w < $w_time) {
        array_shift($warns);
      }
    }
    $warns[]                       = time();
    $warn[(string) $this->fromJID] = $warns;
    $this->roster->rooms[$this->room]->setData("noChanWarns", $warn);

    return count($warns);
  }

  private function getWarnText(int $warnNo) {
    $w = [
      'Please don\'t do that, %s',
      'This isn\'t 4chan, %s',
      'That\'s a bit obnoxious, %s',
    ];
    if ($warnNo >= 2) {
      $w[] = 'You\'d better hope Asimov didn\'t leave a loophole, %s';
      $w[] = 'If I wanted annoying 4chan quotes I\'d go there and post pictures of my robot genetalia.';
      $w[] = '$roster->rooms["' . $this->room . '"]->nickToEntity("%s")->friends--;';
      $w[] = 'If I were Ligrev I\'d adjust the score of %s by -1, bringing it to 1 less than their current score.';
    }
    if ($warnNo >= 3) {
      $w[] = 'Witness me.';
      $w[] = 'I\'ve had it up to here with %s.';
      $w[] = 'ARE YOU FUCKING SORRY';
      $w[] = 'If %s were an overwatch hero they\'d be widowmaker';
      $w[] = 'I\'m disappointed in you, %s';
    }
    $warn = $w[random_int(1, count($w)) - 1];
    if (random_int(1, 20) < min(15, pow($warnNo, 2))) {
      $warn = str_replace("%S", "%s", mb_strtoupper($warn));
    }
    return sprintf($warn, $this->authorHTML);
  }

}
