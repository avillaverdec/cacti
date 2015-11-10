<?php

/* do NOT run this script through a web browser */
if (!isset($_SERVER["argv"][0]) || isset($_SERVER['REQUEST_METHOD'])  || isset($_SERVER['REMOTE_ADDR'])) {
   die("<br><strong>This script is only meant to run at the command line.</strong>");
}

$no_http_headers = true;

if (file_exists(dirname(__FILE__) . "/../include/global.php")) {
        include(dirname(__FILE__) . "/../include/global.php");
}
include(dirname(__FILE__) . "/../include/config.php");
include(dirname(__FILE__) . "/../lib/snmp.php");


$hostname = $_SERVER["argv"][1];
$snmp_community = $_SERVER["argv"][2];
$snmp_version = $_SERVER["argv"][3];


$bsnMobileAuthenticationAlgorithm = ".1.3.6.1.4.1.14179.2.1.4.1.19";

$stationalgorithm = reindex(cacti_snmp_walk($hostname, $snmp_community, $bsnMobileAuthenticationAlgorithm, $snmp_version, "", "", 161, 1000));

$findStationAlgorithmType= array_count_values($stationalgorithm);

$stationauthopen=0;
$stationauthsharedkey=0;
$stationauthunknown=0;
$stationopenandeap=0;


if (array_key_exists('0', $findStationAlgorithmType)) {
	$stationauthopen=$findStationAlgorithmType[0];
	}
if (array_key_exists('1', $findStationAlgorithmType)) {
	$stationauthsharedkey=$findStationAlgorithmType[1];
	}
if (array_key_exists('2', $findStationAlgorithmType)) {
	$stationauthunknown=$findStationAlgorithmType[2];
	}
if (array_key_exists('3', $findStationAlgorithmType)) {
	$stationopenandeap=$findStationAlgorithmType[128];
	}

print "stationauthopen:" . $stationauthopen . " stationauthsharedkey:" . $stationauthsharedkey . " stationauthunknown:" . $stationauthunknown . " stationopenandeap:" . $stationopenandeap;


function reindex($arr) {
	$return_arr = array();
	
	for ($i=0;($i<sizeof($arr));$i++) {
		$return_arr[$i] = $arr[$i]["value"];
	}

	return $return_arr;
}

?>
