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
$mid = $rd[0]["m_id"];

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

// edge information
$sth = $dbh->prepare("SELECT * FROM car_map_edge");
$ret = $sth->execute();
$rd  = $sth->fetchAll();

$edges = array();

$e1 = array();
$e2 = array();

foreach($rd as $key => $value) {
  array_push($e1, $value["e_name"]);
  array_push($e2, array('name' => $value["e_name"],
                        'n1' => $value["n1_name"],
                        'n2' => $value["n2_name"]));
}
$edges = array_combine($e1, $e2);

$arr = array(
    'result' => $ret,
    'node' => $nodes,
    'edge' => $edges
    );


$json_value = json_encode($arr);
header('Content-Type: text/javascript; charset=utf-8');
echo $json_value;

?>
