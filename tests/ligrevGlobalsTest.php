<?php

/**
 * unit test for ligrevGlobals
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
class ligrevGlobalsTest extends PHPUnit_Framework_TestCase {

  public function test_getSetData() {
    $lg = new Ligrev\ligrevGlobals();

    $this->assertFalse($lg->getData("nonexistent key"));

    $this->assertTrue($lg->setData("key", "data"));

    $this->assertEquals("data", $lg->getData("key"));
  }

  public function test_t() {
    $lg = new Ligrev\ligrevGlobals();

    $this->assertEquals("String with no translation", $lg->t("String with no translation"));
  }

}
