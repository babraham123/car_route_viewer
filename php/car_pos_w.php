<?php

require_once 'common.php';

$json_value = null;

$post = file_get_contents("php://input");

$data = json_decode($post, true);
$arr = null;            //this stores results
$ret = 0;
$err = "";

$ip = getRealIP();
$ip_i = ip2long($ip);
if (($ip[0] == '1' && $ip[1] == '2' && $ip[2] == '8') ||
    ($ip[0] == '1' && $ip[1] == '9' && $ip[2] == '2') ||    
    $ip[0] == ':') {
    // echo "connection from CMU or local network";
} else {
  $ret = 0;
  $err = "connection limited to CMU campus.";
  goto end;
}
//connect DB
$dbh = iort_openDb();

if(!$dbh) {
  $ret = 0;
  $err = "DB connection failed.";
  goto end;
}

$j = count($data);
for($i = 0; $i < $j; $i++) {
  $p = $data[$i];
  $sth = $dbh->prepare("INSERT INTO car_pos_att (r_id, c_time, c_url, pos_x, pos_y, dir_x, dir_y, p_from, p_time) VALUES (:ri, :ct, :cu, :px, :py, :dx, :dy, :ip, NOW())");
  $sth->bindParam(':ri', $p["r_id"], PDO::PARAM_INT);
  $sth->bindParam(':ct', $p["c_time"], PDO::PARAM_STR);
  $sth->bindParam(':cu', $p["c_url"], PDO::PARAM_STR);
  $sth->bindParam(':px', $p["pos"][0], PDO::PARAM_STR);
  $sth->bindParam(':py', $p["pos"][1], PDO::PARAM_STR);
  $sth->bindParam(':dx', $p["dir"][0], PDO::PARAM_STR);
  $sth->bindParam(':dy', $p["dir"][1], PDO::PARAM_STR);
  $sth->bindParam(':ip', $ip_i, PDO::PARAM_INT);
  $ret = $sth->execute();
  if (!$ret) {
    $err = $p;
  }
}

end:
$arr = array(
    'ret' => $ret,
    'err' => $err,
    'count' => $j
    );

$json_value = json_encode($arr);
header('Content-Type: text/javascript; charset=utf-8');
echo $json_value;

?>
