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
dbconn(false);

loggedinorreturn();

function puke($text = "w00t")
{
    stderr("w00t", $text);
} 

if (get_user_class() < UC_GUTEAM)
    puke();

function get_typ_name($typ)
{
    switch ($typ) {
        case "torrentupload": return "Torrent hochgeladen";
        case "torrentedit": return "Torrent bearbeitet";
        case "torrentdelete": return "Torrent gelöscht";
        case "torrentgranted":return "Torrent freigeschaltet";
        case "promotion": return "Beförderung";
        case "demotion": return "Degradierung";
        case "addwarn": return "Verwarnung erteilt";
        case "remwarn": return "Verwarnung entfernt";
        case "accenabled": return "Account aktiviert";
        case "accdisabled": return "Account deaktiviert";
        case "accdeleted": return "Account gelöscht";
        case "waitgrant": return "Wartezzeitaufh. zugestimmt";
        case "waitreject": return "Wartezeitaufh. abgelehnt";
        case "passkeyreset": return "PassKey neu gesetzt";
    } 
} 

$timerange = array(3600 => "1 Stunde",
    3 * 3600 => "3 Stunden",
    6 * 3600 => "6 Stunden",
    9 * 3600 => "9 Stunden",
    12 * 3600 => "12 Stunden",
    18 * 3600 => "18 Stunden",
    24 * 3600 => "1 Tag",
    2 * 24 * 3600 => "2 Tage",
    3 * 24 * 3600 => "3 Tage",
    4 * 24 * 3600 => "4 Tage",
    5 * 24 * 3600 => "5 Tage",
    6 * 24 * 3600 => "6 Tage",
    7 * 24 * 3600 => "1 Woche",
    14 * 24 * 3600 => "2 Wochen"
    );

$types = array('torrentupload', 'torrentedit', 'torrentdelete', 'torrentgranted', 'promotion', 'demotion', 'addwarn', 'remwarn', 'accenabled', 'accdisabled', 'accdeleted', 'waitgrant', 'waitreject', 'passkeyreset'); 
// delete items older than two weeks
$secs = 14 * 24 * 3600;
stdhead("Site log");
mysql_query("DELETE FROM sitelog WHERE " . time() . " - UNIX_TIMESTAMP(added) > $secs") or sqlerr(__FILE__, __LINE__);
$where = "WHERE ";
$typelist = Array();

if (isset($_GET["types"])) {
    foreach ($_GET["types"] as $type) {
        $typelist[] = sqlesc($type);
    } 
    $where .= "typ IN (" . implode(",", $typelist) . ") AND ";
} 

if (isset($_GET["timerange"]))
    $where .= time() . "-UNIX_TIMESTAMP(added)<" . intval($_GET["timerange"]);
else {
    $where .= time() . " - UNIX_TIMESTAMP(added) < 432000";
    $_GET["timerange"] = 432000;
} 

begin_frame("Site Log");

print("<form action=\"log.php\" method=\"get\"><a name=\"log\"></a><center>");
begin_table();
print("<tr>\n");
$I = 0;

foreach ($types as $type) {
    if ($I == 4) {
        $I = 0;
        print("</tr><tr>\n");
    } 
    print("<td class=\"tablea\"><input type=\"checkbox\" name=\"types[]\" value=\"$type\"");
    if (in_array(sqlesc($type), $typelist))
        print(" checked=checked");

    print("> <a href=\"log.php?timerange=" . intval($_GET["timerange"]) . "&amp;types[]=$type&amp;filter=1#log\">" . get_typ_name($type) . "</a></td>\n");
    $I++;
} 

if ($I < 4)
    echo "<td colspan=\"" . (4 - $I) . "\"  class=\"tablea\">&nbsp;</td>\n";
print("</tr><tr><td class=\"tablea\" align=\"center\"><a href=\"log.php?timerange=" . intval($_GET["timerange"]) . "&amp;filter=1#log\">Alle anzeigen</a></td><td class=\"tablea\" colspan=\"2\" align=\"center\">Zeitraum: <select name=\"timerange\" size=\"1\">\n");
foreach ($timerange as $range => $desc) {
    print "<option value=\"$range\"";
    if (intval($_GET["timerange"]) == $range)
        echo " selected=\"selected\"";
    print ">$desc</option>\n";
} 

print("</select></td><td class=\"tablea\" align=\"center\"><input type=\"submit\" name=\"filter\" value=\"Filtern\"></td></tr></table></form><br>");

if (isset($_GET["filter"])) {
    $res = mysql_query("SELECT typ, added, txt FROM sitelog $where ORDER BY added DESC") or sqlerr(__FILE__, __LINE__);

    if (mysql_num_rows($res) == 0)
        print("<b>Es liegen keine Ereignisse mit den gewünschten Typen vor.</b>\n");
    else {
        print("<b>Es wurden " . mysql_num_rows($res) . " Ereignisse mit den gewünschten Typen gefunden.</b>\n");
        begin_table(true);
        print("<tr><td class=tablecat align=left>Datum</td><td class=tablecat align=left>Zeit</td><td class=tablecat align=left>Typ</td><td class=tablecat align=left>Ereignis</td></tr>\n");
        while ($arr = mysql_fetch_assoc($res)) {
            $typ = get_typ_name($arr["typ"]);
            $date = substr($arr['added'], 0, strpos($arr['added'], " "));
            $time = substr($arr['added'], strpos($arr['added'], " ") + 1);
            print("<tr><td class=tableb>$date</td><td class=tablea>$time</td><td class=tableb align=left nowrap=nowrap>$typ</td><td class=tablea align=left>$arr[txt]</td></tr>\n");
        } 
        print("</table>");
    } 
    print("<p>Alle Zeitangaben sind lokal.</p>\n");
} 
end_frame();
stdfoot();

?>