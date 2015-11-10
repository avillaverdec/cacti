<?php

function plugin_init_links() {
	global $plugin_hooks;
	$plugin_hooks['top_header_tabs']['links'] = 'links_show_tab';
        $plugin_hooks['top_graph_header_tabs']['links'] = 'links_show_tab';

	$plugin_hooks['config_arrays']['links'] = 'links_config_arrays';
        $plugin_hooks['draw_navigation_text']['links'] = 'links_draw_navigation_text';

}

function links_config_arrays () {
        global $user_auth_realms, $user_auth_realm_filenames, $menu;
        $user_auth_realms[41]='View Links';
        $user_auth_realm_filenames['links.php'] = 41;
}

function links_show_tab () {
	global $config, $user_auth_realms, $user_auth_realm_filenames;
	$realm_id2 = 0;

        if (isset($user_auth_realm_filenames['links.php'])) {
                $realm_id2 = $user_auth_realm_filenames['links.php'];
        }
        if ((db_fetch_assoc("select user_auth_realm.realm_id from user_auth_realm where user_auth_realm.user_id='" . $_SESSION["sess_user_id"] . "' and user_auth_realm.realm_id='$realm_id2'")) || (empty($realm_id2))) {

		print '<a href="' . $config['url_path'] . 'plugins/links/links.php"><img src="' . $config['url_path'] . 'plugins/links/images/tab_links.png" alt="Links" align="absmiddle" border="0"></a>';

        }





}

function links_draw_navigation_text ($nav) {
   $nav["links.php:"] = array("title" => "Links", "mapping" => "index.php:", "url" => "links.php", "level" => "1");
   return $nav;
}

function links_version () {
        return array( 'name'    => 'links',
                        'version'       => '0.3',
                        'longname'      => 'Simple Links page',
                        'author'        => 'Howard Jones',
                        'homepage'      => 'http://wotsit.thingy.com/haj/cacti/links-plugin.html',
                        'email' => 'howie@thingy.com',
                        'url'           => 'http://wotsit.thingy.com/haj/cacti/versions.php'
                        );
}

?>
