<?php

/**
 * Parent class for IQ handlers
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */

namespace Ligrev;

abstract class iq implements iqInterface {

  protected $db;
  protected $client;
  protected $config;

  public final function __construct(\XMPPStanza $stanza) {
    global $db, $client, $config;
    $this->db = &$db;
    $this->client = &$client;
    $this->config = &$config;

    $this->process($stanza);
  }

}
