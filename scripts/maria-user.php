<?php
require_once "/var/www/liteworlds/scripts/key.php";

class User{
    private static $_db_username = 'maria';
    private static $_db_passwort = 'KerkerRocks22';
    private static $_db_host = '127.0.0.1';
    private static $_db_name = 'API_user';
    private static $_db;
    
    function __construct(){
        try{
            self::$_db = new PDO("mysql:host=" . self::$_db_host . ";dbname=" . self::$_db_name, self::$_db_username, self::$_db_passwort);
        }catch(PDOException $e){
            echo "<br>DATABASE ERROR<br>".$e;
            die();
        }
    }

    function hello() {
        $result = (object)array();
        $result->answer = "Hello World, I'm a Litecoin - Wallet and Blockexplorer API. How can I help U?";
        $result->bool = true;
        $result->ip = $_SERVER['REMOTE_ADDR'];

        $result->commands = array(
            'help'=>'more detailed help guide powered by SadFrogLTC',
            'server-stats&authkey='=>'get server load and function counter (DevPass required)',
            'user-total'=>'get total count of accounts',
            'user-register&user=&mail=&pass='=>'prepare a account creation (pass need to be sha512 encrypted!)',
            'user-login&user=&pass=&ip='=>'prepare a login (ip is optionally)',
            'user-logout&authkey='=>'logout user',
            'user-iplock&authkey='=>'prepare toggle on-off the ip-lock, additionall security (ip @ user-login becomes required!)',
            'user-change-pass&authkey=&oldpass=&newpass='=>'prepare a password change',
            'user-get&authkey='=>'request public user data',
            'hack&copperkey=&jadekey=&crystalkey='=>'attack hack dummy'
        );

        return $result;
    }

    function help() {
        return '
        <!doctype html>
            <html lang="en">
            <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <title>Help</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
            </head>
            <body>
                <h1 class="text-center">Walkthrough<img src="icon.png" width="50em"></h1>

            <div class="m-5">
            <content>
                <p >LiteWorlds is an All-in-One secure gateway to open up the potential of the Litecoin ecosystem.  Our non-costodial hot-wallet offers a convenient and secure way to, not only transact, but also, verify for everyone!
                <br> With access to our in house developing tools and block explorer, we hope more developers will help build and automate a community where new people feel welcomed through convenience.
                </p>
                <h3>Public API Documentation</h3>
            <hr>
            <br>
            <h2 class="text-center"><b>https://api.liteworlds.quest</b><u>/?method</u><i>=query</i></h2>
            <h4 class="m-5">The API is used for formatting requests or queries in the URL to the LiteWorlds server. The first part in <b>bold</b> is the host, where we are sending the request to,
            the <u>underlined</u> part prepares the server that we are giving a query and to get ready to accept some parameters. The \'query\' would then be replaced with the instructed commands to generate a response, usually formated in
                JSON.


            <hr>


            <br>

            <p class="text-center">Want to pull up all the pieces of an nft collection? Type this into the URL!

            <br>
            <br>
            <br>

            <b>https://api.liteworlds.quest</b><u>/?method</u>=omni-get-nft-public&propertyid=3545
            <br>
            <br>
            <p class="text-success small"><em>This queries all of the holders of a SadFrogLTC, a great project that is working along side us here at Liteworlds to be able to provide you the best experience.</em></p>
            <h3>Secured API Documentation</h3>
            <hr>
            <ul class="lead"> Intention
            <p>Give you the full funtionality of your Litecoin wallets, transactions, and the capabilities to verify you NFT\'s in a convenient manner for you to use and handle how you want.</p>
            </ul>
            <br>
            </content>

            </div>



                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
            </body>
            </html>
        ';
    }

    function total(){
        $result->answer = "Here is the number of total user accounts";
        $result->bool = true;

        $stmt = self::$_db->prepare("SELECT * FROM user");
        $stmt->execute();
        $result->total_users = $stmt->rowCount();

        return $result;
    }

    function hack($copperkey, $jadekey, $crystalkey){
        $stmt = self::$_db->prepare("SELECT * FROM hack WHERE CopperKey=:copperkey AND JadeKey=:jadekey AND CrystalKey=:crystalkey");
        $stmt->bindParam(":copperkey", $copperkey);
        $stmt->bindParam(":jadekey", $jadekey);
        $stmt->bindParam(":crystalkey", $crystalkey);
        $stmt->execute();

        if($stmt->rowCount() == 1){
            $object = (object)$stmt->fetchAll(PDO::FETCH_ASSOC)[0];
            $success = $object->Success + 1;

            $stmt = self::$_db->prepare("UPDATE hack SET Success=:success");
            $stmt->bindParam(":success", $success);
            $stmt->execute();

            return (object)array("answer"=>"Hack successful","bool"=>1,"Success#"=>$success);
        }else{
            $stmt = self::$_db->prepare("SELECT * FROM hack");
            $stmt->execute();
            $object = (object)$stmt->fetchAll(PDO::FETCH_ASSOC)[0];

            $fail = $object->Fail + 1;

            $stmt = self::$_db->prepare("UPDATE hack SET Fail=:fail");
            $stmt->bindParam(":fail", $fail);
            $stmt->execute();

            return (object)array("answer"=>"Hack failed","bool"=>0,"Fail#"=>$fail);
        }
    }

    function test(){
        $stmt = self::$_db->prepare("SELECT Success, Fail FROM hack");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function kotia_faucet($name){
        $expire = time() + 86400;
        var_dump($expire);
        $stmt = self::$_db->prepare("UPDATE user SET kotia_faucet=:kotia_faucet WHERE Name=:name");
        $stmt->bindParam(":kotia_faucet", $expire);
        $stmt->bindParam(":name", $name);
        $stmt->execute();
    }

    function prepareRegister($user, $mail, $pass){
        // force name to uppercase
        $user = strtoupper($user);
        // force mail to lowercase
        $mail = strtolower($mail);

        // check there are special characters in the name
        if (strlen($user) != strlen(preg_replace( "/[^a-zA-Z0-9]/", "", $user))) {
            return (object)array('answer'=>'Specialchars in Username are not allowed','bool'=>false);
        }

        // check length of Name
        if (strlen($user) < 6) {
            return (object)array('answer'=>'Name is to short, min 6 characters','bool'=>false);
        }
        if (strlen($user) > 18) {
            return (object)array('answer'=>'Name is to long, max 18 characters','bool'=>false);
        }

        // check mail is a mail
        $maildiff = strlen($mail) - strlen(preg_replace( "/[.@]/", "", $mail));
        if ($maildiff < 2 || strlen($mail) < 9) {
            return (object)array('answer'=>'Mail is not a mail','bool'=>false);
        }

        // check mail is gmail or protonmail
        $maillist = array('gmail.com', 'protonmail.com', 'proton.me');
        $check = false;
        for ($a=0; $a < count($maillist); $a++) { 
            if (explode('@', $mail)[1] == $maillist[$a]) {
                $check = true;
            }
        }
        if (!$check) {
            return (object)array('answer'=>'Invalid Mailprovider, plz use gmail.com, protonmail.com or proton.me', 'bool'=>false);
        }

        // check pass is sha512 hash
        if (strlen($pass) != strlen(preg_replace( "/[^a-zA-Z0-9]/", "", $pass)) || strlen($pass) != 128) {
            return (object)array('answer'=>'Password is not sha512 encrypted','bool'=>false);
        }

        // check user availability
        $stmt = self::$_db->prepare("SELECT * FROM user WHERE User=:user");
        $stmt->bindParam(":user", $user);
        $stmt->execute();
        if($stmt->rowCount() == 1)return (object)array('answer'=>'User is allready taken','bool'=>false);

        $stmt = self::$_db->prepare("SELECT * FROM register WHERE User=:user");
        $stmt->bindParam(":user", $user);
        $stmt->execute();
        if($stmt->rowCount() == 1)return (object)array('answer'=>'User is allready taken','bool'=>false);

        // check mail availability
        $stmt = self::$_db->prepare("SELECT * FROM user WHERE Mail=:mail");
        $stmt->bindParam(":mail", $mail);
        $stmt->execute();
        if($stmt->rowCount() == 1)return (object)array('answer'=>'Mail is allready taken','bool'=>false);

        $stmt = self::$_db->prepare("SELECT * FROM register WHERE Mail=:mail");
        $stmt->bindParam(":mail", $mail);
        $stmt->execute();
        if($stmt->rowCount() == 1)return (object)array('answer'=>'Mail is allready taken','bool'=>false);

        // generate the keys
        $done = false;
        $key = new Key;
        do {
            $keys = $key->Craft2FA();
            $stmt = self::$_db->prepare("SELECT * FROM register WHERE CopperKey=:copper AND JadeKey=:jade AND CrystalKey=:crystal");
            $stmt->bindParam(":copper", $keys->copper);
            $stmt->bindParam(":jade", $keys->jade);
            $stmt->bindParam(":crystal", $keys->crystal);
            $stmt->execute();
            if ($stmt->rowCount() == 0) {
                $done = true;
            }
        } while (!$done);

        // adding to MemoryTable
        $time = time() + 600;

        $stmt = self::$_db->prepare("INSERT INTO register (User, Mail, Pass, Time, CopperKey, JadeKey, CrystalKey) VALUES (:user, :mail, :pass, :time, :copper, :jade, :crystal)");
        $stmt->bindParam(":user", $user);
        $stmt->bindParam(":mail", $mail);
        $stmt->bindParam(":pass", $pass);
        $stmt->bindParam(":time", $time);
        $stmt->bindParam(":copper", $keys->copper);
        $stmt->bindParam(":jade", $keys->jade);
        $stmt->bindParam(":crystal", $keys->crystal);
        $stmt->execute();
        //print_r($stmt->errorInfo());
        if($stmt->rowCount() == 1){
            // send mail for verfication
            $empfaenger  = $mail;
            $betreff = 'Sign ur Registration on LiteWorlds.quest Network';

            // message
            $link = 'https://api.liteworlds.quest/?method=user-register-sign&user='.$user.'&copperkey='.$keys->copper.'&jadekey='.$keys->jade.'&crystalkey='.$keys->crystal;
            $nachricht = '
                <p>U are going to create a Account at LiteWorlds.</p>
                <p>User: '.$user.'</p>
                <a target="_blank" rel="noopener noreferrer" href="'.$link.'">
                <button style="font-size:24px;width:37%;background-color:transparent;cursor:crosshair;border:3px solid darkgreen;border-radius:7px;">SIGN</button></a>
                <p>'.$link.'</p>
            ';

            $header = 
                'From: Security <security@liteworlds.quest>' . "\r\n" .
                'Reply-To: Security <security@liteworlds.quest>' . "\r\n" .
                'MIME-Version: 1.0' . "\r\n" .
                'Content-type: text/html; charset=iso-8859-1' . "\r\n" .
                'X-Mailer: PHP/' . phpversion();

            // send mail
            mail($empfaenger, $betreff, $nachricht, $header);

            return (object)array('answer'=>'Account creation prepared','bool'=>true);
        }else{
            return (object)array('answer'=>'Account creation failed by internal database error','bool'=>false);
        }
    }

    function register($user, $copperkey, $jadekey, $crystalkey){ 
        // Grep User's Data
        $stmt = self::$_db->prepare("SELECT * FROM register WHERE User=:user AND CopperKey=:copperkey AND JadeKey=:jadekey AND CrystalKey=:crystalkey");
        $stmt->bindParam(":user", $user);
        $stmt->bindParam(":copperkey", $copperkey);
        $stmt->bindParam(":jadekey", $jadekey);
        $stmt->bindParam(":crystalkey", $crystalkey);
        $stmt->execute();
        if($stmt->rowCount() == 1){
            // create Account
            $data = (object)$stmt->fetchAll(PDO::FETCH_ASSOC)[0];
            $time = time();

            $stmt = self::$_db->prepare("INSERT INTO user (User, Mail, Pass, CreateTime) VALUES (:user, :mail, :pass, :createTime)");
            $stmt->bindParam(":user", $data->User);
            $stmt->bindParam(":mail", $data->Mail);
            $stmt->bindParam(":pass", $data->Pass);
            $stmt->bindParam(":createTime", $time);
            $stmt->execute();
            if($stmt->rowCount() == 1){
                // Delete from HotTable
                $stmt = self::$_db->prepare("DELETE FROM register WHERE User=:user");
                $stmt->bindParam(":user", $data->User);
                $stmt->execute();

                return '<h1>Ur Account has been succesfully signed & created</h1><p>this page will close in 10 seconds</p><script>setTimeout(function(){window.close()}, 10000)</script>';
            }else{
                return '<h1>Internal Database write error</h1>';
            }
        }else{
            return '<h1>Action not found in database</h1>';
        }
    }

    function prepareLogin($user, $pass, $ip){
        // prepare return
        $result = (object)array();
        $result->bool = false;

        // force name to uppercase
        $user = strtoupper($user);

        // check pass is sha512 hash
        if (strlen($pass) != strlen(preg_replace( "/[^a-zA-Z0-9]/", "", $pass)) || strlen($pass) != 128) {
            $result->answer = 'Pass not sha512';
            return $result;
        }

        // check user exists
        $stmt = self::$_db->prepare("SELECT * FROM user WHERE User=:user AND Pass=:pass");
        $stmt->bindParam(":user", $user);
        $stmt->bindParam(":pass", $pass);
        $stmt->execute();
        if ($stmt->rowCount() != 1) {
            $result->answer = 'User or Pass wrong';
            return $result;
        }

        $data = self::Pget($user);

        // check user allrdy prepared to login
        $stmt = self::$_db->prepare("SELECT * FROM login WHERE User=:user");
        $stmt->bindParam(":user", $user);
        $stmt->execute();
        if($stmt->rowCount() === 0){
            // generate the keys
            $done = false;
            $key = new Key;
            do {
                $keys = $key->Craft2FA();
                $stmt = self::$_db->prepare("SELECT * FROM login WHERE CopperKey=:copper AND JadeKey=:jade AND CrystalKey=:crystal");
                $stmt->bindParam(":copper", $keys->copper);
                $stmt->bindParam(":jade", $keys->jade);
                $stmt->bindParam(":crystal", $keys->crystal);
                $stmt->execute();
                if ($stmt->rowCount() == 0) {
                    $done = true;
                }
            } while (!$done);

            $done = false;

            do {
                $authkey = $key->CraftAuth();

                $stmt = self::$_db->prepare("SELECT * FROM login WHERE AuthKey=:authkey");
                $stmt->bindParam(":authkey", $authkey);
                $stmt->execute();
                if($stmt->rowCount() == 0){
                    $stmt = self::$_db->prepare("SELECT * FROM user WHERE AuthKey=:authkey");
                    $stmt->bindParam(":authkey", $authkey);
                    $stmt->execute();
                    if($stmt->rowCount() == 0)$done = true;
                }
            } while (!$done);

            $time = time() + 120;

            

            $stmt = self::$_db->prepare("INSERT INTO login (User, CopperKey, JadeKey, CrystalKey, AuthKey, IP, Time) VALUES (:user, :copper, :jade, :crystal, :auth, :ip, :time)");
            $stmt->bindParam(":user", $user);
            $stmt->bindParam(":copper", $keys->copper);
            $stmt->bindParam(":jade", $keys->jade);
            $stmt->bindParam(":crystal", $keys->crystal);
            $stmt->bindParam(":auth", $authkey);
            $stmt->bindParam(":ip", $ip);
            $stmt->bindParam(":time", $time);
            $stmt->execute();
            if($stmt->rowCount() == 1){
                // send mail for verfication
                $empfaenger  = self::Pget($user)->Mail;
                $betreff = 'Sign ur Login on LiteWorlds.quest Network';

                // Nachricht
                $link = 'https://api.liteworlds.quest/?method=user-login-sign&name='.$user.'&copperkey='.$keys->copper.'&jadekey='.$keys->jade.'&crystalkey='.$keys->crystal;
                $nachricht = '
                        <a target="_blank" rel="noopener noreferrer" href="'.$link.'">
                        <button style="font-size:24px;width:37%;background-color:transparent;cursor:crosshair;border:3px solid darkgreen;border-radius:7px;">SIGN</button></a>
                        <p>'.$link.'</p>
                ';

                $header = 
                    'From: Security <security@liteworlds.quest>' . "\r\n" .
                    'Reply-To: Security <security@liteworlds.quest>' . "\r\n" .
                    'MIME-Version: 1.0' . "\r\n" .
                    'Content-type: text/html; charset=iso-8859-1' . "\r\n" .
                    'X-Mailer: PHP/' . phpversion();

                // verschicke die E-Mail
                mail($empfaenger, $betreff, $nachricht, $header);

                $result->answer = 'Login prepared, User have to verify';
                $result->bool = true;
                $result->AuthKey = $authkey;
                return $result;
            }
        }else{
            $result->answer = 'User allrdy prepared';
            return $result;
        }
    }

    function login($user, $ip, $copperkey, $jadekey, $crystalkey){
        // force name to uppercase
        $user = strtoupper($user);

        // Grep User Data
        $stmt = self::$_db->prepare("SELECT * FROM login WHERE User=:user AND CopperKey=:copperkey AND JadeKey=:jadekey AND CrystalKey=:crystalkey");
        $stmt->bindParam(":user", $user);
        $stmt->bindParam(":copperkey", $copperkey);
        $stmt->bindParam(":jadekey", $jadekey);
        $stmt->bindParam(":crystalkey", $crystalkey);
        $stmt->execute();
        if($stmt->rowCount() == 1){
            $data = (object)$stmt->fetchAll(PDO::FETCH_ASSOC)[0];

            // get ipLock
            $stmt = self::$_db->prepare("SELECT IPlock FROM user WHERE User=:user");
            $stmt->bindParam(":user", $user);
            $stmt->execute();
            $ipLock = $stmt->fetch()['ipLock'];

            if ($ipLock) {
                if ($data->IP == $ip) {
                        //Login the User
                        $time = time();
                        $stmt = self::$_db->prepare("UPDATE user SET AuthKey=:authkey, LastAction=:lastAction, LastIP=:lastIP WHERE User=:user");
                        $stmt->bindParam(":authkey", $data->AuthKey);
                        $stmt->bindParam(":lastAction", $time);
                        $stmt->bindParam(":lastIP", $ip);
                        $stmt->bindParam(":user", $user);
                        $stmt->execute();
    
                        // Remove User from HotTable
                        $stmt = self::$_db->prepare("DELETE FROM login WHERE User=:user");
                        $stmt->bindParam(":user", $user);
                        $stmt->execute();
    
                        return 'User online';
                } else {
                    return 'ipLock Error';
                }
            } else {
                //Login the User
                $time = time();
                $stmt = self::$_db->prepare("UPDATE user SET AuthKey=:authkey, LastAction=:lastAction, LastIP=:lastIP WHERE User=:user");
                $stmt->bindParam(":authkey", $data->AuthKey);
                $stmt->bindParam(":lastAction", $time);
                $stmt->bindParam(":lastIP", $ip);
                $stmt->bindParam(":user", $user);
                $stmt->execute();

                // Remove User from HotList
                $stmt = self::$_db->prepare("DELETE FROM login WHERE User=:user");
                $stmt->bindParam(":user", $user);
                $stmt->execute();

                return 'User online';
            }
        }else{
            return 'User not found';
        }
    }

    function logout($authkey){
        $empty = NULL;

        $stmt = self::$_db->prepare("SELECT User FROM user WHERE AuthKey=:authkey");
        $stmt->bindParam(":authkey", $authkey);
        $stmt->execute();
        $user = $stmt->fetch()['User'];

        if ($stmt->rowCount() === 1) {
            $stmt = self::$_db->prepare("UPDATE user SET AuthKey=:empty WHERE User=:User");
            $stmt->bindParam(":empty", $empty);
            $stmt->bindParam(":user", $user);
            $stmt->execute();

            return true;
        }else{
            return false;
        }
    }

    function online($authkey){
        $stmt = self::$_db->prepare("SELECT * FROM user WHERE AuthKey=:authkey");
        $stmt->bindParam(":authkey", $authkey);
        $stmt->execute();

        if($stmt->rowCount() == 1){
            return (object)array('answer'=>'User is online','bool'=>true);
        }else{
            return (object)array('answer'=>'User is offline','bool'=>false);
        }
    }

    function HTcheck($name, $table){
        $stmt = self::$_db->prepare("SELECT * FROM $table WHERE Name=:name");
        $stmt->bindParam(":name", $name);
        $stmt->execute();

        if($stmt->rowCount() == 1){
            return '{"answer":"User is prepared to sign '.$table.'", "bool":1}';
        }else{
            return '{"answer":"User is not prepared to sign '.$table.'", "bool":0}';
        }
    }

    function get($authkey){
        $stmt = self::$_db->prepare("SELECT AccountLevel, User, Language, CreateTime, kotia_faucet FROM user WHERE AuthKey=:authkey");
        $stmt->bindParam(":authkey", $authkey);
        $stmt->execute();
        $array = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($array) === 1) {
            return (object)$array[0];
        }else{
            return false;
        }
    }

    function Pget($user){
        $stmt = self::$_db->prepare("SELECT * FROM user WHERE User=:user");
        $stmt->bindParam(":user", $user);
        $stmt->execute();
        $array = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($array) === 1) {
            return (object)$array[0];
        }else{
            return false;
        }
    }

    function prepareSetIPLock($authkey){
        $stmt = self::$_db->prepare("SELECT * FROM user WHERE AuthKey=:authkey");
        $stmt->bindParam(":authkey", $authkey);
        $stmt->execute();
        $object = (object)$stmt->fetchALL(PDO::FETCH_ASSOC)[0];

        if ($object->ipLock) {
            $lock = 0;
        } else {
            $lock = 1;
        }

        $key = new Key;
        $keys = $key->Craft2FA();
        $time = time() + 300;

        $stmt = self::$_db->prepare("SELECT * FROM iplock WHERE Name=:name");
        $stmt->bindParam(":name", $object->Name);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            $stmt = self::$_db->prepare("INSERT INTO iplock (Name, CopperKey, JadeKey, CrystalKey, IP, LockState, Time) VALUES (:name, :copperKey, :jadeKey, :crystalKey, :ip, :lock, :time)");
            $stmt->bindParam(":name", $object->Name);
            $stmt->bindParam(":copperKey", $keys->copper);
            $stmt->bindParam(":jadeKey", $keys->jade);
            $stmt->bindParam(":crystalKey", $keys->crystal);
            $stmt->bindParam(":lock", $lock);
            $stmt->bindParam(":ip", $object->LastIP);
            $stmt->bindParam(":time", $time);
            $stmt->execute();
            //print_r($stmt->errorInfo());

            /*$stmt = self::$_db->prepare("UPDATE user SET ipLock=:lock WHERE AuthKey=:authkey");
            $stmt->bindParam(":lock", $lock);
            $stmt->bindParam(":authkey", $authkey);
            $stmt->execute();*/

            if ($stmt->rowCount() == 1) {
                // send mail for verification
                $empfaenger  = $object->Mail;
                $betreff = 'Verify ur IP-Lock change on LiteWorlds.quest Network';

                // Nachricht
                $link = 'https://api.liteworlds.quest/?method=Viplock&name='.$object->Name.'&copperkey='.$keys->copper.'&jadekey='.$keys->jade.'&crystalkey='.$keys->crystal;
                $nachricht = '
                <html>
                    <head>
                        <title>Verify ur IP-Lock change on LiteWorlds.quest Network</title>
                    </head>
                    <body>
                        <a target="_blank" rel="noopener noreferrer" href="'.$link.'">
                        <button style="font-size:24px;width:37%;background-color:transparent;cursor:crosshair;border:3px solid darkgreen;border-radius:7px;">SIGN</button></a>
                    </body>
                </html>
                ';

                $header = 
                    'From: Security <security@liteworlds.quest>' . "\r\n" .
                    'Reply-To: Security <security@liteworlds.quest>' . "\r\n" .
                    'MIME-Version: 1.0' . "\r\n" .
                    'Content-type: text/html; charset=iso-8859-1' . "\r\n" .
                    'X-Mailer: PHP/' . phpversion();

                // verschicke die E-Mail
                mail($empfaenger, $betreff, $nachricht, $header);

                return true;
            } else {
                return false;
            }
        } else {
            # code...
        }
    }

    function setIPLock($name, $ip, $copperkey, $jadekey, $crystalkey){
        // Grep User Data
        $stmt = self::$_db->prepare("SELECT * FROM iplock WHERE Name=:name AND CopperKey=:copperkey AND JadeKey=:jadekey AND CrystalKey=:crystalkey");
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":copperkey", $copperkey);
        $stmt->bindParam(":jadekey", $jadekey);
        $stmt->bindParam(":crystalkey", $crystalkey);
        $stmt->execute();
        if($stmt->rowCount() == 1){
            $object = (object)$stmt->fetchAll(PDO::FETCH_ASSOC)[0];

            // get ipLock
            $stmt = self::$_db->prepare("SELECT ipLock FROM user WHERE Name=:name");
            $stmt->bindParam(":name", $name);
            $stmt->execute();
            $ipLock = $stmt->fetch()['ipLock'];

            if ($ipLock) {
                if ($object->IP === $ip) {
                        //Login the User
                        $time = time();
                        $stmt = self::$_db->prepare("UPDATE user SET ipLock=:lock WHERE Name=:name");
                        $stmt->bindParam(":lock", $object->LockState);
                        $stmt->bindParam(":name", $name);
                        $stmt->execute();
    
                        // Remove User from Waitlist
                        $stmt = self::$_db->prepare("DELETE FROM iplock WHERE Name=:name");
                        $stmt->bindParam(":name", $name);
                        $stmt->execute();
    
                        return 'User online';
                } else {
                    return 'ipLock Error';
                }
            } else {
                //Login the User
                $time = time();
                $stmt = self::$_db->prepare("UPDATE user SET ipLock=:lock WHERE Name=:name");
                $stmt->bindParam(":lock", $object->LockState);
                $stmt->bindParam(":name", $name);
                $stmt->execute();

                // Remove User from Waitlist
                $stmt = self::$_db->prepare("DELETE FROM iplock WHERE Name=:name");
                $stmt->bindParam(":name", $name);
                $stmt->execute();

                return 'User online';
            }
        }else{
            return 'User not found';
        }
    }

    function lastAction($authkey) {
        $time = time();

        $stmt = self::$_db->prepare("UPDATE user SET LastAction=:lastAction WHERE AuthKey=:authkey");
        $stmt->bindParam(":lastAction", $time);
        $stmt->bindParam(":authkey", $authkey);
        $stmt->execute();
    }

    function preparePassChange($user, $old_pass, $new_pass, $ip) {
        // prepare return
        $result = (object)array();
        $result->bool = false;

        // force name to uppercase
        $user = strtoupper($user);

        // check oldpass is sha512 hash
        if (strlen($old_pass) != strlen(preg_replace( "/[^a-zA-Z0-9]/", "", $old_pass)) || strlen($old_pass) != 128) {
            return (object)array('answer'=>'oldpass is not sha512 encrypted','bool'=>false);
        }

        // check newpass is sha512 hash
        if (strlen($new_pass) != strlen(preg_replace( "/[^a-zA-Z0-9]/", "", $new_pass)) || strlen($new_pass) != 128) {
            return (object)array('answer'=>'new_pass is not sha512 encrypted','bool'=>false);
        }

        $stmt = self::$_db->prepare("SELECT * FROM user WHERE User=:user AND Pass=:pass");
        $stmt->bindParam(":user", $user);
        $stmt->bindParam(":pass", $old_pass);
        $stmt->execute();
        //$array = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo $stmt->rowCount();
        if ($stmt->rowCount() === 1) {
            // check user allrdy prepared to change pass
            $stmt = self::$_db->prepare("SELECT * FROM pass WHERE User=:user");
            $stmt->bindParam(":user", $user);
            $stmt->execute();
            echo $stmt->rowCount();
            if($stmt->rowCount() === 0){
                // generate the keys
                $done = false;
                $key = new Key;
                do {
                    $keys = $key->Craft2FA();
                    $stmt = self::$_db->prepare("SELECT * FROM pass WHERE CopperKey=:copper AND JadeKey=:jade AND CrystalKey=:crystal");
                    $stmt->bindParam(":copper", $keys->copper);
                    $stmt->bindParam(":jade", $keys->jade);
                    $stmt->bindParam(":crystal", $keys->crystal);
                    $stmt->execute();
                    if ($stmt->rowCount() == 0) {
                        $done = true;
                    }
                } while (!$done);

                $time = time() + 120;

                $stmt = self::$_db->prepare("INSERT INTO pass (User, CopperKey, JadeKey, CrystalKey, Pass, IP, Time) VALUES (:user, :copper, :jade, :crystal, :pass, :ip, :time)");
                $stmt->bindParam(":user", $user);
                $stmt->bindParam(":copper", $keys->copper);
                $stmt->bindParam(":jade", $keys->jade);
                $stmt->bindParam(":crystal", $keys->crystal);
                $stmt->bindParam(":pass", $new_pass);
                $stmt->bindParam(":ip", $ip);
                $stmt->bindParam(":time", $time);
                $stmt->execute();
                var_dump($stmt->errorInfo());
                if($stmt->rowCount() == 1){
                    // send mail for verfication
                    $empfaenger  = self::Pget($user)->Mail;
                    $betreff = 'Sign ur Password Change on LiteWorlds.quest Network';

                    // Nachricht
                    $link = 'https://api.liteworlds.quest/?method=user-change-pass-sign&user='.$user.'&copperkey='.$keys->copper.'&jadekey='.$keys->jade.'&crystalkey='.$keys->crystal;
                    $nachricht = '
                            <a target="_blank" rel="noopener noreferrer" href="'.$link.'">
                            <button style="font-size:24px;width:37%;background-color:transparent;cursor:crosshair;border:3px solid darkgreen;border-radius:7px;">SIGN</button></a>
                            <p>'.$link.'</p>
                    ';

                    $header = 
                        'From: Security <security@liteworlds.quest>' . "\r\n" .
                        'Reply-To: Security <security@liteworlds.quest>' . "\r\n" .
                        'MIME-Version: 1.0' . "\r\n" .
                        'Content-type: text/html; charset=iso-8859-1' . "\r\n" .
                        'X-Mailer: PHP/' . phpversion();

                    // verschicke die E-Mail
                    mail($empfaenger, $betreff, $nachricht, $header);

                    $result->answer = 'pass change prepared, User have to verify';
                    $result->bool = true;
                    return $result;
                }
            }else{
                $result->answer = 'User allrdy prepared';
                return $result;
            }
        } else {
            $result->answer = 'User or Pass wrong';
            return $result;
        }
    }
    function PassChange($user, $ip, $copperkey, $jadekey, $crystalkey) {
        $stmt = self::$_db->prepare("SELECT * FROM pass WHERE User=:user AND CopperKey=:copperkey AND JadeKey=:jadekey AND CrystalKey=:crystalkey");
        $stmt->bindParam(":user", $user);
        $stmt->bindParam(":copperkey", $copperkey);
        $stmt->bindParam(":jadekey", $jadekey);
        $stmt->bindParam(":crystalkey", $crystalkey);
        $stmt->execute();
        var_dump($stmt->errorInfo());
        if($stmt->rowCount() == 1){
            $data = (object)$stmt->fetchAll(PDO::FETCH_ASSOC)[0];

            $stmt = self::$_db->prepare("UPDATE user SET Pass=:pass WHERE User=:user");
            $stmt->bindParam(":pass", $data->Pass);
            $stmt->bindParam(":user", $user);
            $stmt->execute();
            var_dump($stmt->errorInfo());
            if($stmt->rowCount() == 1){
                $stmt = self::$_db->prepare("DELETE FROM pass WHERE User=:user");
                $stmt->bindParam(":user", $user);
                $stmt->execute();
                var_dump($stmt->errorInfo());
            }
        }
    }

    function prepareMailChange($user, $old_mail, $new_mail) {
        // prepare return
        $result = (object)array();
        $result->bool = false;

        // force name to uppercase
        $user = strtoupper($user);

        $stmt = self::$_db->prepare("SELECT * FROM user WHERE User=:user AND Mail=:mail");
        $stmt->bindParam(":user", $user);
        $stmt->bindParam(":mail", $user);
        $stmt->execute();
        $array = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // check user allrdy prepared to change mail
        $stmt = self::$_db->prepare("SELECT * FROM mail WHERE User=:user");
        $stmt->bindParam(":user", $user);
        $stmt->execute();
        if($stmt->rowCount() === 0){
            // generate the keys
            $done = false;
            $key = new Key;
            do {
                $keys = $key->Craft2FA();
                $stmt = self::$_db->prepare("SELECT * FROM mail WHERE CopperKey=:copper AND JadeKey=:jade AND CrystalKey=:crystal");
                $stmt->bindParam(":copper", $keys->copper);
                $stmt->bindParam(":jade", $keys->jade);
                $stmt->bindParam(":crystal", $keys->crystal);
                $stmt->execute();
                if ($stmt->rowCount() == 0) {
                    $done = true;
                }
            } while (!$done);

            $time = time() + 600;

            $stmt = self::$_db->prepare("INSERT INTO mail (User, CopperKey, JadeKey, CrystalKey, Oldmail, NewMail, IP, Time) VALUES (:user, :copper, :jade, :crystal, :oldmail, :newmail, :ip, :time)");
            $stmt->bindParam(":user", $user);
            $stmt->bindParam(":copper", $keys->copper);
            $stmt->bindParam(":jade", $keys->jade);
            $stmt->bindParam(":crystal", $keys->crystal);
            $stmt->bindParam(":oldmail", $old_mail);
            $stmt->bindParam(":newmail", $new_mail);
            $stmt->bindParam(":ip", $ip);
            $stmt->bindParam(":time", $time);
            $stmt->execute();
            if($stmt->rowCount() == 1){
                // send mail for verfication
                $empfaenger  = self::Pget($user)->Mail;
                $betreff = 'Sign ur Mail Change on LiteWorlds.quest Network';

                // Nachricht
                $link = 'https://api.liteworlds.quest/?method=user-change-mail-sign&name='.$user.'&copperkey='.$keys->copper.'&jadekey='.$keys->jade.'&crystalkey='.$keys->crystal;
                $nachricht = '
                        <a target="_blank" rel="noopener noreferrer" href="'.$link.'">
                        <button style="font-size:24px;width:37%;background-color:transparent;cursor:crosshair;border:3px solid darkgreen;border-radius:7px;">SIGN</button></a>
                        <p>'.$link.'</p>
                ';

                $header = 
                    'From: Security <security@liteworlds.quest>' . "\r\n" .
                    'Reply-To: Security <security@liteworlds.quest>' . "\r\n" .
                    'MIME-Version: 1.0' . "\r\n" .
                    'Content-type: text/html; charset=iso-8859-1' . "\r\n" .
                    'X-Mailer: PHP/' . phpversion();

                // verschicke die E-Mail
                mail($empfaenger, $betreff, $nachricht, $header);

                $result->answer = 'mail change prepared, User have to verify';
                $result->bool = true;
                return $result;
            }
        }else{
            $result->answer = 'User allrdy prepared';
            return $result;
        }
    }
    function MailChange($user, $ip, $copperkey, $jadekey, $crystalkey) {

    }
}
