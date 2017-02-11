<?php

require_once 'common.php';

$json_value = null;

$post = file_get_contents("php://input");

$data = json_decode($post, true);


$arr = null;            // store results
$obj = null;
$sth = null;		// store sql query script
$ok = 0;
$err = null;

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

if (!empty($data["o_key"])) {
  $sth = $dbh->prepare("SELECT * FROM arm_obj_2d WHERE o_key= :ok");
  $sth->bindParam(':ok', $data["o_key"], PDO::PARAM_INT);
  $ret = $sth->execute();
  $rd  = $sth->fetchAll();
} else {
  $sth = $dbh->prepare("SELECT * FROM arm_obj_2d WHERE c_key= :ck AND u_name= :un");
  $sth->bindParam(':ck', $data["c_key"], PDO::PARAM_INT);
  $sth->bindParam(':un', $data["u_name"], PDO::PARAM_STR);
  $ret = $sth->execute();
  $rd  = $sth->fetchAll();
}
//echo count($rd);
$ret = 0;
if (count($rd) > 0) {
    $ret = 1;
    $ok = (int) $rd[0]["o_key"];
    $obj = array();
    foreach ($rd as $key => $value) {
        array_push($obj,
		   array('c_key' => (int) $value["c_key"],
			 'pos_x' => (float) $value["pos_x"],
			 'pos_y' => (float) $value["pos_y"],
			 'rot' => (float) $value["rot"],
			 'aux' => (int) $value["aux"],
			 'u_name' => (int) $value["u_name"],
			 'o_type' => $value["o_type"]));
    }
}

end:
$data = array(
    'o_key' => $ok,
    'obj' => $obj
             );

$arr = array(
    'ret' => $ret,
    'data' => $data
    );


$json_value = json_encode($arr);
header('Content-Type: text/javascript; charset=utf-8');
echo $json_value;

?>
