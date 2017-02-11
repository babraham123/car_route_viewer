<?php

require_once 'common.php';

$json_value = null;

$post = file_get_contents("php://input");

$data = json_decode($post, true);
$arr = null;            // store results
$sth = null;		// store sql query script
$err = "";

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
  $err =  "DB connection failed.";
  goto end;
}

// echo $data["r_id"];
$cid = $data["c_id"];

// get data
$sth = $dbh->prepare("SELECT * FROM arm_camera_status WHERE c_id= :ci");
$sth->bindParam(':ci', $cid, PDO::PARAM_INT);
$ret = $sth->execute();
if (!$ret) {
  $err = "DB retrieve from table t_id:" .  $tid . " error";
  goto end;
}
$rd  = $sth->fetchAll();

$ret = 0;
$i_key = 0;
$o_key = 0;
$time = "";

if (count($rd) > 0) {
    $ret = 1;

    $status = $rd[0]["c_status"];
    $i_key = $rd[0]["i_key"];
    $o_key = $rd[0]["o_key"];
    $time = $rd[0]["c_time"];
}

// echo $status;

end:
$arr = array(
  'ret' => $ret,
  '$err' => $err,
  'c_id' => (int) $cid,
  'status' => $status,
  'i_key' => (int) $i_key,
  'o_key' => (int) $o_key,
  'time' => $time
             );

$json_value = json_encode($arr);
header('Content-Type: text/javascript; charset=utf-8');
echo $json_value;

?>
