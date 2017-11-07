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

dbconn(false);

hit_count();

loggedinorreturn();

if (get_user_class() < UC_GUTEAM)
    stderr("Zugriff verweigert", "Du hast keine Berechtigung, diese Seite zu sehen!");

stdhead("Nicht aktivierte Gastuploads");

$where = "WHERE activated = 'no'";
$res = mysql_query("SELECT COUNT(*) FROM torrents $where");
$row = mysql_fetch_array($res);
$count = $row[0];

if (!$count) {
begin_frame("Keine Torrents vorhanden");
echo "Es sind aktuell keine Gastuploads vorhanden, die auf Freischaltung warten.";
end_frame();
}
else {
	list($pagertop, $pagerbottom, $limit) = pager(20, $count, "mytorrents.php?");

	$res = mysql_query("SELECT torrents.type, torrents.activated, torrents.comments, torrents.leechers, torrents.seeders, IF(torrents.numratings < ".$GLOBALS["MINVOTES"].", NULL, ROUND(torrents.ratingsum / torrents.numratings, 1)) AS rating, torrents.id, torrents.last_action, categories.name AS cat_name, categories.image AS cat_pic, torrents.name, torrents.filename, torrents.gu_agent, torrents.save_as, torrents.numfiles, torrents.added, torrents.owner, torrents.size, torrents.views, torrents.visible, torrents.hits, torrents.times_completed, torrents.category, users.username FROM torrents LEFT JOIN categories ON torrents.category = categories.id LEFT JOIN users ON users.id=torrents.owner $where ORDER BY id DESC $limit");
    
	print($pagertop);

	torrenttable($res, "guestuploads");

	print($pagerbottom);
}

stdfoot();

hit_end();

?>
