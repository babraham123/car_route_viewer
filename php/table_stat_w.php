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

$tid = $data["t_id"];
$status = "";
$pos = 0;
$time = "";

// echo "t_id: " . $tid . ", cmd: ", . $data["cmd"];

// get data
$sth = $dbh->prepare("SELECT * FROM arm_table_status WHERE t_id= :ti");
$sth->bindParam(':ti', $tid, PDO::PARAM_INT);
$ret = $sth->execute();
if (!$ret) {
  $err =  "DB retrieve" . $tid . " error";
  goto end;
}
$rd  = $sth->fetchAll();


// echo count($rd);

$ret = 0;
$status = null;

if (count($rd) == 1) {
  $ret = 1;
  $status = $rd[0]['t_status'];
  if ((strcmp($data["cmd"], "turn") == 0)  && (strcmp($status, "Completed") == 0)) {
    $status = "Running";
    $sth = $dbh->prepare("UPDATE arm_table_status SET t_status= :ts, t_time= NOW() WHERE t_id= :ti");
    $sth->bindParam(':ti', $tid, PDO::PARAM_INT);
    $sth->bindParam(':ts', $status, PDO::PARAM_STR);
    $ret = $sth->execute();
    $rd  = $sth->fetchAll();
  } else if ((strcmp($data["cmd"], "done") == 0) && (strcmp($status, "Running") == 0)) {
    // read from command
    $pos = (int) $data["pos"];
    // calculate new position
    //$pos = (int) $rd[0]['t_pos'];
    //$pos = (++$pos) % 8;
    
    // $err = "cmd: " .  $data["cmd"] . ", pos: " . $pos;
    $status = "Completed";
    $sth = $dbh->prepare("UPDATE arm_table_status SET t_status= :ts, t_pos= :tp, t_time= NOW() WHERE t_id= :ti");
    $sth->bindParam(':ti', $tid, PDO::PARAM_INT);
    $sth->bindParam(':ts', $status, PDO::PARAM_STR);
    $sth->bindParam(':tp', $pos, PDO::PARAM_INT);
    $ret = $sth->execute();
    $rd  = $sth->fetchAll();
  } else if (strcmp($data["cmd"], "status") == 0) {
    $status = $data["status"];
    $sth = $dbh->prepare("UPDATE arm_table_status SET t_status= :ts, t_time= NOW() WHERE t_id= :ti");
    $sth->bindParam(':ti', $tid, PDO::PARAM_INT);
    $sth->bindParam(':ts', $status, PDO::PARAM_STR);
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

$sth = $dbh->prepare("SELECT * FROM arm_table_status WHERE t_id= :ti");
$sth->bindParam(':ti', $tid, PDO::PARAM_INT);
$ret = $sth->execute();
if (!$ret) {
  $err =  "DB retrieve" . $tid . " error";
  goto end;
}
$rd  = $sth->fetchAll();

$time = $rd[0][t_time];
$status = $rd[0]['t_status'];
$pos = $rd[0]['t_pos'];

end:
$arr = array(
  'ret' => $ret,
  'err' => $err,
  'status' => $status,
  'pos' => $pos,
  'time' => $time
             );

$json_value = json_encode($arr);
header('Content-Type: text/javascript; charset=utf-8');
echo $json_value;

?>
