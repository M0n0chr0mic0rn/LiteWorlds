<?php
  header('Access-Control-Allow-Origin:*');

  require_once 'maria-user.php'; $user = new User;
  require_once 'maria-kotia.php'; $kotia = new Kotia;
  require_once 'maria-litecoin.php'; $ltc = new Litecoin;
  require_once 'maria-omni.php'; $omni = new Omnilite;
  require_once 'counter.php'; $counter = new Counter;

  if (isset($_GET['method']) && sys_getloadavg()[0] < 3.7) {
    include 'public.php';
    include 'user.php';
    include 'core.php';
    include 'omni.php';
    include 'kotia.php';
  }
