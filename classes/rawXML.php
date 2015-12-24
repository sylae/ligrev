<?php

namespace Ligrev;

/**
 * Little hack until we can get querypath and JAXL to play nice
 * (if that ever happens)
 * @author Christoph Burschka <christoph@burschka.de>
 */
class RawXML {

  /**
   * The XML string stored
   * @var string
   */
  private $string;

  /**
   * Constructor
   * @param string $string The XML string to store
   */
  public function __construct($string) {
    $this->string = $string;
  }

  /**
   * Get the string
   * @return string The string
   */
  public function to_string() {
    return $this->string;
  }

}
