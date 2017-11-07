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

dbconn(false);

function hex2bin($hexdata) {

   for ($i=0;$i<strlen($hexdata);$i+=2) {
     $bindata.=chr(hexdec(substr($hexdata,$i,2)));
   }

   return $bindata;
}

$res = mysql_query("SELECT * FROM `users` WHERE `passkey`=".sqlesc(hex2bin($_GET["passkey"])));
if (mysql_num_rows($res)) {
    $udata = mysql_fetch_assoc($res);
    echo "User ".$udata["username"]." <a href=\"userdetails.php?id=".$udata["id"]."\">( ".$udata["id"]." )</a>";
} else
    echo "Nix gefunden :("

?>
