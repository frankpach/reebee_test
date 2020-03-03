<?php
//script that accepts flyerID as a parameter and returns a list of pages from that flyerID
//containing the following information: page number, file name. Pages should be ordered by page number in ascending order.
//Usage:
//>php returnPages.php --flyerID 1 OR php returnPages.php --flyerID=1

//Get db configuration from app.ini file
$ini = parse_ini_file('app.ini');

//Get optional data from command line
$options = getopt('', ['flyerID:']);

//Check if optional data is valid
if (isset($options['flyerID'])) {
    if (is_numeric($options['flyerID'])) {
        //Run function when optional data is present and numeric
        $res = getData($ini['server'], $ini['db_name'], $ini['db_user'], $ini['db_password'], intval($options['flyerID']));
    }
}else{
    //Run function when optional data is present and numeric
    $res = getData($ini['server'], $ini['db_name'], $ini['db_user'], $ini['db_password']);
}

var_dump($res);
return $res;

function getData($servername, $dbname, $username, $password, $flyerId = false)
{
    try {
        //Conect to db
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        //Query flyer pages
        $query = $conn->query("SELECT pages.page_number, pages.filename 
            FROM pages
            WHERE pages.flyer_id = '" . $flyerId . "' 
            ORDER BY page_number ASC")->fetchAll();

        $conn = null;
        return $query;

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}