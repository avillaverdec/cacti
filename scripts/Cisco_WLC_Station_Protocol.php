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


$bsnMobileProtocol = ".1.3.6.1.4.1.14179.2.1.4.1.25";

$stationprotocol = reindex(cacti_snmp_walk($hostname, $snmp_community, $bsnMobileProtocol, $snmp_version, "", "", 161, 1000));

$findStationProtocolType= array_count_values($stationprotocol);

$stationdot11a=0;
$stationdot11b=0;
$stationdot11g=0;
$stationunknownprotocol=0;
$stationmobile=0;


if (array_key_exists('1', $findStationProtocolType)) {
	$stationdot11a=$findStationProtocolType[1];
	}
if (array_key_exists('2', $findStationProtocolType)) {
	$stationdot11b=$findStationProtocolType[2];
	}
if (array_key_exists('3', $findStationProtocolType)) {
	$stationdot11g=$findStationProtocolType[3];
	}
if (array_key_exists('4', $findStationProtocolType)) {
	$stationunknownprotocol=$findStationProtocolType[4];
	}
if (array_key_exists('5', $findStationProtocolType)) {
	$stationmobile=$findStationProtocolType[5];
	}




print "stationdot11a:" . $stationdot11a . " stationdot11b:" . $stationdot11b . " stationdot11g:" . $stationdot11g . " stationunknownprotocol:" . $stationunknownprotocol . " stationmobile:" . $stationmobile;


function reindex($arr) {
	$return_arr = array();
	
	for ($i=0;($i<sizeof($arr));$i++) {
		$return_arr[$i] = $arr[$i]["value"];
	}

	return $return_arr;
}

?>
