<?php
  header('Access-Control-Allow-Origin:*');

  require_once '/var/www/liteworlds/scripts/maria-user.php'; $user = new User;
  require_once '/var/www/liteworlds/scripts/maria-kotia.php'; $kotia = new Kotia;
  require_once '/var/www/liteworlds/scripts/maria-core.php'; $core = new Core;
  require_once '/var/www/liteworlds/scripts/maria-omni.php'; $omni = new Omni;
  require_once '/var/www/liteworlds/scripts/counter.php'; $counter = new Counter;

  if (isset($_GET['method']) && sys_getloadavg()[0] < 3.7) {
    include '/var/www/liteworlds/scripts/public.php';
    include '/var/www/liteworlds/scripts/user.php';
    include '/var/www/liteworlds/scripts/core.php';
    include '/var/www/liteworlds/scripts/omni.php';
    include '/var/www/liteworlds/scripts/kotia.php';
  }
