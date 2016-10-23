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
 * makes many sybeams
 */
class sybeam extends \Ligrev\command {

  function process() {
    if (!$this->canDo("sylae/ligrev/sybeam")) {
      return false;
    }
    $textParts = $this->_split($this->text);
    $sybeams   = new \Ligrev\bc((array_key_exists(1, $textParts) ? $textParts[1] : 1));
    $num       = max(1, min(100, $sybeams->result));
    $string    = str_repeat(':sybeam:', $num);
    $this->_send($this->getDefaultResponse(), $string);
  }

}
