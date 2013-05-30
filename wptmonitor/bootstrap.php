<?php
  /**
   * Bootstrap Doctrine.php, register autoloader specify
   * configuration attributes and load models.
   */
  require_once(dirname(__FILE__) . '/lib/vendor/doctrine/Doctrine.php');
  spl_autoload_register(array('Doctrine', 'autoload'));
  Doctrine_Core::loadModels(dirname(__FILE__).'/models');

  $manager = Doctrine_Manager::getInstance();
  $manager->setAttribute(Doctrine_Core::ATTR_AUTO_ACCESSOR_OVERRIDE, true);
  $manager->setAttribute(Doctrine_Core::ATTR_AUTOLOAD_TABLE_CLASSES, true);
  $manager->setAttribute(Doctrine_Core::ATTR_MODEL_LOADING, Doctrine_Core::MODEL_LOADING_CONSERVATIVE);

  spl_autoload_register(array('Doctrine_Core', 'modelsAutoload'));
  
  $logins = parse_ini_file("settings/bootstrap.ini");
  if (($logins===false)||(!$logins['login'])||(!$logins['password'])){
    $dsn= 'sqlite:///'.dirname(__FILE__).'/db/wpt_monitor.sqlite';
  }
  else{
    $dsn= 'mysql://'.$logins['login'].':'.$logins['password'].'@localhost/wpt_monitor';
  }
  $dataConn = Doctrine_Manager::connection($dsn,'data');
//
//  $configDsn= 'sqlite:///'.dirname(__FILE__).'/db/config.sqlite';
//  $configConn = Doctrine_Manager::connection($configDsn, 'config');
