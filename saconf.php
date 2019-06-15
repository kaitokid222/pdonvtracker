<?php

/*
// +--------------------------------------------------------------------------+
// | Project:    pdonvtracker - NetVision BitTorrent Tracker 2017             |
// +--------------------------------------------------------------------------+
// | This file is part of pdonvtracker. NVTracker is based on BTSource,       |
// | originally by RedBeard of TorrentBits, extensively modified by           |
// | Gartenzwerg.                                                             |
// +--------------------------------------------------------------------------+
// | Obige Zeilen dürfen nicht entfernt werden!    Do not remove above lines! |
// +--------------------------------------------------------------------------+
*/
require "include/bittorrent.php";

// http://www.netvision-technik.de/forum/showpost.php?p=84056&postcount=5
// stifler
function execInBackground($cmd){
	if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'){
		pclose(popen("start /B ".$cmd, "r"));
	}else{
		system($cmd." > /dev/null &");
	}
}

if(isset($_GET['socket'], $_GET['operator']) && $_GET['socket'] == 1 && $_GET['operator'] == "admin"){
	$olw = ($GLOBALS["ONLY_LEECHERS_WAIT"] === true) ? "yes" : "no";
	$nwtos = ($GLOBALS["NOWAITTIME_ONLYSEEDS"] === true) ? "yes" : "no";
	$c["config"]["ONLY_LEECHERS_WAIT"] = $olw;
	$c["config"]["NOWAITTIME_ONLYSEEDS"] = $nwtos;
	$c["config"]["ANNOUNCE_INTERVAL"] = "" . $GLOBALS["ANNOUNCE_INTERVAL"];
	$c["config"]["WAIT_TIME_RULES"] = $GLOBALS["WAIT_TIME_RULES"];
	$c["config"]["TORRENT_RULES"] = $GLOBALS["TORRENT_RULES"];
	$c["config"]["MAX_PASSKEY_IPS"] = "" . $GLOBALS["MAX_PASSKEY_IPS"];
	$c["config"]["RATIOFAKER_THRESH"] = "" . $GLOBALS["RATIOFAKER_THRESH"];
	$c["config"]["BAN_USERAGENTS"] = $GLOBALS["BAN_USERAGENTS"];
	$na = array();
	foreach($GLOBALS["BAN_PEERIDS"] as $banc)
		$na[] = urlencode($banc);
	$c["config"]["BAN_PEERIDS"] = $na;
	$resp = json_encode($c, JSON_FORCE_OBJECT);
	header("Content-Type: text/plain");
	die($resp);
}

userlogin();
loggedinorreturn();
// in PHP7
// $action = $_GET["action"] ?? false;
/*if(isset($_GET["action"]) && $_GET["action"] == "kill"){
	if(isset($_GET['sure']) && $_GET['sure'] == 1){
		echo $GLOBALS["SOCKET_URL"];
		$response = @file_get_contents("http://" . $GLOBALS["SOCKET_IP"] . "/control?action=kill&operator=admin");
		echo $response;
		if(substr($response,0,2) == "Si")
			stderr("Erfolg!", "Der Socketserver wurde beendet!<br>Klicke <a href=\"" . $_SERVER['PHP_SELF'] . "\">hier!</a>");
		else
			stderr("Warnung!", "Der Socketserver hat nicht ordnungsgem&auml;ss geantwortet!<br>Klicke <a href=\"" . $_SERVER['PHP_SELF'] . "\">hier!</a>");
	}else
		stderr("Socketserver stoppen?", "Willst Du den Socket-Announce-Server wirklich stoppen? Klicke\n" . "<a href=\"" . $_SERVER['PHP_SELF'] . "?action=kill&sure=1\">hier</a>, wenn Du Dir sicher bist.");
}*/

//if(isset($_GET["action"]) && $_GET["action"] == "start"){
//	execInBackground("C:\\servers\\WinNMP\\bin\\PHP\\64bit-php-7.3\\php.exe -f C:/Users/Administrator/Desktop/socketserver/server.php");
//	stderr("Erfolg!", "Der Socketserver wurde gestartet!<br>Klicke <a href=\"" . $_SERVER['PHP_SELF'] . "\">hier!</a>");
//}
/*$socket = fopen("http://" . $GLOBALS["SOCKET_IP"] . "/control?action=avgping&operator=admin", 80, $errno, $errstr, 30);
if($socket !== false){
	$status = true;
	$avgping_str = fgets($socket);
	fclose($socket);
}else{
	$avgping_str = false;
	$status = false;
}*/

$url = "http://" . $GLOBALS["SOCKET_IP"] . "/control?action=avgping&operator=admin";
$ch = curl_init();
$timeout = 5;
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
// Get URL content
$avgping_str = curl_exec($ch);
// close handle to release resources
curl_close($ch);

//$status = (@file_get_contents($GLOBALS["SOCKET_IP"]) !== false) ? true : false;
$status = ($avgping_str !== false) ? true : false;
//$avgping_str = @file_get_contents($GLOBALS["SOCKET_IP"] . "/control?action=avgping&operator=admin");
$avgping_str = ($avgping_str !== false) ? $avgping_str : "Socketserver offline!";
$avgping_arr = explode("/", $avgping_str);

if(!isset($avgping_arr[0]))
	$avgping_arr[0] = "Socketserver offline!";

if(!isset($avgping_arr[1]))
	$avgping_arr[1] = "Socketserver offline!";

if(!isset($avgping_arr[2]))
	$avgping_arr[2] = "Socketserver offline!";

if(!isset($avgping_arr[3]))
	$avgping_arr[3] = "Socketserver offline!";
else
	$avgping_arr[3] = str_replace(".",",",round($avgping_arr[3],0));

if($avgping_arr[3] < 1)
	$avgping_arr[3] = "< 1";

if(!isset($avgping_arr[4]))
	$avgping_arr[4] = "Socketserver offline!";
else
	$avgping_arr[4] = str_replace(".",",",round($avgping_arr[4],0));

if($avgping_arr[4] < 1)
	$avgping_arr[4] = "< 1";

if(!isset($avgping_arr[5]) || !is_numeric($avgping_arr[5]))
	$avgping_arr[5] = "Socketserver offline!";
else
	$avgping_arr[5] = date("d.m.Y H:i:s", $avgping_arr[5]);

$toggle_link = ($status !== false) ? "<a href=\"" . $_SERVER['PHP_SELF'] . "?action=kill\">Klicke hier!</a>" : "<a href=\"" . $_SERVER['PHP_SELF'] . "?action=start\">Klicke hier!</a>";
$flush_link = ($status !== false) ? "<a href=\"" . $_SERVER['PHP_SELF'] . "?action=flush\">Klicke hier!</a>" : "Socketserver offline!";
$status_img = ($status !== false) ? "<img src=\"" . $GLOBALS["PIC_BASE_URL"] . "button_online2.gif\" border=\"0\" alt=\"online\">" : "<img src=\"" . $GLOBALS["PIC_BASE_URL"] . "button_offline2.gif\" border=\"0\" alt=\"offline\">";
stdhead("Socketkontrollcenter");
begin_frame("Socketkontrollcenter", true, "800px");
begin_table(true);
echo "    <tr>\n".
	"        <td class=\"tablea\" style=\"width:150px\">Socket</td>\n".
	"        <td class=\"tableb\">" . $GLOBALS["SOCKET_IP"] . ", " . $GLOBALS["ANNOUNCE_URLS"][0] . " " . $status_img . "</td> \n".
	"    </tr>\n".
	"    <tr>\n".
	"        <td class=\"tablea\">Stats</td>\n".
	"        <td class=\"tableb\">Startzeit: " . $avgping_arr[5] . "<br>&#216;-Antwortzeit: " . $avgping_arr[0] . "ms<br>Anzahl Requests: " . $avgping_arr[2] . "<br>Daten empfangen: " . $avgping_arr[3] . " KiloBytes<br>Daten gesendet: " . $avgping_arr[4] . " KiloBytes</td>\n".
//	"    </tr>\n".
//	"    <tr>\n".
//	"        <td class=\"tablea\">Flush-Peers</td>\n".
//	"        <td class=\"tableb\">" . $flush_link . "</td>\n".
//	"    </tr>\n".
//	"    <tr>\n".
//	"        <td class=\"tablea\">Start/Stop Socketserver</td>\n".
//	"        <td class=\"tableb\">" . $toggle_link . " Derzeit funktioniert der Start per Webkontrolle nur unter Windows!</td>\n".
	"    </tr>\n";
end_table();
end_frame();
stdfoot();
?>