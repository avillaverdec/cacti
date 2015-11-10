<?php
/*******************************************************************************

Copyright:    Copyright 2009-2012 by Urban-Software.de / Thomas Urban
 *******************************************************************************/

$dir = dirname( __FILE__ );
$mainDir = preg_replace( "@plugins.nmidSmokeping@", "", $dir );
require_once( $mainDir . '/lib/tree.php' );


function plugin_nmidSmokeping_install()
{
	api_plugin_register_hook( 'nmidSmokeping', 'config_settings', 'nmidSmokeping_config_settings', 'setup.php' );
	api_plugin_register_hook( 'nmidSmokeping', 'console_after', 'nmidSmokeping_console_after', 'setup.php' );
	api_plugin_register_hook( 'nmidSmokeping', 'tree_after', 'nmidSmokeping_tree_after', 'setup.php' );
	api_plugin_register_hook( 'nmidSmokeping', 'nmid_plugin_value', 'nmidSmokeping_value', 'setup.php' );
	api_plugin_register_hook( 'nmidSmokeping', 'nmid_plugin_header', 'nmidSmokeping_header', 'setup.php' );
	api_plugin_register_hook( 'nmidSmokeping', 'nmid_plugin_save', 'nmidSmokeping_save', 'setup.php' );
	api_plugin_register_hook( 'nmidSmokeping', 'nmid_plugin_configCreate', 'nmidSmokeping_configCreate', 'setup.php' );
	api_plugin_register_hook( 'nmidSmokeping', 'device_action_array', 'plugin_nmidSmokeping_device_action_array', 'setup.php' );
	api_plugin_register_hook( 'nmidSmokeping', 'device_action_prepare', 'plugin_nmidSmokeping_device_action_prepare', 'setup.php' );
	api_plugin_register_hook( 'nmidSmokeping', 'device_action_execute', 'plugin_nmidSmokeping_device_action_execute', 'setup.php' );
	api_plugin_register_realm( 'nmidSmokeping', 'getSmokePingImage.php', 'NMID - View Smokeping Charts', 1200 );
	nmidSmokeping_setup_table_new();
}

function plugin_nmidSmokeping_device_action_array( $action )
{
	if ( read_config_option( "nmid_spserver1" ) ) {
		$action[ 'plugin_nmidSmokeping_device_sp1' ] = 'Add to Smokeping (' . read_config_option( "nmid_spserver1" ) . ')';
	}
	if ( read_config_option( "nmid_spserver2" ) ) {
		$action[ 'plugin_nmidSmokeping_device_sp2' ] = 'Add to Smokeping (' . read_config_option( "nmid_spserver2" ) . ')';
	}
	if ( read_config_option( "nmid_spserver3" ) ) {
		$action[ 'plugin_nmidSmokeping_device_sp3' ] = 'Add to Smokeping (' . read_config_option( "nmid_spserver3" ) . ')';
	}
	if ( read_config_option( "nmid_spserver4" ) ) {
		$action[ 'plugin_nmidSmokeping_device_sp4' ] = 'Add to Smokeping (' . read_config_option( "nmid_spserver4" ) . ')';
	}
	$action[ 'plugin_nmidSmokeping_device_remove' ]   = 'Remove device(s) from Smokeping config';
	$action[ 'plugin_nmidSmokeping_config_generate' ] = 'Re-Generate Smokeping config';
	return $action;
}

function plugin_nmidSmokeping_device_action_prepare( $save )
{
	# globals used
	global $config, $colors;
	if ( preg_match( '/plugin_nmidSmokeping_device_sp(\d)/', $save[ "drp_action" ], $matches ) ) { /* nmidSmokeping Server x */
		/* find out which (if any) hosts have been checked, so we can tell the user */
		if ( isset( $save[ "host_array" ] ) ) {
			/* list affected hosts */
			print "<tr>";
			print "<td class='textArea' bgcolor='#" . $colors[ "form_alternate1" ] . "'>" .
				"<p>Are you sure you want to add the following hosts to your Smokeping config ?</p>" .
				"<p><ul>" . $save[ "host_list" ] . "</ul></p>" .
				"</td>";
			print "</tr>";
		}
	}
	if ( preg_match( '/plugin_nmidSmokeping_device_remove/', $save[ "drp_action" ] ) ) {
		if ( isset( $save[ "host_array" ] ) ) {
			/* list affected hosts */
			print "<tr>";
			print "<td class='textArea' bgcolor='#" . $colors[ "form_alternate1" ] . "'>" .
				"<p>Are you sure you want to remove the following hosts from your Smokeping config ?</p>" .
				"<p><ul>" . $save[ "host_list" ] . "</ul></p>" .
				"</td>";
			print "</tr>";
		}
	}
	return $save; # required for next hook in chain
}

function plugin_nmidSmokeping_device_action_execute( $action )
{
	global $config;

	# it's our turn
	if ( preg_match( '/plugin_nmidSmokeping_device_sp(\d)/', $action, $matches ) ) { /* nmidSmokeping Server x */
		if ( isset( $_POST[ "selected_items" ] ) ) {
			$selected_items = unserialize( stripslashes( $_POST[ "selected_items" ] ) );
			for ( $i = 0; ( $i < count( $selected_items ) ); $i++ ) {
				/* ================= input validation ================= */
				input_validate_input_number( $selected_items[ $i ] );
				/* ==================================================== */

				$data              = array();
				$data[ "host_id" ] = $selected_items[ $i ];

				$current_nwmgmt_settings = db_fetch_cell( "select nwmgmt_settings from host where id=" . $data[ "host_id" ] );
				if ( preg_match( "/^s1/", $current_nwmgmt_settings ) == 0 ) {
					/* Smokeping not set, yet */
					$current_nwmgmt_settings = preg_replace( "/^s\d/", "s1", $current_nwmgmt_settings );
					db_execute( "UPDATE host SET nwmgmt_settings = \"$current_nwmgmt_settings\" WHERE id=" . $data[ "host_id" ] );
					db_execute( "UPDATE host SET nwmgmt_smokeping_server = \"" . read_config_option( "nmid_spserver" . $matches[ 1 ] ) . "\" WHERE id=" . $data[ "host_id" ] );
				}
			}
			nmidSmokeping_configCreate();
		}
	}
	if ( preg_match( '/plugin_nmidSmokeping_device_remove/', $action ) ) { /* nmidSmokeping Server x */
		if ( isset( $_POST[ "selected_items" ] ) ) {
			$selected_items = unserialize( stripslashes( $_POST[ "selected_items" ] ) );
			for ( $i = 0; ( $i < count( $selected_items ) ); $i++ ) {
				/* ================= input validation ================= */
				input_validate_input_number( $selected_items[ $i ] );
				/* ==================================================== */

				$data              = array();
				$data[ "host_id" ] = $selected_items[ $i ];

				$current_nwmgmt_settings = db_fetch_cell( "select nwmgmt_settings from host where id=" . $data[ "host_id" ] );
				if ( preg_match( "/^s0/", $current_nwmgmt_settings ) == 0 ) {
					/* Remove Smokeping settings */
					$current_nwmgmt_settings = preg_replace( "/^s\d/", "s0", $current_nwmgmt_settings );
					db_execute( "UPDATE host SET nwmgmt_settings = \"$current_nwmgmt_settings\" WHERE id=" . $data[ "host_id" ] );
					db_execute( "UPDATE host SET nwmgmt_smokeping_server=\"\" WHERE id=" . $data[ "host_id" ] );
				}
			}
			nmidSmokeping_configCreate();
		}
	}
	if ( preg_match( '/plugin_nmidSmokeping_config_generate/', $action ) ) { /* nmidSmokeping Server x */
		nmidSmokeping_configCreate();
	}
	return $action;
}

function plugin_nmidSmokeping_uninstall()
{
	// Do any extra Uninstall stuff here
}

function plugin_nmidSmokeping_check_config()
{
	// Here we will check to ensure everything is configured
	nmidSmokeping_check_upgrade();
	return TRUE;
}

function plugin_nmidSmokeping_upgrade()
{
	// Here we will upgrade to the newest version
	nmidSmokeping_check_upgrade();
	return FALSE;
}

function plugin_nmidSmokeping_version()
{
	return nmidSmokeping_version();
}

function nmidSmokeping_check_upgrade()
{
	// We will only run this on pages which really need that data ...
	$files = array( 'plugins.php' );
	if ( isset( $_SERVER[ 'PHP_SELF' ] ) && !in_array( basename( $_SERVER[ 'PHP_SELF' ] ), $files ) ) {
		return;
	}

	$current = nmidSmokeping_version();
	$current = $current[ 'version' ];
	$old     = db_fetch_cell( "SELECT version FROM plugin_config WHERE directory='nmidSmokeping'" );
	if ( $current != $old ) {
		nmidSmokeping_setup_table( $old );
	}
}


function nmidSmokeping_check_dependencies()
{
	global $plugins, $config;
	return TRUE;
}


function nmidSmokeping_setup_table_new()
{
	global $config, $database_default;
	include_once( $config[ "library_path" ] . "/database.php" );

	$data              = array();
	$data[ 'name' ]    = 'nwmgmt_smokeping_path';
	$data[ 'type' ]    = 'varchar(1024)';
	$data[ 'NULL' ]    = FALSE;
	$data[ 'default' ] = '';
	api_plugin_db_add_column( 'nmidSmokeping', 'host', $data );

	$data              = array();
	$data[ 'name' ]    = 'nwmgmt_smokeping_server';
	$data[ 'type' ]    = 'varchar(1024)';
	$data[ 'NULL' ]    = FALSE;
	$data[ 'default' ] = '';
	api_plugin_db_add_column( 'nmidSmokeping', 'host', $data );

	$data              = array();
	$data[ 'name' ]    = 'nwmgmt_smokeping_probe';
	$data[ 'type' ]    = 'varchar(255)';
	$data[ 'NULL' ]    = FALSE;
	$data[ 'default' ] = '';
	api_plugin_db_add_column( 'nmidSmokeping', 'host', $data );

	if ( nmidSmokeping_readPluginStatus( 'nmid' ) ) {
		// fine, no need to add that column again.
	}
	else {
		// nmid is not isntalled, so we need that column added
		$data              = array();
		$data[ 'name' ]    = 'nwmgmt_settings';
		$data[ 'type' ]    = 'varchar(12)';
		$data[ 'NULL' ]    = FALSE;
		$data[ 'default' ] = 's0000000000';
		api_plugin_db_add_column( 'nmid', 'host', $data );
	}
}

function nmidSmokeping_configCreate()
{
	/* Build the Smokeping config */
	if ( is_writeable( read_config_option( "nmid_spdir" ) ) ) {
		if ( read_config_option( "nmid_spserver1" ) ) {
			$array       = nmidSmokeping_create_dhtml_tree( read_config_option( "nmid_spserver1" ) );
			$ourFileName = read_config_option( "nmid_spdir" ) . "smokeping_nmid_spserver1_config.txt";
			if ( $fh = fopen( $ourFileName, 'w' ) ) {
				foreach ( $array as $key => $value ) {
					fwrite( $fh, $value );
				}
				fclose( $fh );
			}
			else {
				die( "I'm sorry, the file " . $ourFileName . " is not writeable/createable." );
			}
		}
		if ( read_config_option( "nmid_spserver2" ) ) {
			$array       = nmidSmokeping_create_dhtml_tree( read_config_option( "nmid_spserver2" ) );
			$ourFileName = read_config_option( "nmid_spdir" ) . "smokeping_nmid_spserver2_config.txt";
			if ( $fh = fopen( $ourFileName, 'w' ) ) {
				foreach ( $array as $key => $value ) {
					fwrite( $fh, $value );
				}
				fclose( $fh );
			}
			else {
				die( "I'm sorry, the file " . $ourFileName . " is not writeable/createable." );
			}
		}
		if ( read_config_option( "nmid_spserver3" ) ) {
			$array       = nmidSmokeping_create_dhtml_tree( read_config_option( "nmid_spserver3" ) );
			$ourFileName = read_config_option( "nmid_spdir" ) . "smokeping_nmid_spserver3_config.txt";
			if ( $fh = fopen( $ourFileName, 'w' ) ) {
				foreach ( $array as $key => $value ) {
					fwrite( $fh, $value );
				}
				fclose( $fh );
			}
			else {
				die( "I'm sorry, the file " . $ourFileName . " is not writeable/createable." );
			}
		}
		if ( read_config_option( "nmid_spserver4" ) ) {
			$array       = nmidSmokeping_create_dhtml_tree( read_config_option( "nmid_spserver4" ) );
			$ourFileName = read_config_option( "nmid_spdir" ) . "smokeping_nmid_spserver4_config.txt";
			if ( $fh = fopen( $ourFileName, 'w' ) ) {
				foreach ( $array as $key => $value ) {
					fwrite( $fh, $value );
				}
				fclose( $fh );
			}
			else {
				die( "I'm sorry, the file " . $ourFileName . " is not writeable/createable." );
			}
		}
	}
	else {
		die( "I'm sorry. The directory " . read_config_option( "nmid_spdir" ) . " is not writeable." );
	}
	return;
}

function nmidSmokeping_tree_after ( $param )
{
	global $config, $database_default;
	preg_match( "/^(.+),(\d+)$/", $param, $hit );
	include_once( $config[ "library_path" ] . "/adodb/adodb.inc.php" );
	include_once( $config[ "library_path" ] . "/database.php" );
	if ( api_user_realm_auth( 'getSmokePingImage.php' ) ) {
        if ( nmidSmokeping_readPluginStatus( 'nmidWeb2' ) ) {
            print "<div class='sidebarBox portlet' id='item-sp01'>\n";
            print "<div class='portlet-header'>Graph Template:</strong> Availability Chart</div>\n";
            print "<div class='guideListing portlet-content'><center>\n";
        }
        else {
            ?>
            <tr bgcolor='#6d88ad'>
            <tr bgcolor='#a9b7cb'>
                <td colspan='3' class='textHeaderDark'>
                    <strong>Graph Template:</strong> SmokePing (external)
                </td>
            </tr>
            <tr align='center' style='background-color: #f9f9f9;'>
            <td align='center'>
            <?php
        }

        $tree_id = $_REQUEST[ 'tree_id' ];
        $host_leaf_id = $_REQUEST[ 'leaf_id' ];

        $host_list = db_fetch_assoc('select * from graph_tree_items where order_key like ( select Concat(replace(order_key,"000",""),"%") from graph_tree_items where id='.$host_leaf_id.' ) and host_id > 0');
        $column_count = 0;
        foreach ($host_list as $host_item ) {
            $host_id = $host_item['host_id'];
            $host_ip = db_fetch_cell( "select hostname from host where id=" . $host_id );
            $current_nwmgmt_settings = db_fetch_cell( "select nwmgmt_settings from host where id=" . $host_id );
            $nwmgmt_smokeping_path = db_fetch_cell( "select nwmgmt_smokeping_path from host where id=" . $host_id );
            $nwmgmt_smokeping_server = db_fetch_cell( "select nwmgmt_smokeping_server from host where id=" . $host_id );
            $nmid_server = '';
            if ( $nwmgmt_smokeping_server == read_config_option( "nmid_spserver1" ) ) {
                $nmid_server = "nmid_spserver1";
            }
            elseif ( $nwmgmt_smokeping_server == read_config_option( "nmid_spserver2" ) ) {
                $nmid_server = "nmid_spserver2";
            }
            elseif ( $nwmgmt_smokeping_server == read_config_option( "nmid_spserver3" ) ) {
                $nmid_server = "nmid_spserver3";
            }
            elseif ( $nwmgmt_smokeping_server == read_config_option( "nmid_spserver4" ) ) {
                $nmid_server = "nmid_spserver4";
            }
            /* Retrieve Smokeping Config Settings */
            $nmid_sp_url = $nwmgmt_smokeping_server . read_config_option( "nmid_spurl" );
            $nmid_sp_userid = read_config_option( "nmid_spuser" );
            $nmid_sp_password = read_config_option( "nmid_sppwd" );

            if ($_SESSION["sess_graph_view_thumbnails"] == "on") {

                /* Display Smokeping Graph if configured for this device */
                if ( preg_match( "/^s1/", $current_nwmgmt_settings ) > 0 ) {
                    if ( $column_count < 1 ) {
                        ?>
                        <tr align='center' style='background-color: #f9f9f9;'>
                        <?php
                    }
                    $column_count++;

                    ?>
                    <td align='center'>
                    <table width='1' cellpadding='0'>
                        <tr>
                            <td align='center' width='<?php print ceil(100 / read_graph_config_option("num_columns"));?>%'>
                            <table align='center' cellpadding='0'>
                                <tr>
                                    <td align='center'>
                                        <div style="min-height: <?php echo (1.6 * read_graph_config_option("default_height")) . "px"?>;">
                                        <?php
                                            print "<a target='_blank' href='" . $nmid_sp_url . "?target=" . $nwmgmt_smokeping_path . "'><img src='" . $config[ 'url_path' ] . "plugins/nmidSmokeping/getSmokePingImage.php?start=" . get_current_graph_start() . "&end=" . get_current_graph_end() . "&target=" . $nwmgmt_smokeping_path . "&server=" . $nmid_server . "&graphtype=detail&height=".read_graph_config_option("default_height")."&width=".read_graph_config_option("default_width") ."&hide=1' border='0'></a>\n";
                                        ?>
                                        </div>
                                    </td>
                                    <td valign='top' style='align: left; padding: 3px;'>
                                        <a href='<?php print htmlspecialchars($config['url_path'] . "graph.php?action=zoom&local_graph_id=" . $graph["local_graph_id"] . "&rra_id=0&" . $extra_url_args);?>'><img src='<?php print $config['url_path'];?>images/graph_zoom.gif' border='0' alt='Zoom Graph' title='Zoom Graph' style='padding: 3px;'></a><br>
                                        <a href='<?php print htmlspecialchars($config['url_path'] . "graph_xport.php?local_graph_id=" . $graph["local_graph_id"] . "&rra_id=0&" . $extra_url_args);?>'><img src='<?php print $config['url_path'];?>images/graph_query.png' border='0' alt='CSV Export' title='CSV Export' style='padding: 3px;'></a><br>
                                        <a href='<?php print htmlspecialchars($config['url_path'] . "graph.php?action=properties&local_graph_id=" . $graph["local_graph_id"] . "&rra_id=0&" . $extra_url_args);?>'><img src='<?php print $config['url_path'];?>images/graph_properties.gif' border='0' alt='Graph Source/Properties' title='Graph Source/Properties' style='padding: 3px;'></a><br>
                                        <?php api_plugin_hook('graph_buttons_thumbnails', array('hook' => 'graphs_thumbnails', 'local_graph_id' => $graph['local_graph_id'], 'rra' =>  0, 'view_type' => '')); ?>
                                        <a href='#page_top'><img src='<?php print $config['url_path'] . "images/graph_page_top.gif";?>' border='0' alt='Page Top' title='Page Top' style='padding: 3px;'></a><br>
                                    </td>
                                </tr>
                            </table>
                            </td>
                        </tr>
                    </table>
                    </td>
                    <?php
                    if ( $column_count > (read_graph_config_option('num_columns')-1) ) {
                        $column_count = 0;
                        ?>
                        </tr>
                        <?php
                    }
                }
            }
            else {
                /* Display Smokeping Graph if configured for this device */
                if ( preg_match( "/^s1/", $current_nwmgmt_settings ) > 0 )
                {


                    print "	<table width='1' cellpadding='0'>\n";
                    print "		<tr>\n";
                    print "			<td valign='top' style='padding: 3px;' class='noprint'>\n";
                    if ( read_config_option( "nmid_spgraphtype" ) == "detail" ) {
                        print "				<a target='_blank' href='" . $nmid_sp_url . "?target=" . $nwmgmt_smokeping_path . "'><img src='" . $config[ 'url_path' ] . "plugins/nmidSmokeping/getSmokePingImage.php?start=" . get_current_graph_start() . "&end=" . get_current_graph_end() . "&target=" . $nwmgmt_smokeping_path . "&server=" . $nmid_server . "&graphtype=detail' border='0'></a>\n";
                    }
                    else {
                        print "				<a target='_blank' href='" . $nmid_sp_url . "?target=" . $nwmgmt_smokeping_path . "'><img src='" . $config[ 'url_path' ] . "plugins/nmidSmokeping/getSmokePingImage.php?start=" . get_current_graph_start() . "&end=" . get_current_graph_end() . "&target=" . $nwmgmt_smokeping_path . "&server=" . $nmid_server . "&graphtype=overview' border='0'></a>\n";
                    }

                    print "			</td>\n";
                    print "			<td valign='top' style='padding: 3px;' class='noprint'>";
                    if ( read_config_option( "nmid_spshowlink" ) ) {
                        print "             <a target='_blank' href='" . $nmid_sp_url . "?target=" . $nwmgmt_smokeping_path . "'><img src='" . $config[ 'url_path' ] . "images/graph_zoom.gif' border='0' alt='Go to Smokeping' title='Go to Smokeping' style='padding: 3px;'></a>";
                    }
                    else {
                        print "             <img src='" . $config[ 'url_path' ] . "images/graph_zoom.gif' border='0' alt='placeholder' title='placeholder' style='padding: 3px;'>";
                    }

                    if ( nmidSmokeping_readPluginStatus( 'nmidCreatePDF' ) ) {
                        print " 	     <input onClick=\"setData('sp_" . $host_id . "');\" type=checkbox id='sp_" . $host_id . "' name='sp_" . $host_id . "' value='" . $host_id . "'><br>";
                    }

                    print "         </td>";
                    print "		</tr>\n";
                    print "</table>\n";


                }
            }
        }
        if ( $column_count > 0 && $column_count < read_graph_config_option('num_columns') ) {
            print "</tr>";
        }
        if ($_SESSION["sess_graph_view_thumbnails"] <> "on") {
            if ( nmidSmokeping_readPluginStatus( 'nmidWeb2' ) ) {
                print "</center></div></div>";
            }
            else {
                print "</td></tr></tr>";
            }
        }
    }
	return $param;
}

function nmidSmokeping_console_after()
{
	global $config, $plugins;
	nmidSmokeping_setup_table();
}

function nmidSmokeping_config_settings()
{
	global $tabs, $settings;
	$tabs[ "nmid" ] = "NMID";

	$temp = array(
		"nmid_spheader"       => array(
			"friendly_name" => "NMID - Smokeping - General",
			"method"        => "spacer",
		),
		"nmid_spurl"          => array(
			"friendly_name" => "Smokeping URL",
			"description"   => "This is the relative URL used to connect to Smokeping .  (ex: /cgi-bin/smokeping.cgi).",
			"method"        => "textbox",
			"max_length"    => 255,
		),
		"nmid_spdir"          => array(
			"friendly_name" => "Smokeping Config Creation Dir",
			"description"   => "This is the local directory to store the smokeping target config to .  (ex: /tmp/).",
			"method"        => "textbox",
			"max_length"    => 255,
		),
		"nmid_spgraphtype"    => array(
			"friendly_name" => "Smokeping Graph Type",
			"description"   => "Choose which Smokeping graph type to display.",
			"method"        => "drop_array",
			"default"       => "detail",
			"array"         => array(
				"detail"   => "Detail Graph",
				"overview" => "Overview Graph"
			),
		),
		"nmid_spshowlink"     => array(
			"friendly_name" => "Show Smokeping link next to graph",
			"description"   => "Show Smokeping link next to graph.",
			"method"        => "checkbox",
			"max_length"    => "255"
		),
		"nmid_spuser"         => array(
			"friendly_name" => "Smokeping UserID",
			"description"   => "UserID used to connect to Smokeping (htaccess).",
			"method"        => "textbox",
			"max_length"    => 20,
		),
		"nmid_sppwd"          => array(
			"friendly_name" => "Smokeping Password",
			"description"   => "Password used to connect to Smokeping (htaccess).",
			"method"        => "textbox_password",
			"max_length"    => 20,
		),
		"nmid_spserverheader" => array(
			"friendly_name" => "NMID - Smokeping Server",
			"method"        => "spacer",
		),
		"nmid_spserver1"      => array(
			"friendly_name" => "Smokeping URL - Server 1",
			"description"   => "This is the server URL used to connect to Smokeping .  (ex: http://1.2.3.4).",
			"method"        => "textbox",
			"max_length"    => 255,
		),
		"nmid_spserver2"      => array(
			"friendly_name" => "Smokeping URL - Server 2",
			"description"   => "This is the server URL used to connect to Smokeping .  (ex: http://1.2.3.4).",
			"method"        => "textbox",
			"max_length"    => 255,
		),
		"nmid_spserver3"      => array(
			"friendly_name" => "Smokeping URL - Server 3",
			"description"   => "This is the full URL used to connect to Smokeping .  (ex: http://1.2.3.4).",
			"method"        => "textbox",
			"max_length"    => 255,
		),
		"nmid_spserver4"      => array(
			"friendly_name" => "Smokeping URL - Server 4",
			"description"   => "This is the full URL used to connect to Smokeping .  (ex: http://1.2.3.4).",
			"method"        => "textbox",
			"max_length"    => 255,
		)
	);

	if ( isset( $settings[ "nmid" ] ) ) {
		$settings[ "nmid" ] = array_merge( $settings[ "nmid" ], $temp );
	}
	else {
		$settings[ "nmid" ] = $temp;
	}
}

function nmidSmokeping_version()
{
	return array( 'name'     => 'nmidSmokeping',
	              'version'  => '2.00',
	              'longname' => 'NMID SmokePing Plugin',
	              'author'   => 'Thomas Urban',
	              'homepage' => 'http://blog.network-outsourcing.de/support/nmid-plugins-support/',
	              'email'    => 'nmid@urban-software.de',
	              'url'      => 'http://urban-software.de/nmid/smokeping/versions.php'
	);
}

function nmidSmokeping_setup_table()
{
	$version_info = nmidSmokeping_version();
	db_execute( 'UPDATE plugin_config SET version = "' . $version_info[ 'version' ] . '" WHERE directory = "nmidSmokeping"' );

}

// Check Plugin status / support
function nmidSmokeping_readPluginStatus( $plugin )
{
	$query         = "select status from plugin_config where directory='" . $plugin . "'";
	$result        = mysql_query( $query );
	$plugin_status = mysql_fetch_assoc( $result );
	// Free the result set
	mysql_free_result( $result );
	return $plugin_status[ 'status' ];
}

function nmidSmokeping_create_dhtml_tree( $SmokePingServer )
{
	/* Record Start Time */
	list( $micro, $seconds ) = split( " ", microtime() );
	$start = $seconds + $micro;

	$dhtml_tree = array();

	$devices = array();

	$i = 0;

	$tree_list = get_graph_tree_array();

	if ( sizeof( $tree_list ) > 0 ) {
		foreach ( $tree_list as $tree ) {
			$i++;
			$heirarchy = db_fetch_assoc( "select
				graph_tree_items.id,
				graph_tree_items.title,
				graph_tree_items.order_key,
				graph_tree_items.host_id,
				graph_tree_items.host_grouping_type,
				host.description as hostname,
                host.hostname as ipaddress
				from graph_tree_items
				left join host on (host.id=graph_tree_items.host_id)
				where graph_tree_items.graph_tree_id=" . $tree[ "id" ] . "
				and graph_tree_items.local_graph_id = 0
				order by graph_tree_items.order_key" );

			$treeName = preg_replace( "/\s/", "_", $tree[ "name" ] );
			$treeName = preg_replace( "@/@", "-", $treeName );
			$treeName = preg_replace( "@\)@", "-", $treeName );
			$treeName = preg_replace( "@\(@", "-", $treeName );
			$treeName = preg_replace( "@\.@", "", $treeName );
			$treeName = preg_replace( "@\&@", "and", $treeName );
			$treeName = preg_replace( "@\,@", "and", $treeName );
			$treeName = preg_replace( "@\'@", "", $treeName );
			$treeName = preg_replace( "@\+@", "", $treeName );
			$treeName = preg_replace( "@\|@", "-", $treeName );
			$treeName = preg_replace( "@\ä@", "ae", $treeName );
			$treeName = preg_replace( "@\ö@", "oe", $treeName );
			$treeName = preg_replace( "@\ü@", "ue", $treeName );
			$treeName = preg_replace( "@\Ä@", "Ae", $treeName );
			$treeName = preg_replace( "@\Ö@", "Oe", $treeName );
			$treeName = preg_replace( "@\Ü@", "Ue", $treeName );
			$treeName = preg_replace( "@\Ç@", "C", $treeName );
			$treeName = preg_replace( "@\²@", "2", $treeName );
			$treeName = preg_replace( "/\\\/", "-", $treeName );
			$treeName = preg_replace( "@\[@", "_", $treeName );
			$treeName = preg_replace( "@\]@", "_", $treeName );
			$treeName = preg_replace( "/::/", "__", $treeName );

			$dhtml_tree[ $i ] = "+ " . $treeName . "\n" .
				"menu = " . $tree[ "name" ] . "\n" .
				"title = " . $tree[ "name" ] . "\n" .
				"\n";
			$current_tier     = $treeName;
			$tierArr          = array();
			if ( sizeof( $heirarchy ) > 0 ) {
				foreach ( $heirarchy as $leaf ) {
					$i++;
					$tier = tree_tier( $leaf[ "order_key" ] );

					if ( $leaf[ "host_id" ] > 0 ) {
						$nmid_data = db_fetch_assoc( "select nwmgmt_smokeping_server,nwmgmt_settings from host where id=" . $leaf[ "host_id" ] );
						if ( preg_match( "@$SmokePingServer@", $nmid_data[ 0 ][ "nwmgmt_smokeping_server" ] ) > 0 ) {
							if ( preg_match( "/^s1/", $nmid_data[ 0 ][ "nwmgmt_settings" ] ) > 0 ) {
								$tierString = '+';
								for ( $tierCount = 0; $tierCount < $tier; $tierCount++ ) {
									$tierString .= '+';
								}
								$host_text = preg_replace( "/\s/", "_", $leaf[ "hostname" ] );
								$host_text = preg_replace( "@/@", "-", $host_text );
								$host_text = preg_replace( "@\)@", "-", $host_text );
								$host_text = preg_replace( "@\(@", "-", $host_text );
								$host_text = preg_replace( "@\.@", "", $host_text );
								$host_text = preg_replace( "@\&@", "and", $host_text );
								$host_text = preg_replace( "@\,@", "and", $host_text );
								$host_text = preg_replace( "@\'@", "", $host_text );
								$host_text = preg_replace( "@\+@", "", $host_text );
								$host_text = preg_replace( "@\|@", "-", $host_text );
								$host_text = preg_replace( "@\ä@", "ae", $host_text );
								$host_text = preg_replace( "@\ö@", "oe", $host_text );
								$host_text = preg_replace( "@\ü@", "ue", $host_text );
								$host_text = preg_replace( "@\Ä@", "Ae", $host_text );
								$host_text = preg_replace( "@\Ö@", "Oe", $host_text );
								$host_text = preg_replace( "@\Ü@", "Ue", $host_text );
								$host_text = preg_replace( "@\Ç@", "C", $host_text );
								$host_text = preg_replace( "@\²@", "2", $host_text );
								$host_text = preg_replace( "/\\\/", "-", $host_text );
								if ( isset ( $devices[ $host_text ] ) ) {
									// nothing
								}
								else {
									$devices[ $host_text ] = TRUE;
									$dhtml_tree[ $i ]      = $tierString . ' ' . $host_text . "\n" .
										"menu = " . $leaf[ "hostname" ] . "\n" .
										"title = Device " . $leaf[ "hostname" ] . "\n" .
										"host = " . $leaf[ "ipaddress" ] . "\n" .
										"\n";
									$tierArr[ $tier ]      = $host_text;
									$url                   = $current_tier;
									for ( $counter = 1; $counter < $tier + 1; $counter++ ) {
										$url .= '.' . $tierArr[ $counter ];
									}
									db_execute( "UPDATE host SET nwmgmt_smokeping_path = \"$url\" WHERE id=" . $leaf[ "host_id" ] );
								}
							}
						}
					}
					else {
						$tierString = '+';
						for ( $tierCount = 0; $tierCount < $tier; $tierCount++ ) {
							$tierString .= '+';
						}
						$title = $leaf[ "title" ];

						$menu_text = preg_replace( "/\s/", "_", $title );
						$menu_text = preg_replace( "@/@", "-", $menu_text );
						$menu_text = preg_replace( "@\)@", "-", $menu_text );
						$menu_text = preg_replace( "@\(@", "-", $menu_text );
						$menu_text = preg_replace( "@\.@", "", $menu_text );
						$menu_text = preg_replace( "@\&@", "and", $menu_text );
						$menu_text = preg_replace( "@\,@", "and", $menu_text );
						$menu_text = preg_replace( "@\'@", "", $menu_text );
						$menu_text = preg_replace( "@\+@", "", $menu_text );
						$menu_text = preg_replace( "@\|@", "-", $menu_text );
						$menu_text = preg_replace( "@\ä@", "ae", $menu_text );
						$menu_text = preg_replace( "@\ö@", "oe", $menu_text );
						$menu_text = preg_replace( "@\ü@", "ue", $menu_text );
						$menu_text = preg_replace( "@\Ä@", "Ae", $menu_text );
						$menu_text = preg_replace( "@\Ö@", "Oe", $menu_text );
						$menu_text = preg_replace( "@\Ü@", "Ue", $menu_text );
						$menu_text = preg_replace( "@\Ç@", "C", $menu_text );
						$menu_text = preg_replace( "@\²@", "2", $menu_text );

						$title            = preg_replace( "@\'@", "\"", $title );
						$menu_text        = preg_replace( "/\\\/", "-", $menu_text );
						$dhtml_tree[ $i ] = $tierString . " " . $menu_text . "\n" .
							"menu = " . $title . "\n" .
							"title = Location/Devices in " . $title . "\n" .
							"\n";
						$tierArr[ $tier ] = $menu_text;

					}
				}
			}
		}
	}

	return $dhtml_tree;
}

?>
