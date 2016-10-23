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
 * Publish RSS feeds to a chatroom
 *
 * @author Christoph Burschka <christoph@burschka.de>
 * @author Keira Sylae Aro <sylae@calref.net>
 */
class RSS {

  function __construct($url, $rooms, $ttl = 300) {
    global $db;
    $this->url = $url;
    $this->ttl = $ttl;
    $this->rooms = $rooms;
    $sql = $db->executeQuery('SELECT request, latest FROM rss WHERE url=? ORDER BY request DESC LIMIT 1;', [$url]);
    $result = $sql->fetchAll();
    $this->last = array_key_exists(0, $result) ? $result[0] : ["request" => 0, "latest" => 0];
    $this->updateLast = $db->prepare('
         INSERT INTO rss (url, request, latest) VALUES(?, ?, ?)
         ON DUPLICATE KEY UPDATE request=VALUES(request), latest=VALUES(latest);', ['string', 'integer', 'integer']);
    // Update once on startup, and then every TTL seconds.
    $this->update();
    \JAXLLoop::$clock->call_fun_periodic($this->ttl * 1000000, function () {
      $this->update();
    });
  }

  function update() {
    global $client;
    $this->last['request'] = time();
    $curl = new \Curl\Curl();
    $lv = V_LIGREV;
    $curl->setUserAgent("ligrev/$lv (https://github.com/sylae/ligrev)");
    $curl->get($this->url);
    if ($curl->error) {
      \Monolog\Registry::CORE()->warning("Failed to retrieve RSS feed", ['code' => $curl->errorCode, 'msg' => $curl->errorMessage]);
      curl_close($curl->curl);
      return false;
    }
    curl_close($curl->curl);
    $data = \qp($curl->response);
    $items = $data->find('item');
    $newest = $this->last['latest'];
    $newItems = [];
    foreach ($items as $item) {
      $published = strtotime($item->find('pubDate')->text());
      if ($published <= $this->last['latest'])
        continue;
      $newest = max($newest, $published);
      $newItems[] = (object) [
                  'channel' => $item->parent('channel')->find('channel>title')->text(),
                  'title' => $item->find('title')->text(),
                  'link' => $item->find('link')->text(),
                  'date' => $published,
                  'category' => $item->find('category')->text(),
                  'body' => $item->find('description')->text(),
      ];
    }
    $this->updateLast->bindValue(1, $this->url, "string");
    $this->updateLast->bindValue(2, $this->last['request'], "integer");
    $this->last['latest'] = $newest;
    $this->updateLast->bindValue(3, $this->last['latest'], "integer");
    $this->updateLast->execute();
    foreach ($newItems as $item) {
      $message = sprintf(_("New post in _%s_ / %s: [%s](%s)"), $item->channel, $item->category, $item->title, $item->link);
      foreach ($this->rooms as $room) {
        ligrevGlobals::sendMessage($room, $message, true, "groupchat");
      }
    }
  }

}
