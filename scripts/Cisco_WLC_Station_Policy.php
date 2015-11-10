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


$bsnMobileStationPolicyType = ".1.3.6.1.4.1.14179.2.1.4.1.30";

$stationpolicy = reindex(cacti_snmp_walk($hostname, $snmp_community, $bsnMobileStationPolicyType, $snmp_version, "", "", 161, 1000));

$findStationPolicyType= array_count_values($stationpolicy);

$stationpolicydot1x=0;
$stationpolicywpa1=0;
$stationpolicywpa2=0;
$stationpolicywpav2vff=0;
$stationpolicynotavailable=0;
$stationpolicyunknown=0;


if (array_key_exists('0', $findStationPolicyType)) {
	$stationpolicydot1x=$findStationPolicyType[0];
	}
if (array_key_exists('1', $findStationPolicyType)) {
	$stationpolicywpa1=$findStationPolicyType[1];
	}
if (array_key_exists('2', $findStationPolicyType)) {
	$stationpolicywpa2=$findStationPolicyType[2];
	}
if (array_key_exists('3', $findStationPolicyType)) {
	$stationpolicywpav2vff=$findStationPolicyType[3];
	}
if (array_key_exists('4', $findStationPolicyType)) {
	$stationpolicynotavailable=$findStationPolicyType[4];
	}
if (array_key_exists('5', $findStationPolicyType)) {
	$stationpolicyunknown=$findStationPolicyType[5];
	}

print "stationpolicydot1x:" . $stationpolicydot1x . " stationpolicywpa1:" . $stationpolicywpa1 . " stationpolicywpa2:" . $stationpolicywpa2 . " stationpolicywpav2vff:" . $stationpolicywpav2vff . " stationpolicynotavailable:" . $stationpolicynotavailable . " stationpolicyunknown:" . $stationpolicyunknown;

function reindex($arr) {
	$return_arr = array();
	
	for ($i=0;($i<sizeof($arr));$i++) {
		$return_arr[$i] = $arr[$i]["value"];
	}

	return $return_arr;
}

?>
