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

namespace Ligrev;

use Monolog\Registry;

$ligrev_inhibit_auto_restart = false;

register_shutdown_function(function() {
  global $ligrev_inhibit_auto_restart;

  if ($ligrev_inhibit_auto_restart) {
    Registry::CORE()->warning("Auto restart has been inhibited. Ligrev shutting down",
      ['status_code' => 0]);
    die(0);
  } else {
    Registry::CORE()->notice("Ligrev shutting down with intent to restart",
      ['status_code' => 1]);
    die(1);
  }
});
