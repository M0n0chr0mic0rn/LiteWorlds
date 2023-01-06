<?php
// omni help
if ($_GET["method"] === 'omni-help') {
    header('Content-type: application/json; charset=utf-8');
    if (isset($_GET["command"])) {
        $omni->help($_GET["command"]);
    } else {
        $omni->help('');
    }
}

if ($_GET["method"] === 'omni-help1') {
    header('Content-type: application/json; charset=utf-8');
    echo $omni->GetNFTss();
}
// omni user data
if ($_GET["method"] === 'getomni') {
    if (isset($_GET["authkey"]) && !is_null($_GET['authkey'])) {
        $user->lastAction($_GET['authkey']);
        $data = $user->get($_GET["authkey"]);
        if ($data) {
            echo json_encode($omni->getAddress($data->User),JSON_PRETTY_PRINT);
            $counter->increase($_GET['method']);
        }    
    } else {
        echo '{"answer":"Where is the key?", "bool":0}';
    }
}
// omni private key
if ($_GET["method"] === 'omni-private-key') {
    if (isset($_GET["authkey"]) && !is_null($_GET['authkey'])) {
        $user->lastAction($_GET['authkey']);
        $data = $user->get($_GET["authkey"]);
        $data = $user->Pget($data->User);
        if ($data) {
            $omni->getprivkey($data);
        }    
    } else {
        echo '{"answer":"Where is the key?", "bool":0}';
    }
}

if ($_GET["method"] === 'omni-get-nft-public') {
    header('Content-type: application/json; charset=utf-8');
    if (isset($_GET["propertyid"])) {
        if (!isset($_GET["tokenstart"])) {
            $ts = 1;
        } else {
            $ts = $_GET["tokenstart"];
        }
        $omni->publicNFTview($_GET["propertyid"], $ts);
    }
}
// omni get nfts
if ($_GET["method"] === 'getnft') {
    header('Content-type: application/json; charset=utf-8');
    if (isset($_GET["authkey"]) && !is_null($_GET['authkey']) && isset($_GET["propertyid"])) {
        $user->lastAction($_GET['authkey']);
        $data = $user->get($_GET["authkey"]);
        if ($data) {
            $data = $omni->getAddress($data->User);
            echo json_encode($omni->getNFTs($data->address, $_GET['propertyid'], $_GET['tokenstart']), JSON_PRETTY_PRINT);
            $counter->increase($_GET['method']);
        }
    } else {
        echo '{"answer":"Where is the key?", "bool":0}';
    }
}
// omni get collections
if ($_GET["method"] === 'getnftcollections') {
    header('Content-type: application/json; charset=utf-8');
    if (isset($_GET["authkey"]) && !is_null($_GET['authkey'])) {
        $user->lastAction($_GET['authkey']);
        $data = $user->get($_GET["authkey"]);
        if ($data) {
            $data = $omni->getAddress($data->User);
            echo json_encode($omni->getCollections($data->address), JSON_PRETTY_PRINT);
            $counter->increase($_GET['method']);
        }
    } else {
        echo '{"answer":"Where is the key?", "bool":0}';
    }
}
// prepare omni create property
if ($_GET["method"] === 'omnicreateproperty') {
    if (isset($_GET["authkey"]) && !is_null($_GET['authkey']) && isset($_GET["user"]) && isset($_GET["category"]) && isset($_GET["subcategory"]) && isset($_GET["url"]) && isset($_GET["data"])) {
        $user->lastAction($_GET['authkey']);
        $data = $user->get($_GET["authkey"]);
        if ($data) {
            $omni->prepareCreateProperty($data, $_GET["user"], $_GET["category"], $_GET["subcategory"], $_GET["url"], $_GET["data"]);
            $counter->increase($_GET['method']);
        }
    } else {
        echo '{"answer":"Where is the key?", "bool":0}';
    }
}

// sign omni create property
if ($_GET["method"] === 'Vomnicreateproperty') {
    if (isset($_GET['user']) && isset($_GET['copperkey']) && isset($_GET['jadekey']) && isset($_GET['crystalkey'])) {
        $omni->createProperty($_GET['user'], $_SERVER['REMOTE_ADDR'], $_GET['copperkey'], $_GET['jadekey'], $_GET['crystalkey']);
        $counter->increase($_GET['method']);
    } else {
        echo '{"answer":"Where is the key?", "bool":0}';
    }
}

// prepare omni mint nft
if ($_GET["method"] === 'omnimintnft') {
    if (isset($_GET["authkey"]) && !is_null($_GET['authkey']) && isset($_GET["propertyid"]) && isset($_GET["grantdata"])) {
        $user->lastAction($_GET['authkey']);
        $data = $user->get($_GET["authkey"]);
        if ($data) {
            print_r($_GET["grantdata"]);echo '<br>';
            $omni->prepareMintNFT($data, $_GET["propertyid"], $_GET["grantdata"]);
            $counter->increase($_GET['method']);
        }
    } else {
        echo '{"answer":"Where is the key?", "bool":0}';
    }
}

// sign mint nft
if ($_GET["method"] === 'Vomnimintnft') {
    if (isset($_GET['name']) && isset($_GET['copperkey']) && isset($_GET['jadekey']) && isset($_GET['crystalkey'])) {
        $omni->mintNFT($_GET['name'], $_SERVER['REMOTE_ADDR'], $_GET['copperkey'], $_GET['jadekey'], $_GET['crystalkey']);
        $counter->increase($_GET['method']);
    } else {
        echo '{"answer":"Where is the key?", "bool":0}';
    }
}

// prepare Omni Send
if ($_GET["method"] === 'omni-send') {
    if (isset($_GET["authkey"]) && !is_null($_GET['authkey']) && isset($_GET['address']) && isset($_GET['amount'])) {
        $user->lastAction($_GET['authkey']);
        $data = $user->get($_GET["authkey"]);
        $data = $user->Pget($data->User);
        if ($data && $_GET['amount'] >= 0.001) {
            //$counter->increase('getkotia');
            echo $omni->prepareSend($data, $_GET['address'], $_GET['amount']);
            $counter->increase($_GET['method']);
        }
    } else {
        echo '{"answer":"Where is the key?", "bool":0}';
    }
}

// sign Omni Send
if ($_GET["method"] === 'Vsendomni') {
    if (isset($_GET['user']) && isset($_GET['copperkey']) && isset($_GET['jadekey']) && isset($_GET['crystalkey'])) {
        $omni->send($_GET['user'], $_SERVER['REMOTE_ADDR'], $_GET['copperkey'], $_GET['jadekey'], $_GET['crystalkey']);
        $counter->increase($_GET['method']);
    }
}

// prepare omni nft send
if ($_GET["method"] === 'sendomninft') {
    if (isset($_GET["authkey"]) && !is_null($_GET['authkey']) && isset($_GET['to']) && isset($_GET['propertyid']) && isset($_GET['start'])) {
        $user->lastAction($_GET['authkey']);
        $data = $user->get($_GET["authkey"]);
        if ($data) {
            //$counter->increase('getkotia');
            echo $omni->prepareSendNFT($data, $_GET['to'], $_GET['propertyid'], $_GET['start']);
            $counter->increase($_GET['method']);
        }
    } else {
        echo '{"answer":"Where is the key?", "bool":0}';
    }
}

// sign Omni nft Send
if ($_GET["method"] === 'Vsendomninft') {
    if (isset($_GET['user']) && isset($_GET['copperkey']) && isset($_GET['jadekey']) && isset($_GET['crystalkey'])) {
        $omni->sendNFT($_GET['user'], $_SERVER['REMOTE_ADDR'], $_GET['copperkey'], $_GET['jadekey'], $_GET['crystalkey']);
        $counter->increase($_GET['method']);
    }
}

// history
if ($_GET["method"] === 'omni-history') {
    header('Content-type: application/json; charset=utf-8');
    if (isset($_GET["authkey"]) && !is_null($_GET['authkey'])) {
        if (isset($_GET['start'])) {
            $start = isset($_GET['start']);
        } else {
            $start = 0;
        }
        $user->lastAction($_GET['authkey']);
        $data = $user->get($_GET["authkey"]);
        if ($data) {
            $omni->history($data->User, $start);
            $counter->increase($_GET['method']);
        }    
    }
}
