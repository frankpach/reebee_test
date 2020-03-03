<?php
//Design a MySql database structure that supports the storage of this information.
//Attach the database creation scripts.
//Normalize the structure where possible
//Usage:
//> Command:  php setDb.php

//Get db configuration from app.ini file
$ini = parse_ini_file('app.ini');

//Create database
$dbCreated = createDatabase($ini['server'], $ini['db_user'], $ini['db_password'], $ini['db_name']);

function createDatabase($servername, $username, $password, $dbname)
{

    try {
        $conn = new PDO("mysql:host=$servername", $username, $password);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "CREATE DATABASE " . $dbname;
        // use exec() because no results are returned
        $conn->exec($sql);
        $conn->exec('USE '.$dbname);

        //Create flyers table
        $sql1 = "CREATE TABLE IF NOT EXISTS flyers (
            id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            flyer_start_date DATE NOT NULL,
            flyer_end_date DATE NOT NULL,
            store_id INT NOT NULL,
            flyer_priority INT NOT NULL,
            category_id INT NOT NULL,
            FOREIGN KEY (store_id) REFERENCES stores(id),
            FOREIGN KEY (category_id) REFERENCES category_id(id)
            )";

        //Create flyers table index
        $sql2="CREATE  INDEX flyer_priority ON flyers(flyer_priority);";

        //Create store table
        $sql3 = "CREATE TABLE IF NOT EXISTS stores (
            id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(30) NOT NULL
            )";

        //Create categories table
        $sql4 = "CREATE TABLE IF NOT EXISTS categories (
            id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(30) NOT NULL
            )";

        //Create page table
        $sql5 = "CREATE TABLE IF NOT EXISTS pages (
            id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            flyer_id INT(6) UNSIGNED NOT NULL,
            page_number INT NOT NULL,
            filename VARCHAR(100),
            FOREIGN KEY (flyer_id) REFERENCES flyers(id)
            )";

        //Create page table index
        $sql6="CREATE  INDEX page_number ON pages(page_number);";

        $sql = array($sql1,$sql2, $sql3, $sql4, $sql5, $sql6);

        foreach ($sql as $s){
            $conn->exec($s);
        }

        $conn = null;
        return array(true, '');

    } catch (PDOException $e) {
        $conn = null;
        return array(false, $sql . "\n" . $e->getMessage());
    }
}