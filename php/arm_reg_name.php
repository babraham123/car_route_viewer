<?php

require_once 'common.php';

$json_value = null;

$post = file_get_contents("php://input");

$data = json_decode($post, true);
$arr = null;            //this stores results
$rid = null;
$err = null;

//connect DB
$dbh = iort_openDb();

if(!$dbh) {
    echo "DB connection failed.";
    exit;
}

$stat = "Completed";

// arm_id table
$sth = $dbh->prepare("SELECT * FROM arm_id WHERE r_name= :rn ");
$sth->bindParam(':rn', $data["name"], PDO::PARAM_STR);
$ret = $sth->execute();
$rd = $sth->fetchAll();

if (count($rd) < 1) {
  if (checkIP()) {
    $sth = $dbh->prepare("INSERT INTO arm_id (r_id, r_name) SELECT COUNT(*)+1, :rn FROM arm_id");
    $sth->bindParam(':rn', $data["name"], PDO::PARAM_STR);
    $sth->execute();
    $rd = $sth->fetchAll();
    $sth = $dbh->prepare("SELECT * FROM arm_id WHERE r_name= :rn ");
    $sth->bindParam(':rn', $data["name"], PDO::PARAM_STR);
    $ret = $sth->execute();
    $rd = $sth->fetchAll();
  } else {
    $ret = 0;
    $err = "connection limited to CMU campus";
    goto end;
  }
}
$rid = (int) $rd[0]["r_id"];

// arm_status table
$sth = $dbh->prepare("SELECT * FROM arm_status WHERE r_id= :ri ");
$sth->bindParam(':ri', $rid, PDO::PARAM_INT);
$ret = $sth->execute();
$rd = $sth->fetchAll();
if (count($rd) < 1) {
  $sth = $dbh->prepare("INSERT INTO arm_status (r_id, r_status, r_time) VALUES (:ri, :st, NOW())");
  $sth->bindParam(':ri', $rid, PDO::PARAM_INT);
  $sth->bindParam(':st', $stat, PDO::PARAM_STR);
  $ret = $sth->execute();
  $rd = $sth->fetchAll();
}

// arm_table_id
$sth = $dbh->prepare("SELECT * FROM arm_table_id WHERE t_name= :rn ");
$sth->bindParam(':rn', $data["name"], PDO::PARAM_STR);
$ret = $sth->execute();
$rd = $sth->fetchAll();

if (count($rd) < 1) {
  $sth = $dbh->prepare("INSERT INTO arm_table_id (t_id, t_name) SELECT COUNT(*)+1, :rn FROM arm_table_id");
  $sth->bindParam(':rn', $data["name"], PDO::PARAM_STR);
  $sth->execute();
  $rd = $sth->fetchAll();
  $sth = $dbh->prepare("SELECT * FROM arm_table_id WHERE t_name= :rn ");
  $sth->bindParam(':rn', $data["name"], PDO::PARAM_STR);
  $ret = $sth->execute();
  $rd = $sth->fetchAll();
}
$tid = (int) $rd[0]["t_id"];

// arm_table_status
$sth = $dbh->prepare("SELECT * FROM arm_table_status WHERE t_id= :ri ");
$sth->bindParam(':ri', $tid, PDO::PARAM_INT);
$ret = $sth->execute();
$rd = $sth->fetchAll();
if (count($rd) < 1) {
  $sth = $dbh->prepare("INSERT INTO arm_table_status (t_id, t_status, t_time) VALUES (:ri, :st, NOW())");
  $sth->bindParam(':ri', $tid, PDO::PARAM_INT);
  $sth->bindParam(':st', $stat, PDO::PARAM_STR);
  $ret = $sth->execute();
  $rd = $sth->fetchAll();
}

// arm_camera_id
$sth = $dbh->prepare("SELECT * FROM arm_camera_id WHERE c_name= :rn ");
$sth->bindParam(':rn', $data["name"], PDO::PARAM_STR);
$ret = $sth->execute();
$rd = $sth->fetchAll();
if (count($rd) < 1) {
  $sth = $dbh->prepare("INSERT INTO arm_camera_id (c_id, c_name) SELECT COUNT(*)+1, :rn FROM arm_camera_id");
  $sth->bindParam(':rn', $data["name"], PDO::PARAM_STR);
  $sth->execute();
  $rd = $sth->fetchAll();
  $sth = $dbh->prepare("SELECT * FROM arm_camera_id WHERE c_name= :rn ");
  $sth->bindParam(':rn', $data["name"], PDO::PARAM_STR);
  $ret = $sth->execute();
  $rd = $sth->fetchAll();
}
$cid = (int) $rd[0]["c_id"];

// arm_camera_status
$sth = $dbh->prepare("SELECT * FROM arm_camera_status WHERE c_id= :ci ");
$sth->bindParam(':ci', $cid, PDO::PARAM_INT);
$ret = $sth->execute();
$rd = $sth->fetchAll();
if (count($rd) < 1) {
  $sth = $dbh->prepare("INSERT INTO arm_camera_status (c_id, c_status, c_time) VALUES (:ri, :st, NOW())");
  $sth->bindParam(':ri', $cid, PDO::PARAM_INT);
  $sth->bindParam(':st', $stat, PDO::PARAM_STR);
  $ret = $sth->execute();
  $rd = $sth->fetchAll();
}


$ret = 1;

end:
$arr = array(
    'ret' => $ret,
    'err' => $err,
    'r_name' => $data["name"],
    'r_id' => $rid,
    't_id' => $tid,
    'c_id' => $cid
    );

$json_value = json_encode($arr);
header('Content-Type: text/javascript; charset=utf-8');
echo $json_value;

?>
