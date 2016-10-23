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

/**
 * Parent class for IQ handlers
 */
abstract class iq implements iqInterface {

  protected $db;
  protected $client;
  protected $config;

  public final function __construct(\XMPPStanza $stanza) {
    global $db, $client, $config;
    $this->db = &$db;
    $this->client = &$client;
    $this->config = &$config;

    $this->process($stanza);
  }

}
