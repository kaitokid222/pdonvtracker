<?php

/*
// +--------------------------------------------------------------------------+
// | Project:    NVTracker - NetVision BitTorrent Tracker                     |
// +--------------------------------------------------------------------------+
// | This file is part of NVTracker. NVTracker is based on BTSource,          |
// | originally by RedBeard of TorrentBits, extensively modified by           |
// | Gartenzwerg.                                                             |
// |                                                                          |
// | NVTracker is free software; you can redistribute it and/or modify        |
// | it under the terms of the GNU General Public License as published by     |
// | the Free Software Foundation; either version 2 of the License, or        |
// | (at your option) any later version.                                      |
// |                                                                          |
// | NVTracker is distributed in the hope that it will be useful,             |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of           |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
// | GNU General Public License for more details.                             |
// |                                                                          |
// | You should have received a copy of the GNU General Public License        |
// | along with NVTracker; if not, write to the Free Software Foundation,     |
// | Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA            |
// +--------------------------------------------------------------------------+
// | Obige Zeilen dürfen nicht entfernt werden!    Do not remove above lines! |
// +--------------------------------------------------------------------------+
 */

require "include/bittorrent.php";

dbconn();

if($_SERVER["REQUEST_METHOD"] == "POST"){
	$message = $_POST["msg"];
	$torrent_id = intval($_POST["id"]);
	$arr = mysql_fetch_assoc(mysql_query("SELECT COUNT(*) AS count, name FROM torrents WHERE id=$torrent_id GROUP BY name")) or sqlerr();
	if($arr["count"] != 1) 
		stderr("Fehler", "Der angegebene Torrent existiert nicht.");

	$torrent_name = $arr["name"];

	if(get_wait_time($CURUSER["id"], $torrent_id) <= 2)
	stderr("Fehler", "Du hast für diesen Torrent keine nennenswerte Wartezeit. Du brauchst keine Aufhebung zu beantragen.");

	if($CURUSER && $message != ""){
		// Prüfen, ob schon eine Anfrage vorliegt
		$res = mysql_query("SELECT `status` FROM nowait WHERE user_id=".$CURUSER["id"]." AND torrent_id=$torrent_id");
		if(mysql_num_rows($res)){
			$arr = mysql_fetch_assoc($res);
			switch($arr["status"]){
				case "pending":
					stderr("Fehler", "Du hast für diesen Torrent bereits eine Wartezeitaufhebung beantragt. Die Anfrage wurde noch nicht geprüft. Sollte die Anfrage schon länger her sein, nimm bitte mit einem Teammitglied direkten Kontakt auf!");
					break;
				case "granted":
					stderr("Fehler", "Du hast für diesen Torrent bereits eine Wartezeitaufhebung beantragt, die Anfrage wurde aber von einem Moderator abgelehnt. Du kannst die Aufhebung nicht noch einmal beantragen.");
					break;
				case "rejected":
					stderr("Fehler", "Du hast für diesen Torrent bereits eine Wartezeitaufhebung beantragt. Die Wartezeit für diesen Torrent wurde von einem Moderator aufgehoben.");
					break;
			}
		}
		$mod_msg = "Benutzer: ".$CURUSER["username"]." ($BASEURL/userdetails.php?id=$CURUSER[id])\nTorrent: $torrent_name\n\nGrund des Antrags: $message\n\nBitte die Bedingungen prüfen, und eventuell Rücksprache mit dem Benutzer halten.";
		sendPersonalMessage(0, 0, $CURUSER["username"]." hat eine Wartezeitaufhebung beantragt", $mod_msg, PM_FOLDERID_MOD, 0, "open");
		mysql_query("INSERT INTO nowait (user_id, torrent_id, status, grantor, msg) VALUES ($CURUSER[id], $torrent_id, 'pending', 0, ".sqlesc($message).")") or sqlerr(__FILE__, __LINE__);
		stderr("Erfolg", "Deine Anfrage auf Aufhebung der Wartezeit für diesen Torrent wurde an die Moderatoren zur Prüfung weitergeleitet.");
	}else
		stderr("Fehler", "Bitte gebe einen Grund für die Wartezeitaufhebung an!");
}
stderr("Fehler", "Falsche Anfragemethode!");
?>
