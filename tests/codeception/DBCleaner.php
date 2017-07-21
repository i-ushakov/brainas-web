<?php
/**
 * Created by PhpStorm.
 * User: kit
 * Date: 4/11/2017
 * Time: 11:31 AM
 */

class DBCleaner
{
    const DATABASE_NAME = "ba_test";

    private  $username = null;
    private $password = null;
    function __construct($dbConfig)
    {
        $this->username = $dbConfig->username;
        $this->password = $dbConfig->password;
    }

    public function clean()
    {
        $mysqli = mysqli_connect("localhost", $this->username, $this->password, self::DATABASE_NAME);
        $mysqli->query("TRUNCATE TABLE tasks;");
        $mysqli->query("TRUNCATE TABLE conditions;");
        $mysqli->query("TRUNCATE TABLE sync_changed_tasks;");
    }

}