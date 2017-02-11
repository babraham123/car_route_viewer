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

// echo "m_name: ", $data["m_name"];

// get data
$sth = $dbh->prepare("SELECT * FROM arm_status WHERE r_id= :ri");
$sth->bindParam(':ri', $data["r_id"], PDO::PARAM_INT);
$ret = $sth->execute();
if (!$ret) {
    echo "DB retrieve", $data["r_id"], " error";
    exit;
}
$rd  = $sth->fetchAll();
$pname = $rd[0]["p_name"];
$pseq = $rd[0]["p_seq"];
$cmd = $rd[0]["cmd"];

$arr = array(
    'ret' => $ret,
    'r_id' => $data["r_id"],
    'p_name' => $pname,
    'p_seq' => $pseq,
    'cmd' => $cmd
    );


$json_value = json_encode($arr);
header('Content-Type: text/javascript; charset=utf-8');
echo $json_value;

?>
