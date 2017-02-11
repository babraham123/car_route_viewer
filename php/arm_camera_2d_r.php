<?php

require_once 'common.php';

$json_value = null;

$post = file_get_contents("php://input");

$data = json_decode($post, true);
$arr = null;            // store results
$sth = null;		// store sql query script
$err = null;
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
  $err = "DB connection failed";
  goto end;
}

if (!empty($data["c_key"])) {
  $sth = $dbh->prepare("SELECT * FROM arm_camera_2d WHERE c_key= :ci ");
  $sth->bindParam(':ci', $data["c_key"], PDO::PARAM_INT);
  $ret = $sth->execute();
  if (!$ret) {
    $err = "Error: DB retrieve c_id: " . $data["c_id"] .  ", c_time: " . $data["c_time"];
    goto end;
  }
  $rd  = $sth->fetchAll();
}  else {
  $sth = $dbh->prepare("SELECT * FROM arm_camera_2d WHERE c_id= :ci ORDER by ABS(TIMEDIFF(:ct, c_time))");
  $sth->bindParam(':ci', $data["c_id"], PDO::PARAM_INT);
  $sth->bindParam(':ct', $data["c_time"], PDO::PARAM_STR);
  $ret = $sth->execute();
  if (!$ret) {
    $err = "Error: DB retrieve c_id: " . $data["c_id"] .  ", c_time: " . $data["c_time"];
    goto end;
  }
  $rd  = $sth->fetchAll();
}
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
		   array('c_id' => $rd[$i]["c_id"],
			 'c_url' => $rd[$i]["c_url"],
			 'c_time' => $rd[$i]["c_time"],
			 'c_key' => (int)$rd[$i]["c_key"],
			 'aux' =>  (int)$rd[$i]["aux"]));
    }
}

end:
$arr = array(
    'ret' => $ret,
    'err' => $err,
    'data' => $obj
    );


$json_value = json_encode($arr);
header('Content-Type: text/javascript; charset=utf-8');
echo $json_value;

?>
