<?php
include('ip_detection/utilities.php');//include some utility functions

//get the current domain
$current_domain = (!isset($current_domain)) ? str_replace("www.", "", preg_replace("/\/(.+)/", "", $_SERVER["SERVER_NAME"])) : $current_domain;

//Load default settings: $site_enviro , $db settings and basic functions
include_once('ip_detection/class.ip_detection.php');

$ipDetection = new IP_Detection($current_domain);
$lang = "en";
$host_framework = "veryveryshort.nfb.ca";

//not found
if(!$ipDetection->dbConnect()) {
    return array(
    	"language" => $lang,
    	"languages" => ["en", "fr", "de"],
    	"environment" => isset($ipDetection) ? $ipDetection->getSiteEnv() : "dev",
    	"domain" => "https://".$current_domain
    );
    exit;
}

// $remoteIP = "65.60.37.194";//US
// $remoteIP = "70.38.98.156";//CA QC
// $remoteIP= "2.21.111.225"; //Austria
// $remoteIP = "92.88.229.164";//FR
$ip = (isset($_SESSION["ip"]) && $_SESSION["ip"] != "") ? $_SESSION["ip"] : get_visitor_ip();
$remoteIP = $ip;
$ipDetection->getIPInfo($remoteIP);

//check language
// if($ipDetection->getIPLanguage() == "fr") $lang = "fr";
// if($ipDetection->getIPLanguage() == "de") $lang = "de";
// if($ipDetection->getIPCountry() == "CA") $lang = "ca";

if(strpos($current_domain, "veryveryshort") !== FALSE) {
    $lang = "en";
    $host_framework = "veryveryshort.nfb.ca";
}

if(strpos($current_domain, "trestrescourt") !== FALSE) {
    $lang = "fr";
    $host_framework = "trestrescourt.onf.ca";
}

if(strpos($current_domain, "sehrsehrkurz") !== FALSE) {
    $lang = "de";
    $host_framework = "trestrescourt.onf.ca";
}

return array(
    "ip" => $remoteIP,
    "country" => $ipDetection->getIPCountry(),
	"language" => $lang,
	"languages" => ["en", "fr", "de"],
    "topnav" => $ipDetection->getIpTopNav(),
	"environment" => $ipDetection->getSiteEnv(),
	"domain" => "https://".$current_domain,
    "framework_domain" => "https://".$host_framework
);
?>