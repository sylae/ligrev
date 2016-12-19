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
 * Dump some info on available permissions
 */
class permissions extends \Ligrev\command {

  function process() {
    global $config;
    if (!$this->canDo("sylae/ligrev/permissions")) { // i know
      return false;
    }
    $perms = $this->_getAllPerms($config['permissions']);

    $p = ["Permissions map for " . $this->authorHTML];
    foreach ($perms as $perm) {
      $returnWhy = "";
      $cd        = $this->canDo($perm, $returnWhy);
      if ($cd) {
        $p[] = "*$perm*: $returnWhy";
      } else {
        $p[] = "~~$perm~~: $returnWhy";
      }
    }

    $this->_send($this->getDefaultResponse(), implode(PHP_EOL, $p));
  }

  private function _getAllPerms(array $config) {
    $perms = [];
    foreach ($config as $k => $v) {
      if (is_bool($v)) {
        $perms[] = $k;
      } elseif (is_array($v)) {
        foreach ($v as $kk => $vv) {
          if (is_bool($vv)) {
            $perms[] = $kk;
          }
        }
      }
    }
    return array_unique($perms);
  }

}
