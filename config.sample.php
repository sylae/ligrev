<?php

/**
 * Sample configuration for ligrev. To make changes, copy to config.php
 * and make your edits there.
 *
 * This file documents the bare-bones minimum to get Ligrev started and running.
 * For more details, see the wiki's documentation.
 *
 * @link https://github.com/sylae/ligrev/wiki
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
$config['db'] = 'mysqli://user:password@localhost/database';

$config['jaxl'] = array(
  'jid' => 'ligrev@example.net',
  'pass' => 'rainbowdashisactuallybestpony',
  'host' => 'example.net',
  'force_tls' => true,
  'protocol' => 'tcp'
);

$config['rooms']['lounge@conference.example.net'] = array();
