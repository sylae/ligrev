<?php

/**
 * Description here
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */

namespace Ligrev;

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;

/**
 * Description of xmppLogHandler
 *
 * @author sylae
 */
class xmppLogHandler extends AbstractProcessingHandler {

  /**
   * @param resource|string $stream
   * @param int             $level          The minimum logging level at which this handler will be triggered
   * @param Boolean         $bubble         Whether the messages that are handled can bubble up the stack or not
   * @param int|null        $filePermission Optional file permissions (default (0644) are only for owner read/write)
   * @param Boolean         $useLocking     Try to lock log file before doing any writes
   *
   * @throws \Exception                If a missing directory is not buildable
   * @throws \InvalidArgumentException If stream is not a resource or string
   */
  public function __construct($level = Logger::DEBUG, $bubble = true) {
    parent::__construct($level, $bubble);
  }

  /**
   * {@inheritdoc}
   */
  public function close() {

  }

  /**
   * {@inheritdoc}
   */
  protected function write(array $record) {
    global $_xmppLogHandler_messageQueue;
    $_xmppLogHandler_messageQueue[] = $record;
  }

}
