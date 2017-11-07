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

//if (!mkglobal("username:password"))
//	die("Hier ist was faul...");
// back 2 post -kaito 07.11.2017
$username = $_POST['username'];
$password = $_POST['password'];

dbconn();

hit_count();

function bark($text = "Benutzername oder Passwort ungültig")
{
  stderr("Login fehlgeschlagen!", $text);
}

session_start();

$qry = $GLOBALS['DB']->prepare("SELECT * FROM users WHERE username = :username AND status = 'confirmed'");
$qry->bindParam(':username', $username, PDO::PARAM_STR);
$qry->execute();
if($qry->rowCount() > 0){
    $row = $qry->fetchObject();
}

if (!$row)
	bark("row fehlerhaft");

if ($row->passhash != md5($row->secret . $password . $row->secret))
	bark("PW problem");

if ($row->enabled == "no")
	bark("Dieser Account wurde deaktiviert.");

logincookie($row->id, $row->passhash);

$ip = getip();

// konvertiere objekt zu array
$array = (array) $row;

$_SESSION["userdata"] = $array;
$_SESSION["userdata"]["ip"] = $ip;

$qry = $GLOBALS['DB']->prepare('UPDATE users SET last_access = :la, ip = :ip WHERE id = :id');
$qry->bindParam(':la', date("Y-m-d H:i:s"), PDO::PARAM_STR);
$qry->bindParam(':ip', $ip, PDO::PARAM_STR);
$qry->bindParam(':id', $row->id, PDO::PARAM_STR);
$qry->execute();

if (!empty($_POST["returnto"]))
	header("Location: ".$BASEURL.$_POST["returnto"]);
else
	header("Location: " . $BASEURL . "/my.php");

hit_end();

?>