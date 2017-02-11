<?php

require_once 'common.php';

$json_value = null;

$post = file_get_contents("php://input");

$data = json_decode($post, true);
$arr = null;            // store results
$sth = null;		// store sql query script

// no ip check for reading
$ip = getRealIP();
// echo $ip;
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

$sth = $dbh->prepare("SELECT * FROM arm_camera_2d WHERE c_key= :ck");
$sth->bindParam(':ck', $data["c_key"], PDO::PARAM_INT);
$ret = $sth->execute();
if (!$ret) {
    echo "DB retrieve", $data["c_key"], " error";
    exit;
}
$rd  = $sth->fetchAll();
// echo count($rd);
// echo count($data["obj"]);
$ret = 0;
$ok = 0;
if (count($rd) > 0) {
  $sth = $dbh->prepare("SELECT * FROM arm_obj_2d WHERE c_key= :ck AND u_name= :un");
  $sth->bindParam(':ck', $data["c_key"], PDO::PARAM_INT);
  $sth->bindParam(':un', $data["u_name"], PDO::PARAM_STR);
  $ret = $sth->execute();
  $rd  = $sth->fetchAll();
  if (count($rd) > 0) {
    $ok = (int) $rd[0]["o_key"];
    $sth = $dbh->prepare("DELETE FROM arm_obj_2d WHERE o_key= :ok");
    $sth->bindParam(':ok', $ok, PDO::PARAM_INT);
    $ret = $sth->execute();
    $rd  = $sth->fetchAll();
  } else {
    $sth = $dbh->prepare("SELECT MAX(o_key) AS o_key FROM arm_obj_2d ");
    $ret = $sth->execute();
    $rd  = $sth->fetchAll();
    $ok = (int) $rd[0]["o_key"] + 1;
  }
  // echo $ok;
  for ($i = 0; $i < count($data["obj"]); $i++) {
    $sth = $dbh->prepare("INSERT INTO arm_obj_2d (pos_x, pos_y, rot, o_type, c_key, aux, u_name, o_time, o_key) VALUES (:px, :py, :rt, :ot, :ck, :ax, :un, NOW(), :ok)");
	$sth->bindParam(':px', $data["obj"][$i]["pos_x"], PDO::PARAM_STR);
	$sth->bindParam(':py', $data["obj"][$i]["pos_y"], PDO::PARAM_STR);
	$sth->bindParam(':rt', $data["obj"][$i]["rot"], PDO::PARAM_STR);
	$sth->bindParam(':ot', $data["obj"][$i]["o_type"], PDO::PARAM_STR);
	$sth->bindParam(':ck', $data["c_key"], PDO::PARAM_INT);
	$sth->bindParam(':ax', $data["obj"][$i]["aux"], PDO::PARAM_INT);
	$sth->bindParam(':un', $data["u_name"], PDO::PARAM_STR);
	$sth->bindParam(':ok', $ok, PDO::PARAM_INT);
	$ret = $sth->execute();
	$rd  = $sth->fetchAll();
    }
    $ret = 1;
}

$arr = array(
  'ret' => $ret,
  'o_key' => $ok
    );


$json_value = json_encode($arr);
header('Content-Type: text/javascript; charset=utf-8');
echo $json_value;

?>
