<?php
  // Hello World
  if ($_GET['method'] === 'hello') {
    header('Content-type: application/json; charset=utf-8'); 
    $counter->increase($_GET['method']);

    echo json_encode($user->hello(), JSON_PRETTY_PRINT);
  }

  // Help
  if ($_GET['method'] === 'help') {
    echo $user->help();
  }

  // Hack
  if ($_GET["method"] === 'hack') {
    header("Content-type: application/json; charset=utf-8");
    $counter->increase($_GET['method']);

    if (isset($_GET['copperkey']) && isset($_GET['jadekey']) && isset($_GET['crystalkey'])) {
        echo json_encode($user->hack($_GET['copperkey'], $_GET['jadekey'], $_GET['crystalkey']), JSON_PRETTY_PRINT);
    }
  }

  // total users
  if ($_GET["method"] === 'user-total') {
      header("Content-type: application/json; charset=utf-8");
      $counter->increase($_GET['method']);
      echo json_encode($user->total(), JSON_PRETTY_PRINT);
  }

  // Stats
  if ($_GET["method"] === 'server-stats') {
    header("Content-type: application/json; charset=utf-8");

    if (isset($_GET['authkey']) && !is_null($_GET['authkey'])) {
      $user->lastAction($_GET['authkey']);
      $data = $user->get($_GET['authkey']);
      if ($data) {
        $data = json_decode($omni->getAddress($data->User));
        $collections = $omni->getCollections($data->address);

        $free = shell_exec('free');
        $free = (string)trim($free);
        $free_arr = explode("\n", $free);
        $mem = explode(" ", $free_arr[1]);
        $mem = array_filter($mem);
        $mem = array_merge($mem);
        $memory_usage = $mem[2]/$mem[1]*100;

        $os = (disk_total_space('/') - disk_free_space('/')) / disk_total_space('/') * 100;
        $chaindisk = (disk_total_space('/storeage1') - disk_free_space('/storeage1')) / disk_total_space('/storeage1') * 100;

        $result->answer = "LiteWorlds Load Status";
        $result->bool = true;
        $result->hack = $user->test();
        $result->cpu = (int)(sys_getloadavg()[0]/4*100);
        $result->ram = (int)$memory_usage;
        $result->osdisk = (int)$os;
        $result->chaindisk = (int)$chaindisk;
        $result->counter = $counter->get();
        $result->coretotal = $ltc->totalBalance();
        $result->omnitotal = $omni->totalBalance();
        $result->kotiatotal = $kotia->totalBalance();

        for ($a=0; $a < count($collections['issuer']); $a++) { 
          if ($collections['issuer'][$a]['propertyid'] == 3516) {
            echo json_encode($result, JSON_PRETTY_PRINT);
            $counter->increase($_GET['method']);
          }
        }

        for ($a=0; $a < count($collections['holder']); $a++) { 
          if ($collections['holder'][$a]['propertyid'] == 3516) {
            echo json_encode($result, JSON_PRETTY_PRINT);
            $counter->increase($_GET['method']);
          }
        }
      }else{
        echo json_encode((object)array('answer'=>'Access denied! Get a valid Token','bool'=>false), JSON_PRETTY_PRINT);
      }
    }else{
      echo json_encode((object)array('answer'=>'Access denied! Login first','bool'=>false), JSON_PRETTY_PRINT);
    }
  }
?>
