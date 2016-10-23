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

/**
 * Sample configuration for ligrev. To make changes, copy to config.php
 * and make your edits there.
 *
 * This file documents the bare-bones minimum to get Ligrev started and running.
 * For more details, see the wiki's documentation.
 *
 * @link https://github.com/sylae/ligrev/wiki
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
$config['db'] = 'mysqli://user:password@localhost/database';

$config['jaxl'] = [
  'jid'       => 'ligrev@example.net',
  'pass'      => 'rainbowdashisactuallybestpony',
  'host'      => 'example.net',
  'force_tls' => true,
  'protocol'  => 'tcp'
];

$config['rooms']['lounge@conference.example.net'] = [];
