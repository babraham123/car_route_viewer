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

$sth = $dbh->prepare("SELECT * FROM arm_prog WHERE p_name= :pn AND u_name = :un AND o_key = :ok");
$sth->bindParam(':pn', $data["p_name"], PDO::PARAM_STR);
$sth->bindParam(':un', $data["u_name"], PDO::PARAM_STR);
$sth->bindParam(':ok', $data["o_key"], PDO::PARAM_INT);
$ret = $sth->execute();
$rd = $sth->fetchAll();
$pid = 0;

if (count($rd) < 1) {
    $sth = $dbh->prepare("INSERT INTO arm_prog (p_id, p_name, u_name, o_key, c_time) SELECT COUNT(*)+1, :pn, :un, :ok, NOW() FROM arm_prog");
    $sth->bindParam(':pn', $data["p_name"], PDO::PARAM_STR);
    $sth->bindParam(':un', $data["u_name"], PDO::PARAM_STR);
    $sth->bindParam(':ok', $data["o_key"], PDO::PARAM_INT);
    $sth->execute();
    $rd = $sth->fetchAll();
} else {
    $pid = (int) $rd[0]["p_id"];
    // echo $pid;
    $sth = $dbh->prepare("UPDATE arm_prog SET c_time=NOW() WHERE p_name= :pn AND u_name = :un AND o_key = :ok");
    $sth->bindParam(':pn', $data["p_name"], PDO::PARAM_STR);
    $sth->bindParam(':un', $data["u_name"], PDO::PARAM_STR);
    $sth->bindParam(':ok', $data["o_key"], PDO::PARAM_INT);
    $ret = $sth->execute();
    $rd = $sth->fetchAll();

    // clear existing program data
    $sth = $dbh->prepare("DELETE FROM arm_prog_data WHERE p_id= :pi");
    $sth->bindParam(':pi', $pid, PDO::PARAM_INT);
    $ret = $sth->execute();
    $rd = $sth->fetchAll();
}

// query again
$sth = $dbh->prepare("SELECT * FROM arm_prog WHERE p_name= :pn AND u_name = :un AND o_key = :ok");
$sth->bindParam(':pn', $data["p_name"], PDO::PARAM_STR);
$sth->bindParam(':un', $data["u_name"], PDO::PARAM_STR);
$sth->bindParam(':ok', $data["o_key"], PDO::PARAM_INT);
$ret = $sth->execute();
$rd = $sth->fetchAll();
$pid = (int) $rd[0]["p_id"];
// echo $pid;
$zero = "0.0";
$spc = " ";
$minus = "-1";

for ($i = 0; $i < count($data["prog"]); $i++) {
    // echo $pid;
    $ii = (int) $i + 1;
    $sth = $dbh->prepare("INSERT INTO arm_prog_data (p_id, p_seq, cmd, pos_x, pos_y, pos_z, dir_x, dir_y, dir_z, cmd_opt, cfg, memory_id) VALUES (:pi, :ps, :cm, :px, :py, :pz, :dx, :dy, :dz, :co, :cf, :mi)");
    $sth->bindParam(':pi', $pid, PDO::PARAM_INT);
    $sth->bindParam(':ps', $ii, PDO::PARAM_STR);
    $sth->bindParam(':cm', $data["prog"][$i]["cmd"], PDO::PARAM_STR);
    if (!empty($data["prog"][$i]["pos_x"])) {
	$sth->bindParam(':px', $data["prog"][$i]["pos_x"], PDO::PARAM_STR);
    } else {
	$sth->bindParam(':px', $zero, PDO::PARAM_STR);
    }
    if (!empty($data["prog"][$i]["pos_y"])) {
	$sth->bindParam(':py', $data["prog"][$i]["pos_y"], PDO::PARAM_STR);
    } else {
	$sth->bindParam(':py', $zero, PDO::PARAM_STR);
    }
    if (!empty($data["prog"][$i]["pos_z"])) {
	$sth->bindParam(':pz', $data["prog"][$i]["pos_z"], PDO::PARAM_STR);
    } else {
	$sth->bindParam(':pz', $zero, PDO::PARAM_STR);
    }
    if (!empty($data["prog"][$i]["dir_x"])) {
	$sth->bindParam(':dx', $data["prog"][$i]["dir_x"], PDO::PARAM_STR);
    } else {
	$sth->bindParam(':dx', $zero, PDO::PARAM_STR);
    }
    if (!empty($data["prog"][$i]["dir_y"])) {
	$sth->bindParam(':dy', $data["prog"][$i]["dir_y"], PDO::PARAM_STR);
    } else {
	$sth->bindParam(':dy', $zero, PDO::PARAM_STR);
    }
    if (!empty($data["prog"][$i]["dir_z"])) {
	$sth->bindParam(':dz', $data["prog"][$i]["dir_z"], PDO::PARAM_STR);
    } else {
	$sth->bindParam(':dz', $zero, PDO::PARAM_STR);
    }
    if (!empty($data["prog"][$i]["cmd_opt"])) {
	$sth->bindParam(':co', $data["prog"][$i]["cmd_opt"], PDO::PARAM_STR);
    } else {
	$sth->bindParam(':co', $spc, PDO::PARAM_STR);
    }
    if (!empty($data["prog"][$i]["cfg"])) {
	$sth->bindParam(':cf', $data["prog"][$i]["cfg"], PDO::PARAM_STR);
    } else {
	$sth->bindParam(':cf', $minus, PDO::PARAM_STR);
    }
    if (!empty($data["prog"][$i]["memory_id"])) {
	$sth->bindParam(':mi', $data["prog"][$i]["memory_id"], PDO::PARAM_STR);
    } else {
	$sth->bindParam(':mi', $minus, PDO::PARAM_STR);
    }
    $ret = $sth->execute();
    $rd = $sth->fetchAll();
}
$ret = 1;

$arr = array(
    'result' => $ret,
    'p_id'   => $pid,
    'p_name' => $data["p_name"],
    'u_name' => $data["u_name"]
    );

$json_value = json_encode($arr);
header('Content-Type: text/javascript; charset=utf-8');
echo $json_value;

?>
