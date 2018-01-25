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
//dbconn();
userlogin();

function get_count($inactive_time)
{
    $arr['cnt'] = $GLOBALS['database']->row_count('users','UNIX_TIMESTAMP(`last_access`)<'.$inactive_time);
    return $arr["cnt"];
}
        
function stat_row($time, $count)
{
    global $usercount;
    
    echo '<tr><td class="tablea">', $time, "</td>\n";
    echo '<td class="tableb" nowrap><img src="'.$GLOBALS["PIC_BASE_URL"].'bar.gif" height="9" width="';
    echo (int)((float)$count/(float)$usercount*600), '"> ', $count, '</td></tr>', "\n";
}

stdhead("Benutzeraktivitäts-Chart");
begin_frame("Benutzeraktivitäts-Chart", FALSE, "650px");
begin_table(TRUE);
echo '<tr><td class="tablecat">Inakt. seit</td><td class="tablecat">Anzahl (prozentual von Gesamtbenutzern)</td></tr>', "\n";

$curtime = time();
$usercount = $database->row_count('users');
// 24h, stdl. --> 24x
for ($I=0; $I<24; $I++) {
    stat_row($I."h", get_count($curtime-($I*3600)));
}

// 48 Stunden (2d), 3 stdl. --> 8x
for ($I=24; $I<48; $I+=3) {
    stat_row($I."h", get_count($curtime-($I*3600)));
}

// 72 Stunden (3d), 6 stdl. --> 4x
for ($I=48; $I<72; $I+=6) {
    stat_row($I."h", get_count($curtime-($I*3600)));
}

// 96 Stunden (4d), 12 stdl. --> 2x
for ($I=72; $I<96; $I+=12) {
    stat_row($I."h", get_count($curtime-($I*3600)));
}

// 5-7d, 24stdl. --> 3x
for ($I=4; $I<7; $I++) {
    stat_row($I."d", get_count($curtime-($I*3600*24)));
}

// 1-6 Wochen, wtl.
for ($I=1; $I<7; $I++) {
    stat_row($I."w", get_count($curtime-($I*3600*24*7)));
}

end_table();
end_frame();
stdfoot();
?>