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

if(!isset($_GET['id']) || !isset($_GET["secret"]))
	httperr();
else{
	$id = 0 + $_GET["id"];
	$md5 = $_GET["secret"];
}

userlogin();

$qry = $GLOBALS['DB']->prepare('SELECT passhash, editsecret, status FROM users WHERE id = :id');
$qry->bindParam(':id', $id, PDO::PARAM_INT);
$qry->execute();
if($qry->rowCount() > 0){
	$row = $qry->FetchAll();
else
	httperr();

if ($row["status"] != "pending") {
	header("Refresh: 0; url=" . $BASEURL . "/ok.php?type=confirmed");
	exit();
}

$sec = hash_pad($row["editsecret"]);
if ($md5 != md5($sec))
	httperr();

$qry = $GLOBALS['DB']->prepare("UPDATE users SET status='confirmed', editsecret='' WHERE id= :id AND status='pending'");
$qry->bindParam(':id', $id, PDO::PARAM_INT);
$qry->execute();
if(!$qry->rowCount())
	httperr();

logincookie($id, $row["passhash"]);

header("Refresh: 0; url=" . $BASEURL . "/ok.php?type=confirm");
?>