<?php

// Some code mercilessly copied/edited from AJAX Chat and donjon
// https://github.com/Frug/AJAX-Chat
// http://donjon.bin.sh/

function send($room, $text) {
  global $client;
  return $client->xeps['0045']->send_groupchat($room, $text);
}

function rollDice($sides) {
  return crypto_rand_secure(1, $sides);
}

function rd_dice ($n,$d) {
  $n = (int)$n;
    if (!is_int($n) || $n < 1) $n = 1;
  $d = (int)$d;
    if (!is_int($d) || $d < 0) return 0;

  $die = array();

  for ($i = 0; $i < $n; $i++) {
    $die[] = rollDice($d);
  }
  
  return array_sum($die);
}

// http://us3.php.net/manual/en/function.openssl-random-pseudo-bytes.php#104322
function crypto_rand_secure($min, $max) {
  $range = $max - $min;
  if ($range == 0) return $min; // not so random...
  $log = log($range, 2);
  $bytes = (int) ($log / 8) + 1; // length in bytes
  $bits = (int) $log + 1; // length in bits
  $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
  do {
    $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes, $s)));
    $rnd = $rnd & $filter; // discard irrelevant bits
  } while ($rnd >= $range);
  return $min + $rnd;
}

function bcFilter($cmd) {
  $remove = array(
    'read',
  );
  $cmd = str_replace($remove, '', $cmd);
  return $cmd;
}

function parseCustomCommands($text, $textParts, $room, $res) { 
  switch($textParts[0]) {
    case '/slap':
    case '!slap':
    case ':slap':
      $vic = (array_key_exists(1, $textParts) ? $textParts[1] : 'Ligrev');
      $wep = (array_key_exists(2, $textParts) ? $textParts[2] : array_rand(array_flip(array('poach', 'salmon', 'greyling', 'coelecanth', 'trout'))));
      send($room, $res.' slaps '.$vic.' with a large '.$wep);
      return $text;
    case '/roll':
    case '!roll':
    case ':roll':
      $text = str_replace($textParts[0], "", $text);
      $strings = explode(",", $text);
      $dice = "/(\d*)d(\d+)/";
      $st = array();
      foreach ($strings as $i => $s) {
        $sa = preg_replace_callback($dice,
          function ($m) {
            return "(".rd_dice($m[1], $m[2]).")";
          },
          $s
        );
        $sa = bcFilter($sa);
        $cmd = 'echo '.escapeshellarg($sa).' | bc';
        l($cmd);
        $sa =  trim(shell_exec($cmd));
        
        $st[] = $sa;
      }
      $res = implode(", ", $st);
      send($room, $res);
      return $res;
    default:  
      return false;  
  }
}