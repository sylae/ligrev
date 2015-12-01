<?php

/**
 * Dump some diagnostic and version information
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */

namespace Ligrev\Command;

class diag extends \Ligrev\command {

  function process() {
    $string = 'Ligrev Diagnostic Information' . PHP_EOL .
      'Ligrev Version: ' . trim(`git rev-parse HEAD`) . PHP_EOL .
      'PHP Version: ' . phpversion() . PHP_EOL .
      'JAXL Version: ' . trim(`cd lib/JAXL && git rev-parse HEAD`) . PHP_EOL .
      'Process ID ' . getmypid() . ' as ' . get_current_user() . PHP_EOL .
      'System: ' . php_uname();
    $this->_send($this->room, $string);
  }

}
