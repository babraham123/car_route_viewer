<?php
//////////////////////////
// Concepea common codes
// MIRAI IT SOLUTIONS
//////////////////////////

//static settings
//create global variable
//

class Constants{
    const EMAIL_RYUUSKE = 'iort.info@gmail.com';
    const SIGNUP_EMAIL_FROM = 'iort.info@gmail.com';
    const DESIGN_CHECK_EMAIL_FROM = 'iort.info@gmail.com';
}

function iort_getDefaultObjectThumbnailImagePath(){
    return iort_getBaseUrl() . "/image/object/thumbnail/pending.png";
}

function iort_getBaseUrl(){
    return "http://cerlab29.andrew.cmu.edu";
}

function iort_getDefaultRingSize(){
    return 8;
}

function iort_getDefaultRingMaterial(){
    return 1;
}

function iort_getDefaultObjectName($lang){
    return "No name";
}

function iort_openDb(){
    $dsn = 'mysql:host=localhost;dbname=iort;charset=utf8';
    $username = 'iort';
    $password = 'ShimadaKenji';
    $options = array(
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
    ); 

    try {
        $dbh = new PDO($dsn, $username, $password, $options);
        $sth = $dbh->prepare("SELECT @@session.time_zone, SET time_zone='-05:00'");
        $sth->execute();
    } catch (PDOException $e) {
        return null;
    }
    return $dbh;
}

// generates 32 bytes CSRF token
function get_csrf_token() {
  $TOKEN_LENGTH = 16;//16*2=32bytes
  $bytes = openssl_random_pseudo_bytes($TOKEN_LENGTH);
  return bin2hex($bytes);
}

// Check login
// Check email and password
function isLogin($dbh, $email, $pass) {
    $sth = $dbh -> prepare("SELECT * FROM user WHERE u_email= :email and u_password= :pass and u_enabled = 1");
    $sth->bindParam(':email', $email, PDO::PARAM_STR);
    $sth->bindParam(':pass', sha1($pass), PDO::PARAM_STR);
    $sth->execute();
    $resultuser = $sth->fetchAll();

    if(count($resultuser)>0){
        return true;
    }else{
        return false;
    }
}

// Generate a random string
function randomString($length) {
    $str = "";
    $characters = array_merge(range('A','Z'), range('a','z'), range('0','9'));
    $max = count($characters) - 1;
    for ($i = 0; $i < $length; $i++) {
        $rand = mt_rand(0, $max);
        $str .= $characters[$rand];
    }  
    return $str;
}

function getRealIP() {
    if (isset($_SERVER)){
	if(isset($_SERVER["HTTP_X_FORWARDED_FOR"])){
	    $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
	    if(strpos($ip,",")){
		$exp_ip = explode(",",$ip);
		$ip = $exp_ip[0];
	    }
	}else if(isset($_SERVER["HTTP_CLIENT_IP"])){
	    $ip = $_SERVER["HTTP_CLIENT_IP"];
	}else{
	    $ip = $_SERVER["REMOTE_ADDR"];
	}
    }else{
	if(getenv('HTTP_X_FORWARDED_FOR')){
	    $ip = getenv('HTTP_X_FORWARDED_FOR');
	    if(strpos($ip,",")){
		$exp_ip=explode(",",$ip);
		$ip = $exp_ip[0];
	    }
	}else if(getenv('HTTP_CLIENT_IP')){
	    $ip = getenv('HTTP_CLIENT_IP');
	}else {
	    $ip = getenv('REMOTE_ADDR');
	}
    }
    return $ip; 
}

function checkIP() {
  $ip = getRealIP();
  if (($ip[0] == '1' && $ip[1] == '2' && $ip[2] == '8') ||
      ($ip[0] == '1' && $ip[1] == '9' && $ip[2] == '2') ||    
      $ip[0] == ':') {
    // echo "connection from CMU or local network";
    return(1);
  } else {
    // echo "connection limited to CMU campus.";
    return(0);
  }
}

