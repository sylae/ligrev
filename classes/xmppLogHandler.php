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

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;

/**
 * Send logs over XMPP
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
