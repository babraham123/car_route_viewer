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

// echo "p_name: ", $data["p_name"];

// get data
$sth = $dbh->prepare("SELECT * FROM arm_prog WHERE p_name= :pn AND u_name= :un");
$sth->bindParam(':pn', $data["p_name"], PDO::PARAM_STR);
$sth->bindParam(':un', $data["u_name"], PDO::PARAM_STR);
$ret = $sth->execute();
if (!$ret) {
    echo "DB retrieve", $data["r_id"], " error";
    exit;
}
$rd  = $sth->fetchAll();

//echo count($rd);
$ret = 0;
$ok = 0;
$path = array();

if (count($rd) > 0) {
    $ret = 1;
    $pi = $rd[0]["p_id"];
    $ok = $rd[0]["o_key"];
    
    $sth = $dbh->prepare("SELECT * FROM arm_prog_data WHERE p_id= :pi ORDER BY p_seq");
    $sth->bindParam(':pi', $pi, PDO::PARAM_INT);
    $ret = $sth->execute();
    $rd = $sth->fetchAll();
    foreach ($rd as $key => $value) {
        array_push($path,
	           array('seq' => (int) $value["p_seq"],
		         'pos_x' => (float) $value["pos_x"],
		         'pos_y' => (float) $value["pos_y"],
		         'pos_z' => (float) $value["pos_z"],
		         'dir_x' => (float) $value["dir_x"],
		         'dir_y' => (float) $value["dir_y"],
		         'dir_z' => (float) $value["dir_z"],
			 'cmd' => $value["cmd"],
			 'cmd_opt' => $value["cmd_opt"],
			 'cfg' => (int) $value["cfg"],
			 'm_id' => (int) $value["m_id"]));
    }
}

$data = array(
  'o_key' => $ok,
  'path' => $path );

// echo $data;

$arr = array(
    'ret' => $ret,
    'data' => $data );


$json_value = json_encode($arr);
header('Content-Type: text/javascript; charset=utf-8');
echo $json_value;

?>
