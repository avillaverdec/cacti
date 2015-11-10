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


$bsnMobileStationStatus = ".1.3.6.1.4.1.14179.2.1.4.1.9";

$stationstatus = reindex(cacti_snmp_walk($hostname, $snmp_community, $bsnMobileStationStatus, $snmp_version, "", "", 161, 1000));

$findStationStatusType= array_count_values($stationstatus);

$stationidle=0;
$stationpending=0;
$stationauthenticated=0;
$stationassociated=0;
$stationpowersave=0;
$stationdisassociated=0;
$stationtobedeleted=0;
$stationprobing=0;
$stationblacklisted=0;


if (array_key_exists('0', $findStationStatusType)) {
	$stationidle=$findStationStatusType[0];
	}
if (array_key_exists('1', $findStationStatusType)) {
	$stationpending=$findStationStatusType[1];
	}
if (array_key_exists('2', $findStationStatusType)) {
	$stationauthenticated=$findStationStatusType[2];
	}
if (array_key_exists('3', $findStationStatusType)) {
	$stationassociated=$findStationStatusType[3];
	}
if (array_key_exists('4', $findStationStatusType)) {
	$stationpowersave=$findStationStatusType[4];
	}
if (array_key_exists('5', $findStationStatusType)) {
	$stationdisassociated=$findStationStatusType[5];
	}
if (array_key_exists('6', $findStationStatusType)) {
	$stationtobedeleted=$findStationStatusType[6];
	}
if (array_key_exists('7', $findStationStatusType)) {
	$stationprobing=$findStationStatusType[7];
	}
if (array_key_exists('8', $findStationStatusType)) {
	$stationblacklisted=$findStationStatusType[8];
	}

print "stationidle:" . $stationidle . " stationpending:" . $stationpending . " stationauthenticated:" . $stationauthenticated . " stationassociated:" . $stationassociated . " stationpowersave:" . $stationpowersave . " stationdisassociated:" . $stationdisassociated . " stationtobedeleted:" . $stationtobedeleted . " stationprobing:" . $stationprobing . " stationblacklisted:" . $stationblacklisted;

function reindex($arr) {
	$return_arr = array();
	
	for ($i=0;($i<sizeof($arr));$i++) {
		$return_arr[$i] = $arr[$i]["value"];
	}

	return $return_arr;
}

?>
