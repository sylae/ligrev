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
    $textParts = $this->_split($this->text);
    $vic = (array_key_exists(1, $textParts) ? $textParts[1] : 'Ligrev');
    $wep = (array_key_exists(2, $textParts) ? $textParts[2] : array_rand(array_flip(array(_('poach'), _('salmon'), _('greyling'), _('coelecanth'), _('trout')))));
    $this->_send($this->from, sprintf(_("%s slaps %s with a large %s"), $this->authorHTML, $vic, $wep));
  }

}
