<?php

/**
 * Description here
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */

namespace Ligrev\Command;

class slap extends \Ligrev\command {

  function process() {
    if (!$this->canDo("sylae/ligrev/slap")) {
      return false;
    }
    $textParts = $this->_split($this->text);
    $vic = (array_key_exists(1, $textParts) ? $textParts[1] : $this->t('Ligrev'));
    $fish = [$this->t('poach'), $this->t('salmon'), $this->t('greyling'), $this->t('coelecanth'), $this->t('trout')];
    $wep = (array_key_exists(2, $textParts) ? $textParts[2] : array_rand(array_flip($fish)));
    $this->_send($this->getDefaultResponse(), sprintf($this->t("%s slaps %s with a large %s"), $this->authorHTML, $vic, $wep));
  }

}
