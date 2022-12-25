<?php
class Counter {
    private static $_db_username = 'maria';
    private static $_db_passwort = 'KerkerRocks22';
    private static $_db_host = '127.0.0.1';
    private static $_db_name = 'API_counter';
    private static $_db;

    function __construct(){
        try{
            self::$_db = new PDO("mysql:host=" . self::$_db_host . ";dbname=" . self::$_db_name, self::$_db_username, self::$_db_passwort);
        }catch(PDOException $e){
            echo "COUNTER ERROR";
            die();
        }
    }

    function get(){
        $stmt = self::$_db->prepare("SELECT * FROM counter");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function increase($name){
        $stmt = self::$_db->prepare("SELECT Value FROM counter WHERE Name=:name");
        $stmt->bindParam(":name", $name);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $value = (int)$stmt->fetch()['Value'] + 1;

            $stmt = self::$_db->prepare("UPDATE counter SET Value=:value WHERE Name=:name");
            $stmt->bindParam(":value", $value);
            $stmt->bindParam(":name", $name);
            $stmt->execute();
        }
    }

    function decrease(){
        
    }
}