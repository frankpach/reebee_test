<?php
//command line script that accepts the csv file and uploads the information to your database.
//The only argument to this command line script should be the csv file location.
//categoryID and flyerID should be created on upload.
//Usage:
//> php csvUpload /path.csv

//Get db configuration from app.ini file
$ini = parse_ini_file('app.ini');

//Run function to upload csv file
setData($ini['server'], $ini['db_name'], $ini['db_user'], $ini['db_password'], uploadCSV($argv[1]) );

function setData($servername, $dbname, $username, $password, $data)
{

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        foreach ($data as $flyer) {

            $query = $conn->query("SELECT * FROM flyers INNER JOIN stores ON stores.id=flyers.store_id INNER JOIN
            categories ON flyers.category_id=categories.id LEFT JOIN pages ON pages.flyer_id=flyers.id WHERE
            flyers.flyer_start_date = '" . $flyer['flyer_start_date'] . "' AND flyers.flyer_end_date = '" . $flyer['flyer_end_date'] . "' AND
            stores.name = '" . $flyer['store'] . "' AND categories.name = '" . $flyer['category'] . "' AND flyers.flyer_priority = " . $flyer['flyer_priority'])
                ->fetch();

            if (!$query) {
                $store= $conn->query("SELECT * FROM stores WHERE stores.name = '" . $flyer['store']."'")->fetch();

                if (!$store) {
                    $conn->query("INSERT INTO stores (name) VALUES ('" . $flyer['store']."')");
                    $store_id = $conn->lastInsertId();
                } else {
                    $store_id = $store['id'];
                }

                $category = $conn->query("SELECT * FROM categories 
                    WHERE categories.name = '" . $flyer['category'] . "'")->fetch();

                if (!$category) {
                    $conn->query("INSERT INTO categories (name) VALUES ('".$flyer['category']."')");
                    $category_id = $conn->lastInsertId();
                } else {
                    $category_id = $category['id'];
                }

                $sql="INSERT INTO flyers (flyer_start_date, flyer_end_date, store_id, flyer_priority,category_id)
                        VALUES (CAST('" . $flyer['flyer_start_date'] . "' AS DATE), CAST('" . $flyer['flyer_end_date'] . "' 
                        AS DATE), " . $store_id . ", " . $flyer['flyer_priority'] . ", " . $category_id . ")";

                $query = $conn->query($sql);

                if ($query) {
                    $flyerId = $conn->lastInsertId();
                    $sql = "INSERT INTO pages (flyer_id, page_number, filename)
                        VALUES (" . $flyerId . ", " . $flyer['page_number'] . ", '" . $flyer['filename'] . "')";
                    $conn->query($sql);
                } else {
                    return "Error: " . $sql . "<br>" . $conn->error;
                }

            } else {
                $query1 = $conn->query("SELECT * FROM pages 
                        WHERE pages.flyer_id = '" . $query[0] . "'
                        AND pages.page_number = '" . $flyer['page_number'] . "' 
                        AND pages.filename = '" . $flyer['filename'] . "'")->fetch();

                if (!$query1) {
                    $sql = "INSERT INTO pages (flyer_id, page_number, filename)
                        VALUES (" . intval ($query[0]) . ", " . $flyer['page_number'] . ", '" . $flyer['filename'] . "')";
                    $conn->query($sql);
                }
            }

        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
    $conn = null;
}

function uploadCSV($location)
{
    $row = true;
    $res = array();

    if (($handle = fopen($location, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if (!$row) {
                array_push($res, ['flyer_start_date' => $data[0], 'flyer_end_date' => $data[1],
                    'store' => $data[2], 'flyer_priority' => $data[3], 'category' => $data[4], 'page_number' => $data[5],
                    'filename' => $data[6]]);
            }
            $row = false;
        }
        fclose($handle);
        return $res;
    }
}