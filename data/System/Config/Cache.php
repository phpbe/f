<?php
namespace Be\Data\System\Config;

class Cache
{
  public $driver = 'File';
  public $redis = array (
  'host' => '127.0.0.1',
  'port' => 6379,
  'timeout' => 10,
  'persistent' => false,
  'password' => '',
  'db' => 0,
);
}
