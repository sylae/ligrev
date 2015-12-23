<?php

/**
 * :roll
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License 3
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */

namespace Ligrev\Command;

class roll extends \Ligrev\command {

  function process() {
    $textParts = $this->_split($this->text);
    $text = str_replace($textParts[0], "", $this->text);
    $strings = explode(":", $text, 5);

    // TODO: regex hell
    $dice = "/(\d*)d(\d+)/";
    $savdice = "/(\d*)d(\d+)e/";
    $dlist = "/(\d*)a(\d+)/";
    $savdlist = "/(\d*)a(\d+)e/";
    $st = array();
    foreach ($strings as $i => $s) {
      $sa = preg_replace_callback($savdice, function ($m) {
        $m[2] = (($m[2] == 0) ? 1 : $m[2]);
        $m[1] = (($m[1] == 0) ? 1 : $m[1]);
        $d = new \Ligrev\dice($m[1], $m[2], "sum", true);
        return "(" . $d->result . ")";
      }, $s
      );
      $sa = preg_replace_callback($dice, function ($m) {
        $m[2] = (($m[2] == 0) ? 1 : $m[2]);
        $m[1] = (($m[1] == 0) ? 1 : $m[1]);
        $d = new \Ligrev\dice($m[1], $m[2], "sum", false);
        return "(" . $d->result . ")";
      }, $sa
      );
      $sa = preg_replace_callback($savdlist, function ($m) {
        $m[2] = (($m[2] == 0) ? 1 : $m[2]);
        $m[1] = (($m[1] == 0) ? 1 : $m[1]);
        $d = new \Ligrev\dice($m[1], $m[2], "array", true);
        var_dump($d);
        return 'print "' . $d->result . '"';
      }, $sa
      );
      $sa = preg_replace_callback($dlist, function ($m) {
        $m[2] = (($m[2] == 0) ? 1 : $m[2]);
        $m[1] = (($m[1] == 0) ? 1 : $m[1]);
        $d = new \Ligrev\dice($m[1], $m[2], "array", false);
        var_dump($d);
        return 'print "' . $d->result . '"';
      }, $sa
      );
      $bc = new \Ligrev\bc($sa);
      $sa = $bc->result;

      $st[] = $sa;
    }
    $snd = sprintf(_("%s rolls %s"), $this->authorHTML, implode(", ", $st));
    $this->_send($this->getDefaultResponse(), $snd);
  }

}
