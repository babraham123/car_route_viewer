<?php

require_once 'common.php';

$json_value = null;

$post = file_get_contents("php://input");

$data = json_decode($post, true);
$arr = null;            // store results
$sth = null;		// store sql query script

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
    echo "DB connection failed.";
    exit;
}

// echo "m_name: ", $data["m_name"];

// get data
$sth = $dbh->prepare("SELECT * FROM car_map WHERE m_name= :mn");
$sth->bindParam(':mn', $data["m_name"], PDO::PARAM_STR);
$ret = $sth->execute();
if (!$ret) {
    echo "DB retrieve", $data["m_name"], " error";
    exit;
}
$rd  = $sth->fetchAll();
$mid = (int) $rd[0]["m_id"];
$t_time = $data["t_time"];

// echo "mid: ", $mid;


// node information
$sth = $dbh->prepare("SELECT * FROM car_map_node");
$ret = $sth->execute();
$rd  = $sth->fetchAll();

$nodes = array();

$n1 = array();
$n2 = array();
foreach($rd as $key => $value) {
  array_push($n1, $value["n_name"]);
  array_push($n2, array('name' => $value["n_name"],
                        'pos' => [(float)$value["pos_x"],(float)$value["pos_y"]]));
}
$nodes = array_combine($n1, $n2);

// edge information, traffic is good (1.0) for both ways
$sth = $dbh->prepare("SELECT * FROM car_map_edge WHERE m_id= :mi");
$sth->bindParam(':mi', $mid, PDO::PARAM_INT);
$ret = $sth->execute();
$rd  = $sth->fetchAll();

$edges = array();

$e1 = array();
$e2 = array();

$t1 = array();
$t2 = array();
foreach($rd as $key => $value) {
  array_push($e1, $value["e_name"]);
  array_push($t1, $value["e_name"]);
  array_push($e2, array('name' => $value["e_name"],
                        'n1' => $value["n1_name"],
                        'n2' => $value["n2_name"]));
  array_push($t2, [1.0, 1.0]);
}
$edges = array_combine($e1, $e2);
$traffic = array_combine($t1, $t2);


// traffic informatation
// echo "m_id " . $mid . "tt " . $data['t_time'];

$sth = $dbh->prepare("SELECT * FROM car_traffic WHERE m_id= :mi ORDER by ABS(TIMEDIFF(:tt, t_time))");
$sth->bindParam(':mi', $mid, PDO::PARAM_INT);
$sth->bindParam(':tt', $data['t_time'], PDO::PARAM_STR);
$ret = $sth->execute();
$rd  = $sth->fetchAll();

//echo count($rd);

// override the traffic data
if (count($rd) > 0) {
  $tid = (int) $rd[0]["t_id"];
  $t_time = $rd[0]["t_time"];
  $sth = $dbh->prepare("SELECT * FROM car_traffic_status WHERE t_id= :ti");
  $sth->bindParam(':ti', $tid, PDO::PARAM_INT);
  $ret = $sth->execute();
  $rd  = $sth->fetchAll();
  //echo "tid " . $tid;
  //print_r($rd);
  foreach($rd as $key => $value) {
    $traffic[$value["e_name"]][0] = (float)$value["e_w1"];
    $traffic[$value["e_name"]][1] = (float)$value["e_w2"];
  }
}

$arr = array(
    'result' => $ret,
    'time' => $t_time,
    'traffic' => $traffic,
    'node' => $nodes,
    'edge' => $edges
    );


$json_value = json_encode($arr);
header('Content-Type: text/javascript; charset=utf-8');
echo $json_value;

?>
