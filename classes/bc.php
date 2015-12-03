<?php

/**
 * Parse and return a string using gnu bc
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */

namespace Ligrev;

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
    global $config;

    $remove = array(
      'read',
    );
    $expr = trim("scale=3; " . str_replace($remove, '', html_entity_decode($math)));

    $descriptorspec = array(
      0 => array("pipe", "r"),
      1 => array("pipe", "w"),
      2 => array("pipe", "w")
    );
    $pipes = array();

    $process = proc_open('timeout 5 bc -l ' . $this->libs, $descriptorspec, $pipes);

    if (is_resource($process)) {
      fwrite($pipes[0], $expr . PHP_EOL);
      fclose($pipes[0]);
      l("STDIN:  $expr", "DICE", L_DEBUG);

      $pipeout = trim(str_replace('\\' . PHP_EOL, '', stream_get_contents($pipes[1])));

      $stdout = (strlen($pipeout) > 80) ? substr($pipeout, 0, 77) . '...' : $pipeout;
      l("STDOUT: $stdout", "DICE", L_DEBUG);

      $stderr = trim(str_replace('\\' . PHP_EOL, PHP_EOL, stream_get_contents($pipes[2])));
      l("STDERR: $stderr", "DICE", L_DEBUG);

      $stderr = preg_replace('/\\(standard_in\\) \\d+: /', '', $stderr);
      $pinfo = proc_get_status($process);
      fclose($pipes[1]);
      fclose($pipes[2]);
      proc_close($process);

      l(sprintf(_("Exited with status code %s"), $pinfo['exitcode']), "DICE", L_DEBUG);

      if ($pinfo['exitcode'] == 124) {
        $this->result = "timeout";
      } elseif (strlen($stderr) > 0) {
        $this->result = $stderr;
      } else {
        $this->result = $stdout;
      }
    } else {
      l(_("Could not create dice roll process!"), "DICE", L_WARN);
      $this->result = sprintf(_("Ligrev error in bc parsing module: %s"), _("Process not created"));
    }
  }

}
