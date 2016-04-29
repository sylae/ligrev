<?php

/**
 * Mercilessly stolen from the iq class to make PHP shut up about abstract static functions...
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */

namespace Ligrev;

interface iqInterface {

  /**
   * This function will be called to determine if a given IQ can be used by this class.
   * Do try and make it quick
   *
   * @return boolean if this function can make use of the IQ, return true, otherwise return false
   * @param \XMPPStanza $stanza The IQ stanza sent by the server
   * @param \QueryPath $qp The stanza, as a querypath XML object
   */
  static public function canUse(\XMPPStanza $stanza, \QueryPath\DOMQuery $qp);

  /**
   * This function will be called to populate XEP-0030 support (disco).
   *
   * @link http://xmpp.org/registrar/disco-features.html
   * @return array An array of strings, each containing a disco feature string
   */
  static public function disco();

  /**
   * If the class can use this IQ, this will be called. Send responses, use the db, knock yourself out
   * @param \XMPPStanza $stanza The IQ stanza sent by the server
   */
  public function process(\XMPPStanza $stanza);
}
