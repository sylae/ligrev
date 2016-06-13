<?php

/*
 * Copyright (C) 2016 Sylae Jiendra Corell <sylae@calref.net>
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
 * This is the ligrev core class - everything should be done from here.
 *
 * @author Sylae Jiendra Corell <sylae@calref.net>
 */
class Ligrev {

  /**
   * @var array
   */
  public $config = [];

  /**
   * @var \JAXL
   */
  public $client;

  /**
   * @var \Doctrine\DBAL\Connection
   */
  public $db;

  /**
   * @var \JAXLEvent
   */
  public $ev;

  /**
   * @var boolean
   */
  public $isStartup = true;

  /**
   * @var array
   */
  public $disco = [];

  function __construct($config) {

    // register hook on_default_config

    $this->config = $config;
    $this->init_jaxl();
    $this->load_plugins();
    $this->connect_db();
  }

  private function load_plugins() {
    // TODO: get all classes that impliment
    // Ligrev\Plugin or something.
    // call ::pluginLoad();
  }

  private function get_service_disco_stanza() {
    /**
     * Why not use JAXL->set_status()? Well, a very popular XMPP messenger
     * buggily does not send a service disco (0030) if the client it is interested
     * in doesn't support XEP-0115. So we've basically had to rewrite JAXL's
     * presence info here, to bodge in XEP-0115 saupport.
     *
     * Also, JAXL's XEP-0115 xep/ class is...lacking.
     */
    $S = "";
    $S_id = [];

    // TODO: Register hook on_service_disco. Hook adds additional consts to $this->disco


    foreach ($this->disco['identity'] as $id) {
      $S_id[] = "{$id[0]}/{$id[1]}/{$id[2]}/{$id[3]}<";
    }
    sort($S_id);
    foreach ($S_id as $id) {
      $S .= $id;
    }
    foreach ($this->disco['features'] as $feature) {
      $S .= $feature . "<";
    }

    $pres = new \XMPPPres(['from' => $this->client->full_jid->to_string()], '', 'chat', 10);
    $pres->id = $this->client->get_id();
    $pres->c("c", NS_CAPS, ['hash' => 'sha-1', 'node' => 'https://github.com/sylae/ligrev', 'ver' => base64_encode(sha1($S, true))]);
    return $pres;
  }

  private function connect_db() {
    $this->db = \Doctrine\DBAL\DriverManager::getConnection(
        ['url' => $this->config['db']], new \Doctrine\DBAL\Configuration());
    $sm = $this->db->getSchemaManager();
    $fromSchema = $sm->createSchema();

    // Initialize existing schema database.
    $schema = new \Doctrine\DBAL\Schema\Schema();

    // TODO: Register hook on_db_schema. pass $schema to hook.

    $comparator = new \Doctrine\DBAL\Schema\Comparator();
    $schemaDiff = $comparator->compare($fromSchema, $schema);
    $sql = $schemaDiff->toSaveSql($this->db->getDatabasePlatform());
    $total_changes = count($sql);

    if ($total_changes > 0) {
      \Monolog\Registry::DB()->info("Schema needs initialization or upgrade", ["statements_to_execute" => $total_changes]);
      foreach ($sql as $s) {
        // TODO: re-impliment once testing is done.
        var_dump($s);
        // $this->db->exec($s);
      }
    } else {
      \Monolog\Registry::DB()->info("Schema up to date", ["statements_to_execute" => $total_changes]);
    }
  }

  private function init_jaxl() {
    $this->client = new \JAXL($this->config['jaxl']);

    $this->client->require_xep([
      '0045', // MUC
      '0203', // Delayed Delivery
      '0199'  // XMPP Ping
    ]);
  }

  public function register_hook($hook, $callback) {

  }

}
