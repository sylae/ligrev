<?php

// Some code mercilessly copied/edited from AJAX Chat and donjon
// https://github.com/Frug/AJAX-Chat
// http://donjon.bin.sh/



function parseCustomCommands($text, $textParts, $room, $res, $jid) {
  global $config, $client;
  switch($textParts[0]) {
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