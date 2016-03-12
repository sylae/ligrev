<?php

/**
 * Register functions to handle stuff like automatic restarts.
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
/**
 * Set to TRUE to prevent ligrev exiting with status code other than zero.
 * Kinda experimental.
 */

namespace Ligrev;

use Monolog\Registry;

$ligrev_inhibit_auto_restart = false;

register_shutdown_function(function() {
  global $ligrev_inhibit_auto_restart;

  if ($ligrev_inhibit_auto_restart) {
    Registry::CORE()->warning("Auto restart has been inhibited. Ligrev shutting down", ['status_code' => 0]);
    die(0);
  } else {
    Registry::CORE()->notice("Ligrev shutting down with intent to restart", ['status_code' => 1]);
    die(1);
  }
});
