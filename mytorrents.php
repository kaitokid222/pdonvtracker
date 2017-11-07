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

dbconn(false);

hit_count();

loggedinorreturn();

stdhead($CURUSER["username"] . "'s torrents");

$where = "WHERE owner = " . $CURUSER["id"] . " AND banned != 'yes'";
$res = mysql_query("SELECT COUNT(*) FROM torrents $where");
$row = mysql_fetch_array($res);
$count = $row[0];

if (!$count) {
begin_frame("Keine Torrent vorhanden");
echo "Du hast noch keine Torrents hochgeladen, also ist hier auch nix!";
end_frame();
}
else {
	list($pagertop, $pagerbottom, $limit) = pager(20, $count, "mytorrents.php?");

	$res = mysql_query("SELECT torrents.type, torrents.activated, torrents.comments, torrents.leechers, torrents.seeders, IF(torrents.numratings < ".$GLOBALS["MINVOTES"].", NULL, ROUND(torrents.ratingsum / torrents.numratings, 1)) AS rating, torrents.id, torrents.last_action, categories.name AS cat_name, categories.image AS cat_pic, torrents.name, torrents.filename, save_as, numfiles, added, size, views, visible, hits, times_completed, category FROM torrents LEFT JOIN categories ON torrents.category = categories.id $where ORDER BY id DESC $limit");
    
	print($pagertop);

	torrenttable($res, "mytorrents");

	print($pagerbottom);
}

stdfoot();

hit_end();

?>
