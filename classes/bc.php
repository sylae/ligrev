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
 * Parse and return a string using gnu bc
 */
class bc {

  /**
   * Space-seperated list of includes to call bc with
   * @var string
   */
  public $libs = 'lib/phodd/array.bc lib/phodd/digits.bc lib/phodd/digits_describe.bc lib/phodd/digits_happy.bc lib/phodd/digits_misc.bc lib/phodd/factorial.bc lib/phodd/factorial_gamma.bc lib/phodd/fibonacci.bc lib/phodd/funcs.bc lib/phodd/intdiff.bc lib/phodd/interest.bc lib/phodd/logic.bc lib/phodd/logic_andm.bc lib/phodd/logic_inverse.bc lib/phodd/logic_otherbase.bc lib/phodd/logic_striping.bc lib/phodd/logic_striping_meta.bc lib/phodd/melancholy.bc lib/phodd/melancholy_b.bc lib/phodd/misc_235.bc lib/phodd/misc_ack.bc lib/phodd/misc_anglepow.bc lib/phodd/misc_perfectpow.bc lib/phodd/misc_srr.bc lib/phodd/orialc.bc lib/phodd/output_formatting.bc lib/phodd/output_roman.bc lib/phodd/primes.bc lib/phodd/primes_db_code.bc lib/phodd/primes_db_minipack.bc lib/phodd/primes_other.bc lib/phodd/primes_twin.bc lib/phodd/rand/rand.bc lib/phodd/thermometer.bc lib/bcmisc/minmax.bc lib/bcmisc/mod.bc';

  /**
   * Result from bc--if you want to risk it you can probably soft-cast this as a number.
   * @var string
   */
  public $result;

  function __construct($math) {

    $remove = [
        'read',
    ];
    $expr = trim("scale=3; " . str_replace($remove, '', html_entity_decode($math)));

    $descriptorspec = [
        0 => ["pipe", "r"],
        1 => ["pipe", "w"],
        2 => ["pipe", "w"]
    ];
    $pipes = [];

    if (php_uname('s') == "Windows NT") {
      // currently, timeout does something completely dfferent on windows
      $process = proc_open('bc -l ' . $this->libs, $descriptorspec, $pipes);
    } else {
      $process = proc_open('timeout 5 bc -l ' . $this->libs, $descriptorspec, $pipes);
    }

    if (is_resource($process)) {
      fwrite($pipes[0], $expr . PHP_EOL);
      fclose($pipes[0]);
      \Monolog\Registry::MATH()->debug("Piping data to STDIN", ['data' => $expr]);

      $pipeout = trim(str_replace('\\' . PHP_EOL, '', stream_get_contents($pipes[1])));

      $stdout = (strlen($pipeout) > 80) ? substr($pipeout, 0, 77) . '...' : $pipeout;
      \Monolog\Registry::MATH()->debug("Received data from STDOUT", ['data' => $stdout]);

      $stderr = trim(str_replace('\\' . PHP_EOL, PHP_EOL, stream_get_contents($pipes[2])));
      \Monolog\Registry::MATH()->debug("Received data from STDERR", ['data' => $stderr]);

      $stderr = preg_replace('/\\(standard_in\\) \\d+: /', '', $stderr);
      $pinfo = proc_get_status($process);
      fclose($pipes[1]);
      fclose($pipes[2]);
      proc_close($process);

      \Monolog\Registry::MATH()->debug("Exited bc process", ['status_code' => $pinfo['exitcode']]);

      if ($pinfo['exitcode'] == 124) {
        $this->result = "timeout";
      } elseif (strlen($stderr) > 0) {
        $this->result = $stderr;
      } else {
        $this->result = $stdout;
      }
    } else {
      \Monolog\Registry::MATH()->warning("Could not create bc process");
      $this->result = sprintf(_("Ligrev error in bc parsing module: %s"), _("Process not created"));
    }
  }

}
