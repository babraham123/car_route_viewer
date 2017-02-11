<?php

require_once 'common.php';

$json_value = null;

$post = file_get_contents("php://input");

$data = json_decode($post, true);
$arr = null;            //this stores results
$path = null;
$ret = 0;
$err = "";

// $ip = getRealIP();
// if (($ip[0] == '1' && $ip[1] == '2' && $ip[2] == '8') ||
//     ($ip[0] == '1' && $ip[1] == '9' && $ip[2] == '2') ||    
//     $ip[0] == ':') {
//     //echo "connection from CMU or local network";
// } else {
//     echo "connection limited to CMU campus.";
// }

//connect DB
$dbh = iort_openDb();

// echo $data["p_name"];

if(!$dbh) {
  $err = "DB connection failed.";
  goto end;
}


$sth = $dbh->prepare("SELECT * FROM car_prog WHERE p_name= :pn AND u_name = :un");
$sth->bindParam(':pn', $data["p_name"], PDO::PARAM_STR);
$sth->bindParam(':un', $data["u_name"], PDO::PARAM_STR);
$ret = $sth->execute();
$rd = $sth->fetchAll();

$pi = 0;

if (count($rd) > 0) {
    $pi = $rd[0]["p_id"];
    $sth = $dbh->prepare("SELECT * FROM car_prog_path WHERE p_id= :pi ORDER BY p_seq");
    $sth->bindParam(':pi', $pi, PDO::PARAM_INT);
    $ret = $sth->execute();
    $rd = $sth->fetchAll();
    $path = array();
    foreach ($rd as $key => $value) {
	array_push($path,
		   array('seq' => $value["p_seq"],
			 'pos' => [(float)$value["tgt_pos_x"],(float)$value["tgt_pos_y"]],
			 'name' =>  $value["tgt_name"]));
    }
} else {
  $ret = 0;
  $err =  "No program: " . $data["p_name"] . " is registered under: " . $data["u_name"];
}

end:
$arr = array(
    'ret' => $ret,
    'err' => $err,
    'path' => $path
    );

$json_value = json_encode($arr);
header('Content-Type: text/javascript; charset=utf-8');
echo $json_value;

