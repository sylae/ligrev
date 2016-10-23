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
 * Dump some diagnostic and version information
 */
class diag extends \Ligrev\command {

  function process() {
    if (!$this->canDo("sylae/ligrev/diag")) {
      return false;
    }
    $lv     = V_LIGREV;
    $string = $this->t('Ligrev Diagnostic Information') . PHP_EOL .
      sprintf($this->t('Ligrev Version: %s'),
                       "[$lv](https://github.com/sylae/ligrev/commit/$lv)") . PHP_EOL .
      sprintf($this->t('PHP Version: %s'), phpversion()) . PHP_EOL .
      sprintf($this->t('Process ID %s as %s'), getmypid(), get_current_user()) . PHP_EOL .
      sprintf($this->t('System: %s'), php_uname());
    $this->_send($this->getDefaultResponse(), $string);
  }

}
