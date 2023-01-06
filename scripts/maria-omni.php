<?php
    require_once "/var/www/liteworlds/scripts/coin.php";
	require_once "/var/www/liteworlds/scripts/coin-dev.php";
	require_once "/var/www/liteworlds/scripts/maria-user.php";

	class Omni {
		private static $_db_username = 'maria';
		private static $_db_passwort = 'KerkerRocks22';
		private static $_db_host = '127.0.0.1';
		private static $_db_name = 'API_litecoin';
		private static $_db;

		private static $_rpc_user = 'user';
		private static $_rpc_pw = 'password';
		private static $_rpc_host = '127.0.0.1';
		private static $_rpc_port = '10000';
 
		function __construct(){
			try{
				self::$_db = new PDO("mysql:host=" . self::$_db_host . ";dbname=" . self::$_db_name, self::$_db_username, self::$_db_passwort);
			}catch(PDOException $e){
				echo "OMNILITE ERROR";
				die();
			}
        }

		function help($command) {
			$coin = new Coin(self::$_rpc_user, self::$_rpc_pw, self::$_rpc_host, self::$_rpc_port);
			$help = $coin->help($command);
			echo $help;
		}

		private function craftKeys($name, $ip, $column){
			$stmt = self::$_db->prepare("SELECT * FROM $column WHERE Name=:name");
			$stmt->bindParam(":name", $name);
			$stmt->execute();
	
			if($stmt->rowCount() === 0){
				// create 3 unique array with 0-3 for shuffling the key parameters
				$keys = (object)array();
				$max = 3;
				$done = false;
	
				while(!$done){
					$copper = range(0, $max);
					shuffle($copper);
					$done = true;
					foreach($copper as $key => $val){
						if($key == $val){
							$done = false;
							break;
						}
					}
				}
	
				$done = false;
	
				while(!$done){
					$jade = range(0, $max);
					shuffle($jade);
					
					foreach($jade as $key => $val){
						if($key == $val){
							$done = false;
							break;
						}
					}
					if ($copper != $jade) {
						$done = true;
					}else{
						$done = false;
					}
				}
	
				$done = false;
	
				while(!$done){
					$crystal = range(0, $max);
					shuffle($crystal);
					
					foreach($crystal as $key => $val){
						if($key == $val){
							$done = false;
							break;
						}
					}
					if ($copper != $crystal && $jade != $crystal) {
						$done = true;
					}else{
						$done = false;
					}
				}
	
				$done = false;
	
				do {
					// create a random number
					$rnd = rand(0,1000000000000);
					$dataArray = array($name, $ip, time(), $rnd);
	
					// shuffle the keys
					$copper = $dataArray[$copper[0]].$dataArray[$copper[1]].$dataArray[$copper[2]].$dataArray[$copper[3]];
					$jade = $dataArray[$jade[0]].$dataArray[$jade[1]].$dataArray[$jade[2]].$dataArray[$jade[3]];
					$crystal = $dataArray[$crystal[0]].$dataArray[$crystal[1]].$dataArray[$crystal[2]].$dataArray[$crystal[3]];
	
					// hash the keys
					$keys->copper = hash('sha3-512', $copper);
					$keys->jade =  hash('sha3-512', $jade);
					$keys->crystal = hash('sha3-512', $crystal);
	
					// verify keycombo is unique
					$stmt = self::$_db->prepare("SELECT * FROM $column WHERE CopperKey=:copper AND JadeKey=:jade AND CrystalKey=:crystal");
					$stmt->bindParam(":copper", $keys->copper);
					$stmt->bindParam(":jade", $keys->jade);
					$stmt->bindParam(":crystal", $keys->crystal);
					$stmt->execute();
					
					if($stmt->rowCount() == 0)$done = true;
				} while (!$done);
	
				return $keys;
			}else{
				return 'login allready prepared';
			}
		}
		
        function totalBalance(){
			$coin = new Coin(self::$_rpc_user, self::$_rpc_pw, self::$_rpc_host, self::$_rpc_port);
			return $coin->getbalance();
		}

		function testpayload() {
			$coin = new Coin(self::$_rpc_user, self::$_rpc_pw, self::$_rpc_host, self::$_rpc_port);
			$ecosystem = 2;
			$type = 5;
			$previousid = 0;
			$category = "";
			$subcategory = "";
			$name = 'rawtransactiontest';
			$url = "";
			$data = "";

			$address = "M9gZJYf8MFSy3x7T7Puf3BkeTVL8wK2hVh";


			//$payload = $coin->omni_createpayload_issuancemanaged($ecosystem, $type, $previousid, $category, $subcategory, $name, $url, $data);
			$payload = "000000360200050000000000007261777472616e73616374696f6e74657374000000";

			


			$unspent = $coin->listunspent(0, 999999999, (array)$address);
			$inputAmount = 0;

			for ($a=0; $a < count($unspent); $a++) { 
				if ($inputAmount < 0.0005) {
					$input[$a] = array("txid"=>$unspent[$a]['txid'], "vout"=>$unspent[$a]['vout']);
					$inputAmount += $unspent[$a]['amount'];
				}
			}

			$output[$address] = number_format($inputAmount - 0.00005, 8, ".", "");

			//$txid = $coin->createrawtransaction($input, (object)$output);

			$rawtx = "02000000014c46d425ff704a629d3883e814e8465b6ec12b2aea19292faa36457314bdc34d0000000000ffffffff01187301000000000017a914138e1a4d5b14909d2974a17110f9b1bf9adcb4a88700000000";

			//$modrawtx = $coin->omni_createrawtx_opreturn($rawtx, $payload);
			$modrawtx = "02000000014c46d425ff704a629d3883e814e8465b6ec12b2aea19292faa36457314bdc34d0000000000ffffffff02187301000000000017a914138e1a4d5b14909d2974a17110f9b1bf9adcb4a8870000000000000000286a266f6d6e69000000360200050000000000007261777472616e73616374696f6e7465737400000000000000";

			//$signtx = $coin->signrawtransaction($modrawtx);
			$signtx = "020000000001014c46d425ff704a629d3883e814e8465b6ec12b2aea19292faa36457314bdc34d0000000017160014f46fe26961edafcf0677f68dc4b130a039f43e8affffffff02187301000000000017a914138e1a4d5b14909d2974a17110f9b1bf9adcb4a8870000000000000000286a266f6d6e69000000360200050000000000007261777472616e73616374696f6e746573740000000247304402200ef1abdc75fdf1db211387d085e5090c6c26089f6fac7bb571e62f361f4ed65c0220485a9d8b1dcc25e589d4194b5fc50bffaf5e443337cbe1aa7368d7cf19fe8e0c012103ff9467b4c68e4b5fbd89c020378ee0045c0b31662422bd53cd72b98dac9ae71b00000000";
			
			//$finaltx = $coin->sendrawtransaction($signtx);
			$finaltx = "1508ff0cbfe8d59f2bde88db1196c6c251b9d574f37f0ed580759dc6da996c0c";
			
			var_dump($finaltx);
		}

		function getAddress($name){
			$coin = new Coin(self::$_rpc_user, self::$_rpc_pw, self::$_rpc_host, self::$_rpc_port);
			$address = array_keys($coin->getaddressesbylabel($name));
	
			if (count($address) === 0) {
				$address = $coin->getnewaddress($name);
				return '{"answer":"Omni address generated", "address":"'.$address.'", "bool":1, "balance":0, "pending":0}';
			}

			$result->answer = "Omni address found";
			$result->bool = true;
			$result->address = $address[0];
			$result->pending = self::getPending($address);
			$result->balance = self::getBalance($address);
			$result->nfts = self::getNFTss($address[0]);
			
			return $result;
		}

		function prepareSend($userdata, $address, $amount){
			$coin = new Coin_Dev(self::$_rpc_user, self::$_rpc_pw, self::$_rpc_host, self::$_rpc_port);
			$myCoins = self::getAddress($userdata->User);
			$unspent = $coin->listunspent(6, 999999999, (array)$myCoins->address);
			

			$changeAddress = $myCoins->address;
			$feeAddress = 'ltc1qtqkdavyufq8wjzh6hgrn7z68pe5mnt3vvjj3p3';
			$frogAddress = 'ltc1qduhgah34d7wl8aq235mkstrx5kn770rwzr369u';
			$frogAmount = 0.00003;
			$fee = 0.00013;
			$monoAmount = $fee - $frogAmount;
			$chainfee = 0.0000023;
			
			$inputAmount = 0;
			$input = array();
			$output = array();

			$loop = true;
			$i = -1;

			//create outputs
			$output[$address] = 0;
			$output[$feeAddress] = 0;
			$output[$frogAddress] = 0;

			$chainfee += 0.00000040 * count($output);

			//create inputs
			do {
				$outputAmount = $amount + $fee + $chainfee;
				if ($inputAmount < $outputAmount) {
					$i++;
					if ($i >= count($unspent)) {
						echo 'Not enough coins';
						return false;
					}
					$input[$i] = array("txid"=>$unspent[$i]['txid'], "vout"=>$unspent[$i]['vout']);
					$inputAmount += $unspent[$i]['amount'];
					$chainfee += 0.00000150;
				} else {
					$loop = false;
				}
			} while ($loop);

			//calc change
			$changeAmount = $inputAmount - $outputAmount;
			
			// if change is less then 10000 sats spend it as dust to the network
			if ($changeAmount >= 0.0001) {
				$output[$changeAddress] = 0;
				$output[$changeAddress] = $changeAmount;
				$output[$changeAddress] = number_format($output[$changeAddress], 8, ".", "");
			}

			// assign output amounts
			$output[$address] += $amount;
			$output[$feeAddress] += $monoAmount;
			$output[$frogAddress] += $frogAmount;

			// format outputs to avoid errors
			$output[$address] = number_format($output[$address], 8, ".", "");
			$output[$feeAddress] = number_format($output[$feeAddress], 8, ".", "");
			$output[$frogAddress] = number_format($output[$frogAddress], 8, ".", "");

			echo 'outputAmount: '.$outputAmount.'<br>inputAmount: '.number_format($inputAmount, 8, ".", "").'<br>changeAmount: '.$changeAmount.'<br>Fee: '.number_format($chainfee, 8, ".", "").'<br>';
			var_dump($output);
			$txid = $coin->createrawtransaction($input, (object)$output);
			echo '<br>txid: '.$txid;
	
			if ($txid != '') {
				$keys = self::craftKeys($userdata->User, $userdata->LastIP, "send");
				$time = time() + 120;
	
				$stmt = self::$_db->prepare("INSERT INTO send (User, TXID, IP, Time, CopperKey, JadeKey, CrystalKey) VALUES (:user, :txid, :ip, :time, :copperkey, :jadekey, :crystalkey)");
				$stmt->bindParam(":user", $userdata->User);
				$stmt->bindParam(":txid", $txid);
				$stmt->bindParam(":ip", $userdata->LastIP);
				$stmt->bindParam(":time", $time);
				$stmt->bindParam(":copperkey", $keys->copper);
				$stmt->bindParam(":jadekey", $keys->jade);
				$stmt->bindParam(":crystalkey", $keys->crystal);
				$stmt->execute();
				var_dump($stmt->errorInfo());
				if($stmt->rowCount() === 1){
					// send mail for verfication
					$empfaenger  = $userdata->Mail;
					$betreff = 'Sign ur Transaction on LiteWorlds.quest Network';
		
					// message
					$link = 'https://api.liteworlds.quest/?method=Vsendomni&user='.$userdata->User.'&copperkey='.$keys->copper.'&jadekey='.$keys->jade.'&crystalkey='.$keys->crystal;
					//echo '<br>'.$link;
					$nachricht = '
						<a target="_blank" rel="noopener noreferrer" href="'.$link.'">
							<button style="font-size:24px;width:37%;background-color:transparent;cursor:crosshair;border:3px solid darkgreen;border-radius:7px;">SIGN</button>
						</a>
						<p>'.$link.'</p>
	
	
	
					<html>
						<body>
							<p>Please sign ur Transaction</p>

							<p>Blockchain Fee: '.number_format($chainfee, 8, ".", "").' LTC</p>
							
							<p>INPUT<br>';
							for ($i=0; $i < count($input); $i++) { 
								$nachricht .= $unspent[$i]['address'].' => '.number_format($unspent[$i]['amount'], 8, ".", "").'<br>';
							}
							$nachricht .= '</p>';
	
							$nachricht .= '<p>OUTPUT<br>';
							for ($i=0; $i < count($output); $i++) { 
								$nachricht .= array_keys($output)[$i].' => '.number_format(array_values($output)[$i], 8, ".", "").'<br>';
							}
							$nachricht .= '</p>';
							$nachricht .= '
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
					mail($empfaenger, $betreff, $nachricht, $header);
		
					return '{"answer":"Sending creation prepared, sign it via mail", "bool":1}';
				}else{
					return '{"answer":"Sending creation error1", "bool":0}';
				}
			}
	
			echo '{"answer":"Sending creation error", "bool":0}';
			
			return 0;
		}
		function send($user, $ip, $copperkey, $jadekey, $crystalkey){
			echo 'User: '.$user.'<br>';
			echo 'IP: '.$ip.'<br>';
			echo 'COPPER: '.$copperkey.'<br>';
			echo 'JADE: '.$jadekey.'<br>';
			echo 'CRYSTAL: '.$crystalkey.'<br>';

			$stmt = self::$_db->prepare("SELECT TXID FROM send WHERE User=:user AND CopperKey=:copperkey AND JadeKey=:jadekey AND CrystalKey=:crystalkey");
			$stmt->bindParam(":user", $user);
			$stmt->bindParam(":copperkey", $copperkey);
			$stmt->bindParam(":jadekey", $jadekey);
			$stmt->bindParam(":crystalkey", $crystalkey);
			$stmt->execute();
			var_dump($stmt->errorInfo());
			if($stmt->rowCount() === 1){
				$txid = $stmt->fetch()['TXID'];
				//echo $txid;
				//return 0;
				$coin = new Coin(self::$_rpc_user, self::$_rpc_pw, self::$_rpc_host, self::$_rpc_port);
				$signtx = $coin->signrawtransaction($txid);
				print_r($signtx);
				if($signtx['complete'] == 1){
					$txid = $coin->sendrawtransaction($signtx['hex']);
	
					if ($txid != '') {
						$stmt = self::$_db->prepare("DELETE FROM send WHERE User=:user AND CopperKey=:copperkey AND JadeKey=:jadekey AND CrystalKey=:crystalkey");
						$stmt->bindParam(":user", $user);
						$stmt->bindParam(":copperkey", $copperkey);
						$stmt->bindParam(":jadekey", $jadekey);
						$stmt->bindParam(":crystalkey", $crystalkey);
						$stmt->execute();
	
						echo 'Transaction succesfull<br>Ur TXID: '.$txid;
					}else {
						echo 'Transaction Error';
					}
				}
			}
		}


		private function getPending($address){
			$coin = new Coin(self::$_rpc_user, self::$_rpc_pw, self::$_rpc_host, self::$_rpc_port);
	
			$array = $coin->listunspent(0, 5, (array)$address);
			$pending = 0;
	
			for ($i=0; $i < count($array); $i++) { 
				$pending += $array[$i]['amount'];
			}
	
			return $pending;
		}
		private function getBalance($address){
			$coin = new Coin(self::$_rpc_user, self::$_rpc_pw, self::$_rpc_host, self::$_rpc_port);
	
			$array = $coin->listunspent(6, 999999999, (array)$address);
			$balance = 0;
	
			for ($i=0; $i < count($array); $i++) { 
				$balance += $array[$i]['amount'];
			}
	
			return $balance;
		}
		function getNFTss($address = "MU78ANEyiaAAjM4Z7HT8zTB3HWCzrXvM6i") {
			$coin = new Coin(self::$_rpc_user, self::$_rpc_pw, self::$_rpc_host, self::$_rpc_port);
			$nfts = $coin->omni_getallbalancesforaddress($address);

			$balance = 0;
			for ($a=0; $a < count($nfts); $a++) { 
				$balance += (int)$nfts[$a]['balance'];
			}

			return $balance;
		}

		function getprivkey($user){
			$coin = new Coin(self::$_rpc_user, self::$_rpc_pw, self::$_rpc_host, self::$_rpc_port);
			$address = self::getAddress($user->User)->address;

			//var_dump($address);
	
			$privkey = $coin->dumpprivkey($address);
			$betreff = 'Ur LiteWorlds.quest Network Private Omnilite Key';
			$nachricht = $privkey;
			$header = 
				'From: Security <security@liteworlds.quest>' . "\r\n" .
				'Reply-To: Security <security@liteworlds.quest>' . "\r\n" .
				'MIME-Version: 1.0' . "\r\n" .
				'Content-type: text/html; charset=iso-8859-1' . "\r\n" .
				'X-Mailer: PHP/' . phpversion();
			mail($user->Mail, $betreff, $nachricht, $header);
		}

		function prepareCreateProperty($userdata, $name, $category, $subcategory, $url, $data){
			$keys = self::craftKeys($userdata->name, $userdata->lastip, "createproperty");
			$time = time() + 300;

			$stmt = self::$_db->prepare("INSERT INTO createproperty (Name, IP, Time, CopperKey, JadeKey, CrystalKey, PropertyName, Category, SubCategory, PropertyURL, PropertyData) VALUES (:name, :ip, :time, :copperkey, :jadekey, :crystalkey, :propertyname, :category, :subcategory, :propertyurl, :propertydata)");
			$stmt->bindParam(":name", $userdata->name);
			$stmt->bindParam(":ip", $userdata->lastip);
			$stmt->bindParam(":time", $time);
			$stmt->bindParam(":copperkey", $keys->copper);
			$stmt->bindParam(":jadekey", $keys->jade);
			$stmt->bindParam(":crystalkey", $keys->crystal);
			$stmt->bindParam(":propertyname", $name);
			$stmt->bindParam(":category", $category);
			$stmt->bindParam(":subcategory", $subcategory);
			$stmt->bindParam(":propertyurl", $url);
			$stmt->bindParam(":propertydata", $data);
			$stmt->execute();

			echo $stmt->rowCount();
			print_r($stmt->errorInfo());

			if($stmt->rowCount() === 1){
				// send mail for verfication
				$empfaenger  = $userdata->mail;
				$betreff = 'Sign ur Transaction on LiteWorlds.quest Network';
	
				// message
				$link = 'https://api.liteworlds.quest/?method=Vomnicreateproperty&name='.$userdata->name.'&copperkey='.$keys->copper.'&jadekey='.$keys->jade.'&crystalkey='.$keys->crystal;
				//echo '<br>'.$link;
				$nachricht = '
					<a target="_blank" rel="noopener noreferrer" href="'.$link.'">
						<button style="font-size:24px;width:37%;background-color:transparent;cursor:crosshair;border:3px solid darkgreen;border-radius:7px;">SIGN</button>
					</a>
					<p>'.$link.'</p>



				<html>
					<body>
						<p>Create Property '.$name.'</p>
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
				mail($empfaenger, $betreff, $nachricht, $header);
	
				return '{"answer":"Sending creation prepared, sign it via mail", "bool":1}';
			}
		}
		function createProperty($name, $ip, $copperkey, $jadekey, $crystalkey){
			$wallet = json_decode(self::getAddress($name));

			$stmt = self::$_db->prepare("SELECT * FROM createproperty WHERE Name=:name AND IP=:ip AND CopperKey=:copperkey AND JadeKey=:jadekey AND CrystalKey=:crystalkey");
			$stmt->bindParam(":name", $name);
			$stmt->bindParam(":ip", $ip);
			$stmt->bindParam(":copperkey", $copperkey);
			$stmt->bindParam(":jadekey", $jadekey);
			$stmt->bindParam(":crystalkey", $crystalkey);
			$stmt->execute();

			echo $stmt->rowCount();
			$data = (object)$stmt->fetchALL(PDO::FETCH_ASSOC)[0];
			print_r($data);

			$coin = new Coin(self::$_rpc_user, self::$_rpc_pw, self::$_rpc_host, self::$_rpc_port);
			$txid = $coin->omni_sendissuancemanaged($wallet->address, 1, 5, 0, $data->Category, $data->SubCategory, $data->PropertyName, $data->PropertyURL, $data->PropertyData);

			if ($txid != '') {
				$stmt = self::$_db->prepare("DELETE FROM createproperty WHERE Name=:name AND IP=:ip AND CopperKey=:copperkey AND JadeKey=:jadekey AND CrystalKey=:crystalkey");
				$stmt->bindParam(":name", $name);
				$stmt->bindParam(":ip", $ip);
				$stmt->bindParam(":copperkey", $copperkey);
				$stmt->bindParam(":jadekey", $jadekey);
				$stmt->bindParam(":crystalkey", $crystalkey);
				$stmt->execute();

				echo 'Transaction succesfull<br>Ur TXID: '.$txid;
			}else {
				echo 'Transaction Error';
			}
		}

		function prepareMintNFT($userdata, $propertyid, $grantdata){
			$user = new User;
			$privateUserdata = $user->Pget($userdata->name);
			$keys = self::craftKeys($userdata->name, $privateUserdata->lastip, "mintnft");
			$time = time() + 300;
			$amount = 1;

			print_r($privateUserdata);

			//$grantdata->object = json_decode($grantdata->object);

			//print_r(json_encode($grantdata));
			//return 0;

			//$grantdata = json_encode($grantdata);

			$stmt = self::$_db->prepare("INSERT INTO mintnft (Name, IP, Time, CopperKey, JadeKey, CrystalKey, PropertyID, Amount, GrantData) VALUES (:name, :ip, :time, :copperkey, :jadekey, :crystalkey, :propertyid, :amount, :grantdata)");
			$stmt->bindParam(":name", $userdata->name);
			$stmt->bindParam(":ip", $privateUserdata->lastip);
			$stmt->bindParam(":time", $time);
			$stmt->bindParam(":copperkey", $keys->copper);
			$stmt->bindParam(":jadekey", $keys->jade);
			$stmt->bindParam(":crystalkey", $keys->crystal);
			$stmt->bindParam(":propertyid", $propertyid);
			$stmt->bindParam(":amount", $amount);
			$stmt->bindParam(":grantdata", $grantdata);
			$stmt->execute();

			//echo $stmt->rowCount();
			print_r($stmt->errorInfo());

			$grantdata = json_decode($grantdata);

			if($stmt->rowCount() === 1){
				// send mail for verfication
				$empfaenger  = $privateUserdata->mail;
				$betreff = 'Sign ur Transaction on LiteWorlds.quest Network';
	
				// message
				$link = 'https://api.liteworlds.quest/?method=Vomnimintnft&name='.$userdata->name.'&copperkey='.$keys->copper.'&jadekey='.$keys->jade.'&crystalkey='.$keys->crystal;
				//echo '<br>'.$link;
				$nachricht = '
					<a target="_blank" rel="noopener noreferrer" href="'.$link.'">
						<button style="font-size:24px;width:37%;background-color:transparent;cursor:crosshair;border:3px solid darkgreen;border-radius:7px;">SIGN</button>
					</a>
					<p>'.$link.'</p>



				<html>
					<body>
						<p>Mint '.$amount.' '.$grantdata->name.'</p>
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
				mail($empfaenger, $betreff, $nachricht, $header);
	
				return '{"answer":"Sending creation prepared, sign it via mail", "bool":1}';
			}
		}
		function mintNFT($name, $ip, $copperkey, $jadekey, $crystalkey){
			$wallet = json_decode(self::getAddress($name));

			$stmt = self::$_db->prepare("SELECT * FROM mintnft WHERE Name=:name AND IP=:ip AND CopperKey=:copperkey AND JadeKey=:jadekey AND CrystalKey=:crystalkey");
			$stmt->bindParam(":name", $name);
			$stmt->bindParam(":ip", $ip);
			$stmt->bindParam(":copperkey", $copperkey);
			$stmt->bindParam(":jadekey", $jadekey);
			$stmt->bindParam(":crystalkey", $crystalkey);
			$stmt->execute();

			$data = (object)$stmt->fetchALL(PDO::FETCH_ASSOC)[0];
			$data->GrantData = str_replace('\\', '', $data->GrantData);

			$grantdata = json_decode($data->GrantData);
			//$grantdata->object = json_encode($grantdata->object);
			
			//print_r($grantdata);
			//$data->GrantData->object = null;
			//print_r(json_decode($data->GrantData));

			$coin = new Coin(self::$_rpc_user, self::$_rpc_pw, self::$_rpc_host, self::$_rpc_port);
			$txid = $coin->omni_sendgrant($wallet->address, "", (int)$data->PropertyID, $data->Amount, json_encode($grantdata));

			echo '<br>TXID: '.$txid.'<br>';

			if ($txid != '') {
				$stmt = self::$_db->prepare("DELETE FROM mintnft WHERE Name=:name AND IP=:ip AND CopperKey=:copperkey AND JadeKey=:jadekey AND CrystalKey=:crystalkey");
				$stmt->bindParam(":name", $name);
				$stmt->bindParam(":ip", $ip);
				$stmt->bindParam(":copperkey", $copperkey);
				$stmt->bindParam(":jadekey", $jadekey);
				$stmt->bindParam(":crystalkey", $crystalkey);
				$stmt->execute();

				echo 'Transaction succesfull<br>Ur TXID: '.$txid;
			}else {
				echo 'Transaction Error';
			}
		}

		function prepareSendNFT($user, $to, $propertyid, $start){
			$USER = new User;
			$wallet = self::getAddress($user->User);
			$lastip = $USER->Pget($user->User)->LastIP;
			$keys = self::craftKeys($user->User, $lastip, "sendnft");
			$time = time() + 120;

			$stmt = self::$_db->prepare("INSERT INTO sendnft (User, IP, Time, CopperKey, JadeKey, CrystalKey, LFrom, LTo, PropertyID, TokenStart, TokenEnd) VALUES (:user, :ip, :time, :copperkey, :jadekey, :crystalkey, :lfrom, :lto, :propertyid, :tstart, :tend)");
			$stmt->bindParam(":user", $user->User);
			$stmt->bindParam(":ip", $lastip);
			$stmt->bindParam(":time", $time);
			$stmt->bindParam(":copperkey", $keys->copper);
			$stmt->bindParam(":jadekey", $keys->jade);
			$stmt->bindParam(":crystalkey", $keys->crystal);
			$stmt->bindParam(":lfrom", $wallet->address);
			$stmt->bindParam(":lto", $to);
			$stmt->bindParam(":propertyid", $propertyid);
			$stmt->bindParam(":tstart", $start);
			$stmt->bindParam(":tend", $start);
			$stmt->execute();

			var_dump($stmt->errorInfo());

			if($stmt->rowCount() === 1){
				// send mail for verfication
				$empfaenger  = $USER->Pget($user->User)->Mail;
				$betreff = 'Sign ur Transaction on LiteWorlds.quest Network';
	
				// message
				$link = 'https://api.liteworlds.quest/?method=Vsendomninft&user='.$user->User.'&copperkey='.$keys->copper.'&jadekey='.$keys->jade.'&crystalkey='.$keys->crystal;
				//echo '<br>'.$link;
				$nachricht = '
					<a target="_blank" rel="noopener noreferrer" href="'.$link.'">
						<button style="font-size:24px;width:37%;background-color:transparent;cursor:crosshair;border:3px solid darkgreen;border-radius:7px;">SIGN</button>
					</a>
					<p>'.$link.'</p>



				<html>
					<body>
						<p>Sending 1 NFT to '.$to.'</p>
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
				mail($empfaenger, $betreff, $nachricht, $header);
	
				return '{"answer":"Sending creation prepared, sign it via mail", "bool":1}';
			}

		}
		function sendNFT($user, $ip, $copperkey, $jadekey, $crystalkey){
			$stmt = self::$_db->prepare("SELECT * FROM sendnft WHERE User=:user AND IP=:ip AND CopperKey=:copperkey AND JadeKey=:jadekey AND CrystalKey=:crystalkey");
			$stmt->bindParam(":user", $user);
			$stmt->bindParam(":ip", $ip);
			$stmt->bindParam(":copperkey", $copperkey);
			$stmt->bindParam(":jadekey", $jadekey);
			$stmt->bindParam(":crystalkey", $crystalkey);
			$stmt->execute();
			$data = (object)$stmt->fetchALL(PDO::FETCH_ASSOC)[0];
			print_r($data);

			$coin = new Coin(self::$_rpc_user, self::$_rpc_pw, self::$_rpc_host, self::$_rpc_port);
			$txid = $coin->omni_sendnonfungible($data->LFrom, $data->LTo, (int)$data->PropertyID, (int)$data->TokenStart, (int)$data->TokenEnd);

			if ($txid != '') {
				$stmt = self::$_db->prepare("DELETE FROM sendnft WHERE User=:user AND IP=:ip AND CopperKey=:copperkey AND JadeKey=:jadekey AND CrystalKey=:crystalkey");
				$stmt->bindParam(":user", $user);
				$stmt->bindParam(":ip", $ip);
				$stmt->bindParam(":copperkey", $copperkey);
				$stmt->bindParam(":jadekey", $jadekey);
				$stmt->bindParam(":crystalkey", $crystalkey);
				$stmt->execute();

				echo 'Transaction succesfull<br>Ur TXID: '.$txid;
			}else {
				echo 'Transaction Error';
			}
		}

		function getCollections($address){
			$coin = new Coin(self::$_rpc_user, self::$_rpc_pw, self::$_rpc_host, self::$_rpc_port);
			$Ocollections = $coin->omni_getallbalancesforaddress($address);
			$collections = $coin->omni_listproperties();
			$issuer = array();
			$holder = array();

			//print_r($Ocollections);

			for ($i=0; $i < count($collections); $i++) { 
				if ($collections[$i]['non-fungibletoken'] == 1 && $collections[$i]['issuer'] == $address) {
					$property = $coin->omni_getproperty($collections[$i]['propertyid']);
					array_push($issuer, $property);
				}
				
			}

			//print_r($result);
			if (count($issuer) > 0) {
				for ($i=0; $i < count($Ocollections); $i++) {
					for ($x=0; $x < count($issuer); $x++) { 
						if ($Ocollections[$i]['propertyid'] == $issuer[$x]['propertyid']) {
							$x = count($issuer);
						}
	
						if ($x == (count($issuer) - 1)) {
							$property = $coin->omni_getproperty($Ocollections[$i]['propertyid']);					
							if ($property['non-fungibletoken'] == 1) {
								array_push($holder, $property);
							}
						}
					}
				}
			}else{
				for ($i=0; $i < count($Ocollections); $i++) {
					$property = $coin->omni_getproperty($Ocollections[$i]['propertyid']);					
					if ($property['non-fungibletoken'] == 1) {
						array_push($holder, $property);
					}
				}
			}

			$result = array("issuer"=>$issuer,"holder"=>$holder);
			return $result;
		}
		function getNFTs($address, $propertyid, $tokenstart){
			$coin = new Coin(self::$_rpc_user, self::$_rpc_pw, self::$_rpc_host, self::$_rpc_port);
			$myToken = $coin->omni_getnonfungibletokens($address);
			$result = array();
			$nft = array();
			$amount = 15;

			//echo json_encode($myToken, JSON_PRETTY_PRINT);
			//print_r($myToken[0]["tokens"][0]["tokenstart"]);

			for ($a=0; $a < count($myToken); $a++) {
				if ($myToken[$a]['propertyid'] == $propertyid) {
					for ($b=0; $b < count($myToken[$a]["tokens"]); $b++) { 
						if ($tokenstart <= $myToken[$a]["tokens"][$b]["tokenend"]) {
							$ts = $myToken[$a]["tokens"][$b]["tokenstart"];
							$te = $myToken[$a]["tokens"][$b]["tokenend"];

							if ($tokenstart > $ts) {
								$ts = $tokenstart;
							}

							if (($te - $ts) > $amount) {
								$te = $ts + $amount - 1;
							}

							$nft = $coin->omni_getnonfungibletokendata((int)$propertyid, (int)$ts, (int)$te);

							//echo json_encode($nft, JSON_PRETTY_PRINT);

							for ($c=0; $c < count($nft); $c++) { 
								if ($amount > 0) {
									array_push($result, $nft[$c]);
									$amount--;

									if (count($result) === 15) {
										return $result;
									}
								}
							}
							
						}
					}
				}
			}
			return $result;
		}
		function publicNFTview($propertyid, $ts) {
			$te = $ts + 25;
			$coin = new Coin(self::$_rpc_user, self::$_rpc_pw, self::$_rpc_host, self::$_rpc_port);
			$result = $coin->omni_getnonfungibletokendata((int)$propertyid, (int)$ts, (int)$te);
			echo json_encode($result, JSON_PRETTY_PRINT);
		}

		function publicGetBalance($address) {
			$coin = new Coin(self::$_rpc_user, self::$_rpc_pw, self::$_rpc_host, self::$_rpc_port);
			//$addresses = array("MT8httQRtAQHwYT1mM8bxewgXSdsDWuFsr");
			$result = $coin->omni_getallbalancesforaddress($address);
			echo json_encode($result, JSON_PRETTY_PRINT);
		}

		function devground(){
			$coin = new Coin(self::$_rpc_user, self::$_rpc_pw, self::$_rpc_host, self::$_rpc_port);
			$balances = $coin->omni_getnonfungibletokens('MU78ANEyiaAAjM4Z7HT8zTB3HWCzrXvM6i');
			$amount = 0;
			$result = (object)array();
			$result->answer = 'LiteWorlds DevGround Zero Sale ';
			$result->bool = false;

			for ($a=0; $a < count($balances); $a++) { 
				if ($balances[$a]['propertyid'] === 3516) {
					for ($b=0; $b < count($balances[$a]['tokens']); $b++) { 
						if ($balances[$a]['tokens'][$b]['tokenstart'] <= 10000) {
							$amount += $balances[$a]['tokens'][$b]['amount'];
						}
						if ($balances[$a]['tokens'][$b]['tokenstart'] <= 10000 && $balances[$a]['tokens'][$b]['tokenend'] > 10000) {
							$amount -= ($balances[$a]['tokens'][$b]['tokenend'] - 10000); 
						}
					}
				}
			}

			if ($amount - 5000 > 0) {
				$result->answer .= 'active';
				$result->bool = true;
				$result->supply = 5000;
				$result->sale = $amount - 5000;
				$result->price = 0.25;
			}else{
				$result->answer .= 'inactive';
			}

			return $result;
		}
		function devgroundPrepare($userdata){
			$payto = 'ltc1qt7celuzafdh20x4dgaq4as050uealnnhhuzfja';
			$useromni = json_decode(self::getAddress($userdata->name));

			if ($useromni->balance > 0.25) {
				# code...
			}

			print_r($useromni);
		}

		function history($user, $start) {
			$count = 10000;
			$bcfactor = 100000000;
			$omni = self::getAddress($user);
			$coin = new Coin(self::$_rpc_user, self::$_rpc_pw, self::$_rpc_host, self::$_rpc_port);
			$history = $coin->listtransactions($user, $count, $start);
			$history = array_reverse($history);
			$txids = array();
			$legacy = array();
			$segwit = array();
			$taproot = array();

			$unspent = $coin->listunspent(0, 999999999, (array)$omni->address);
			//var_dump($unspent);
			

			for ($a=0; $a < count($history); $a++) { 
				$history[$a]['unspent'] = false;
				$history[$a]['amount'] = number_format($history[$a]['amount'], 8, ".", "");
				for ($b=0; $b < count($unspent); $b++) { 
					if ($history[$a]['txid'] == $unspent[$b]['txid']) {
						$history[$a]['unspent'] = true;
					}
				}
			}

			//var_dump($history);
			echo json_encode($history, JSON_PRETTY_PRINT);

			/*for ($a=0; $a < count($segwit); $a++) { 
				for ($b=0; $b < count($unspent['segwit']); $b++) { 
					if ($segwit[$a]->txid == $unspent['segwit'][$b]['txid']) {
						$segwit[$a]->unspent = true;
					}
				}
			}

			for ($a=0; $a < count($taproot); $a++) { 
				for ($b=0; $b < count($unspent['taproot']); $b++) { 
					if ($taproot[$a]->txid == $unspent['taproot'][$b]['txid']) {
						$taproot[$a]->unspent = true;
					}
				}
			}*/

			//var_dump($array);
			//echo json_encode($omni, JSON_PRETTY_PRINT);
		}
   }
