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
    //echo "connection from CMU or local network";
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

$pi = 0;

if (count($rd) > 0) {
    $pi = $rd[0]["p_id"];
    // clean existing path information
    $sth = $dbh->prepare("DELETE FROM car_prog_path WHERE p_id= :pi");
    $sth->bindParam(':pi', $pi, PDO::PARAM_INT);
    $ret = $sth->execute();
    $rd = $sth->fetchAll();
    // uppdate C_time
    $sth = $dbh->prepare("UPDATE car_prog SET c_time=NOW() WHERE p_id= :pi");
    $sth->bindParam(':pi', $pi, PDO::PARAM_INT);
    $ret = $sth->execute();
    $rd = $sth->fetchAll();
} else {
    // create entry
    $sth = $dbh->prepare("INSERT INTO car_prog (p_id, p_name, u_name, c_time) SELECT COUNT(*)+1, :pn, :un, NOW() FROM car_prog");
    $sth->bindParam(':pn', $data["p_name"], PDO::PARAM_STR);
    $sth->bindParam(':un', $data["u_name"], PDO::PARAM_STR);
    $sth->execute();
    // retrieve p_id
    $sth = $dbh->prepare("SELECT * FROM car_prog WHERE p_name= :pn AND u_name = :un");
    $sth->bindParam(':pn', $data["p_name"], PDO::PARAM_STR);
    $sth->bindParam(':un', $data["u_name"], PDO::PARAM_STR);
    $ret = $sth->execute();
    $rd = $sth->fetchAll();
    $pi = $rd[0]["p_id"];
}

for ($i = 0; $i < count($data["path"]); $i++) {
    $sth = $dbh->prepare("INSERT INTO car_prog_path (p_id, p_seq, tgt_pos_x, tgt_pos_y, tgt_name, c_time) VALUES (:pi, :ps, :tx, :ty, :tn, NOW())");
    $sth->bindParam(':pi', $pi, PDO::PARAM_INT);
    $sth->bindParam(':ps', $data["path"][$i]["seq"], PDO::PARAM_INT);
    $sth->bindParam(':tx', $data["path"][$i]["pos"][0], PDO::PARAM_STR);
    $sth->bindParam(':ty', $data["path"][$i]["pos"][1], PDO::PARAM_STR);
    $sth->bindParam(':tn', $data["path"][$i]["name"], PDO::PARAM_STR);
    
    $sth->execute();
    $rd = $sth->fetchAll();
}

$arr = array(
    'result' => $ret,
    'num' => count($data["path"])
    );

$json_value = json_encode($arr);
header('Content-Type: text/javascript; charset=utf-8');
echo $json_value;

