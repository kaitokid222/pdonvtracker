<?

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

if (!mkglobal("username:password"))
	die("Hier ist was faul...");

dbconn();

hit_count();

function bark($text = "Benutzername oder Passwort ungültig")
{
  stderr("Login fehlgeschlagen!", $text);
}

session_start();

$res = mysql_query("SELECT * FROM users WHERE username = " . sqlesc($username) . " AND status = 'confirmed'");
$row = mysql_fetch_assoc($res);

if (!$row)
	bark();

if ($row["passhash"] != md5($row["secret"] . $password . $row["secret"]))
	bark();

if ($row["enabled"] == "no")
	bark("Dieser Account wurde deaktiviert.");

logincookie($row["id"], $row["passhash"]);

$ip = getip();

$_SESSION["userdata"] = $row;
$_SESSION["userdata"]["ip"] = $ip;

mysql_query("UPDATE users SET last_access='" . date("Y-m-d H:i:s") . "', ip='$ip' WHERE id=" . $row["id"]); // or die(mysql_error());

if (!empty($_POST["returnto"]))
	header("Location: ".$BASEURL.$_POST["returnto"]);
else
	header("Location: $BASEURL/my.php?".SID);

hit_end();

?>