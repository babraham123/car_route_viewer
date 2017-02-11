<?php

require_once 'common.php';

$json_value = null;

$post = file_get_contents("php://input");

$data = json_decode($post, true);
$arr = null;            // store results
$sth = null;		// store sql query script

$ret = 0;
$err = "";
$ts1 = "";
$ts2 = "";
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
  $ret = 0;
  $err = "DB connection failed.";
  goto end;
}

if (!empty($data["ts1"])) {
  $ts1 = $data["ts1"];
} else {
  $sth = $dbh->prepare("SELECT * FROM car_map_img_status WHERE c_id= :ci ");
  $sth->bindParam(':ci', $data["c_id"], PDO::PARAM_INT);
  $ret = $sth->execute();
  if (!$ret) {
    $ret = 0;
    $err = "Error: DB retrieve from car_camera_status c_id: " . $data["c_id"];
    goto end;
  }
  $rd  = $sth->fetchAll();
  if (count($rd) < 1) {
    $ts = "2017-01-01 00:00:00";
  } else {
    $ts1 = $rd[0]["c_time"];
  }
}

//$sth = $dbh->prepare("SELECT * FROM car_map_img WHERE c_id= :ci ORDER by ABS(TIMEDIFF(:ct, c_time))");
if (!empty($data["ts2"])) {
  $ts2 = $data["ts2"];
  $sth = $dbh->prepare("SELECT * FROM car_map_img WHERE c_id= :ci AND c_time >= :ts1 AND c_time <= :ts2 ORDER by c_time DESC");
  $sth->bindParam(':ci', $data["c_id"], PDO::PARAM_INT);
  $sth->bindParam(':ts1', $ts1, PDO::PARAM_STR);
  $sth->bindParam(':ts2', $ts2, PDO::PARAM_STR);
  $ret = $sth->execute();
  if (!$ret) {
    $err = "Error: DB retrieve from car_camera_img c_id: " . $data["c_id"] . ", ts1: " . $data["ts1"] . ", ts2: " . $data["ts2"];
    goto end;
  }
  $rd  = $sth->fetchAll();
} else {
  $ts2 = date('Y-m-d h:i:s');
  $sth = $dbh->prepare("SELECT * FROM car_map_img WHERE c_id= :ci AND c_time >= :ts1 ORDER by c_time DESC");
  $sth->bindParam(':ci', $data["c_id"], PDO::PARAM_INT);
  $sth->bindParam(':ts1', $data["ts1"], PDO::PARAM_STR);
  $ret = $sth->execute();
  if (!$ret) {
    $err = "Error: DB retrieve from car_map_img c_id: " . $data["c_id"] . ", ts1: " . $data["ts1"];
    goto end;
  }
  $rd  = $sth->fetchAll();
}

$j = count($rd);
if ($j > 0) {
  $ret = 1;
  $obj = array();
  for($i = 0; $i < $j; $i++) {
    array_push($obj,
               array('c_id' => $rd[$i]["c_id"],
                     'c_url' => $rd[$i]["c_url"],
                     'c_time' => $rd[$i]["c_time"],
                     'c_key' => (int)$rd[$i]["c_key"],
                     'aux' =>  (int)$rd[$i]["aux"]));
  }
} else {
  $ret = 0;
}

end:
$arr = array(
    'ret' => $ret,
    'err' => $err,
    'ts1' => $ts1,
    'ts2' => $ts2,
    'img' => $obj
    );


$json_value = json_encode($arr);
header('Content-Type: text/javascript; charset=utf-8');
echo $json_value;

?>
