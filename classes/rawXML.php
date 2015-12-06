<?php
/**
 * Little hack until we can get querypath and JAXL to play nice
 * (if that ever happens)
 * @author Christoph Burschka <christoph@burschka.de>
 */

namespace Ligrev;

class RawXML {
  private $string;
  public function __construct($string) {
    $this->string = $string;
  }
  public function to_string() {
    return $this->string;
  }
}
