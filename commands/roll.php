<?php

/**
 * :roll
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */

namespace Ligrev;

class roll extends command {

  function process() {
    $textParts = $this->_split($this->text);
    $text = str_replace($textParts[0], "", $this->text);
    $strings = explode(":", $this->text, 5);
    $dice = "/(\d*)d(\d+)/";
    $savdice = "/(\d*)d(\d+)e/";
    $dlist = "/(\d*)a(\d+)/";
    $st = array();
    foreach ($strings as $i => $s) {
      $sa = preg_replace_callback($savdice, function ($m) {
        $m[2] = (($m[2] == 0) ? 1 : $m[2]);
        $m[1] = (($m[1] == 0) ? 1 : $m[1]);
        $d = new dice($m[1], $m[2], "savage");
        return "(" . $d->result . ")";
      }, $s
      );
      $sa = preg_replace_callback($dice, function ($m) {
        $m[2] = (($m[2] == 0) ? 1 : $m[2]);
        $m[1] = (($m[1] == 0) ? 1 : $m[1]);
        $d = new dice($m[1], $m[2], "sum");
        return "(" . $d->result . ")";
      }, $sa
      );
      $sa = preg_replace_callback($dlist, function ($m) {
        $m[2] = (($m[2] == 0) ? 1 : $m[2]);
        $m[1] = (($m[1] == 0) ? 1 : $m[1]);
        $d = new dice($m[1], $m[2], "array");
        return "(" . $d->result . ")";
      }, $sa
      );
      $bc = new bc($sa);
      $sa = $bc->result;

      $st[] = $sa;
    }
    $snd = $this->author . ' rolls ' . implode(", ", $st);
    $this->_send($this->room, $snd);
  }

}
