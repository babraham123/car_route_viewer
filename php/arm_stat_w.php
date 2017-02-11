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
  $err = "DB connection failed.";
  goto end;
}

#echo "r_id: ", $data["r_id"];
#echo "cmd : ", $data["cmd"];

// get data
$sth = $dbh->prepare("SELECT * FROM arm_status WHERE r_id= :ri");
$sth->bindParam(':ri', $data["r_id"], PDO::PARAM_INT);
$ret = $sth->execute();
if (!$ret) {
  $err =  "DB retrieve" . $data["r_id"] . " error";
  goto end;
}
$rd  = $sth->fetchAll();
if (count($rd) < 1) {
  $ret = 0;
  $err = "Error: no table for " . $data["r_id"];
  goto end;
}
$ret = 0;
$pname = null;
$uname = null;
$pid = 0;
$psec = 0;
$cmd = null;
$status = $rd[0]["r_status"];

if (count($rd) == 1) {
  $ret = 1;
  $pname = $rd[0]["p_name"];
  $uname = $rd[0]["u_name"];
  $pseq = (int) $rd[0]["p_seq"];
  $cmd = $rd[0]["cmd"];
  // echo "strcmp", strcmp($data["cmd"], "inc");
  if (strcmp($data["cmd"], "inc") == 0) {
    $sth = $dbh->prepare("SELECT * FROM arm_prog WHERE p_name= :pn AND u_name= :un");
    $sth->bindParam(':pn', $pname, PDO::PARAM_STR);
    $sth->bindParam(':un', $uname, PDO::PARAM_STR);
    $ret = $sth->execute();
    if (!$ret) {
      $err = "DB retrieve" . $data["r_id"] . " error";
      goto end;
    }
    $rd  = $sth->fetchAll();
    $pseq = $pseq + 1;
    $pid = $rd[0]["p_id"];
    // echo "pi ", $pi;
    $sth = $dbh->prepare("SELECT * FROM arm_prog_data WHERE p_id= :pi ORDER BY p_seq");
    $sth->bindParam(':pi', $pid, PDO::PARAM_INT);
    $ret = $sth->execute();
    $rd = $sth->fetchAll();
    // echo "count ", count($rd), "pseq ", $pseq;
    if (count($rd) <= $pseq) {
      // reach end of the program
      $pseq = 0;
      $pname = "";
      $uname = "";
      $status = "Completed";
      $pid = 0;
      $sth = $dbh->prepare("UPDATE arm_status SET p_seq= :ps, r_status= :cmd, p_name= :pn, p_id= :pi, u_name= :un, r_time= NOW() WHERE r_id= :ri");
      $sth->bindParam(':ps', $pseq, PDO::PARAM_INT);
      $sth->bindParam(':ri', $data["r_id"], PDO::PARAM_INT);
      $sth->bindParam(':cmd', $status, PDO::PARAM_STR);
      $sth->bindParam(':pn', $pname, PDO::PARAM_STR);
      $sth->bindParam(':pi', $pid, PDO::PARAM_STR);
      $sth->bindParam(':un', $uname, PDO::PARAM_STR);
      $ret = $sth->execute();
      $rd = $sth->fetchAll();
    } else {
      // can increment
      $sth = $dbh->prepare("UPDATE arm_status SET p_seq= :ps, r_time= NOW() WHERE r_id= :ri");
      $sth->bindParam(':ps', $pseq, PDO::PARAM_INT);
      $sth->bindParam(':ri', $data["r_id"], PDO::PARAM_INT);
      $ret = $sth->execute();
      $rd = $sth->fetchAll();
    }
  } else if (strcmp($data["cmd"], "reset") == 0) {
    $pseq = 0;
    $pid = 0;
    $pname = " ";
    $uname = " ";
    $status = "Completed";
    $sth = $dbh->prepare("UPDATE arm_status SET r_status= :cmd, p_seq= :ps, p_id= :pi, p_name= :pn, u_name= :un, r_time= NOW()  WHERE r_id= :ri");
    $sth->bindParam(':ri', $data["r_id"], PDO::PARAM_INT);
    $sth->bindParam(':cmd', $status, PDO::PARAM_STR);
    $sth->bindParam(':ps', $pseq, PDO::PARAM_INT);
    $sth->bindParam(':pi', $pid, PDO::PARAM_STR);
    $sth->bindParam(':pn', $pname, PDO::PARAM_STR);
    $sth->bindParam(':un', $uname, PDO::PARAM_STR);
    $ret = $sth->execute();
    $rd = $sth->fetchAll();
  } else if (strcmp($data["cmd"], "stop") == 0) {
    $pseq = 0;
    $pid = 0;
    $pname = "";
    $uname = "";
    $status = "Stop";
    $sth = $dbh->prepare("UPDATE arm_status SET r_status= :cmd, r_time= NOW() WHERE r_id= :ri");
    $sth->bindParam(':ri', $data["r_id"], PDO::PARAM_INT);
    $sth->bindParam(':cmd', $status, PDO::PARAM_STR);
    $ret = $sth->execute();
    $rd = $sth->fetchAll();
  } else if (strcmp($data["cmd"], "start") == 0) {
    $pname = $data["p_name"];
    $uname = $data["u_name"];
    $sth = $dbh->prepare("SELECT * FROM arm_prog WHERE p_name= :pn AND u_name= :un");
    $sth->bindParam(':pn', $pname, PDO::PARAM_STR);
    $sth->bindParam(':un', $uname, PDO::PARAM_STR);
    $ret = $sth->execute();
    if (!$ret) {
      $err = "DB select from arm_prog error";
      goto end;
    } else if (count($rd) < 1) {
      $ret = 0;
      $err = "No program entry of " .  $pname . " for " . $uname;
    } else {
      $rd  = $sth->fetchAll();
      $pid = $rd[0]["p_id"];
      $pseq = 0;
      $status = "Running";
      $sth = $dbh->prepare("UPDATE arm_status SET p_seq= :ps, r_status= :cmd, p_name= :pn, p_id= :pi, u_name= :un, r_time= NOW() WHERE r_id= :ri");
      $sth->bindParam(':ps', $pseq, PDO::PARAM_INT);
      $sth->bindParam(':ri', $data["r_id"], PDO::PARAM_INT);
      $sth->bindParam(':cmd', $status, PDO::PARAM_STR);
      $sth->bindParam(':pn', $pname, PDO::PARAM_STR);
      $sth->bindParam(':pi', $pid, PDO::PARAM_STR);
      $sth->bindParam(':un', $uname, PDO::PARAM_STR);
      $ret = $sth->execute();
      $rd = $sth->fetchAll();
    }
  } else if (strcmp($data["cmd"], "status") == 0) {
      $sth = $dbh->prepare("UPDATE arm_status SET r_status= :st, r_time= NOW() WHERE r_id= :ri");
      $sth->bindParam(':ri', $data["r_id"], PDO::PARAM_INT);
      $sth->bindParam(':st', $data["status"], PDO::PARAM_STR);
      $ret = $sth->execute();
      $rd = $sth->fetchAll();
  }
}

end:
$arr = array(
  'ret' => $ret,
  'err' => $err,
  'r_id' => $data["r_id"],
  'p_id' => $pid,
  'p_name' => $pname,
  'u_name' => $uname,
  'p_seq' => $pseq,
  'r_status' => $status
             );


$json_value = json_encode($arr);
header('Content-Type: text/javascript; charset=utf-8');
echo $json_value;

?>
