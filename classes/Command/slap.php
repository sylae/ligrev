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
 * Slap someone with a fish
 */
class slap extends \Ligrev\command {

  function process() {
    if (!$this->canDo("sylae/ligrev/slap")) {
      return false;
    }
    $textParts = $this->_split($this->text);
    $vic       = (array_key_exists(1, $textParts) ? $textParts[1] : $this->t('Ligrev'));
    $fish      = [$this->t('poach'), $this->t('salmon'), $this->t('greyling'), $this->t('coelecanth'), $this->t('trout')];
    $wep       = (array_key_exists(2, $textParts) ? $textParts[2] : array_rand(array_flip($fish)));
    $this->_send($this->getDefaultResponse(),
      sprintf($this->t("%s slaps %s with a large %s"), $this->authorHTML, $vic,
        $wep));
  }

}
