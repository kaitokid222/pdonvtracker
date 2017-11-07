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

function noFeed()
{
    echo "<item>\n<title>Nicht eingeloggt oder kein RSS-Feed verfügbar!</title>\n";
    echo "<category domain=\"$DEFAULTBASEURL\">(Keine Kategorie)</category>\n";
    echo "<description>Der RSS-Feed ist nur verfügbar, wenn Du eingeloggt bist, Cookies aktiviert hast, und der Administrator diese Funktion aktiviert hat.</description>\n";
    echo "<link>".$DEFAULTBASEURL."/login.php</link>";
    echo "</item></channel>\n</rss>\n";
    die();
}

header("Content-type: application/xml");

echo "<?xml version=\"1.0\" encoding=\"iso-8859-1\" ?>\n<rss version=\"2.0\">\n<channel>\n";
echo "<title>$title</title>\n<description>Aktuelle Torrents</description>\n<link>$DEFAULTBASEURL/</link>\n";


if (!isset($GLOBALS["CURUSER"]) || !$GLOBALS["DYNAMIC_RSS"])
    noFeed();

$query = "SELECT `id`,`name`,`descr`,`filename`,`category` FROM `torrents` WHERE `activated`='yes' ";
if ($_GET["categories"] == "profile") {
    $categories = Array();
    @preg_match_all("/\\[cat(\\d+)\\]/", $CURUSER['notifs'], $catids);
    for ($I=0; $I<count($catids[1]);$I++)
        array_push($categories, $catids[1][$I]);
    if (count($categories) > 0) {
        $categories = implode(",", $categories);
        $query .= "AND `category` IN ($categories) ";
    }
}
$res = mysql_query($query."ORDER BY `added` DESC LIMIT 15");
    
while ($arr = mysql_fetch_assoc($res)) {
    $cat = $cats[$arr["category"]];
    echo "<item>\n<title>" . htmlspecialchars($arr["name"]) . "</title>\n";
    echo "<category domain=\"$DEFAULTBASEURL/browse.php?cat=" . $arr["category"] . "\">" . htmlspecialchars($cat) . "</category>\n";
    echo "<description>" . htmlspecialchars($arr["descr"]) . "</description>\n<link>$DEFAULTBASEURL/";
    if ($_GET["type"] == "directdl")
        echo "download/" . $arr["id"] . "/" . htmlspecialchars($arr["filename"]);
    else
        echo "details.php?id=" . $arr["id"] . "&amp;hit=1";
    echo "</link>\n</item>\n";
} 

echo "</channel>\n</rss>\n";

?>