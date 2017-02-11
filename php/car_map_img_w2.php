<?php
$target_dir = "../car/car_camera_map/";
$target_file = $target_dir . basename($_FILES["file"]["name"]);

// no ip check for reading
//$ip = getRealIP();
//if (($ip[0] == '1' && $ip[1] == '2' && $ip[2] == '8') ||
//    ($ip[0] == '1' && $ip[1] == '9' && $ip[2] == '2') ||    
//    $ip[0] == ':') {
//    // echo "connection from CMU or local network";
//} else {
//    echo "connection limited to CMU campus.";
//}

//print_r($_FILES);
//echo $_FILES["file"]["tmp_name"];
//echo $target_file;
move_uploaded_file($_FILES["file"]["tmp_name"],$target_file);
?>
