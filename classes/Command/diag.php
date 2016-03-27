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
    if (!$this->canDo("sylae/ligrev/diag")) {
      return false;
    }
    $lv = V_LIGREV;
    $string = $this->t('Ligrev Diagnostic Information') . PHP_EOL .
      sprintf($this->t('Ligrev Version: %s'), "[$lv](https://github.com/sylae/ligrev/commit/$lv)") . PHP_EOL .
      sprintf($this->t('PHP Version: %s'), phpversion()) . PHP_EOL .
      sprintf($this->t('Process ID %s as %s'), getmypid(), get_current_user()) . PHP_EOL .
      sprintf($this->t('System: %s'), php_uname());
    $this->_send($this->getDefaultResponse(), $string);
  }

}
