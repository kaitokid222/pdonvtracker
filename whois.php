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

require_once "include/bittorrent.php";

if(isset($_GET['ip'])){
	$ip = $_GET['ip'];
	if(!is_valid_ip($ip))
		die("Keine gültige IP!");
}else
	die("Kein Parameter \"IP\"");

echo "<html>\n".
	"<head>\n".
	"    <title>WHOIS Data zu IP " . $_GET["ip"] . "</title>\n".
	"</head>\n".
	"<body>\n".
	"    <pre>".
	system("whois ".escapeshellcmd($_GET["ip"])).
	"    </pre>\n".
	"</body>\n".
	"</html>\n";
?>