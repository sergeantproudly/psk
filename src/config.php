<?php

$global = require 'config/config.global.php';

if (getenv('DEV')) {
  $local = require('config/config.dev.php');
} else {
  $local = require('config/config.prod.php');
}

$Config = array_merge_recursive($local, $global);

return $Config;
