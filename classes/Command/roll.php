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

namespace Ligrev\Command;

/**
 * Roll some dice
 */
class roll extends \Ligrev\command {

  function process() {
    if (!$this->canDo("sylae/ligrev/roll")) {
      return false;
    }
    $textParts = $this->_split($this->text);
    $text      = str_replace($textParts[0], "", $this->text);
    $strings   = explode(":", $text, 5);

    // TODO: regex hell
    $dice     = "/(\d*)d(\d+)/";
    $savdice  = "/(\d*)d(\d+)e/";
    $dlist    = "/(\d*)a(\d+)/";
    $savdlist = "/(\d*)a(\d+)e/";
    $st       = [];
    foreach ($strings as $i => $s) {
      $sa = preg_replace_callback($savdice,
        function ($m) {
        $m[2] = (($m[2] == 0) ? 1 : $m[2]);
        $m[1] = (($m[1] == 0) ? 1 : $m[1]);
        $d    = new \Ligrev\dice($m[1], $m[2], "sum", true);
        return "(" . $d->result . ")";
      }, $s
      );
      $sa = preg_replace_callback($dice,
        function ($m) {
        $m[2] = (($m[2] == 0) ? 1 : $m[2]);
        $m[1] = (($m[1] == 0) ? 1 : $m[1]);
        $d    = new \Ligrev\dice($m[1], $m[2], "sum", false);
        return "(" . $d->result . ")";
      }, $sa
      );
      $sa = preg_replace_callback($savdlist,
        function ($m) {
        $m[2] = (($m[2] == 0) ? 1 : $m[2]);
        $m[1] = (($m[1] == 0) ? 1 : $m[1]);
        $d    = new \Ligrev\dice($m[1], $m[2], "array", true);
        var_dump($d);
        return 'print "' . $d->result . '"';
      }, $sa
      );
      $sa = preg_replace_callback($dlist,
        function ($m) {
        $m[2] = (($m[2] == 0) ? 1 : $m[2]);
        $m[1] = (($m[1] == 0) ? 1 : $m[1]);
        $d    = new \Ligrev\dice($m[1], $m[2], "array", false);
        var_dump($d);
        return 'print "' . $d->result . '"';
      }, $sa
      );
      $bc = new \Ligrev\bc($sa);
      $sa = $bc->result;

      $st[] = $sa;
    }
    $snd = sprintf($this->t("%s rolls %s"), $this->authorHTML,
      implode(", ", $st));
    $this->_send($this->getDefaultResponse(), $snd);
  }

}
