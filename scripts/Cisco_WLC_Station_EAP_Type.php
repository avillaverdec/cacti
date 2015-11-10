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


$bsnMobileStationEapType = ".1.3.6.1.4.1.14179.2.1.4.1.32";

$stationEAP = reindex(cacti_snmp_walk($hostname, $snmp_community, $bsnMobileStationEapType, $snmp_version, "", "", 161, 1000));

$findStationEAPType= array_count_values($stationEAP);

$stationeaptls=0;
$stationttls=0;
$stationpeap=0;
$stationleap=0;
$stationspeke=0;
$stationeapfast=0;
$stationeapnotavailable=0;
$stationeapunknown=0;

if (array_key_exists('0', $findStationEAPType)) {
	$stationeaptls=$findStationEAPType[0];
	}
if (array_key_exists('1', $findStationEAPType)) {
	$stationttls=$findStationEAPType[1];
	}
if (array_key_exists('2', $findStationEAPType)) {
	$stationpeap=$findStationEAPType[2];
	}
if (array_key_exists('3', $findStationEAPType)) {
	$stationleap=$findStationEAPType[3];
	}
if (array_key_exists('4', $findStationEAPType)) {
	$stationspeke=$findStationEAPType[4];
	}
if (array_key_exists('5', $findStationEAPType)) {
	$stationeapfast=$findStationEAPType[5];
	}
if (array_key_exists('6', $findStationEAPType)) {
	$$stationeapnotavailable=$findStationEAPType[6];
	}
if (array_key_exists('7', $findStationEAPType)) {
	$stationeapunknown=$findStationEAPType[7];
	}

print "stationeaptls:" . $stationeaptls . " stationttls:" . $stationttls . " stationpeap:" . $stationpeap . " stationleap:" . $stationleap . " stationspeke:" . $stationspeke . " stationeapfast:" . $stationeapfast . " stationeapnotavailable:" . $stationeapnotavailable . " stationeapunknown:" . $stationeapunknown;

function reindex($arr) {
	$return_arr = array();
	
	for ($i=0;($i<sizeof($arr));$i++) {
		$return_arr[$i] = $arr[$i]["value"];
	}

	return $return_arr;
}

?>
