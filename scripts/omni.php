<?php
if (isset($_GET['authkey'])) {
    if (!is_null($_GET['authkey'])) {
        header('Content-type: application/json; charset=utf-8');
        // data get
        if ($_GET["method"] === 'omni-get') {
            $user->lastAction($_GET['authkey']);
            $userdata = $user->get($_GET['authkey'], false);
            if ($userdata) {
                echo json_encode($omni->Wallet($userdata),JSON_PRETTY_PRINT);
                $counter->increase($_GET['method']);
            }
        }

        // litecoin send
        if ($_GET['method'] === 'omni-send') {
            if (isset($_GET['destination']) && isset($_GET['amount'])) {
                if (isset($_GET['challenger'])) {
                    $challenger = $_GET['challenger'];
                } else {
                    $challenger = '';
                }
                $user->lastAction($_GET['authkey']);
                $userdata = $user->get($_GET['authkey'], true);
                //$userdata = $user->Pget($data->User);
                //var_dump($data);
                if ($userdata) {
                    if ($_GET['amount'] >= 0.001) {
                        echo json_encode($omni->SendCreate($userdata, $_GET['destination'], (float)$_GET['amount'], $challenger), JSON_PRETTY_PRINT);
                        $counter->increase($_GET['method']);
                    } else {
                        echo '{"answer":"amount to low", "bool":0}';
                    }
                } else {
                    echo '{"answer":"Invalid AuthKey", "bool":0}';
                }
                
            } else {
                echo '{"answer":"Missing Parameters", "bool":0}';
            }
        }

        // property create
        if ($_GET['method'] === 'omni-mint-property') {
            $result = (object)array();
            $result->bool = false;
            
            // fixed or managed ???
            if (isset($_GET['fixed'])) {
                if ($_GET['fixed'] == 0 || $_GET['fixed'] == 1) {
                    $fixed = $_GET['fixed'];
                } else {
                    $result->answer = 'fixed wrong value, 0 = managed, 1 = fixed';
                    echo json_encode($result, JSON_PRETTY_PRINT);
                    return false;
                }
            } else {
                $result->answer = 'fixed missing';
                echo json_encode($result, JSON_PRETTY_PRINT);
                return false;
            }

            // main or test ???
            if (isset($_GET['ecosystem'])) {
                if ($_GET['ecosystem'] == 1 || $_GET['ecosystem'] == 2) {
                    $ecosystem = $_GET['ecosystem'];
                } else {
                    $result->answer = 'ecosystem wrong value, 1 = main, 2 = test';
                    echo json_encode($result, JSON_PRETTY_PRINT);
                    return false;
                }
            } else {
                $result->answer = 'ecosystem missing';
                echo json_encode($result, JSON_PRETTY_PRINT);
                return false;
            }

            // indivisible, divisible or non-fungilbe ???
            if (isset($_GET['type']) && $fixed == 0) {
                if ($_GET['type'] == 1 || $_GET['type'] == 2 || $_GET['type'] == 5) {
                    $type = $_GET['type'];
                } else {
                    $result->answer = 'type wrong value, 1 = indivisible, 2 = divisible, 5 = non-fungible';
                    echo json_encode($result, JSON_PRETTY_PRINT);
                    return false;
                }
            } 
            if (isset($_GET['type']) && $fixed == 1) {
                if ($_GET['type'] == 1 || $_GET['type'] == 2) {
                    $type = $_GET['type'];
                } else {
                    $result->answer = 'type wrong value, 1 = indivisible, 2 = divisible';
                    echo json_encode($result, JSON_PRETTY_PRINT);
                    return false;
                }
            } 
            if (!isset($_GET['type'])) {
                $result->answer = 'type missing';
                echo json_encode($result, JSON_PRETTY_PRINT);
                return false;
            }

            // previousid
            $previousid = 0;

            // category
            if (isset($_GET['category']) && $_GET['category'] != '') {
                $category = str_replace('LWQ-PH-000', '#', $_GET['category']);
            } else {
                $result->answer = 'Category cant be empty';
                echo json_encode($result, JSON_PRETTY_PRINT);
                return false;
            }

            // subcategory
            if (isset($_GET['subcategory']) && $_GET['subcategory'] != '') {
                $subcategory = str_replace('LWQ-PH-000', '#', $_GET['subcategory']);
            } else {
                $result->answer = 'Subcategory cant be empty';
                echo json_encode($result, JSON_PRETTY_PRINT);
                return false;
            }

            // name
            if (isset($_GET['name']) && $_GET['name'] != '') {
                $name = str_replace('LWQ-PH-000', '#', $_GET['name']);
            } else {
                $result->answer = 'Name cant be empty';
                echo json_encode($result, JSON_PRETTY_PRINT);
                return false;
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
                    return false;
                }
            } else {
                $amount = 0;
            }

            if (isset($_GET['challenger'])) {
                $challenger = $_GET['challenger'];
            } else {
                $challenger = '';
            }

            $user->lastAction($_GET['authkey']);
            $userdata = $user->get($_GET["authkey"], true);
            if ($userdata) {
                $json = $omni->PropertyCreate($userdata, (int)$fixed, (int)$ecosystem, (int)$type, $previousid, $category, $subcategory, $name, $url, $propertydata, $amount, $challenger);
                echo json_encode($json, JSON_PRETTY_PRINT);
                $counter->increase($_GET['method']);
            }
        }

        // NFT mint
        if ($_GET['method'] === 'omni-mint-nft') {
            if (isset($_GET['property']) && isset($_GET['grantdata'])) {
                $user->lastAction($_GET['authkey']);
                $userdata = $user->get($_GET["authkey"], true);

                if ($userdata) {
                    $grantdata = $_GET['grantdata'];
                    $grantdata = str_replace('LWQ-PH-000', '#', $grantdata);
                    if (isset($_GET['challenger'])) {
                        if (substr($_GET['challenger'], 0, 4) != 'ltc1') {
                            echo json_encode((object)array("answer"=>"challenger address not Bech32 (ltc1...)","bool"=>false), JSON_PRETTY_PRINT);
                            return false;
                        } else {
                            $challenger = $_GET['challenger'];
                        }
                        
                    } else {
                        $challenger = '';
                    }
                    echo json_encode($omni->NFTMint($userdata, (int)$_GET['property'], $grantdata, $challenger), JSON_PRETTY_PRINT);
                    $counter->increase($_GET['method']);
                }
            }
        }

        // send token
        if ($_GET["method"] === 'omni-send-token') {
            if (isset($_GET['destination']) && isset($_GET['property']) && isset($_GET['amount'])) {
                $user->lastAction($_GET['authkey']);
                $userdata = $user->get($_GET["authkey"], true);
                
                if ($userdata) {
                    if (isset($_GET['challenger'])) {
                        $challenger = $_GET['challenger'];
                    } else {
                        $challenger = '';
                    }
                    echo json_encode($omni->SendToken($userdata, $_GET['destination'], (int)$_GET['property'], $_GET['amount'], $challenger), JSON_PRETTY_PRINT);
                    $counter->increase($_GET['method']);
                }
            }
        }

        // send nft
        if ($_GET["method"] === 'omni-send-nft') {
            if (isset($_GET['destination']) && isset($_GET['property']) && isset($_GET['token'])) {
                $user->lastAction($_GET['authkey']);
                $userdata = $user->get($_GET["authkey"], true);
                
                if ($userdata) {
                    if (isset($_GET['challenger'])) {
                        $challenger = $_GET['challenger'];
                    } else {
                        $challenger = '';
                    }
                    echo json_encode($omni->SendNFT($userdata, $_GET['destination'], (int)$_GET['property'], (int)$_GET['token'], $challenger), JSON_PRETTY_PRINT);
                    $counter->increase($_GET['method']);
                }
            }
        }

        // create DEX
        if ($_GET["method"] === 'omni-create-dex') {
            if (isset($_GET['property'])) {
                $user->lastAction($_GET['authkey']);
                $userdata = $user->get($_GET["authkey"], true);

                if ($userdata) {
                    echo json_encode($omni->createDEX($userdata, (int)$_GET['property'], $_GET['amount'], $_GET['desire']), JSON_PRETTY_PRINT);
                    $counter->increase($_GET['method']);
                }
            }
        }

        // cancel DEX
        if ($_GET["method"] === 'omni-cancel-dex') {
            if (isset($_GET['property'])) {
                $user->lastAction($_GET['authkey']);
                $userdata = $user->get($_GET["authkey"], true);

                if ($userdata) {
                    echo json_encode($omni->cancelDEX($userdata, (int)$_GET['property']), JSON_PRETTY_PRINT);
                    $counter->increase($_GET['method']);
                }
            }
        }

        // accept DEX
        if ($_GET["method"] === 'omni-accept-dex') {
            if (isset($_GET['property']) && isset($_GET['amount']) && isset($_GET['destination'])) {
                $user->lastAction($_GET['authkey']);
                $userdata = $user->get($_GET["authkey"], true);

                if ($userdata) {
                    echo json_encode($omni->acceptDEX($userdata, (int)$_GET['property'], $_GET['amount'], $_GET['destination']), JSON_PRETTY_PRINT);
                    $counter->increase($_GET['method']);
                }
            }
        }

        // pay DEX
        if ($_GET["method"] === 'omni-pay-dex') {
            if (isset($_GET['destination']) && isset($_GET['property']) && isset($_GET['amount'])) {
                $user->lastAction($_GET['authkey']);
                $userdata = $user->get($_GET["authkey"], false);

                if ($userdata) {
                    echo json_encode($omni->payDEX($userdata, $_GET['destination'], (int)$_GET['property'], $_GET['amount']), JSON_PRETTY_PRINT);
                }
            }
        }

        // cancel TraderBot
        if ($_GET['method'] === 'omni-cancel-trader') {
            if (isset($_GET['property']) && isset($_GET['token'])) {
                $user->lastAction($_GET['authkey']);
                $userdata = $user->get($_GET["authkey"], true);

                if ($userdata) {
                    if (isset($_GET['challenger'])) {
                        $challenger = $_GET['challenger'];
                    } else {
                        $challenger = '';
                    }
                    echo json_encode($omni->cancelTrader($userdata, (int)$_GET['property'], (int)$_GET['token'], $challenger), JSON_PRETTY_PRINT);
                }
            }
        }

        // take TraderBot
        if ($_GET['method'] === 'omni-take-trader') {
            if (isset($_GET['property']) && isset($_GET['token'])) {
                $user->lastAction($_GET['authkey']);
                $userdata = $user->get($_GET["authkey"], true);

                if ($userdata) {
                    if (isset($_GET['challenger'])) {
                        $challenger = $_GET['challenger'];
                    } else {
                        $challenger = '';
                    }
                    echo json_encode($omni->takeTrader($userdata, (int)$_GET['property'], (int)$_GET['token'], $challenger), JSON_PRETTY_PRINT);
                }
            }
        }

        // set desire TraderBot
        if ($_GET['method'] === 'omni-desire-trader') {
            if (isset($_GET['property']) && isset($_GET['token']) && isset($_GET['holderdata'])) {
                $user->lastAction($_GET['authkey']);
                $userdata = $user->get($_GET["authkey"], true);

                if ($userdata) {
                    if (isset($_GET['challenger'])) {
                        $challenger = $_GET['challenger'];
                    } else {
                        $challenger = '';
                    }
                    echo json_encode($omni->desireTrader($userdata, (int)$_GET['property'], (int)$_GET['token'], $_GET['holderdata'], $challenger), JSON_PRETTY_PRINT);
                }
            }
        }

        // list Trader
        if ($_GET['method'] === 'omni-list-trader') {
            if (isset($_GET['property']) && isset($_GET['token'])) {
                $user->lastAction($_GET['authkey']);
                $userdata = $user->get($_GET["authkey"], true);

                if ($userdata) {
                    if (isset($_GET['challenger'])) {
                        $challenger = $_GET['challenger'];
                    } else {
                        $challenger = '';
                    }
                    echo json_encode($omni->listTrader($userdata, (int)$_GET['property'], (int)$_GET['token'], $challenger), JSON_PRETTY_PRINT);
                }
            }
        }

        // send faucet
        if ($_GET['method'] === 'omni-send-faucet') {
            $user->lastAction($_GET['authkey']);
            $userdata = $user->get($_GET["authkey"], true);
            $time = time();
            
            if ($userdata->core_faucet < $time) {
                echo json_encode($omni->faucet($userdata), JSON_PRETTY_PRINT);
                $counter->increase($_GET['method']);
                $user->ltcfaucet($userdata->User);
            } else {
                $result = (object)array();
                $result->answer = 'You allrdy claimed in the last 24 hours, come back later.';
                $result->bool = false;
                echo json_encode($result, JSON_PRETTY_PRINT);
            }
            
        }
    }
} else {
    // help
    if ($_GET["method"] === 'omni-help') {
        header('Content-type: application/json; charset=utf-8');
        if (isset($_GET["command"])) {
            $omni->help($_GET["command"]);
        } else {
            $omni->help('');
        }
        $counter->increase($_GET['method']);
    }

    // sign Omni Send
    if ($_GET["method"] === 'omni-sign') {
        if (isset($_GET['user']) && isset($_GET['copper']) && isset($_GET['jade']) && isset($_GET['crystal'])) {
            $omni->send($_GET['user'], $_SERVER['REMOTE_ADDR'], $_GET['copper'], $_GET['jade'], $_GET['crystal']);
            $counter->increase($_GET['method']);
        }
    }

    // get nft data
    if ($_GET["method"] === 'omni-get-nft') {
        header('Content-type: application/json; charset=utf-8');
        if (isset($_GET["property"])) {
            if (!isset($_GET["token"])) {
                $token = 1;
            } else {
                $token = $_GET["token"];
            }
            
            echo json_encode($omni->NFTGet($_GET["property"], $token), JSON_PRETTY_PRINT);
            $counter->increase($_GET['method']);
        }
    }

    // get Trader Bot
    if ($_GET["method"] === 'omni-get-trader') {
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($omni->NFTGetTrader(), JSON_PRETTY_PRINT);
        $counter->increase($_GET['method']);
    }

    // get DEX
    if ($_GET["method"] === 'omni-get-dex') {
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($omni->getDEX(), JSON_PRETTY_PRINT);
        $counter->increase($_GET['method']);
    }

    // get faucet
    if ($_GET['method'] === 'omni-get-faucet') {

    }
}
