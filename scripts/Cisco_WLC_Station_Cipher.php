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


$bsnMobileEncryptionCipher = ".1.3.6.1.4.1.14179.2.1.4.1.31";

$stationcipher = reindex(cacti_snmp_walk($hostname, $snmp_community, $bsnMobileEncryptionCipher, $snmp_version, "", "", 161, 1000));

$findStationCipherType= array_count_values($stationcipher);

$stationccmpaes=0;
$stationtkipmic=0;
$stationwep40=0;
$stationwep104=0;
$stationwep128=0;
$stationciphernone=0;
$stationciphernotavailable=0;
$stationcipherunknown=0;


if (array_key_exists('0', $findStationCipherType)) {
	$stationccmpaes=$findStationCipherType[0];
	}
if (array_key_exists('1', $findStationCipherType)) {
	$stationtkipmic=$findStationCipherType[1];
	}
if (array_key_exists('2', $findStationCipherType)) {
	$stationwep40=$findStationCipherType[2];
	}
if (array_key_exists('3', $findStationCipherType)) {
	$stationwep104=$findStationCipherType[3];
	}
if (array_key_exists('4', $findStationCipherType)) {
	$stationwep128=$findStationCipherType[4];
	}
if (array_key_exists('5', $findStationCipherType)) {
	$stationciphernone=$findStationCipherType[5];
	}
if (array_key_exists('6', $findStationCipherType)) {
	$stationciphernotavailable=$findStationCipherType[6];
	}
if (array_key_exists('7', $findStationCipherType)) {
	$stationcipherunknown=$findStationCipherType[7];
	}

print "stationccmpaes:" . $stationccmpaes . " stationtkipmic:" . $stationtkipmic . " stationwep40:" . $stationwep40 . " stationwep104:" . $stationwep104 . " stationwep128:" . $stationwep128 . " stationciphernone:" . $stationciphernone . " stationciphernotavailable:" . $stationciphernotavailable . " stationcipherunknown:" . $stationcipherunknown;

function reindex($arr) {
	$return_arr = array();
	
	for ($i=0;($i<sizeof($arr));$i++) {
		$return_arr[$i] = $arr[$i]["value"];
	}

	return $return_arr;
}

?>
