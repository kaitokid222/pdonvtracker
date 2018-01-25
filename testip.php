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
loggedinorreturn();
if (get_user_class() < UC_MODERATOR) stderr("Error", "Permission denied");

if ($_SERVER["REQUEST_METHOD"] == "POST")
	$ip = $_POST["ip"];
else
	$ip = $_GET["ip"];
if ($ip)
{
	//$nip = ip2long($ip);
	$nip = ipaddress_to_ipnumber($ip);
	//if ($nip == -1)
	if ($nip < 1)
	  stderr("Error", "Bad IP.");
	$res = mysql_query("SELECT * FROM bans WHERE $nip >= first AND $nip <= last") or sqlerr(__FILE__, __LINE__);
	if (mysql_num_rows($res) == 0)
	  stderr("Result", "The IP address <b>$ip</b> is not banned.");
	else
	{
	  $banstable = "<table class=main border=0 cellspacing=0 cellpadding=5>\n" .
	    "<tr><td class=colhead>First</td><td class=colhead>Last</td><td class=colhead>Comment</td></tr>\n";
	  while ($arr = mysql_fetch_assoc($res))
	  {
	    $first = long2ip($arr["first"]);
	    $last = long2ip($arr["last"]);
	    $comment = htmlspecialchars($arr["comment"]);
	    $banstable .= "<tr><td>$first</td><td>$last</td><td>$comment</td></tr>\n";
	  }
	  $banstable .= "</table>\n";
	  stderr("Result", "<table border=0 cellspacing=0 cellpadding=0><tr><td class=embedded style='padding-right: 5px'><img src=\"".$GLOBALS["PIC_BASE_URL"]."smilies/excl.gif\"></td><td class=embedded>The IP address <b>$ip</b> is banned:</td></tr></table><p>$banstable</p>");
	}
}
stdhead();

?>
<h1>Test IP address</h1>
<form method=post action=testip.php>
<table border=1 cellspacing=0 cellpadding=5>
<tr><td class=rowhead>IP address</td><td><input type=text name=ip></td></tr>
<tr><td colspan=2 align=center><input type=submit class=btn value='OK'></td></tr>
</form>
</table>

<?php
stdfoot();
?>