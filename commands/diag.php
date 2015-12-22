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
    $lv = V_LIGREV;
    $string = _('Ligrev Diagnostic Information') . PHP_EOL .
      sprintf(_('Ligrev Version: %s'), "[$lv](https://github.com/sylae/ligrev/commit/$lv)") . PHP_EOL .
      sprintf(_('PHP Version: %s'), phpversion()) . PHP_EOL .
      sprintf(_('Process ID %s as %s'), getmypid(), get_current_user()) . PHP_EOL .
      sprintf(_('System: %s'), php_uname());
    $this->_send($this->from, $string);
  }

}
