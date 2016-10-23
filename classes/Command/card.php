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
 * Pick a random card from the $deck global variable.
 * @see \Ligrev\Command\shuffle
 *
 * @todo Translate cards
 */
class card extends \Ligrev\command {

  function process() {
    global $decks;
    if (!$this->canDo("sylae/ligrev/card-draw")) {
      return false;
    }
    if ($this->origin == "chat") {
      $this->_send($this->getDefaultResponse(),
        $this->t("Cannot use cards in private context."));
      return;
    }
    if (!array_key_exists($this->room, $decks)) {
      $this->_send($this->getDefaultResponse(),
        $this->t("Deck uninitialized, use :shuffle."));
    } elseif (count($decks[$this->room]) == 0) {
      $this->_send($this->getDefaultResponse(),
        $this->t("Deck depleted, use :shuffle."));
    } else {
      $c = array_pop($decks[$this->room]);
      $this->_send($this->getDefaultResponse(),
        sprintf($this->t("%s draws a %s"), $this->authorHTML, $c));
    }
  }

}
