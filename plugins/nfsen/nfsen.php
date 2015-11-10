<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2009 The Cacti Group                                 |
 |                                                                         |
 | This program is free software; you can redistribute it and/or           |
 | modify it under the terms of the GNU General Public License             |
 | as published by the Free Software Foundation; either version 2          |
 | of the License, or (at your option) any later version.                  |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,         |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           |
 | GNU General Public License for more details.                            |
 +-------------------------------------------------------------------------+
 | Cacti: The Complete RRDTool-based Graphing Solution                     |
 +-------------------------------------------------------------------------+
 | This code is designed, written, and maintained by the Cacti Group. See  |
 | about.php and/or the AUTHORS file for specific developer information.   |
 +-------------------------------------------------------------------------+
 | http://www.cacti.net/                                                   |
 +-------------------------------------------------------------------------+
*/
/*******************************************************************************

    Author ......... Author plgexample
    Contact ........ example@plgexample.net
    Home Site ...... http://plgexample.net
    Program ........ plgexample "plugin plgexample"
    Purpose ........ Example for plugins 
*******************************************************************************/

chdir('../../');
include_once("./include/auth.php");
include_once("./include/config.php");
?>
<script language="javascript" type="text/javascript">
	function resizeIframe(obj) {
		obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
	}
</script>
<?php
$_SESSION['custom']=false;

  include_once("./include/top_graph_header.php");

  print('<iframe src="http://monitorizacion.urjc.es/nfsen/nfsen.php" frameborder="0" width="100%" onload="javascript:resizeIframe(this);"></iframe>');
  
  include_once("./include/bottom_footer.php");

?>

