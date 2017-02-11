<?php

require_once 'common.php';

$json_value = null;

$post = file_get_contents("php://input");

$data = json_decode($post, true);

$arr = null;            // store results
$sth = null;		// store sql query script
$err = null;
$ret = 0;

// no ip check for reading

//connect DB
$dbh = iort_openDb();

if(!$dbh) {
  $ret = 0;
  $err = "DB connection failed.";
  goto end;
}

$cid = $data["c_id"];
$status = "";
$pos = 0;
$time = "";

// echo "t_id: " . $tid . ", cmd: ", . $data["cmd"];

// get data
$sth = $dbh->prepare("SELECT * FROM arm_camera_status WHERE c_id= :ti");
$sth->bindParam(':ti', $cid, PDO::PARAM_INT);
$ret = $sth->execute();
if (!$ret) {
  $err =  "DB retrieve" . $tid . " error";
  goto end;
}
$rd  = $sth->fetchAll();

$ret = 0;
$status = "";
$o_key = 0;
$i_key = 0;

if (count($rd) == 1) {
  $ret = 1;
  $status = $rd[0]['c_status'];
  $o_key = $rd[0]['o_key'];
  $i_key = $rd[0]['i_key'];
  if (strcmp($data["cmd"], "status") == 0) {
    $status = $data["status"];
    if (!empty($data["o_key"])) {
      $o_key = (int) $data["o_key"];
    }
    if (!empty($data["i_key"])) {
      $i_key = (int) $data["i_key"];
    }
    $sth = $dbh->prepare("UPDATE arm_camera_status SET c_status= :cs, c_time= NOW(), i_key= :ik, o_key= :ok WHERE c_id= :ci");
    $sth->bindParam(':ci', $cid, PDO::PARAM_INT);
    $sth->bindParam(':cs', $status, PDO::PARAM_STR);
    $sth->bindParam(':ok', $o_key, PDO::PARAM_INT);
    $sth->bindParam(':ik', $i_key, PDO::PARAM_INT);
    $ret = $sth->execute();
    $rd  = $sth->fetchAll();
  } else {
    $ret = 0;
    $err = "cmd is wrong, or already that status, cmd: " . $data["cmd"] . ", status: " . $status;
    goto end;
  }
} else {
  $err = "no data";
  goto end;
}

$sth = $dbh->prepare("SELECT * FROM arm_camera_status WHERE c_id= :ti");
$sth->bindParam(':ti', $cid, PDO::PARAM_INT);
$ret = $sth->execute();
if (!$ret) {
  $err =  "DB retrieve" . $tid . " error";
  goto end;
}
$rd  = $sth->fetchAll();

$time = $rd[0][c_time];
$status = $rd[0]['c_status'];
$i_key = (int) $rd[0]['i_key'];
$o_key = (int) $rd[0]['o_key'];

end:
$arr = array(
  'ret' => $ret,
  'err' => $err,
  'status' => $status,
  'i_key' => $i_key,
  'o_key' => $o_key,
  'time' => $time
             );

$json_value = json_encode($arr);
header('Content-Type: text/javascript; charset=utf-8');
echo $json_value;

?>
