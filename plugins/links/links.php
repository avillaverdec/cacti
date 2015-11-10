<?php

chdir('../../');
include_once("./include/auth.php");
include_once("./include/config.php");

$_SESSION['custom']=false;
include_once("./include/top_graph_header.php");

print '<div style="margin: 20px;">';
include_once($config["base_path"] . "/plugins/links/editme.php");

print '</div></body></html>';

?>
