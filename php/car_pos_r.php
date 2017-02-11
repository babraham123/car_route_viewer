<?php

require_once 'common.php';

$json_value = null;

$post = file_get_contents("php://input");

$data = json_decode($post, true);
$arr = null;            // store results
$sth = null;		// store sql query script

// no ip check for reading
//$ip = getRealIP();
//if (($ip[0] == '1' && $ip[1] == '2' && $ip[2] == '8') ||
//    ($ip[0] == '1' && $ip[1] == '9' && $ip[2] == '2') ||    
//    $ip[0] == ':') {
//    // echo "connection from CMU or local network";
//} else {
//    echo "connection limited to CMU campus.";
//}

//connect DB
$dbh = iort_openDb();

if(!$dbh) {
    echo "DB connection failed.";
    exit;
}

// get data
if ($data["s_time"] == null) {
    $sth = $dbh->prepare("SELECT * FROM car_pos_att WHERE r_id= :ri ORDER BY p_time DESC LIMIT 1");
    $sth->bindParam(':ri', $data["r_id"], PDO::PARAM_INT);
} else if ($data["e_time"] == null) {
    // specify both search start and end time
    $sth = $dbh->prepare("SELECT * FROM car_pos_att WHERE r_id= :ri AND p_time BETWEEN :st AND :et ORDER BY p_time");
    $sth->bindParam(':ri', $data["r_id"], PDO::PARAM_INT);
    $sth->bindParam(':st', $data["s_time"], PDO::PARAM_STR);
    $sth->bindParam(':et', $data["e_time"], PDO::PARAM_STR);
} else {
    // specify only search start time
    $sth = $dbh->prepare("SELECT * FROM car_pos_att WHERE r_id= :ri AND p_time >= :st ORDER BY p_time LIMIT 1");
    $sth->bindParam(':ri', $data["r_id"], PDO::PARAM_INT);
    $sth->bindParam(':st', $data["s_time"], PDO::PARAM_STR);
}
$ret = $sth->execute();
$rd  = $sth->fetchAll();

$records = array();

foreach($rd as $key => $value) {
    array_push($records,
	       array('r_id' => $value["r_id"],
		     'p_time' => $value["p_time"],
		     'pos_x' => $value["pos_x"],
		     'pos_y' => $value["pos_y"],
		     'dir_x' => $value["dir_x"],
		     'dir_y' => $value["dir_y"],
		     'i_url' => $value["i_url"],
		     'c_url' => $value["c_url"],
		     'c_from' => long2ip($value["c_from"])
	       )
	);
}

$arr = array(
    'result' => $ret,
    'pos_array' => $records
    );


$json_value = json_encode($arr);
header('Content-Type: text/javascript; charset=utf-8');
echo $json_value;

?>
