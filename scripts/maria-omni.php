<?php
require_once "/var/www/liteworlds/scripts/key.php";
require_once "/var/www/liteworlds/scripts/node.php";
require_once "/var/www/liteworlds/scripts/node-dev.php";

class Omni {
	private static $_db_username = 'maria';
	private static $_db_passwort = 'KerkerRocks22';
	private static $_db_host = '127.0.0.1';
	private static $_db_name = 'API_litecoin';
	private static $_db;

	private static $_rpc_user = 'user';
	private static $_rpc_pw = 'password';
	private static $_rpc_host = '192.168.0.165';
	private static $_rpc_port = '10000';
	private static $_node;
	private static $_node_dev;

	function __construct(){
		try{
			self::$_db = new PDO("mysql:host=" . self::$_db_host . ";dbname=" . self::$_db_name, self::$_db_username, self::$_db_passwort);
			self::$_node = new Node(self::$_rpc_user, self::$_rpc_pw, self::$_rpc_host, self::$_rpc_port);
			self::$_node_dev = new Node_Dev(self::$_rpc_user, self::$_rpc_pw, self::$_rpc_host, self::$_rpc_port);
		}catch(PDOException $e){
			echo "OMNILITE ERROR";
			die();
		}
	}

	function help($command) {
		echo self::$_node->help($command);
	}
	function NodeBalance(){
		return self::$_node->getbalance();
	}

	private function Balance($address, $minimum_confirmations, $maximum_confirmations){
		$utxo = self::$_node->listunspent($minimum_confirmations, $maximum_confirmations, (array)$address);
		$balance = 0;

		for ($a=0; $a < count($utxo); $a++) { 
			$balance += $utxo[$a]['amount'];
		}

		return $balance;
	}
	private function Property($address){
		$return = array("fungible"=>array(), "nft"=>array());
		$allPropertys = self::$_node->omni_listproperties();

		for ($a=0; $a < count($allPropertys); $a++) { 
			if ($allPropertys[$a]['issuer'] == $address) {
				$property = self::$_node->omni_getproperty($allPropertys[$a]['propertyid']);
				if ($property['non-fungibletoken']) {
					array_push($return['nft'], $property);
				} else {
					array_push($return['fungible'], $property);
				}
			}
		}
		return $return;
	}
	private function Token($address) {
		$return = array("fungible"=>array(), "nft"=>array());
		$balances = self::$_node->omni_getallbalancesforaddress($address);
		$nft = self::$_node->omni_getnonfungibletokens($address);

		if ($balances) {
			for ($a=0; $a < count($balances); $a++) { 
				$property = self::$_node->omni_getproperty($balances[$a]['propertyid']);
				if (!$property['non-fungibletoken']) {
					$balances[$a]['data'] = $property['data'];
					$balances[$a]['supply'] = $property['totaltokens'];
					array_push($return['fungible'], $balances[$a]);
				}
			}
		}

		if (count($nft) > 0) {
			for ($a=0; $a < count($nft); $a++) { 
				$nft[$a]['tokenindex'] = array();
				$property = self::$_node->omni_getproperty($nft[$a]['propertyid']);
	
				$balance = 0;
				for ($b=0; $b < count($nft[$a]['tokens']); $b++) { 
					$balance += $nft[$a]['tokens'][$b]['amount'];
	
					$start = $nft[$a]['tokens'][$b]['tokenstart'];
					for ($c=0; $c < $nft[$a]['tokens'][$b]['amount']; $c++) { 
						array_push($nft[$a]['tokenindex'], $start);
						$start++;
					}
				}
				$nft[$a]['balance'] = (string)$balance;
				$nft[$a]['name'] = $property['name'];
				$nft[$a]['data'] = $property['data'];
				$nft[$a]['supply'] = $property['totaltokens'];
	
				//shuffle($nft[$a]['tokenindex']);
				$array = $nft[$a]['tokenindex'];
				shuffle($array);
				$fulllist[$a] = array($nft[$a]['propertyid'] => $array);
			}
			shuffle($fulllist);
			array_push($nft, $fulllist);
	
			$return['nft'] = $nft;
		}
		
		return $return;
	}
	private function Dive($token) {
		$dive = false;
		for ($a=0; $a < count($token); $a++) { 
			if ($token[$a]['propertyid'] == 3843) {
				$dive = true;
			}
		}
		return $dive;
	}
	function Wallet($userdata){
		$result = (object)array();
		$wallet = self::$_node->getaddressesbylabel($userdata->User);

		if (!$wallet) {
			$wallet = self::$_node->getnewaddress($userdata->User);
			return '{"answer":"Omni address generated", "address":"'.$wallet.'", "bool":1, "balance":0, "pending":0}';
		} else {
			$wallet = array_keys($wallet);

			$result->answer = "Omni address found";
			$result->bool = true;
			$result->address = $wallet[0];
			$result->pending = number_format(self::Balance($result->address, 1, 5), 8, ".", "");
			$result->balance = number_format(self::Balance($result->address, 6, 999999999), 8, ".", "");
			$result->property = self::Property($result->address);
			$result->token = self::Token($result->address);
			$result->utxo = self::$_node->listunspent(0, 999999999, (array)$result->address);
			$result->dive = self::Dive($result->token['fungible']);
			
			return $result;
		}
	}

	private function FeeBuild($challenger) {
		$return = (object)array();
		$return->amount = 0.0001;

		$random = rand(1,100);
		if ($random <= 50) {
			// Mono wins
			$return->destination = 'ltc1qx70d3g2rkdmze2nf9cykgek7xxrjquglfexulu';
		} else {
			if ($challenger == '') {
				// faucet wins, if no challenger is present
				$return->destination = 'MCtYmUDUvjCatos2whjAsPaBr2a1nwA1tG';
			} else {
				$random = rand(1,100);
				if ($random <= 50) {
					// Challenger wins
					$return->destination = $challenger;
				} else {
					// Faucet wins
					$return->destination = 'MCtYmUDUvjCatos2whjAsPaBr2a1nwA1tG';
				}
			}
		}

		return $return;
	}
	private function InputBuild($utxo, $amount) {
		$return = (object)array();
		$return->list = array();
		$return->amounts = array();
		$return->amount = 0;
		$return->chainfee = 0.00000044;

		for ($a=0; $a < count($utxo); $a++) { 
			if (($return->amount - ($amount + $return->chainfee + 0.0001)) < 0.0001) {
				$return->list[$a] = array('txid'=>$utxo[$a]['txid'], 'vout'=>$utxo[$a]['vout']);
				$return->amounts[$a] = $utxo[$a]['amount'];
				$return->amount += $utxo[$a]['amount'];
				$return->chainfee += 0.00000148;
			} else {
				$a = count($utxo);
			}
		}

		if (0.0001 < ($return->amount - ($amount + $return->chainfee + 0.0001))) {
			$return->change = $return->amount - ($amount + $return->chainfee + 0.0001);
			return $return;
		} else {
			return false;
		}
	}
	private function InputBuildMultiSig($utxo, $amount) {
		$return = (object)array();
		$return->list = array();
		$return->amounts = array();
		$return->amount = 0;
		$return->chainfee = 0.00000044;

		for ($a=0; $a < count($utxo); $a++) { 
			if ($return->amount < (0.001 + $amount)) {
				$return->list[$a] = array('txid'=>$utxo[$a]['txid'], 'vout'=>$utxo[$a]['vout']);
				$return->amounts[$a] = $utxo[$a]['amount'];
				$return->amount += $utxo[$a]['amount'];
				$return->chainfee += 0.00000148;
			} else {
				$a = count($utxo);
			}
		}

		if ($return->amount >= (0.001 + $amount)) {
			return $return;
		} else {
			return false;
		}
	}
	private function InputBuildTrader($utxoT, $amountT, $trader, $utxoU, $amountU, $user) {
		$return = (object)array();
		$return->list = array();
		$return->amounts = array();
		$return->amount = 0;
		$return->amountTrader = 0;
		$return->amountUser = 0;
		$return->chainfee = 0.00000044;
		$c = 0;

		for ($a=0; $a < count($utxoT); $a++) { 
			if ($return->amountTrader < $amountT) {
				$return->list[$a] = array('txid'=>$utxoT[$a]['txid'], 'vout'=>$utxoT[$a]['vout']);
				$return->amounts[$a] = $utxoT[$a]['amount'];
				$return->amount += $utxoT[$a]['amount'];
				$return->amountTrader += $utxoT[$a]['amount'];
				$return->chainfee += 0.00000148;
				$c++;
			} else {
				$a = count($utxoT);
			}
		}
		$return->changeTrader = $return->amountTrader;

		for ($b=0; $b < count($utxoU); $b++) { 
			if ($return->amountUser < ($amountU + 0.0001 + $return->chainfee)) {
				$return->list[$b+$c] = array('txid'=>$utxoU[$b]['txid'], 'vout'=>$utxoU[$b]['vout']);
				$return->amounts[$b+$c] = $utxoU[$b]['amount'];
				$return->amount += $utxoU[$b]['amount'];
				$return->amountUser += $utxoU[$b]['amount'];
				$return->chainfee += 0.00000148;
			} else {
				$b = count($utxoU);
			}
		}
		$return->changeUser = $return->amountUser - (0.0001 + $return->chainfee);
		if ($return->changeUser < 0.0001 && $return->changeUser > 0) {
			$return->changeUser = 0;
		}

		return $return;
		var_dump($return);
	}
	private function SendPrepare($userdata, $txid, $origin, $input, $output) {
		$stmt = self::$_db->prepare("SELECT * FROM send WHERE BINARY User=:user");
		$stmt->bindParam(":user", $userdata->User);
		$stmt->execute();
		if ($stmt->rowCount() == 0) {
			$key = new Key;
			$keys = $key->Craft2FA();
			$time = time() + 120;

			$stmt = self::$_db->prepare("INSERT INTO send (User, TXID, IP, Time, Copper, Jade, Crystal) VALUES (:user, :txid, :ip, :time, :copper, :jade, :crystal)");
			$stmt->bindParam(":user", $userdata->User);
			$stmt->bindParam(":txid", $txid);
			$stmt->bindParam(":ip", $userdata->LastIP);
			$stmt->bindParam(":time", $time);
			$stmt->bindParam(":copper", $keys->copper);
			$stmt->bindParam(":jade", $keys->jade);
			$stmt->bindParam(":crystal", $keys->crystal);
			$stmt->execute();
			//var_dump($stmt->errorInfo()[0]);
			if ($stmt->errorInfo()[0] == "00000") {
				// send mail for verfication
				$empfaenger  = $userdata->Mail;
				$betreff = 'Sign ur Transaction on LiteWorlds.quest Network';
				
				// message
				$link = 'https://api.liteworlds.quest/?method=omni-sign&user='.$userdata->User.'&copper='.$keys->copper.'&jade='.$keys->jade.'&crystal='.$keys->crystal;
				//echo '<br>'.$link;
				$nachricht = '
				<html>
					<body style="background-color: black; color: deepskyblue;">
					<table align="center">
					<tr>
						<td><img src="https://api.liteworlds.quest/LWLA.png" style="height:250px; margin-left:auto; margin-right:auto; display:block;"></td>
					</tr>

					<tr>
						<td><p align="center">Blockchain Fee: '.$input->chainfee.' LTC</p></td>
					</tr>
					<tr>
						<td><p align="center">INPUT<br>';
						for ($a=0; $a < count($input->list); $a++) { 
							$nachricht .= $origin[0].' => '.array_values($input->amounts)[$a].' LTC<br>';
						}
						$nachricht .= '</p></td>
					</tr>
					<tr>
						<td>
							<p align="center">OUTPUT<br>';
							for ($a=0; $a < count($output); $a++) { 
								$nachricht .= array_keys($output)[$a].' => '.array_values($output)[$a].' LTC<br>';
							}
							$nachricht .= '</p>
						</td>
					</tr>

					<tr>
						<td>
							<p align="center" style="color:crimson;">Please sign your Transaction</p>
							<a target="_blank" rel="noopener noreferrer" href="'.$link.'">
								<button style="font-size:24px;width:100%;background-color:transparent;border:3px solid deepskyblue;border-radius:7px;color: deepskyblue">SIGN & SEND</button>
							</a>
						</td>
					</tr>
					
					</table>

					</body>
				</html>
				';

				$header = 
					'From: Security <security@liteworlds.quest>' . "\r\n" .
					'Reply-To: Security <security@liteworlds.quest>' . "\r\n" .
					'MIME-Version: 1.0' . "\r\n" .
					'Content-type: text/html; charset=iso-8859-1' . "\r\n" .
					'X-Mailer: PHP/' . phpversion();

				// send mail
				if (mail($empfaenger, $betreff, $nachricht, $header)) {
					return true;
				} else {
					return false;
				}
			}
		} else {
			return false;
		}

		
	}

	function Send($user, $ip, $copper, $jade, $crystal){
		$stmt = self::$_db->prepare("SELECT TXID FROM send WHERE BINARY User=:user AND BINARY Copper=:copper AND BINARY Jade=:jade AND BINARY Crystal=:crystal");
		$stmt->bindParam(":user", $user);
		$stmt->bindParam(":copper", $copper);
		$stmt->bindParam(":jade", $jade);
		$stmt->bindParam(":crystal", $crystal);
		$stmt->execute();
		//var_dump($stmt->errorInfo());
		if($stmt->rowCount() === 1){
			$txid = $stmt->fetch()['TXID'];
			//echo $txid;
			//return 0;
			$signtx = self::$_node->signrawtransaction($txid);
			//print_r($signtx);
			//var_dump($signtx['hex']);
			if($signtx['complete'] == 1){
				$txid = self::$_node->sendrawtransaction($signtx['hex']);

				if ($txid) {
					$stmt = self::$_db->prepare("DELETE FROM send WHERE BINARY User=:user AND BINARY Copper=:copper AND BINARY Jade=:jade AND BINARY Crystal=:crystal");
					$stmt->bindParam(":user", $user);
					$stmt->bindParam(":copper", $copper);
					$stmt->bindParam(":jade", $jade);
					$stmt->bindParam(":crystal", $crystal);
					$stmt->execute();

					echo '
						<!DOCTYPE html>
						<html>
						<head>
							<link rel="stylesheet" href="style.css">
						</head>
						<body style="background-color: black;">
							<h1 style="text-align: center;margin-left: auto;margin-right: auto;width: 50%;font-weight: bold;color: deepskyblue">You made it!</h1>
							<img src="https://api.liteworlds.quest/LWLA.png" style="height:250px; margin-left:auto; margin-right:auto; display:block;">
							<h1 style="text-align: center;margin-left: auto;margin-right: auto;width: 50%;font-weight: bold;color: deepskyblue">HURRAY</h1>
							<p style="text-align: center;margin-left: auto;margin-right: auto;width: 50%;font-weight: bold;color: deepskyblue">Your Transaction has been succesfully created and submitted to the Litecoin MemoryPool</p>
							<p style="text-align: center;margin-left: auto;margin-right: auto;width: 50%;font-weight: bold;color: deepskyblue">You get redirected in <b id="time">10</b> seconds</p>
						</body>
						</html>
						<script>
							setTimeout(function(){
								window.location.replace("https://blockchair.com/litecoin/transaction/'.$txid.'")
							}, 10000)

							let sec = 9
							setInterval(function () {
								document.getElementById("time").innerHTML = sec
								
								if (sec > 0) {
									sec--
								}
							}, 1000)
						</script>
					';
				}else {
					echo 'Transaction Error';
					var_dump($signtx['hex']);
				}
			}
		}
	}

	function SendCreate($userdata, $destination, $amount, $challenger) {
		$return = (object)array();
		$wallet = self::Wallet($userdata);
		$utxo = self::$_node->listunspent(6, 999999999, (array)$wallet->address);

		// create inputs
		$input = self::InputBuild($utxo, $amount);
		if ($input) {
			// create default output
			$fee = self::FeeBuild($challenger);
			$output = array();

			//var_dump($fee);

			// add custom outputs
			// check fee == destination
			if (!property_exists((object)$output, $destination)) {
				$output[$destination] = $amount;
			} else {
				$output[$destination] += $mount;
			}

			// check change == destination
			if ($wallet->address != $destination) {
				$output[$wallet->address] = $input->change;
			} else {
				$output[$wallet->address] += $input->change;
			}
		
			$output[$fee->destination] = number_format($fee->amount, 8, '.', '');
			$output[$destination] = number_format($output[$destination], 8, '.', '');
			
			$input->chainfee += count($output) * 0.00000034;
			$input->chainfee = number_format($input->chainfee, 8, '.', '');

			$output[$wallet->address] -= count($output) * 0.00000034;
			$output[$wallet->address] = number_format($output[$wallet->address], 8, '.', '');

			//var_dump($input, (object)$output);
			$total = 0;
			for ($a=0; $a < count($output); $a++) { 
				$total += array_values($output)[$a];
			}
			//var_dump($input->chainfee, number_format($input->amount - $total, 8, '.', ''));
			$txid = self::$_node->createrawtransaction($input->list, (object)$output);
			//var_dump($txid);
			if ($txid) {
				if(self::SendPrepare($userdata, $txid, (array)$wallet->address, $input, $output)){
					$return->answer = 'Sending creation prepared, sign it via mail';
					$return->bool = true;
					$return->input = $input;
					$return->output = $output;
					return $return;
				}else{
					$return->answer = 'Sending creation error';
					$return->bool = false;
					return $return;
				}
			}
		}
	}

	function PropertyCreate($userdata, $fixed, $ecosystem, $type, $previousid, $category, $subcategory, $name, $url, $data, $amount, $challenger) {
		$return = (object)array();
		$wallet = self::Wallet($userdata);
		$utxo = self::$_node->listunspent(1, 999999999, (array)$wallet->address);

		// create inputs
		if ($ecosystem == 1) {
			$input = self::InputBuildMultiSig($utxo, 0.1);
		} else {
			$input = self::InputBuildMultiSig($utxo, 0);
		}
		if ($input) {
			//var_dump($input);
			// create default output
			$fee = self::FeeBuild($challenger);
			$output = array();
			if ($ecosystem == 1) {
				$output['ltc1qx70d3g2rkdmze2nf9cykgek7xxrjquglfexulu'] = "0.10000000";
				$input->amount -= 0.1;
			}
			$output[$fee->destination] = number_format($fee->amount, 8, '.', '');
			$output[$wallet->address] = $input->amount - 0.0005 - $input->chainfee;

			$input->chainfee += count($output) * 0.00000034;
			$input->chainfee = number_format($input->chainfee, 8, '.', '');

			$output[$wallet->address] -= count($output) * 0.00000034;
			$output[$wallet->address] = number_format($output[$wallet->address], 8, '.', '');

			//var_dump($output);

			$rawtx = self::$_node->createrawtransaction($input->list, (object)$output);
			//var_dump($rawtx);
			if ($rawtx) {
				if ($fixed == 0) {
					//var_dump($ecosystem, $type, $previousid, $category, $subcategory, $name, $url, $data);
					$payload = self::$_node->omni_createpayload_issuancemanaged($ecosystem, $type, $previousid, $category, $subcategory, $name, $url, $data);
					//var_dump($payload);
				} else {
					$payload = self::$_node->omni_createpayload_issuancefixed($ecosystem, $type, $previousid, $category, $subcategory, $name, $url, $data, $amount);
				}
				
				$modrawtx = self::$_node->omni_createrawtx_multisig($rawtx, $payload, $wallet->address, $wallet->address);
				//var_dump($modrawtx);
				if ($modrawtx) {
					$input->chainfee = "0.00001225";
					if(self::SendPrepare($userdata, $modrawtx, (array)$wallet->address, $input, $output)){
						$return->answer = 'Sending creation prepared, sign it via mail';
						$return->bool = true;
						$return->input = $input;
						$return->output = $output;
						return $return;
					}else{
						$return->answer = 'Sending creation error';
						$return->bool = false;
						return $return;
					}
				}
			}
		}
	}

	function NFTGet($property, $token) {
		return self::$_node->omni_getnonfungibletokendata((int)$property, (int)$token, (int)$token);
	}

	function NFTMint($userdata, $property, $grantdata, $challenger){
		$return = (object)array();
		$wallet = self::Wallet($userdata);
		$utxo = self::$_node->listunspent(1, 999999999, (array)$wallet->address);
		$input = self::InputBuildMultiSig($utxo, 0);

		if ($input) {
			$fee = self::FeeBuild($challenger);
			$output = array();
			//var_dump($input->amount);
			//var_dump($input->amount - 0.00028780 - 0.00001225);
			if (substr($fee->destination, 0, 1) == 'M' || substr($fee->destination, 0, 1) == 'L') {
				$fee->destination = 'ltc1qx70d3g2rkdmze2nf9cykgek7xxrjquglfexulu';
			}

			$output[$fee->destination] = number_format($fee->amount, 8, '.', '');
			$output[$wallet->address] = $input->amount - 0.0005;
			
			$input->chainfee += count($output) * 0.00000034;
			$input->chainfee = number_format($input->chainfee, 8, '.', '');

			$output[$wallet->address] -= count($output) * 0.00000034;
			$output[$wallet->address] = number_format($output[$wallet->address], 8, '.', '');

			$rawtx = self::$_node->createrawtransaction($input->list, (object)$output);
			//var_dump($rawtx);
			if ($rawtx) {
				$payload = self::$_node->omni_createpayload_grant($property, '1', $grantdata);
				//var_dump($payload);
				$modrawtx = self::$_node->omni_createrawtx_multisig($rawtx, $payload, $wallet->address, $wallet->address);
				//var_dump($modrawtx);
				if ($modrawtx) {
					$input->chainfee = "0.00001225";
					if(self::SendPrepare($userdata, $modrawtx, (array)$wallet->address, $input, $output)){
						$return->answer = 'Sending creation prepared, sign it via mail';
						$return->bool = true;
						$return->input = $input;
						$return->output = $output;
						return $return;
					}else{
						$return->answer = 'Sending creation error';
						$return->bool = false;
						return $return;
					}
				}
			}
		}
	}
	//function NFTMassMint(){}

	function SendNFT($userdata, $destination, $property, $token, $challenger) {
		$return = (object)array();
		$wallet = self::Wallet($userdata);
		$utxo = self::$_node->listunspent(1, 999999999, (array)$wallet->address);

		// create inputs
		$input = self::InputBuild($utxo, 0.0001);
		if ($input) {
			// create default output
			$fee = self::FeeBuild($challenger);
			$output = array();

			$output[$wallet->address] = $input->change;

			if ($destination == $fee->destination) {
				$output[$fee->destination] = $fee->amount + 0.0001;
			} else {
				$output[$fee->destination] = number_format($fee->amount, 8, '.', '');
			}

			$input->chainfee += count($output) * 0.00000034;
			$input->chainfee = number_format($input->chainfee, 8, '.', '');

			$output[$wallet->address] -= count($output) * 0.00000034;
			$output[$wallet->address] = number_format($output[$wallet->address], 8, '.', '');

			if ($destination != $fee->destination) {
				$output[$destination] = number_format(0.0001, 8, '.', '');
			}

			$rawtx = self::$_node->createrawtransaction($input->list, (object)$output);
			if ($rawtx) {
				$payload = self::$_node->omni_createpayload_sendnonfungible($property, $token, $token);
				$modrawtx = self::$_node->omni_createrawtx_opreturn($rawtx, $payload);
				if ($modrawtx) {
					if(self::SendPrepare($userdata, $modrawtx, (array)$wallet->address, $input, $output)){
						$return->answer = 'Sending creation prepared, sign it via mail';
						$return->bool = true;
						$return->input = $input;
						$return->output = $output;
						return $return;
					}else{
						$return->answer = 'Sending creation error';
						$return->bool = false;
						return $return;
					}
				}
			}
		}
	}

	function NFTGetTrader() {
		$balances = self::$_node->omni_getnonfungibletokens('MGTjUjDccbaCQZEyhFHDr1x9SAGwhyxa2L');
		$result = array();

		if (count($balances) == 0) {
			$result['answer'] = 'no NFTs for sale';
			$result['bool'] = false;
			return $result;
		} else {
			for ($a=0; $a < count($balances); $a++) { 
				$element = array();
	
				$element = self::$_node->omni_getproperty($balances[$a]['propertyid']);
				$element['list'] = array();
	
				for ($b=0; $b < count($balances[$a]['tokens']); $b++) { 
					$token = $balances[$a]['tokens'][$b]['tokenstart'];
	
					for ($c=0; $c < $balances[$a]['tokens'][$b]['amount']; $c++) { 
						array_push($element['list'], $token);
						$token++;
					}
				}
				array_push($result, $element);
	
				shuffle($element['list']);
				$fulllist[$a] = array($balances[$a]['propertyid'] => $element['list']);
			}
			shuffle($fulllist);
			array_push($result, $fulllist);
			return $result;
		}

		
	}

	function cancelTrader($userdata, $property, $token, $challenger) {
		$return = (object)array();
		$trader = 'MGTjUjDccbaCQZEyhFHDr1x9SAGwhyxa2L';
		$utxoT = self::$_node->listunspent(1, 999999999, (array)$trader);
		$wallet = self::wallet($userdata);
		$utxoU = self::$_node->listunspent(1, 999999999, (array)$wallet->address);

		$input = self::InputBuildTrader($utxoT, 0.0001, $trader, $utxoU, 0.0001, $wallet->address);
		if ($input) {
			$fee = self::FeeBuild($challenger);
			$output = array();

			$output[$fee->destination] = number_format($fee->amount, 8, '.', '');
			$output[$trader] = number_format($input->changeTrader, 8, '.', '');
			$output[$wallet->address] = $input->changeUser;

			$input->chainfee += count($output) * 0.00000034;
			$input->chainfee = number_format($input->chainfee, 8, '.', '');

			$output[$wallet->address] -= count($output) * 0.00000034;
			$output[$wallet->address] = number_format($output[$wallet->address], 8, '.', '');

			//$output[$destination] = number_format(0.0001, 8, '.', '');

			//var_dump($input->list);

			$rawtx = self::$_node->createrawtransaction($input->list, (object)$output);
			//var_dump($rawtx);
			if ($rawtx) {
				$payload = self::$_node->omni_createpayload_sendnonfungible($property, $token, $token);
				$modrawtx = self::$_node->omni_createrawtx_opreturn($rawtx, $payload);
				//var_dump($payload, $modrawtx);
				if ($modrawtx) {
					if(self::SendPrepare($userdata, $modrawtx, (array)$wallet->address, $input, $output)){
						$return->answer = 'Sending creation prepared, sign it via mail';
						$return->bool = true;
						$return->input = $input;
						$return->output = $output;
						return $return;
					}else{
						$return->answer = 'Sending creation error';
						$return->bool = false;
						return $return;
					}
				}
			}
		}
	}

	function takeTrader($userdata, $property, $token, $challenger) {
		$return = (object)array();
		$trader = 'MGTjUjDccbaCQZEyhFHDr1x9SAGwhyxa2L';
		$utxoT = self::$_node->listunspent(1, 999999999, (array)$trader);
		$wallet = self::wallet($userdata);
		$utxoU = self::$_node->listunspent(1, 999999999, (array)$wallet->address);

		$holderdata = self::$_node->omni_getnonfungibletokendata($property, $token, $token)[0]['holderdata'];
		$desire = (float)json_decode($holderdata)->desire;
		$destination = json_decode($holderdata)->destination;

		$available = ((float)$wallet->balance + (float)$wallet->pending) - $desire;

		if ($available < 0) {
			$return->answer = 'not enough coins';
			$return->bool = false;
			return $return;
		}
		


		$input = self::InputBuildTrader($utxoT, 0.0001, $trader, $utxoU, (0.0001 + $desire), $wallet->address);
		if ($input) {
			$fee = self::FeeBuild($challenger);
			$output = array();

			$output[$fee->destination] = number_format($fee->amount, 8, '.', '');
			$output[$trader] = number_format($input->changeTrader, 8, '.', '');
			$output[$destination] = number_format($desire, 8, '.', '');
			$output[$wallet->address] = $input->changeUser - $desire;

			$input->chainfee += count($output) * 0.00000034;
			$input->chainfee = number_format($input->chainfee, 8, '.', '');

			$output[$wallet->address] -= count($output) * 0.00000034;
			$output[$wallet->address] = number_format($output[$wallet->address], 8, '.', '');

			//var_dump($input, $output);
			$rawtx = self::$_node->createrawtransaction($input->list, (object)$output);
			//var_dump($rawtx);
			if ($rawtx) {
				$payload = self::$_node->omni_createpayload_sendnonfungible($property, $token, $token);
				$modrawtx = self::$_node->omni_createrawtx_opreturn($rawtx, $payload);
				//var_dump($payload, $modrawtx);
				if ($modrawtx) {
					if(self::SendPrepare($userdata, $modrawtx, (array)$wallet->address, $input, $output)){
						$return->answer = 'Sending creation prepared, sign it via mail';
						$return->bool = true;
						$return->input = $input;
						$return->output = $output;
						return $return;
					}else{
						$return->answer = 'Sending creation error';
						$return->bool = false;
						return $return;
					}
				}
			}
		}
	}

	function desireTrader($userdata, $property, $token, $holderdata, $challenger) {
		$return = (object)array();
		$wallet = self::Wallet($userdata);
		$utxo = self::$_node->listunspent(1, 999999999, (array)$wallet->address);
		$input = self::InputBuildMultiSig($utxo, 0);

		if ($input) {
			$fee = self::FeeBuild($challenger);
			$output = array();

			$output[$fee->destination] = number_format($fee->amount, 8, '.', '');
			$output[$wallet->address] = $input->amount - 0.00031937;
			
			$input->chainfee += count($output) * 0.00000034;
			$input->chainfee = number_format($input->chainfee, 8, '.', '');

			$output[$wallet->address] -= count($output) * 0.00000034;
			$output[$wallet->address] = number_format($output[$wallet->address], 8, '.', '');

			$rawtx = self::$_node->createrawtransaction($input->list, (object)$output);
			if ($rawtx) {
				$payload = self::$_node->omni_createpayload_setnonfungibledata($property, $token, $token, false, $holderdata);
				$modrawtx = self::$_node->omni_createrawtx_multisig($rawtx, $payload, $wallet->address, $wallet->address);
				if ($modrawtx) {
					if(self::SendPrepare($userdata, $modrawtx, (array)$wallet->address, $input, $output)){
						$return->answer = 'Sending creation prepared, sign it via mail';
						$return->bool = true;
						$return->input = $input;
						$return->output = $output;
						return $return;
					}else{
						$return->answer = 'Sending creation error';
						$return->bool = false;
						return $return;
					}
				}
			}
		}
	}

	function listTrader($userdata, $property, $token, $challenger) {
		$return = (object)array();
		$trader = 'MGTjUjDccbaCQZEyhFHDr1x9SAGwhyxa2L';
		$wallet = self::wallet($userdata);
		$utxo = self::$_node->listunspent(1, 999999999, (array)$wallet->address);

		$input = self::InputBuild($utxo, 0.0001);
		if ($input) {
			$fee = self::FeeBuild($challenger);
			$output = array();

			$output[$fee->destination] = number_format($fee->amount, 8, '.', '');
			$output[$wallet->address] = $input->change;

			$input->chainfee += count($output) * 0.00000034;
			$input->chainfee = number_format($input->chainfee, 8, '.', '');

			$output[$wallet->address] -= count($output) * 0.00000034;
			$output[$wallet->address] = number_format($output[$wallet->address], 8, '.', '');

			$output[$trader] = number_format(0.0001, 8, '.', '');

			$rawtx = self::$_node->createrawtransaction($input->list, (object)$output);
			if ($rawtx) {
				$payload = self::$_node->omni_createpayload_sendnonfungible($property, $token, $token);
				$modrawtx = self::$_node->omni_createrawtx_opreturn($rawtx, $payload);
				if ($modrawtx) {
					if(self::SendPrepare($userdata, $modrawtx, (array)$wallet->address, $input, $output)){
						$return->answer = 'Sending creation prepared, sign it via mail';
						$return->bool = true;
						$return->input = $input;
						$return->output = $output;
						return $return;
					}else{
						$return->answer = 'Sending creation error';
						$return->bool = false;
						return $return;
					}
				}
			}
		}
	}

	function SendToken($userdata, $destination, $property, $amount, $challenger) {
		$return = (object)array();
		$wallet = self::Wallet($userdata);
		$utxo = self::$_node->listunspent(1, 999999999, (array)$wallet->address);

		$input = self::InputBuild($utxo, 0.0001);
		if ($input) {
			// create default output
			$fee = self::FeeBuild($challenger);
			$output = array();

			$output[$fee->destination] = number_format($fee->amount, 8, '.', '');
			$output[$wallet->address] = $input->change;

			$input->chainfee += count($output) * 0.00000034;
			$input->chainfee = number_format($input->chainfee, 8, '.', '');

			$output[$wallet->address] -= count($output) * 0.00000034;
			$output[$wallet->address] = number_format($output[$wallet->address], 8, '.', '');

			$output[$destination] = number_format(0.0001, 8, '.', '');
			

			$rawtx = self::$_node->createrawtransaction($input->list, (object)$output);
			if ($rawtx) {
				$payload = self::$_node->omni_createpayload_simplesend($property, $amount);
				$modrawtx = self::$_node->omni_createrawtx_opreturn($rawtx, $payload);
				if ($modrawtx) {
					if(self::SendPrepare($userdata, $modrawtx, (array)$wallet->address, $input, $output)){
						$return->answer = 'Sending creation prepared, sign it via mail';
						$return->bool = true;
						$return->input = $input;
						$return->output = $output;
						return $return;
					}else{
						$return->answer = 'Sending creation error';
						$return->bool = false;
						return $return;
					}
				}
			}
		}
	}

	function getDEX() {
		$dex = self::$_node->omni_getactivedexsells();
		//$object = (object)array();

		for ($a=0; $a < count($dex); $a++) { 
			$dex[$a]['data'] = self::$_node->omni_getproperty($dex[$a]['propertyid']);
			$dex[$a]['data']['structure'] = 'custom';

			try {
				$object = json_decode($dex[$a]['data']['data']);
				if (property_exists($object, 'structure')) {
					$dex[$a]['data']['structure'] = $object->structure;
				}
			} catch (\Throwable $th) {}
		}

		return $dex;
		//echo json_encode($dex, JSON_PRETTY_PRINT);
	}

	function createDEX($userdata, $property, $amount, $desire) {
		$window = 21;
		$minfee = '0.00000100';
		$action = 1;

		$return = (object)array();
		$wallet = self::Wallet($userdata);
		$utxo = self::$_node->listunspent(1, 999999999, (array)$wallet->address);

		$input = self::InputBuild($utxo, 0);
		if ($input) {
			// create default output
			$fee = self::FeeBuild($challenger);
			$output = array();

			$output[$fee->destination] = number_format($fee->amount, 8, '.', '');
			$output[$wallet->address] = $input->change;

			$input->chainfee += count($output) * 0.00000034;
			$input->chainfee = number_format($input->chainfee, 8, '.', '');

			$output[$wallet->address] -= count($output) * 0.00000034;
			$output[$wallet->address] = number_format($output[$wallet->address], 8, '.', '');

			$rawtx = self::$_node->createrawtransaction($input->list, (object)$output);
			var_dump($input, $output);
			var_dump($rawtx);
			if ($rawtx) {
				$payload = self::$_node->omni_createpayload_dexsell($property, $amount, $desire, $window, $minfee, $action);
				$modrawtx = self::$_node->omni_createrawtx_opreturn($rawtx, $payload);
				if ($modrawtx) {
					if(self::SendPrepare($userdata, $modrawtx, (array)$wallet->address, $input, $output)){
						$return->answer = 'Sending creation prepared, sign it via mail';
						$return->bool = true;
						$return->input = $input;
						$return->output = $output;
						return $return;
					}else{
						$return->answer = 'Sending creation error';
						$return->bool = false;
						return $return;
					}
				}
			}
		}
	}

	function cancelDEX($userdata, $property) {
		$return = (object)array();
		$wallet = self::Wallet($userdata);
		$utxo = self::$_node->listunspent(1, 999999999, (array)$wallet->address);

		$input = self::InputBuild($utxo, 0);
		if ($input) {
			// create default output
			$fee = self::FeeBuild($challenger);
			$output = array();

			$output[$fee->destination] = number_format($fee->amount, 8, '.', '');
			$output[$wallet->address] = $input->change;

			$input->chainfee += count($output) * 0.00000034;
			$input->chainfee = number_format($input->chainfee, 8, '.', '');

			$output[$wallet->address] -= count($output) * 0.00000034;
			$output[$wallet->address] = number_format($output[$wallet->address], 8, '.', '');

			$rawtx = self::$_node->createrawtransaction($input->list, (object)$output);
			var_dump($input, $output);
			var_dump($rawtx);
			if ($rawtx) {
				$payload = self::$_node->omni_createpayload_dexsell($property, "", "", 0, "", 3);
				$modrawtx = self::$_node->omni_createrawtx_opreturn($rawtx, $payload);
				if ($modrawtx) {
					if(self::SendPrepare($userdata, $modrawtx, (array)$wallet->address, $input, $output)){
						$return->answer = 'Sending creation prepared, sign it via mail';
						$return->bool = true;
						$return->input = $input;
						$return->output = $output;
						return $return;
					}else{
						$return->answer = 'Sending creation error';
						$return->bool = false;
						return $return;
					}
				}
			}
		}
	}

	function acceptDEX($userdata, $property, $amount, $destination) {
		$return = (object)array();
		$wallet = self::Wallet($userdata);
		$utxo = self::$_node->listunspent(1, 999999999, (array)$wallet->address);

		$input = self::InputBuild($utxo, 0.0001);
		if ($input) {
			// create default output
			$fee = self::FeeBuild($challenger);
			$output = array();

			$output[$fee->destination] = number_format($fee->amount, 8, '.', '');
			$output[$wallet->address] = $input->change;
			$output[$destination] = '0.0001';

			$input->chainfee += count($output) * 0.00000034;
			$input->chainfee = number_format($input->chainfee, 8, '.', '');

			$output[$wallet->address] -= count($output) * 0.00000034;
			$output[$wallet->address] = number_format($output[$wallet->address], 8, '.', '');

			$rawtx = self::$_node_dev->createrawtransaction($input->list, (object)$output);
			var_dump($input, $output);
			if ($rawtx) {
				$payload = self::$_node_dev->omni_createpayload_dexaccept($property, $amount);
				$modrawtx = self::$_node_dev->omni_createrawtx_opreturn($rawtx, $payload);
				if ($modrawtx) {
					if(self::SendPrepare($userdata, $modrawtx, (array)$wallet->address, $input, $output)){
						$return->answer = 'Sending creation prepared, sign it via mail';
						$return->bool = true;
						$return->input = $input;
						$return->output = $output;
						return $return;
					}else{
						$return->answer = 'Sending creation error';
						$return->bool = false;
						return $return;
					}
				}
			}
		}
	}

	function payDEX($userdata, $destination, $property, $amount) {
		$return = (object)array();
		$wallet = self::Wallet($userdata);

		var_dump($userdata, $wallet);

		$txid = self::$_node_dev->omni_senddexpay($wallet->address, $destination, $property, $amount);
		if ($txid) {
			return (object)array("answer"=>"Transaction executed","bool"=>true,"TransactionID"=>$txid);
		} else {
			return (object)array("answer"=>"Transaction failed","bool"=>false);
		}
		
	}

	function faucet($userdata) {
		$return = (object)array();
		$wallet = self::Wallet($userdata);
		$object = (object)array();
		$object->User = '#faucet';
		$object = self::Wallet($object);
		$faucet = 'MCtYmUDUvjCatos2whjAsPaBr2a1nwA1tG';
		$utxo = self::$_node->listunspent(6, 999999999, (array)$faucet);
		$rand = rand(1,100);
		$nft = false;

		//var_dump($rand);
		if ((float)$object->balance > 0.00025) {
			if ($wallet->dive) {
				$amount = (float)$object->balance * 0.01;

				if ($amount < 0.0001) {
					$amount = 0.0001;
				}
				if ($rand <= 10) {
					$nft = true;
				}
			} else {
				$amount = (float)$object->balance * 0.001;

				if ($amount < 0.0001) {
					$amount = 0.0001;
				}
				if ($rand <= 5) {
					$nft = true;
				}
			}
			//var_dump($amount);
			//var_dump($nft);
		}
		array_pop($object->token['nft']);
		
		if ($nft) {
			$array = array();
			for ($a=0; $a < count($object->token['nft']); $a++) { 
				$element = $object->token['nft'][$a];
				array_push($array, $element['propertyid']);
				
			}
			shuffle($array);
			$propertyid = $array[0];
			for ($a=0; $a < count($object->token['nft']); $a++) { 
				$element = $object->token['nft'][$a];
				if ($element['propertyid'] == $propertyid) {
					shuffle($element['tokenindex']);
					$tokenid = $element['tokenindex'][0];
				}
			}
			//var_dump($propertyid, $tokenid);
			//var_dump($object->token['nft']);
			// send nft with given faucet amount
			$input = self::InputBuild($utxo, $amount);
			if ($input) {
				$output = array();

				$output[$wallet->address] = number_format($amount, 8, '.', '');
				$output[$object->address] = $input->change;

				$input->chainfee += count($output) * 0.00000034;
				$input->chainfee = number_format($input->chainfee, 8, '.', '');

				$output[$object->address] -= count($output) * 0.00000034;
				$output[$object->address] = number_format($output[$object->address], 8, '.', '');

				//var_dump($input, $output);
				$rawtx = self::$_node->createrawtransaction($input->list, (object)$output);
				if ($rawtx) {
					$payload = self::$_node->omni_createpayload_sendnonfungible($propertyid, $tokenid, $tokenid);
					$modrawtx = self::$_node->omni_createrawtx_opreturn($rawtx, $payload);
					if ($modrawtx) {
						$signtx = self::$_node->signrawtransaction($rawtx);
						$txid = self::$_node->sendrawtransaction($signtx['hex']);

						$return->answer = $amount.' LTC & a NFT are on the Way!';
						$return->bool = true;
						$return->amount = $amount;
						$return->txid = $txid;
						$return->propertyid = $propertyid;
						$return->tokenid = $tokenid;
						return $return;
					}
				}

			}
		} else {
			// send faucet amount
			$input = self::InputBuild($utxo, $amount);
			if ($input) {
				$output = array();

				$output[$wallet->address] = number_format($amount, 8, '.', '');
				$output[$object->address] = $input->change;

				$input->chainfee += count($output) * 0.00000034;
				$input->chainfee = number_format($input->chainfee, 8, '.', '');

				$output[$object->address] -= count($output) * 0.00000034;
				$output[$object->address] = number_format($output[$object->address], 8, '.', '');

				//var_dump($input, $output);
				$rawtx = self::$_node->createrawtransaction($input->list, (object)$output);
				if ($rawtx) {
					$signtx = self::$_node->signrawtransaction($rawtx);
					$txid = self::$_node->sendrawtransaction($signtx['hex']);

					$return->answer = $amount.' LTC are on the Way!';
					$return->bool = true;
					$return->amount = $amount;
					$return->txid = $txid;
					return $return;
				}
			}
		}
	}
