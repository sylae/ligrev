<?php

/**
 * makes many sybeams
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */

namespace Ligrev\Command;

class sybeam extends \Ligrev\command {

  function process() {
    if ($this->from->to_string() == "lounge@conference.calref.net/sylae") {
      $textParts = $this->_split($this->text);
      $sybeams = new \Ligrev\bc((array_key_exists(1, $textParts) ? $textParts[1] : 1));
      $num = max(1, min(100, $sybeams->result));
      $string = str_repeat(':sybeam:', $num);
      $this->_send($this->from, $string);
    }
  }

}
