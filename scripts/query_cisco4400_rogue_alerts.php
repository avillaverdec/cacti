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


$bsnRogueAPEntry = ".1.3.6.1.4.1.14179.2.1.7.1.24";

$rogues = reindex(cacti_snmp_walk($hostname, $snmp_community, $bsnRogueAPEntry, $snmp_version, "", "", 161, 1000));

$sorted = array_count_values($rogues);

for ($i = 0; $i <= 10; $i++) {
	if (array_key_exists($i, $sorted)) {
	}
	else {
	$sorted[$i] = 0;}

}


print "reta:" . $sorted[0] . " retb:" . $sorted[1] . " retc:" . $sorted[2] . " retd:" . $sorted[3] . " rete:" . $sorted[4] . " retf:" . $sorted[5] . " retg:" . $sorted[6] . " reth:" . $sorted[7] . " reti:" . $sorted[8] . " retj:" . $sorted[9] . " retk:" . $sorted[10];

function reindex($arr) {
   $return_arr = array();
   
   for ($i=0;($i<sizeof($arr));$i++) {
      $return_arr[$i] = $arr[$i]["value"];
   }

   return $return_arr;
}
?>
