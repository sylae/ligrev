<?php

/**
 * Parent class for IQ handlers
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */

namespace Ligrev;

abstract class iq {

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

  /**
   * This function will be called to determine if a given IQ can be used by this class.
   * Do try and make it quick
   *
   * @return boolean if this function can make use of the IQ, return true, otherwise return false
   * @param \XMPPStanza $stanza The IQ stanza sent by the server
   * @param \QueryPath $qp The stanza, as a querypath XML object
   */
  abstract static public function canUse(\XMPPStanza $stanza, \QueryPath\DOMQuery $qp);

  /**
   * If the class can use this IQ, this will be called. Send responses, use the db, knock yourself out
   * @param \XMPPStanza $stanza The IQ stanza sent by the server
   */
  abstract public function process(\XMPPStanza $stanza);
}
