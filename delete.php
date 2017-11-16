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

require_once("include/bittorrent.php");

hit_start();



function bark($msg) {
  stdhead();
  stdmsg("Löschen fehlgeschlagen!", $msg);
  stdfoot();
  exit;
}

if (!mkglobal("id"))
	bark("Fehlenden Formulardaten");

$id = 0 + $id;
if (!$id)
	die();

dbconn();

hit_count();

loggedinorreturn();

$res = mysql_query("SELECT torrents.name,torrents.owner,torrents.seeders,torrents.activated,users.class FROM torrents LEFT JOIN users ON torrents.owner=users.id WHERE torrents.id = $id");
$row = mysql_fetch_array($res);
if (!$row)
	die();

if ($CURUSER["id"] != $row["owner"] && !(get_user_class() >= UC_MODERATOR || ($row["activated"] == "no" && get_user_class() == UC_GUTEAM && $row["class"] < UC_UPLOADER)))
	bark("Dir gehört der Torrent nicht! Wie konnte das passieren?\n");

$rt = 0 + $_POST["reasontype"];

if (!is_int($rt) || $rt < 1 || $rt > 5)
	bark("Ungültiger Grund (" . $rt . ").");
/*
Notice: Undefined index: r in C:\xampp\htdocs\delete.php on line 66 (jetzt 70)
*/
//$r = $_POST["r"]; // ?
$reason = $_POST["reason"];

if ($rt == 1)
	$reasonstr = "Tot: 0 Seeder, 0 Leecher = 0 Peers gesamt";
elseif ($rt == 2)
	$reasonstr = "Doppelt" . ($reason[0] ? (": " . trim($reason[0])) : "!");
elseif ($rt == 3)
	$reasonstr = "Nuked" . ($reason[1] ? (": " . trim($reason[1])) : "!");
elseif ($rt == 4)
{
	if (!$reason[2])
		bark("Bitte beschreibe, welche Regel verletzt wurde.");
    $reasonstr = "NetVision Regeln verletzt: " . trim($reason[2]);
}
else
{
	if (!$reason[3])
		bark("Bitte gebe einen Grund an, warum dieser Torrent gelöscht werden soll.");
  $reasonstr = trim($reason[3]);
}

deletetorrent($id, $row["owner"], $reasonstr);

write_log("torrentdelete","Der Torrent " . $id . " (" . $row['name'] . ") wurde von '<a href=\"userdetails.php?id=" . $CURUSER['id'] . "\">" . $CURUSER['username'] . "</a>' gelöscht (" . $reasonstr . ")\n");

stdhead("Torrent gelöscht!");

if (isset($_POST["returnto"]))
	$ret = "<a href=\"" . htmlspecialchars($_POST["returnto"]) . "\">Gehe dorthin zurück, von wo Du kamst</a>";
else
	$ret = "<a href=\"./\">Zurück zum Index</a>";

?>
<h2>Torrent gel&ouml;scht!</h2>
<p><?=$ret ?></p>
<?php

stdfoot();

hit_end();

?>