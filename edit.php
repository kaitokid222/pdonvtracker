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

if (!mkglobal("id"))
	die();

$id = 0 + $id;
if (!$id)
	die();

dbconn();

hit_count();

loggedinorreturn();

$res = mysql_query("SELECT torrents.*,users.class FROM torrents LEFT JOIN users ON torrents.owner=users.id WHERE torrents.id = $id");
$row = mysql_fetch_array($res);
if (!$row)
	die();

stdhead("Torrent \"" . $row["name"] . "\" bearbeiten");

if (!isset($CURUSER) || !($CURUSER["id"] == $row["owner"] || get_user_class() >= UC_MODERATOR || ($row["activated"] == "no" && get_user_class() == UC_GUTEAM && $row["class"] < UC_UPLOADER))) {
?>
<table cellpadding="4" cellspacing="1" border="0" style="width:650px" class="tableinborder">
 <tr class="tabletitle" width="100%">
  <td colspan="10" width="100%"><span class="normalfont"><center><b> Du darfst diesen Torrent nicht bearbeiten </b></center></span></td> 
 </tr><tr><td width="100%" class="tablea">Du bist nicht
 der rechtm&auml;&szlig;ige Besitzer, oder Du bist nicht korrekt
 <a href="login.php?returnto=<?=urlencode($_SERVER["REQUEST_URI"])?>&amp;nowarn=1">eingeloggt</a>.
</td></tr></table>
<?
}
else {
	print("<form method=post action=takeedit.php enctype=multipart/form-data>\n");
	print("<input type=\"hidden\" name=\"id\" value=\"$id\">\n");
	if (isset($_GET["returnto"]))
		print("<input type=\"hidden\" name=\"returnto\" value=\"" . htmlspecialchars($_GET["returnto"]) . "\" />\n");
?>
<table cellpadding="4" cellspacing="1" border="0" style="width:650px" class="tableinborder">
 <tr class="tabletitle" width="100%">
  <td colspan="10" width="100%"><span class="normalfont"><center><b>Torrent bearbeiten</b></center></span></td> 
 </tr>
<?        
	tr("Torrent Name", "<input type=\"text\" name=\"name\" value=\"" . htmlspecialchars($row["name"]) . "\" size=\"80\" />", 1);
	tr("NFO Datei", "<input type=radio name=nfoaction value='keep' checked>Aktuelle beibehalten<br>".
	"<input type=radio name=nfoaction value='update'>Ändern:<br><input type=file name=nfo size=60>", 1);
if ((strpos($row["ori_descr"], "<") === false) || (strpos($row["ori_descr"], "&lt;") !== false))
  $c = "";
else
  $c = " checked";

    tr("Bilder", "<input type=radio name=picaction value='keep' checked>Aktuelle beibehalten<br>"
	   ."<input type=radio name=picaction value='update'>Ändern (leer lassen, um Bilder zu löschen):<br>"
       ."$s<input type=\"file\" name=\"pic1\" size=\"80\"><br>(Optional. Wird oberhalb der "
       ."Torrentbeschreibung angezeigt. Max. Größe: ".mksizeint($GLOBALS["MAX_UPLOAD_FILESIZE"]).")<br><br>\n"
       ."<input type=\"file\" name=\"pic2\" size=\"80\"><br>(Optional. Wird oberhalb der "
       ."Torrentbeschreibung angezeigt. Max. Größe: ".mksizeint($GLOBALS["MAX_UPLOAD_FILESIZE"]).")\n", 1);
  
	tr("Beschreibung", "<textarea name=\"descr\" rows=\"15\" cols=\"80\">" . htmlspecialchars($row["ori_descr"]) . "</textarea>".
       "<br><input type=\"checkbox\" name=\"stripasciiart\" value=\"1\"> ASCII-Art automatisch entfernen" .
       "<br>(HTML ist <b>nicht</b> erlaubt. Klick <a href=tags.php>hier</a>, f&uuml;r die Ansicht des BB-Codes.)", 1);

	$s = "<select name=\"type\">\n";

	$cats = genrelist();
	foreach ($cats as $subrow) {
		$s .= "<option value=\"" . $subrow["id"] . "\"";
		if ($subrow["id"] == $row["category"])
			$s .= " selected=\"selected\"";
		$s .= ">" . htmlspecialchars($subrow["name"]) . "</option>\n";
	}

	$s .= "</select>\n";
	tr("Type", $s, 1);
	tr("Visible", "<input type=\"checkbox\" name=\"visible\"" . (($row["visible"] == "yes") ? " checked=\"checked\"" : "" ) . " value=\"1\" /> Visible on main page<br /><table border=0 cellspacing=0 cellpadding=0 width=420><tr><td class=embedded>Note that the torrent will automatically become visible when there's a seeder, and will become automatically invisible (dead) when there has been no seeder for a while. Use this switch to speed the process up manually. Also note that invisible (dead) torrents can still be viewed or searched for, it's just not the default.</td></tr></table>", 1);

	if ($CURUSER["class"] >= UC_MODERATOR)
		tr("Gebannt", "<input type=\"checkbox\" name=\"banned\"" . (($row["banned"] == "yes") ? " checked=\"checked\"" : "" ) . " value=\"1\" /> Diesen Torrent bannen", 1);

	print("<tr><td class=\"tablea\" colspan=\"2\" align=\"center\"><input type=\"submit\" value='Edit it!' style='height: 25px; width: 100px'> <input type=reset value='Revert changes' style='height: 25px; width: 100px'></td></tr>\n");
	print("</table>\n");
	print("</form>\n");
	print("<br>\n");
	print("<form method=\"post\" action=\"delete.php\">\n");
  print("<table border=\"0\" cellspacing=\"1\" cellpadding=\"4\" style=\"width:650px;\" class=\"tableinborder\">\n");
  print("<tr class=\"tabletitle\"><td colspan=\"2\"><span class=\"normalfont\"><center><b>Torrent löschen.</b> Grund:</b></center></span></td></tr>");
  print("<td class=\"tableb\"><input name=\"reasontype\" type=\"radio\" value=\"1\">&nbsp;Tot </td><td class=\"tablea\"> 0 Seeder, 0 Leecher = 0 Peers gesamt</td></tr>\n");
  print("<tr><td class=\"tableb\"><input name=\"reasontype\" type=\"radio\" value=\"2\">&nbsp;Doppelt</td><td class=\"tablea\"><input type=\"text\" size=\"60\" name=\"reason[]\"></td></tr>\n");
  print("<tr><td class=\"tableb\"><input name=\"reasontype\" type=\"radio\" value=\"3\">&nbsp;Nuked</td><td class=\"tablea\"><input type=\"text\" size=\"60\" name=\"reason[]\"></td></tr>\n");
  print("<tr><td class=\"tableb\"><input name=\"reasontype\" type=\"radio\" value=\"4\">&nbsp;Regelbruch</td><td class=\"tablea\"><input type=\"text\" size=\"60\" name=\"reason[]\">(req)</td></tr>");
  print("<tr><td class=\"tableb\"><input name=\"reasontype\" type=\"radio\" value=\"5\" checked>&nbsp;Anderer</td><td class=\"tablea\"><input type=\"text\" size=\"60\" name=\"reason[]\">(req)</td></tr>\n");
	print("<input type=\"hidden\" name=\"id\" value=\"$id\">\n");
	if (isset($_GET["returnto"]))
		print("<input type=\"hidden\" name=\"returnto\" value=\"" . htmlspecialchars($_GET["returnto"]) . "\" />\n");
  print("<td class=\"tablea\" colspan=\"2\" align=\"center\"><input type=submit value='Löschen!' style='height: 25px'></td></tr>\n");
  print("</table>");
	print("</form>\n");
	print("</p>\n");
}

stdfoot();

hit_end();

?>