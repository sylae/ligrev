<?php

/*
 * Copyright (C) 2016 Sylae Jiendra Corell <sylae@calref.net>
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

namespace Ligrev\Plugin;

/**
 * Ligrev plugin providing basic management and diagnostic commands
 *
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
class LigrevManagement implements \Ligrev\iLigrevPlugin {

  public static function register(\Ligrev $ligrev) {
    $ligrev->register_command("ligrev", "LigrevManagementCommand");
  }

}
