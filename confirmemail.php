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

if (!isset($_GET["id"]) OR !isset($_GET["secret"]) OR !isset($_GET["email"]))
	httperr();
else{
	$id = 0 + $_GET["id"];
	$md5 = $_GET["secret"];
	$email = urldecode($_GET["email"]);
}

userlogin();

$qry = $GLOBALS['DB']->prepare('SELECT editsecret FROM users WHERE id = :id');
$qry->bindParam(':id', $id, PDO::PARAM_INT);
$qry->execute();
if($qry->rowCount() > 0)
	$row = $qry->FetchAll();
else
	httperr();

$sec = hash_pad($row["editsecret"]);

if (preg_match('/^ *$/s', $sec))
	httperr();
if ($md5 != md5($sec . $email . $sec))
	httperr();

$qry = $GLOBALS['DB']->prepare("UPDATE users SET editsecret='', email= :email WHERE id= :id AND editsecret= :esec");
$qry->bindParam(':email', $email, PDO::PARAM_STR);
$qry->bindParam(':id', $id, PDO::PARAM_INT);
$qry->bindParam(':esec', $row["editsecret"], PDO::PARAM_STR);
$qry->execute();
if(!$qry->rowCount())
	httperr();

header("Refresh: 0; url=" . $BASEURL . "/my.php?emailch=1");
?>