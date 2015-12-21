<?php

/**
 * Handler for any uncaught IQs
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */

namespace Ligrev;

define("NS_TIME", 'urn:xmpp:time');

class iqHandler {

  protected $stanza;
  protected $qp;

  function __construct(\XMPPStanza $stanza) {
    $this->stanza = $stanza;
    $this->qp = \qp('<?xml version="1.0"?>' . $stanza->to_string());

    $class = $this->querySupport();

    if (!$class) {
      // <service-unavailable/>
    } else {
      $iq = new $class($this->stanza);
    }
  }

  function querySupport() {
    global $iq_classes;

    foreach ($iq_classes as $class) {
      $can = $class::canUse($this->stanza, $this->qp);
      if ($can) {
        return $class;
      }
    }
    return false;
  }

}
