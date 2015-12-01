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
    $wep = (array_key_exists(2, $textParts) ? $textParts[2] : array_rand(array_flip(array('poach', 'salmon', 'greyling', 'coelecanth', 'trout'))));
    $this->_send($this->room, $this->author . ' slaps ' . $vic . ' with a large ' . $wep);
  }

}
