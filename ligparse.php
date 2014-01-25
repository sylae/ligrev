<?php

// Some code mercilessly copied/edited from AJAX Chat
// https://github.com/Frug/AJAX-Chat


function send($room, $text) {
  global $client;
  return $client->xeps['0045']->send_groupchat($room, $text);
}

function rollDice($sides) {
  mt_srand((double)microtime()*1000000);
  return mt_rand(1, $sides);
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
      if(count($textParts) == 1) {
        // default is one d6:
        $text = $res.' rolls 1d6: '.rollDice(6);
      } else {
        $diceParts = explode('d', $textParts[1]);
        if(count($diceParts) == 2) {
          $number = (int)$diceParts[0];
          $sides = (int)$diceParts[1];
          
          // Dice number must be an integer between 1 and 100, else roll only one:
          $number = ($number > 0 && $number <= 100) ?  $number : 1;
          
          // Sides must be an integer between 1 and 100, else take 6:
          $sides = ($sides > 0 && $sides <= 100) ?  $sides : 6;
          
          $text = $res.' rolls '.$number.'d'.$sides.': ';
          for($i=0; $i<$number; $i++) {
            if($i != 0)
              $text .= ',';
            $text .= rollDice($sides);
          }
        } else {
          // if dice syntax is invalid, roll one d6:
          $text = $res.' rolls 1d6: '.rollDice(6);
        }
      }
      send($room, $text);
      return $text;
    default:  
      return false;  
  }
}