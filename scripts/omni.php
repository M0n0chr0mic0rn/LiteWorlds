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
if ($_GET["method"] === 'omni-property-get') {
    header('Content-type: application/json; charset=utf-8');
    if (isset($_GET["authkey"]) && !is_null($_GET['authkey'])) {
        $user->lastAction($_GET['authkey']);
        $userdata = $user->get($_GET["authkey"]);
        if ($userdata) {
            echo json_encode($omni->getPropertys($userdata), JSON_PRETTY_PRINT);
            $counter->increase($_GET['method']);
        }
    } else {
        echo '{"answer":"Where is the key?", "bool":0}';
    }
}
// prepare omni create property
if ($_GET["method"] === 'omni-property-create') {
    header('Content-type: application/json; charset=utf-8');
    $result->bool = false;
    if (isset($_GET['authkey']) && !is_null($_GET['authkey'])) {
        // fixed or managed ???
        if (isset($_GET['fixed'])) {
            if ($_GET['fixed'] == 0 || $_GET['fixed'] == 1) {
                $fixed = $_GET['fixed'];
            } else {
                $result->answer = 'fixed wrong value, 0 = managed, 1 = fixed';
                echo json_encode($result, JSON_PRETTY_PRINT);
            }
        } else {
            $result->answer = 'fixed missing';
            echo json_encode($result, JSON_PRETTY_PRINT);
        }

        // main or test ???
        if (isset($_GET['ecosystem'])) {
            if ($_GET['ecosystem'] == 1 || $_GET['ecosystem'] == 2) {
                $ecosystem = $_GET['ecosystem'];
            } else {
                $result->answer = 'ecosystem wrong value, 1 = main, 2 = test';
                echo json_encode($result, JSON_PRETTY_PRINT);
            }
        } else {
            $result->answer = 'ecosystem missing';
            echo json_encode($result, JSON_PRETTY_PRINT);
        }

        // indivisible, divisible or non-fungilbe ???
        if (isset($_GET['type']) && $fixed == 0) {
            if ($_GET['type'] == 1 || $_GET['type'] == 2 || $_GET['type'] == 5) {
                $type = $_GET['type'];
            } else {
                $result->answer = 'type wrong value, 1 = indivisible, 2 = divisible, 5 = non-fungible';
                echo json_encode($result, JSON_PRETTY_PRINT);
            }
        } 
        if (isset($_GET['type']) && $fixed == 1) {
            if ($_GET['type'] == 1 || $_GET['type'] == 2) {
                $type = $_GET['type'];
            } else {
                $result->answer = 'type wrong value, 1 = indivisible, 2 = divisible';
                echo json_encode($result, JSON_PRETTY_PRINT);
            }
        } 
        if (!isset($_GET['type'])) {
            $result->answer = 'type missing';
            echo json_encode($result, JSON_PRETTY_PRINT);
        }

        // previousid
        $previousid = 0;

        // category
        if (isset($_GET['category']) && $_GET['category'] != '') {
            $category = str_replace('LWQ-PH-000', '#', $_GET['category']);
        } else {
            $result->answer = 'Category cant be empty';
            echo json_encode($result, JSON_PRETTY_PRINT);
        }

        // subcategory
        if (isset($_GET['subcategory']) && $_GET['subcategory'] != '') {
            $subcategory = str_replace('LWQ-PH-000', '#', $_GET['subcategory']);
        } else {
            $result->answer = 'Subcategory cant be empty';
            echo json_encode($result, JSON_PRETTY_PRINT);
        }

        // name
        if (isset($_GET['name']) && $_GET['name'] != '') {
            $name = str_replace('LWQ-PH-000', '#', $_GET['name']);
        } else {
            $result->answer = 'Name cant be empty';
            echo json_encode($result, JSON_PRETTY_PRINT);
        }

        // url
        if (isset($_GET['url']) && $_GET['url'] != '') {
            $url = str_replace('LWQ-PH-000', '#', $_GET['url']);
        } else {
            $url = '';
        }
        
        // data
        if (isset($_GET['data']) && $_GET['data'] != '') {
            $propertydata = str_replace('LWQ-PH-000', '#', $_GET['data']);
        } else {
            $propertydata = '';
        }

        // amount
        if ($fixed == 1) {
            if (isset($_GET['amount'])) {
                $amount = $_GET['amount'];
            } else {
                $result->answer = 'fixed property needs an amount';
                echo json_encode($result, JSON_PRETTY_PRINT);
            }
        } else {
            $amount = 0;
        }

        $user->lastAction($_GET['authkey']);
        $userdata = $user->get($_GET["authkey"]);
        $userdata = $user->Pget($userdata->User);
        if ($userdata) {
            $omni->prepareCreateProperty($userdata, (int)$fixed, (int)$ecosystem, (int)$type, $previousid, $category, $subcategory, $name, $url, $propertydata, $amount);
            $counter->increase($_GET['method']);
        }
    } else {
        echo '{"answer":"Where is the key?", "bool":0}';
    }
}

// sign omni create property
if ($_GET["method"] === 'omni-property-create-sign') {
    if (isset($_GET['copperkey']) && isset($_GET['jadekey']) && isset($_GET['crystalkey'])) {
        $omni->createProperty($_SERVER['REMOTE_ADDR'], $_GET['copperkey'], $_GET['jadekey'], $_GET['crystalkey']);
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
            //$omni->prepareMintNFT($data, $_GET["propertyid"], $_GET["grantdata"]);
            $counter->increase($_GET['method']);
        }
    } else {
        echo '{"answer":"Where is the key?", "bool":0}';
    }
}

// sign mint nft
if ($_GET["method"] === 'Vomnimintnft') {
    if (isset($_GET['name']) && isset($_GET['copperkey']) && isset($_GET['jadekey']) && isset($_GET['crystalkey'])) {
        //$omni->mintNFT($_GET['name'], $_SERVER['REMOTE_ADDR'], $_GET['copperkey'], $_GET['jadekey'], $_GET['crystalkey']);
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
        //var_dump($data);
        if ($data) {
            if ($_GET['amount'] >= 0.001) {
                echo $omni->prepareSend($data, $_GET['address'], $_GET['amount']);
                $counter->increase($_GET['method']);
            } else {
                echo '{"answer":"amount to low", "bool":0}';
            }
        } else {
            echo '{"answer":"Invalid AuthKey", "bool":0}';
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

if ($_GET["method"] === 'dontdothis123') {
    header('Content-type: application/json; charset=utf-8');
    //$omni->testpayload();
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

// prepare set nft price
if ($_GET["method"] === 'omni-setprice') {
    header('Content-type: application/json; charset=utf-8');
    if (isset($_GET["authkey"]) && !is_null($_GET['authkey']) && isset($_GET['propertyid']) && isset($_GET['tokenid']) && isset($_GET['price'])) {
        $user->lastAction($_GET['authkey']);
        $data = $user->get($_GET["authkey"]);
        $data = $user->Pget($data->User);
        if ($data) {
            $omni->prepareSetPrice($data, $_GET['propertyid'], $_GET['tokenid'], $_GET['price']);
            $counter->increase($_GET['method']);
        }    
    }
}

// sign set nft price
if ($_GET["method"] === 'omni-setprice-sign') {
    if (isset($_GET['copperkey']) && isset($_GET['jadekey']) && isset($_GET['crystalkey'])) {
        $omni->setPrice($_SERVER['REMOTE_ADDR'], $_GET['copperkey'], $_GET['jadekey'], $_GET['crystalkey']);
        $counter->increase($_GET['method']);
    }
}

// prepare listing
if ($_GET["method"] === 'omni-listing-create') {
    header('Content-type: application/json; charset=utf-8');
    if (isset($_GET["authkey"]) && !is_null($_GET['authkey']) && isset($_GET['propertyid']) && isset($_GET['tokenid'])) {
        $user->lastAction($_GET['authkey']);
        $data = $user->get($_GET["authkey"]);
        $data = $user->Pget($data->User);
        if ($data) {
            $omni->prepareListing($data, $_GET['propertyid'], $_GET['tokenid']);
            $counter->increase($_GET['method']);
        }    
    }
}

// sign listing
if ($_GET["method"] === 'omni-listing-create-sign') {
    if (isset($_GET['copperkey']) && isset($_GET['jadekey']) && isset($_GET['crystalkey'])) {
        $omni->listing($_SERVER['REMOTE_ADDR'], $_GET['copperkey'], $_GET['jadekey'], $_GET['crystalkey']);
        $counter->increase($_GET['method']);
    }
}


// prepare buy
if ($_GET["method"] === 'omni-listing-buy') {
    header('Content-type: application/json; charset=utf-8');
    if (isset($_GET["authkey"]) && !is_null($_GET['authkey']) && isset($_GET['propertyid']) && isset($_GET['tokenid'])) {
        $user->lastAction($_GET['authkey']);
        $data = $user->get($_GET["authkey"]);
        $data = $user->Pget($data->User);
        if ($data) {
            $omni->prepareTraderBuy($data, $_GET['propertyid'], $_GET['tokenid']);
            $counter->increase($_GET['method']);
        }    
    }
}

// sign buy
if ($_GET["method"] === 'omni-listing-buy-sign') {
    if (isset($_GET['copperkey']) && isset($_GET['jadekey']) && isset($_GET['crystalkey'])) {
        $omni->TraderBuy($_SERVER['REMOTE_ADDR'], $_GET['copperkey'], $_GET['jadekey'], $_GET['crystalkey']);
        $counter->increase($_GET['method']);
    }
}

// prepare cancel listing
if ($_GET["method"] === 'omni-listing-cancel') {
    header('Content-type: application/json; charset=utf-8');
    if (isset($_GET["authkey"]) && !is_null($_GET['authkey']) && isset($_GET['propertyid']) && isset($_GET['tokenid'])) {
        $user->lastAction($_GET['authkey']);
        $data = $user->get($_GET["authkey"]);
        $data = $user->Pget($data->User);
        if ($data) {
            $omni->prepareTraderCancel($data, $_GET['propertyid'], $_GET['tokenid']);
            $counter->increase($_GET['method']);
        }    
    }
}

// sign cancel listing
if ($_GET["method"] === 'omni-listing-cancel-sign') {
    if (isset($_GET['copperkey']) && isset($_GET['jadekey']) && isset($_GET['crystalkey'])) {
        $omni->TraderCancel($_SERVER['REMOTE_ADDR'], $_GET['copperkey'], $_GET['jadekey'], $_GET['crystalkey']);
        $counter->increase($_GET['method']);
    }
}
