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

    Author ......... Authornfsen
    Contact ........ nfsen@nfsen.net
    Home Site ...... http://nfsen.net
    Program ........ nfsen "plugin nfsen"
    Purpose ........ Example for plugins 
           !!!! YOU have to:
                1/ create a directory with the name of your plugin where you will copy all your files
                2/ replace all "nfsen" with the name of your plugin
                3/ The realm is automatically created
                   But your can register your realm under "http://cactiusers.org/wiki/Homepage"
                4/ change/create your menu in nfsen_config_arrays ()
                5/ change your tab images. Use the *.psd example to create yours (with f.ex gimp)
*******************************************************************************/
define("nfsen", "0.1");

function plugin_init_nfsen() {
	global $plugin_hooks;

}

/* plugin install - provides a generic PIA 2.x installer routine to register all plugin hook functions.
*/
function plugin_nfsen_install() {
   api_plugin_register_hook('nfsen', 'top_header_tabs',       'nfsen_show_tab',             "setup.php");
   api_plugin_register_hook('nfsen', 'top_graph_header_tabs', 'nfsen_show_tab',             "setup.php");
   //api_plugin_register_hook('nfsen', 'config_arrays',         'nfsen_config_arrays',        "setup.php");
   //api_plugin_register_hook('nfsen', 'draw_navigation_text',  'nfsen_draw_navigation_text', "setup.php");
   api_plugin_register_hook('nfsen', 'config_form',           'nfsen_config_form',          "setup.php");
   api_plugin_register_hook('nfsen', 'config_settings',       'nfsen_config_settings',      "setup.php");
   //api_plugin_register_hook('nfsen', 'api_graph_save',        'nfsen_api_graph_save',       "setup.php");
   //api_plugin_register_hook('nfsen', 'body_style', 'nfsen_api_plugin_hook_function_body_style', "setup.php");
   /* uncomment if you need more hooks */
   //api_plugin_register_hook('nfsen', 'top_graph_refresh',     'nfsen_top_graph_refresh',    "setup.php");
//   api_plugin_register_hook('nfsen', 'device_action_array',   'nfsen_device_action_array',  "setup.php");
//   api_plugin_register_hook('nfsen', 'device_action_execute', 'nfsen_device_action_execute',"setup.php");
//   api_plugin_register_hook('nfsen', 'device_action_prepare', 'nfsen_device_action_prepare',"setup.php");
//   api_plugin_register_hook('nfsen', 'poller_output', 'nfsen_poller_output', 'poller_nfsen.php');
//   api_plugin_register_hook('nfsen', 'poller_bottom', 'nfsen_poller_bottom', 'poller_nfsen.php');

   // register the realm for each php file
   api_plugin_register_realm('nfsen', 'nfsen.php', 'View nfsen', 1);
   //other example api_plugin_register_realm('nfsen', 'nfsen.php,nfsen2.php', 'View nfsen', 1);

   nfsen_setup_table_new ();
}

/* plugin uninstall - a generic uninstall routine.  Right now it will do nothing as I
   If you don't want the tables removed from the system except let it empty. */
function plugin_nfsen_uninstall () {
	/* Do any extra Uninstall stuff here */
}

function plugin_nfsen_check_config () {
	/* Here we will check to ensure everything is configured */
	return true;
}

function plugin_nfsen_upgrade () {
	/* Here we will upgrade to the newest version */
	nfsen_check_upgrade();
	return false;
}

function plugin_nfsen_version () {
	return nfsen_version();
}

function nfsen_check_upgrade () {
	global $config;

	$files = array('index.php', 'plugins.php', 'nfsen.php');
	if (isset($_SERVER['PHP_SELF']) && !in_array(basename($_SERVER['PHP_SELF']), $files)) {
		return;
	}

	$current = plugin_nfsen_version();
	$current = $current['version'];
	$old     = db_fetch_row("SELECT * FROM plugin_config WHERE directory='nfsen'");
	if (sizeof($old) && $current != $old["version"]) {
		/* if the plugin is installed and/or active */
		if ($old["status"] == 1 || $old["status"] == 4) {
			/* re-register the hooks */
			plugin_nfsen_install();

			/* perform a database upgrade */
			nfsen_database_upgrade();
		}

		/* update the plugin information */
		$info = plugin_nfsen_version();
		$id   = db_fetch_cell("SELECT id FROM plugin_config WHERE directory='nfsen'");
		db_execute("UPDATE plugin_config
			SET name='" . $info["longname"] . "',
			author='"   . $info["author"]   . "',
			webpage='"  . $info["homepage"] . "',
			version='"  . $info["version"]  . "'
			WHERE id='$id'");
	}
}

function nfsen_database_upgrade () {
}

function nfsen_check_dependencies() {
	global $plugins, $config;

	return true;
}

function nfsen_setup_table_new () {
}

function nfsen_version () {
    return array( 'name'     => 'nfsen',
            'version'     => '0.1',
            'longname'    => 'plugin to insert nfsen',
            'author'    => 'Alberto',
            'homepage'    => '',
            'email'    => '',
            'url'        => 'http://cactiusers.org/cacti/versions.php'
            );
}

function nfsen_config_settings () {
	global $tabs, $settings, $page_refresh_interval, $graph_timespans;

	/* check for an upgrade */
	plugin_nfsen_check_config();

	if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) != 'settings.php')
		return;

	/* IF you want your own tab settings you have to create a new $tab and change "misc" */
	$tabs["misc"] = "Misc";
	// change the line above and uncomment the two following when you want your tabs
	// $tabs["nfsen"] = "nfsen";
	// $treeList = array_rekey(get_graph_tree_array(null, true), 'id', 'name');

  /* YOU can CREATE here your settings */
    $defaultallowguest="off";
	$temp = array(
		"nfsen_header" => array(
			"friendly_name" => "nfsen settings",
			"method" => "spacer",
			),
		"nfsen_allowed_guest_account" => array(
			"friendly_name" => "allow guest account",
			"description" => "if checked this plugin is allowed to the 'guest' account",
			"method" => "checkbox",
                        'default' => $defaultallowguest,
			),
		/*"nfsen_refresh" => array(
			"friendly_name" => "refresh time",
			"description" => "Time to refresh the page.",
			"method" => "textbox",
			"max_length" => 3,
			),
		"nfsen_settingsvariable2" => array(
			"friendly_name" => "your second setting variable",
			"description" => "This is your second setting variable.",
			"method" => "textbox",
			"max_length" => 5,
			),
		"nfsen_settingsvariable3" => array(
			"friendly_name" => "your third setting variable",
			"description" => "Check your third setting variable.",
			"method" => "checkbox",
			), */
	);
	if (isset($settings["misc"]))
		$settings["misc"] = array_merge($settings["misc"], $temp);
	else
		$settings["misc"]=$temp;
	// change the 4 lines above and uncomment the 4 lines following when you want your tabs
	//if (isset($settings["nfsen"])) {
	//	$settings["nfsen"] = array_merge($settings["nfsen"], $temp);
	//}else {
	//	$settings["nfsen"]=$temp;
	//}
}

function nfsen_device_action_execute ($action) {
        global $config;

        if ($action != 'nfsen_your_action')
                return;
        /* Look at monitor as example */
        return;
}

function nfsen_device_action_prepare($save) {
        global $colors, $host_list;
        /* Look at monitor as example */
}

function nfsen_device_action_array($device_action_array) {
        /* here you can add actions in the devices view */
        /* Look at monitor as example */
#        $device_action_array['nfsen_your_action'] = 'nfsen action';
        return $device_action_array;
}

function nfsen_top_graph_refresh ($refresh) {
        /* change the refresh time of your view. Here as example nfsen.php */
        /* nfsen_refresh is supposed to be one parameter of the setting */
        if (basename($_SERVER['PHP_SELF']) != 'nfsen.php')
                return $refresh;
        $r = read_config_option('nfsen_refresh');
        if ($r == '' or $r < 1)
                return $refresh;
        return $r;
}

function nfsen_show_tab () {
  global $config, $user_auth_realms, $user_auth_realm_filenames;
  $realm_id2 = 0;

  /*---- Begin of the minimal functions needed ----*/
  if (isset($user_auth_realm_filenames{basename('nfsen.php')})) {
		$realm_id2 = $user_auth_realm_filenames{basename('nfsen.php')};
  }
  if ((db_fetch_assoc("select user_auth_realm.realm_id
	from user_auth_realm where user_auth_realm.user_id='" . $_SESSION["sess_user_id"] . "'
	and user_auth_realm.realm_id='$realm_id2'")) || (empty($realm_id2))) {

	if (substr_count($_SERVER["REQUEST_URI"], "nfsen.php")) {
		print '<a href="' . $config['url_path'] . 'plugins/nfsen/nfsen.php"><img src="' . $config['url_path'] . 'plugins/nfsen/images/tab_nfsen-green.png" alt="nfsen" align="absmiddle" border="0"></a>';
	}else{
		print '<a href="' . $config['url_path'] . 'plugins/nfsen/nfsen.php"><img src="' . $config['url_path'] . 'plugins/nfsen/images/tab_nfsen.png" alt="nfsen" align="absmiddle" border="0"></a>';
	}

  }
  /*---- End of the minimal functions needed ----*/
  
  /* now you can put your needs */
}

function nfsen_config_arrays () {
   global $menu, $messages, $nfsen_menu;

   $menu['Utilities']['plugins/nfsen/nfsen.php'] = 'Menu nfsen';

}

function nfsen_draw_navigation_text ($nav) {
   /* insert all your PHP functions that are accessible */
   $nav["nfsen.php:"] = array("title" => "nfsen title", "mapping" => "index.php:", "url" => "nfsen.php", "level" => "1");
  // return $nav;
}

function nfsen_api_graph_save ($save) {
	/* what happend after a click on the save button */
}

function nfsen_api_plugin_hook_function_body_style () {
#print ">\n";
#print "<div>print SALUT</div>";
#print "print SALUT";
#print "<br>\n<!-- comment to  close properly body cacti --";
/*
print ">\n";
#print "					<td>";
if ($banneractivation) bannerMessage(false,$show_console_tab);
#print "					</td>";
print "<br>\n<!-- comment to  close properly body cacti --";
*/
}

?>
