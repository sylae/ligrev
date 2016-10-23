<?php

/*
 * Copyright (C) 2016 Keira Sylae Aro <sylae@calref.net>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Ligrev;

/**
 * Mercilessly stolen from the iq class to make PHP shut up about abstract static functions...
 */
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
