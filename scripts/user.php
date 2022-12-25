<?php
// Register
if ($_GET["method"] === 'user-register') {
    header("Content-type: application/json; charset=utf-8");

    if (isset($_GET['user']) && isset($_GET['mail']) && isset($_GET['pass'])) {
        $counter->increase($_GET['method']);
        echo json_encode($user->prepareRegister($_GET['user'], $_GET['mail'], $_GET['pass']), JSON_PRETTY_PRINT);
    }else{
        echo json_encode((object)array('answer'=>'Missing Parameters','bool'=>false));
    }
}

// Verify Register
if ($_GET["method"] === 'user-register-sign') {
    if (isset($_GET['user']) && isset($_GET['copperkey']) && isset($_GET['jadekey']) && isset($_GET['crystalkey'])) {
        echo json_encode($user->register($_GET['user'], $_GET['copperkey'], $_GET['jadekey'], $_GET['crystalkey']));
        $counter->increase($_GET['method']);
    }else{
        echo json_encode((object)array('answer'=>'Missing Parameters','bool'=>false));
    }
}

// Login
if ($_GET["method"] === 'user-login') {
    header("Content-type: application/json; charset=utf-8");

    if (isset($_GET['user']) && isset($_GET['pass'])) {
        if (isset($_GET['ip'])) {
            $IP = $_GET['ip'];
        } else {
            $IP = '0.0.0.0';
        }
        echo json_encode($user->prepareLogin($_GET['user'], $_GET['pass'], $IP), JSON_PRETTY_PRINT);
        $counter->increase($_GET['method']);
    }else{
        echo json_encode((object)array('answer'=>'Missing Parameters','bool'=>false));
    }
}

// Verify Login
if ($_GET["method"] === 'user-login-sign') {
    if (isset($_GET['name']) && isset($_GET['copperkey']) && isset($_GET['jadekey']) && isset($_GET['crystalkey'])) {
        $answer = $user->login($_GET['name'], $_SERVER['REMOTE_ADDR'], $_GET['copperkey'], $_GET['jadekey'], $_GET['crystalkey']);

        if ($answer === 'User online') {
            $counter->increase($_GET['method']);
            echo '<script>window.close()</script>';
        } else {
            echo 'Oops, something went wrong. U have not been logged in';
        }
    }
}

// Logout
if ($_GET["method"] === 'user-logout') {
    if (isset($_GET['authkey']) && !is_null($_GET['authkey'])) {
        $user->lastAction($_GET['authkey']);
        $data = $user->logout($_GET['authkey']);
        if ($data) {
            echo '{"answer":"Logout successful", "bool":1}';
            $counter->increase($_GET['method']);
        }else{
            echo '{"answer":"Logout failed", "bool":0}';
        }
    }else{
        echo '{"answer":"Where is the key?", "bool":0}';
    }
}

// Is User online?
if ($_GET["method"] === 'user-online') {
    header("Content-type: application/json; charset=utf-8");
    if (isset($_GET['authkey'])) {
        if (!is_null($_GET['authkey'])) {
            echo json_encode($user->online($_GET['authkey']), JSON_PRETTY_PRINT);
            $counter->increase($_GET['method']);
        }
    }else{
        echo '{"answer":"Where is the key?", "bool":0}';
    }
}

// Is User in a Memory-Table?
if ($_GET["method"] === 'user-memorytablecheck') {
    if (isset($_GET['user']) && isset($_GET['table'])) {
        echo $user->HTcheck($_GET['user'], $_GET['table']);
        $counter->increase($_GET['method']);
    }else{
        echo '{"answer":"Parameters missing", "bool":0}';
    }
}

// Get Userdata
if ($_GET["method"] === 'user-get') {
    if (isset($_GET['authkey']) && !is_null($_GET['authkey'])) {
        $user->lastAction($_GET['authkey']);
        $data = $user->get($_GET['authkey']);
        if ($data) {
            $data->answer = 'I found this data with the key';
            $data->bool = true;

            echo json_encode($data, JSON_PRETTY_PRINT);
            $counter->increase($_GET['method']);
        }else{
            echo json_encode((object)array('answer'=>'I found no data with this key','bool'=>false));
        }
    }else{
        echo (object)array('answer'=>'Where is the key?','bool'=>false);
    }

}

// prepare ipLock
if ($_GET["method"] === 'user-iplock') {
    if (isset($_GET['authkey']) && !is_null($_GET['authkey'])) {
        $user->lastAction($_GET['authkey']);
        $bool = $user->prepareSetIPLock($_GET['authkey']);
        if ($bool) {
            echo '{"answer":"ipLock is prepared", "bool":1}';
            $counter->increase($_GET['method']);
        }else{
            echo '{"answer":"Could not prepare ipLock", "bool":0}';
        }
    }else{
        echo '{"answer":"Missing Parameters", "bool":0}';
    }
}

// Verify ipLock
if ($_GET["method"] === 'user-iplock-sign') {
    if (isset($_GET['user']) && isset($_GET['copperkey']) && isset($_GET['jadekey']) && isset($_GET['crystalkey'])) {
        $result = $user->setIPLock($_GET['user'], $_SERVER['REMOTE_ADDR'], $_GET['copperkey'], $_GET['jadekey'], $_GET['crystalkey']);

        if ($result) {
            $counter->increase($_GET['method']);
            echo '<script>window.close()</script>';
        } else {
            echo 'Oops, something went wrong. Ur IP-Lock has not changed';
        }
    }
}

// prepare change pass
if ($_GET['method'] === 'user-change-pass') {
    if (isset($_GET['authkey']) && !is_null($_GET['authkey']) && isset($_GET['oldpass']) && isset($_GET['newpass'])) {
        $user->lastAction($_GET['authkey']);
        $data = $user->get($_GET['authkey']);
        if ($data) {
            if (isset($_GET['ip'])) {
                $IP = $_GET['ip'];
            } else {
                $IP = '0.0.0.0';
            }
            echo json_encode($user->preparePassChange($data->User, $_GET['oldpass'], $_GET['newpass'], $IP), JSON_PRETTY_PRINT);
            $counter->increase($_GET['method']);
        }else{
            echo json_encode((object)array('answer'=>'I found no data with this key','bool'=>false));
        }
    }
}

// Verify change pass
if ($_GET['method'] === 'user-change-pass-sign') {
    if (isset($_GET['user']) && isset($_GET['copperkey']) && isset($_GET['jadekey']) && isset($_GET['crystalkey'])) {
        $user->PassChange($_GET['user'], $_SERVER['REMOTE_ADDR'], $_GET['copperkey'], $_GET['jadekey'], $_GET['crystalkey']);
    }
}

// prepare change mail
if ($_GET['method'] === 'user-change-mail') {
    if (isset($_GET['authkey']) && !is_null($_GET['authkey']) && isset($_GET['oldmail']) && isset($_GET['newmail'])) {}
}

// Verify change mail
if ($_GET['method'] === 'user-change-mail-sign') {
    if (isset($_GET['user']) && isset($_GET['copperkey']) && isset($_GET['jadekey']) && isset($_GET['crystalkey'])) {}
}
?>
