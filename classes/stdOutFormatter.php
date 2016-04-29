<?php

/**
 * Since we log to STDOUT for stuff, let's make it a bit readable...
 *
 * This is shoddy as fuck and should be redone properly.
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */

namespace Ligrev;

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
