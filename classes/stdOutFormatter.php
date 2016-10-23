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
 * Since we log to STDOUT for stuff, let's make it a bit readable...
 *
 * This is shoddy as fuck and should be redone properly.
 */
class stdOutFormatter implements \Monolog\Formatter\FormatterInterface {

  public function format(array $record) {

    $output = $record['datetime']->format("(H:i:s) ");

    switch ($record['message']) {
      case "Message received":
        $output .= "[" . $record['context']['room'] . "] ";
        $output .= $record['context']['nick'] . ": ";
        $output .= $record['context']['body'];
        break;
      case "Username change":
        $output .= "[" . $record['context']['room'] . "] ";
        $output .= $record['context']['old_nick'];
        $output .= " is now known as ";
        $output .= $record['context']['nick'];
        break;
      case "User left room":
        $output .= "[" . $record['context']['room'] . "] ";
        $output .= $record['context']['nick'] . " has left the room.";
        break;
      case "User joined room":
        $output .= "[" . $record['context']['room'] . "] ";
        $output .= $record['context']['nick'] . " has joined the room.";
        break;
      case "User booted from chat":
        $output .= "[" . $record['context']['room'] . "] ";
        $output .= $record['context']['nick'];
        $output .= " has been ";
        $output .= $record['context']['type'] . ".";
        break;
      default:
        $output .= $record['level_name'] . " " . $record['message'] . " " . json_encode($record['context']);
    }

    return $output . PHP_EOL;
  }

  public function formatBatch(array $records) {
    $message = '';
    foreach ($records as $record) {
      $message .= $this->format($record);
    }
    return $message;
  }

}
