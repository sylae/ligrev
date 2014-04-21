<?php

// Some code mercilessly copied/edited from AJAX Chat and donjon
// https://github.com/Frug/AJAX-Chat
// http://donjon.bin.sh/

function send($room, $text) {
  global $client;
  return $client->xeps['0045']->send_groupchat($room, $text);
}

function rollDice($sides) {
  global $config;
  if ($config['diceSecure']) {
    return dice_secure(1, $sides);
  } else {
    return dice_prng(1, $sides);
  }
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

function rd_dice_array ($n,$d) {
  $n = (int)$n;
    if (!is_int($n) || $n < 1) $n = 1;
  $d = (int)$d;
    if (!is_int($d) || $d < 0) return 0;

  $die = array();

  for ($i = 0; $i < $n; $i++) {
    $die[] = rollDice($d);
  }
  
  return implode(", ", $die);
}

function dice_prng($min, $max) {
  // put in right order
  $m = min($min, $max);
  $x = max($min, $max);
  mt_srand((double)microtime()*1000000);
  
  return mt_rand($m, $x);
}

// http://us3.php.net/manual/en/function.openssl-random-pseudo-bytes.php#104322
function dice_secure($min, $max) {
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
  $cmd = "scale=3; ".$cmd;
  return trim($cmd);
}

function pipeToBc($cmd) {
  global $config;

  $expr = bcFilter($cmd);
  
  $run = 'echo '.escapeshellarg($expr).' | bc -l '.$config['bclibs'];
  l("[DICE] Piping in shell: $expr", L_DEBUG);
  $descriptorspec = array(
    0 => array("pipe", "r"),
    1 => array("pipe", "w"),
    2 => array("pipe", "w")
  );

  $process = proc_open('echo 1+1e | bc', $descriptorspec, $pipes);

  if (is_resource($process)) {
    fclose($pipes[0]);

    $stdout = trim(stream_get_contents($pipes[1]));
    fclose($pipes[1]);
    l("[DICE] STDOUT: $stdout", L_DEBUG);

    $stderr = trim(stream_get_contents($pipes[2]));
    fclose($pipes[2]);
    l("[DICE] STDERR: $stderr", L_DEBUG);

    proc_close($process);
    
    if (strlen($stderr) > 0) {
      return $stderr;
    } else {
      return $stdout;
    }
  } else {
    l("[DICE] Could not create dice roll process!", L_WARN);
    return "Ligrev Error in bc Parsing module: PROC NOT CREATED";
  }
}

function parseCustomCommands($text, $textParts, $room, $res) {
  global $config;
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
      $strings = explode(":", $text);
      $dice = "/(\d*)d(\d+)/";
      $dlist = "/(\d*)a(\d+)/";
      $st = array();
      foreach ($strings as $i => $s) {
        $sa = preg_replace_callback($dice,
          function ($m) {
            $m[2] = (($m[2] == 0) ? 1 : $m[2]);
            $m[1] = (($m[1] == 0) ? 1 : $m[1]);
            return "(".rd_dice($m[1], $m[2]).")";
          },
          $s
        );
        $sa = preg_replace_callback($dlist,
          function ($m) {
            $m[2] = (($m[2] == 0) ? 1 : $m[2]);
            $m[1] = (($m[1] == 0) ? 1 : $m[1]);
            return "(".rd_dice_array($m[1], $m[2]).")";
          },
          $sa
        );
        $sa = pipeToBc($sa);
        
        $st[] = $sa;
      }
      $snd = $res.' rolls '.implode(", ", $st);
      send($room, $snd);
      return $snd;
    default:  
      return false;  
  }
}