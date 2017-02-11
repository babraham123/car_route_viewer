<?php

require_once 'common.php';

$json_value = null;

$post = file_get_contents("php://input");

$data = json_decode($post, true);

//echo $data;

$arr = null;            // store results
$sth = null;		// store sql query script
$obj = null;

// no ip check for reading
$ip = getRealIP();
if (($ip[0] == '1' && $ip[1] == '2' && $ip[2] == '8') ||
    ($ip[0] == '1' && $ip[1] == '9' && $ip[2] == '2') ||    
    $ip[0] == ':') {
    // echo "connection from CMU or local network";
} else {
    echo "connection limited to CMU campus.";
}

//connect DB
$dbh = iort_openDb();

if(!$dbh) {
    echo "DB connection failed.";
    exit;
}

$f_parts = pathinfo($data["file"]);
$f_name = $f_parts['basename'];

//echo $f_name;
//echo $data["file"];

$r_dir = "../car/car_camera_map/";
$u_dir = "http://cerlab29.andrew.cmu.edu/IoRT/car/car_camera_map/";

$fn = $u_dir . $f_name;

$sth = $dbh->prepare("INSERT INTO car_camera_map (c_key, c_id, c_time, c_url, aux) SELECT COUNT(*)+1, :ci, :ct, :cu, :au FROM car_camera_map");
$sth->bindParam(':ci', $data["c_id"], PDO::PARAM_INT);
$sth->bindParam(':ct', $data["c_time"], PDO::PARAM_STR);
$sth->bindParam(':cu', $fn, PDO::PARAM_STR);
$sth->bindParam(':au', $data["aux"], PDO::PARAM_INT);
$ret = $sth->execute();

if (!$ret) {
    echo "DB INSERT ", $data["c_id"], " ", $data["c_time"], " error";
    exit;
}

// echo $data["c_time"];

// query again
$sth = $dbh->prepare("SELECT * FROM car_camera_map WHERE c_id= :ci AND c_time= :ct AND c_url= :cu AND aux= :au");
$sth->bindParam(':ci', $data["c_id"], PDO::PARAM_INT);
$sth->bindParam(':ct', $data["c_time"], PDO::PARAM_STR);
$sth->bindParam(':cu', $fn, PDO::PARAM_STR);
$sth->bindParam(':au', $data["aux"], PDO::PARAM_INT);
$ret = $sth->execute();
$rd = $sth->fetchAll();

// echo count($rd);

$obj = array(
    'c_id' => $rd[0]["c_id"],
    'c_time' => $rd[0]["c_time"],
    'c_url' => $rd[0]["c_url"],
    'aux' => $rd[0]["aux"],
    'c_key' => $rd[0]["c_key"]
    );

$arr = array(
    'ret' => $ret,
    'data' => $obj
    );


$json_value = json_encode($arr);
header('Content-Type: text/javascript; charset=utf-8');
echo $json_value;

?>
