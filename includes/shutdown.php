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

$ligrev_inhibit_auto_restart = false;

register_shutdown_function(function() {
  global $ligrev_inhibit_auto_restart;

  if ($ligrev_inhibit_auto_restart) {
    l(_("Auto restart inhibited...exiting with status code 0."));
    die(0);
  } else {
    l(_("Auto restarting...exiting with status code 1."));
    die(1);
  }
});
