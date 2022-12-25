<?php
class UserEater{
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

    function login(){
        $time = time();

        $stmt = self::$_db->prepare("DELETE FROM login WHERE Time<=:time");
        $stmt->bindParam(":time", $time);
        $stmt->execute();
    }

    function register(){
        $time = time();

        $stmt = self::$_db->prepare("DELETE FROM register WHERE Time<=:time");
        $stmt->bindParam(":time", $time);
        $stmt->execute();
    }

    function ipLock(){
        $time = time();

        $stmt = self::$_db->prepare("DELETE FROM iplock WHERE Time<=:time");
        $stmt->bindParam(":time", $time);
        $stmt->execute();
    }
}
class LitecoinEater{
    private static $_db_username = 'maria';
    private static $_db_passwort = 'KerkerRocks22';
    private static $_db_host = '127.0.0.1';
    private static $_db_name = 'API_litecoin';
    private static $_db;
    
    function __construct(){
        try{
            self::$_db = new PDO("mysql:host=" . self::$_db_host . ";dbname=" . self::$_db_name, self::$_db_username, self::$_db_passwort);
        }catch(PDOException $e){
            echo "<br>DATABASE ERROR<br>".$e;
            die();
        }
    }

    function send(){
        $time = time();

        $stmt = self::$_db->prepare("DELETE FROM send WHERE Time<=:time");
        $stmt->bindParam(":time", $time);
        $stmt->execute();
    }
}

class KotiaEater{
    private static $_db_username = 'maria';
    private static $_db_passwort = 'KerkerRocks22';
    private static $_db_host = '127.0.0.1';
    private static $_db_name = 'API_kotia';
    private static $_db;
    
    function __construct(){
        try{
            self::$_db = new PDO("mysql:host=" . self::$_db_host . ";dbname=" . self::$_db_name, self::$_db_username, self::$_db_passwort);
        }catch(PDOException $e){
            echo "<br>DATABASE ERROR<br>".$e;
            die();
        }
    }

    function send(){
        $time = time();

        $stmt = self::$_db->prepare("DELETE FROM send WHERE Time<=:time");
        $stmt->bindParam(":time", $time);
        $stmt->execute();
    }
}

$usereater = new UserEater;
$usereater->login();
$usereater->register();
$usereater->ipLock();

$ltceater = new LitecoinEater;
$ltceater->send();

$koteater = new KotiaEater;
$koteater->send();
