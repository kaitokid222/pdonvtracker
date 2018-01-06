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

if(isset($_GET["action"]) && $_GET["action"] == "kill"){
	if(isset($_GET['sure']) && $_GET['sure'] == 1){
		$response = @file_get_contents($GLOBALS["SOCKET_URL"] . "/control?action=kill&operator=admin");
		if(substr($response,0,2) == "Si")
			stderr("Erfolg!", "Der Socketserver wurde beendet!<br>Klicke <a href=\"" . $_SERVER['PHP_SELF'] . "\">hier!</a>");
		else
			stderr("Warnung!", "Der Socketserver hat nicht ordnungsgem&auml;ss geantwortet!<br>Klicke <a href=\"" . $_SERVER['PHP_SELF'] . "\">hier!</a>");
	}else
		stderr("Socketserver stoppen?", "Willst Du den Socket-Announce-Server wirklich stoppen? Klicke\n" . "<a href=\"" . $_SERVER['PHP_SELF'] . "?action=kill&sure=1\">hier</a>, wenn Du Dir sicher bist.");
}

/* tötet apache.. aber der socket startet :D
if(isset($_GET["action"]) && $_GET["action"] == "start"){
	$path = "c:\\xampp\\htdocs\\announce\\";
	chdir($path);
	exec("start start.bat");
	$path = "c:\\xampp\\htdocs\\";
	chdir($path);
	die();
}*/

$status = (@file_get_contents($GLOBALS["ANNOUNCE_URLS"][0]) !== false) ? true : false;
$avgping_str = @file_get_contents($GLOBALS["SOCKET_URL"] . "/control?action=avgping&operator=admin");
$avgping_str = ($avgping_str !== false) ? $avgping_str : "Socketserver offline! - 0/0/0";
$avgping_arr = explode("/", $avgping_str);

$kill_link = ($status !== false) ? "<a href=\"" . $_SERVER['PHP_SELF'] . "?action=kill\">Klicke hier!</a>" : "Socketserver offline!";
$flush_link = ($status !== false) ? "<a href=\"" . $_SERVER['PHP_SELF'] . "?action=flush\">Klicke hier!</a>" : "Socketserver offline!";

$status_img = ($status !== false) ? "<img src=\"" . $GLOBALS["PIC_BASE_URL"] . "button_online2.gif\" border=\"0\" alt=\"online\">" : "<img src=\"" . $GLOBALS["PIC_BASE_URL"] . "button_offline2.gif\" border=\"0\" alt=\"offline\">";
stdhead("Socketkontrollcenter");
begin_frame("Socketkontrollcenter", true, "800px");
begin_table(true);
echo "    <tr>\n".
	"        <td class=\"tablea\" style=\"width:100px\">Socket</td>\n".
	"        <td class=\"tableb\">" . $GLOBALS["SOCKET_URL"] . "/, " . $GLOBALS["ANNOUNCE_URLS"][0] . " " . $status_img . "</td> \n".
	"    </tr>\n".
	"    <tr>\n".
	"        <td class=\"tablea\" style=\"width:100px\">&#216;-Antwortzeit</td>\n".
	"        <td class=\"tableb\">" . $avgping_arr[0] . " ms (Pingsumme: " . $avgping_arr[1] . " / Anzahl Requests: " . $avgping_arr[2] . ")</td>\n".
	"    </tr>\n".
	"    <tr>\n".
	"        <td class=\"tablea\" style=\"width:100px\">Flush-Peers</td>\n".
	"        <td class=\"tableb\">" . $flush_link . "</td>\n".
	"    </tr>\n".
	"    <tr>\n".
	"        <td class=\"tablea\" style=\"width:100px\">Stoppe Socketserver</td>\n".
	"        <td class=\"tableb\">" . $kill_link . "</td>\n".
	"    </tr>\n";
end_table();
end_frame();
stdfoot();
?>