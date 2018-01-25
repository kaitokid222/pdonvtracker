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
userlogin();
loggedinorreturn();

if (get_user_class() < UC_MODERATOR)
	die();

if(isset($_GET['remove']) && is_valid_id($_GET['remove'])){
	$qry = GLOBALS["DB"]->prepare("DELETE FROM bans WHERE id= :remove");
	$qry->bindParam(':remove', $_GET['remove'], PDO::PARAM_INT);
	$qry->execute();
	if($qry->rowCount())
		write_log("Ban " . $_GET['remove'] . " was removed by " . $CURUSER["username"]);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && get_user_class() >= UC_ADMINISTRATOR){
	$first = trim($_POST["first"]);
	$last = trim($_POST["last"]);
	$comment = trim($_POST["comment"]);
	if(!$first || !$last || !$comment)
		stderr("Error", "Missing form data.");
	$first = ipaddress_to_ipnumber($first);
	$last = ipaddress_to_ipnumber($last);
	if ($first == -1 || $last == -1)
		stderr("Error", "Bad IP address.");
	$added = get_date_time();

	$qry = GLOBALS["DB"]->prepare("INSERT INTO bans (added, addedby, first, last, comment) VALUES(:added, :op, :first, :last, :comment)");
	$qry->bindParam(':added', $added, PDO::PARAM_STR);
	$qry->bindParam(':op', $CURUSER["id"], PDO::PARAM_INT);
	$qry->bindParam(':first', $first, PDO::PARAM_INT);
	$qry->bindParam(':last', $last, PDO::PARAM_INT);
	$qry->bindParam(':comment', $comment, PDO::PARAM_INT);
	$qry->execute();
	if($qry->rowCount())
		header("Location: " . $BASEURL . $_SERVER["REQUEST_URI"]);
	die;
}

ob_start("ob_gzhandler");

$qry = GLOBALS["DB"]->prepare("SELECT bans.*, users.username as op FROM bans LEFT JOIN users ON users.id = bans.addedby ORDER BY added DESC");
$qry->execute();
stdhead("Bans");

echo "<h1>Aktuelle Bans</h1>\n";

if (!$qry->rowCount())
	echo "<p align=\"center\"><b>Momentan ist niemand gebannt.</b></p>\n";
else{
	echo "<table border=1 cellspacing=0 cellpadding=5>\n".
		"    <tr>\n".
		"        <td class=\"colhead\">Added</td>\n".
		"        <td class=\"colhead\" align=\"left\">First IP</td>\n".
		"        <td class=\"colhead\" align=\"left\">Last IP</td>\n".
		"        <td class=\"colhead\" align=\"left\">By</td>\n".
		"        <td class=\"colhead\" align=\"left\">Comment</td>\n".
		"        <td class=\"colhead\">Remove</td>\n".
		"    </tr>\n";
	$data = $qry->FetchAll(PDO::FETCH_ASSOC);
	foreach($data as $arr){
		$arr["first"] = long2ip($arr["first"]);
		$arr["last"] = long2ip($arr["last"]);
		echo "    <tr>\n".
			"        <td>" . $arr["added"] . "</td>\n".
			"        <td align=\"left\">" . $arr["first"] . "</td>\n".
			"        <td align=\"left\">" . $arr["last"] . "</td>\n".
			"        <td align=\"left\"><a href=\"userdetails.php?id=" . $arr["addedby"] . "\">" . $arr["op"] . "</a></td>\n".
			"        <td align=\"left\">" . $arr["comment"] . "</td>\n".
			"        <td><a href=\"bans.php?remove=" . $arr["id"] . "\">Remove</a></td>\n".
			"    </tr>\n";
	}
	echo "</table>\n";
}

if (get_user_class() >= UC_ADMINISTRATOR){
	echo "<h2>Add ban</h2>\n".
		"<table border=\"1\" cellspacing=\"0\" cellpadding=\"5\">\n".
		"    <form method=\"post\" action=\"bans.php\">\n".
		"    <tr>\n".
		"        <td class=\"rowhead\">First IP</td>\n".
		"        <td><input type=\"text\" name=\"first\" size=\"40\"></td>\n".
		"    </tr>\n".
		"    <tr>\n".
		"        <td class=\"rowhead\">Last IP</td>\n".
		"        <td><input type=\"text\" name=\"last\" size=\"40\"></td>\n".
		"    </tr>\n".
		"    <tr>\n".
		"        <td class=\"rowhead\">Comment</td>\n".
		"        <td><input type=\"text\" name=\"comment\" size=\"40\"></td>\n".
		"    </tr>\n".
		"    <tr>\n".
		"        <td colspan=\"2\"><input type=\"submit\" value=\"Okay\" class=\"btn\"></td>\n".
		"    </tr>\n".
		"    </form>\n" .
		"</table>\n";
}
stdfoot();
?>