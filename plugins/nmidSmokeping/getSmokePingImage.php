<?php

$dir = dirname(__FILE__);
$mainDir = preg_replace("@plugins.nmidSmokeping@","",$dir);
chdir ($mainDir);
include_once("./include/auth.php");

$start = $_REQUEST['start'];
$end = $_REQUEST['end'];
$target = $_REQUEST['target'];
$graphtype = $_REQUEST['graphtype'];
$server = $_REQUEST['server'];
$tmp_dir = my_sys_get_temp_dir();
$debug = false;
if ( isset( $_REQUEST['debug'] ) ) {
    $debug = true;
}

if (version_compare(PHP_VERSION, '5.2.1') >= 0) {
	$tmp_dir = sys_get_temp_dir();
}

$url = read_config_option( $server ) . read_config_option( 'nmid_spurl' );
$userId = read_config_option( 'nmid_spuser' );
$password = read_config_option('nmid_sppwd');

$sp_url = '';
$real_target = '';

$encoded = '';
        
if ($graphtype == "detail") {
    $encoded .= urlencode('displaymode').'=n&';
    $encoded .= urlencode('start').'='.urlencode($start).'&';
    $encoded .= urlencode('end').'='.urlencode($end).'&';
    $encoded .= urlencode('target').'='.urlencode($target);
    $sp_url = $url; //.'?displaymode=n;start='.$start.';end='.$end.';target='.$target;
} else {
    $real_target = $target;
    $target_data = preg_split('/\./',$real_target);
    $real_target = '';
    $pathSize = sizeof($target_data);
    foreach ( $target_data as $data) {
        $real_target .= $data.'.';
    }
    $real_target = preg_replace('/\.$/','',$real_target);
    $encoded .= urlencode('target').'='.urlencode($real_target);
    $sp_url = $url; //.'?target='.$real_target;
}
$mainServer = read_config_option( $server );

//if ( preg_match("/(http:.*)\/cgi-bin\/smokeping\.cgi.*/i",$url,$matches) ) {
//    $mainServer = $matches[1];
//} elseif ( preg_match("/(http:.*)\/smokeping\.cgi.*/i",$url,$matches) ) {
//    $mainServer = $matches[1];
//} elseif ( preg_match("/(http:.*)\/cgi-bin\/smokeping\.fcgi.*/i",$url,$matches) ) {
//    $mainServer = $matches[1];
//} elseif ( preg_match("/(http:.*)\/smokeping\.fcgi.*/i",$url,$matches) ) {
//    $mainServer = $matches[1];
//}

$mainServer = $mainServer . '/';
$filePart = '';
$responseStr = getUrl( $sp_url, $userId, $password, $encoded );

if ( $debug ) {
    echo "<h3>Main Server</h3>".$mainServer."\n";
    print "<hr>"."\n";
    print "<h3>SP URL</h3>".$sp_url."\n";
    print "<hr>"."\n";
    print "<h3>Response Str</h3>".$responseStr."\n";
    print "<hr>"."\n";
}

$filePart = '';
if ($graphtype == "detail") {
    $filePart = $end.'_'.$start.'.png';
} else {
    $targetFile = preg_replace('/\./','/',$target);
    $filePart = $targetFile.'_mini.png';
}

if ( $debug ) {
    print "<h3>File Part</h3>".$filePart."<hr>";
}

$imageUrl = '';
if ( preg_match("/src=\"\.\.([^\s]*$filePart)\"/i",$responseStr, $matches ) ) {
    $imageUrl = $matches[1];
} elseif ( preg_match("/src=\"([^\s]*$filePart)\"/i",$responseStr, $matches) ) {
    $imageUrl = $matches[1];
} else {
    $imageUrl = $mainServer.'cache/'.$filePart;
}

if ($graphtype == "overview") {
       $imageUrl = $mainServer.'cache/'.$filePart;
}

if ( preg_match('/^http/', $imageUrl ) == 0 ) {
    $imageUrl = $mainServer.$imageUrl;
}

$output = getUrl($imageUrl, $userId, $password );

if ( $debug ) {
    print "<h3>Image URL</h3>".$imageUrl."<hr>";
    print "<h3>Output</h3>".$output."<hr>";
}

header("Content-Type: image/jpeg");
echo $output;

function my_sys_get_temp_dir() {
      if( $temp=getenv('TMP') )        return $temp;
      if( $temp=getenv('TEMP') )        return $temp;
      if( $temp=getenv('TMPDIR') )    return $temp;
      $temp=tempnam(__FILE__,'');
      if (file_exists($temp)) {
          unlink($temp);
          return dirname($temp);
      }
      return null;
  }

function getUrl( $url, $userid='', $password='', $encoded = '' ) {
    global $tmp_dir;
    $ch = curl_init($url);
    // chop off last ampersand
    curl_setopt($ch, CURLOPT_POSTFIELDS,  $encoded);
    curl_setopt($ch, CURLOPT_HEADER, 0);
	if ($userid != '' && $password != '')
    	curl_setopt($ch, CURLOPT_USERPWD, $userid . ":" . $password);                                                
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);        
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST , false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $tmp_dir.'cookie.txt');
    curl_setopt($ch, CURLOPT_COOKIEFILE, $tmp_dir.'cookie.txt');
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}


?>
