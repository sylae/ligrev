<?php

/**
 * Allow authorities to restart Ligrev remotely.
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */

namespace Ligrev\Command;

class restart extends \Ligrev\command {

  function process() {
    global $roster;
    $senderAff = $this->fromJID->getData("affiliation");

    if ($senderAff == "admin" || $senderAff == "owner") {
      foreach ($this->config['rooms'] as $room => $c) {
        $this->_send($room, "Restarting...");
      }
      \JAXLLoop::$clock->call_fun_after(5000000, function () {
        die();
      });
    }
  }

}
