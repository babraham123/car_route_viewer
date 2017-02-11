<?php

require_once 'common.php';

$json_value = null;

$post = file_get_contents("php://input");

$data = json_decode($post, true);
$arr = null;            //this stores results

$ip = getRealIP();
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

$sth = $dbh->prepare("SELECT * FROM car_prog WHERE p_name= :pn AND u_name = :un");
$sth->bindParam(':pn', $data["p_name"], PDO::PARAM_STR);
$sth->bindParam(':un', $data["u_name"], PDO::PARAM_STR);
$ret = $sth->execute();
$rd = $sth->fetchAll();

if (count($rd) < 1) {
    $sth = $dbh->prepare("INSERT INTO car_prog (p_id, p_name, u_name, c_time) SELECT COUNT(*)+1, :pn, :un, NOW() FROM car_prog");
    $sth->bindParam(':pn', $data["p_name"], PDO::PARAM_STR);
    $sth->bindParam(':un', $data["u_name"], PDO::PARAM_STR);
    $sth->execute();
} else {
    $sth = $dbh->prepare("UPDATE car_prog SET c_time=NOW() WHERE p_name= :pn AND u_name = :un");
    $sth->bindParam(':pn', $data["p_name"], PDO::PARAM_STR);
    $sth->bindParam(':un', $data["u_name"], PDO::PARAM_STR);
    $ret = $sth->execute();
    $rd = $sth->fetchAll();
}
// query again
$sth = $dbh->prepare("SELECT * FROM car_prog WHERE p_name= :pn AND u_name = :un");
$sth->bindParam(':pn', $data["p_name"], PDO::PARAM_STR);
$sth->bindParam(':un', $data["u_name"], PDO::PARAM_STR);
$ret = $sth->execute();
$rd = $sth->fetchAll();

$arr = array(
    'result' => $ret,
    'p_name' => $rd["p_name"],
    'u_name' => $rd["u_name"],
    'c_time' => $rd["c_time"]
    );

$json_value = json_encode($arr);
header('Content-Type: text/javascript; charset=utf-8');
echo $json_value;

?>
