<?php
//script that accepts an optional categoryID as a parameter and returns a list of flyers
//containing the following information: flyerID, flyer start date, flyer end date, category, flyer priority, store.
//The list should be ordered by flyer priority in ascending order.
//Only flyers valid on the current date should be returned.
//Add indices to the database where appropriate.
//Usage:
//> php returnFlyers OR php returnFlyers --categoryID=#

//Get db configuration from app.ini file
$ini = parse_ini_file('app.ini');

//Get optional data from command line
$options = getopt('', ['categoryID::']);

//Check if optional data is valid
if (isset($options['categoryID'])) {
    if (is_numeric($options['categoryID'])) {
        if (intval($options['categoryID']) > 0) {
            //Run function when optional data is present, is numeric and is greater than 0
            $res = getData($ini['server'], $ini['db_name'], $ini['db_user'], $ini['db_password'], intval($options['categoryID']));
        } else {
            $res = array(['error' => 'categoryID is not valid, please check']);
        }
    }else{
        $res = array(['error' => 'categoryID is not numeric']);
    }
} else {
    //Run function when optional data is mot present or numeric
    $res = getData($ini['server'], $ini['db_name'], $ini['db_user'], $ini['db_password']);
}

//var_dump($res);
return $res;


function getData($servername, $dbname, $username, $password, $category = false)
{
    try {

        //Conect to db
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if ($category) {
            //If categor => query where category
            $query = $conn->query("SELECT flyers.id as id, flyers.flyer_start_date as flyer_start_date,
            flyers.flyer_end_date as flyer_end_date, categories.name as category, flyers.flyer_priority as flyer_priority,
            stores.name  as store FROM flyers INNER JOIN stores ON stores.id=flyers.store_id INNER JOIN
            categories ON flyers.category_id=categories.id 
            WHERE flyers.store_id = '" . $category . "' 
            AND flyers.flyer_start_date <= NOW() 
            AND flyers.flyer_end_date >= NOW()
            ORDER BY flyer_priority ASC")
                ->fetchAll();
        } else {
            //If category missing => query without where category
            $query = $conn->query("SELECT flyers.id,flyers.flyer_start_date,flyers.flyer_end_date,categories.name,
            flyers.flyer_priority, stores.name  FROM flyers INNER JOIN stores ON stores.id=flyers.store_id INNER JOIN
            categories ON flyers.category_id=categories.id 
            WHERE flyers.flyer_start_date <= NOW() 
            AND flyers.flyer_end_date >= NOW()
            ORDER BY flyer_priority ASC")
                ->fetchAll();
        }

        $conn = null;
        return ($query);

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
    $conn = null;
}