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


$bsnMobileStationCcxVersion = ".1.3.6.1.4.1.14179.2.1.4.1.33";

$stationccxversion = reindex(cacti_snmp_walk($hostname, $snmp_community, $bsnMobileStationCcxVersion, $snmp_version, "", "", 161, 1000));

$findStationccxversionType= array_count_values($stationccxversion);

$stationccxunsupported=0;
$stationccxv1=0;
$stationccxv2=0;
$stationccxv3=0;
$stationccxv4=0;
$stationccxv5=0;

if (array_key_exists('0', $findStationccxversionType)) {
	$stationccxunsupported=$findStationccxversionType[0];
	}
if (array_key_exists('1', $findStationccxversionType)) {
	$stationccxv1=$findStationccxversionType[1];
	}
if (array_key_exists('2', $findStationccxversionType)) {
	$stationccxv2=$findStationccxversionType[2];
	}
if (array_key_exists('3', $findStationccxversionType)) {
	$stationccxv3=$findStationccxversionType[3];
	}
if (array_key_exists('4', $findStationccxversionType)) {
	$stationccxv4=$findStationccxversionType[4];
	}
if (array_key_exists('5', $findStationccxversionType)) {
	$stationccxv5=$findStationccxversionType[5];
	}

print "stationccxunsupported:" . $stationccxunsupported . " stationccxv1:" . $stationccxv1 . " stationccxv2:" . $stationccxv2 . " stationccxv3:" . $stationccxv3 . " stationccxv4:" . $stationccxv4 . " stationccxv5:" . $stationccxv5;

function reindex($arr) {
	$return_arr = array();
	
	for ($i=0;($i<sizeof($arr));$i++) {
		$return_arr[$i] = $arr[$i]["value"];
	}

	return $return_arr;
}

?>
