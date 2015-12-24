<?php

/**
 * unit test for xmppEntity
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
class xmppEntityTest extends PHPUnit_Framework_TestCase {

  public function test_construct() {
    $x = new Ligrev\xmppEntity(new XMPPJid("test@example.com/test"));
    $this->assertEquals("test@example.com/test", $x->jid->to_string());
  }

  public function test_html() {
    $x = new Ligrev\xmppEntity(new XMPPJid("test@example.com/test"));

    $this->assertEquals('<span class="user jid-node-test jid-domain-example.com jid-resource-test" data-jid="test@example.com/test">test@example.com</span>', $x->generateHTML());

    $this->assertEquals('<span class="user jid-node-test jid-domain-example.com jid-resource-test" data-jid="test@example.com/test" data-nick="nick">(nick)</span>', $x->generateHTML("nick"));
  }

  /*
   * @todo check spaces
   */
}
