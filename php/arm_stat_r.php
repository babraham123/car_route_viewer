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

// echo $data["r_id"];
$rid = $data["r_id"];

// get data
$sth = $dbh->prepare("SELECT * FROM arm_status WHERE r_id= :ri");
$sth->bindParam(':ri', $rid, PDO::PARAM_INT);
$ret = $sth->execute();
if (!$ret) {
    echo "DB retrieve", $rid, " error";
    exit;
}
$rd  = $sth->fetchAll();

$ret = 0;
$pid = null;
$pseq = 0;
$status = null;
$pname = null;
$uname = null;
$flag = null;
$time = "";

if (count($rd) > 0) {
    $ret = 1;

    $pid = (int) $rd[0]["p_id"];
    $pseq = (int) $rd[0]["p_seq"];
    $status = $rd[0]["r_status"];
    $flag = (int) $rd[0]["r_flag"];
    $pname = $rd[0]["p_name"];
    $uname = $rd[0]["u_name"];
    $time = $rd[0]["r_time"];
}

// echo $status;

$arr = array(
  'ret' => $ret,
  'r_id' => (int) $rid,
  'p_id' => (int) $pid,
  'p_seq' => (int) $pseq,
  'p_name' => $pname,
  'u_name' => $uname,
  'status' => $status,
  'flag' => (int) $flag,
  'time' => $time
             );

$json_value = json_encode($arr);
header('Content-Type: text/javascript; charset=utf-8');
echo $json_value;

?>
