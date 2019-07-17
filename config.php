<?php
    session_start();
    //set timezone
    date_default_timezone_set('Europe/London');

    //database credentials
    define('DBHOST','localhost');
    define('DBUSER','YOUR_DB_USER');
    define('DBPASS','YOUR_DB_PASS');
    define('DBNAME','YOUR_DB_NAME');

    try {
        $db = new PDO("mysql:host=".DBHOST.";dbname=".DBNAME, DBUSER, DBPASS);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        echo '<p class="bg-danger">'.$e->getMessage().'</p>';
        exit;
    }

    function getRealIpAddr() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip=$_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip=$_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
