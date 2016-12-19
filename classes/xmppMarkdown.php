<?php

/*
 * Copyright (C) 2016 Keira Sylae Aro <sylae@calref.net>
 *
 * this is licensed under mit i guess? i dont fucking know but since i'm
 * modifying mit code, i have to be mit for this bit right? i'm not a fucking
 * lawyer just pls dont sue; i just use GPL for everything because its the first
 * one i heard of and it seems to get people pretty mad about inane shit so i
 * like using it
 */

namespace Ligrev;

/**
 * Edit of the lovely Parsedown processor, with a minor fix to use XMPP's
 * limited HTML.
 *
 * @author Keira Sylae Aro <sylae@calref.net>
 */
class xmppMarkdown extends \Parsedown {

  protected function inlineStrikethrough($Excerpt) {
    if (!isset($Excerpt['text'][1])) {
      return;
    }
    if ($Excerpt['text'][1] === '~' and preg_match('/^~~(?=\S)(.+?)(?<=\S)~~/',
        $Excerpt['text'], $matches)) {
      return array(
        'extent'  => strlen($matches[0]),
        'element' => array(
          'name'       => 'span',
          'text'       => $matches[1],
          'handler'    => 'line',
          'attributes' => array(
            'style' => "text-decoration: line-through;",
          ),
        ),
      );
    }
  }

}
