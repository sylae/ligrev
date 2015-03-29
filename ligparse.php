<?php

// Some code mercilessly copied/edited from AJAX Chat and donjon
// https://github.com/Frug/AJAX-Chat
// http://donjon.bin.sh/



function parseCustomCommands($text, $textParts, $room, $res, $jid) {
  global $config, $client;
  switch($textParts[0]) {
    case '/card':
    case '!card':
    case ':card':
      $c = rollDice(54);
      $suits = array(
        'Hearts',
        'Diamonds',
        'Clubs',
        'Spades',
      );
      $nums = array(
        'Ace',
        '2',
        '3',
        '4',
        '5',
        '6',
        '7',
        '8',
        '9',
        '10',
        'Jack',
        'Queen',
        'King',
      );
      if ($c > 52) {
        $card = "Joker";
      } else {
        $card = $nums[($c-1) % 13]." of ".$suits[($c-1) % 4];
      }
      l("[CARD] Dice rolled a ".$c, L_DEBUG);
      $snd = $res.' draws a '.$card;
      send($room, $snd);
      return $snd;
    case '/diag':
    case '!diag':
    case ':diag':
      $string = 'Ligrev Diagnostic Information'.PHP_EOL.
                'Ligrev Version: '.trim(`git rev-parse HEAD`).PHP_EOL.
                'PHP Version: '.phpversion().PHP_EOL.
                'JAXL Version: '.trim(`cd lib/JAXL && git rev-parse HEAD`).PHP_EOL.
                'Process ID '.getmypid().' as '.get_current_user().PHP_EOL.
                'System: '.php_uname();
      send($room, $string);
      return $text;
    case '/sybeam':
    case '!sybeam':
    case ':sybeam':
      if ($jid->to_string() == "lounge@conference.calref.net/sylae") {
        $num = max(1, min(100, (int)pipeToBc((array_key_exists(1, $textParts) ? $textParts[1] : 1))));
        $string = str_repeat(':sybeam:',$num);
        send($room, $string);
      }
      return $text;
    default:  
      return false;  
  }
}