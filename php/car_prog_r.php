<?php

require_once 'common.php';

$json_value = null;

$post = file_get_contents("php://input");

$data = json_decode($post, true);
$arr = null;            // store results
$sth = null;		// store sql query script
$obj = null;

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

$sth = $dbh->prepare("SELECT * FROM car_prog WHERE u_name= :un");
$sth->bindParam(':un', $data["u_name"], PDO::PARAM_INT);
$ret = $sth->execute();
if (!$ret) {
    echo "DB retrieve ", $data["u_name"], " error";
    exit;
}
$rd  = $sth->fetchAll();
//echo count($rd);
$ret = 0;
$j = count($rd);
if ($j > 0) {
    $ret = 1;
    $obj = array();
    if ($j > 3) {
	$imax = 3;
    } else {
	$imax = $j;
    }
    // echo "imax ", $imax;
    for($i = 0; $i < $imax; $i++) {
	array_push($obj,
		   array('p_name' => $rd[$i]["p_name"],
			 'p_id' => $rd[$i]["p_id"],
			 'c_time' => $rd[$i]["c_time"]));
    }
}

$arr = array(
    'ret' => $ret,
    'data' => $obj
    );


$json_value = json_encode($arr);
header('Content-Type: text/javascript; charset=utf-8');
echo $json_value;

?>
