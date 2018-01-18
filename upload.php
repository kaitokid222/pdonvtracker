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

userlogin();
loggedinorreturn();
stdhead("Upload");

if ($CURUSER["allowupload"] != "yes"){
	stdmsg("Sorry...", "Es ist Dir nicht gestattet, Torrents hochzuladen!");
	stdfoot();
	exit;
}

$s = "<select name=\"type\">\n<option value=\"0\">(ausw&auml;hlen)</option>\n";
$cats = genrelist();
foreach ($cats as $row)
	$s .= "<option value=\"" . $row["id"] . "\">" . htmlspecialchars($row["name"]) . "</option>\n";
$s .= "</select>\n";

echo "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" style=\"width:680px\" class=\"tableinborder\">\n".
	"    <tr class=\"tabletitle\" width=\"100%\">\n".
	"        <td width=\"100%\"><span class=\"normalfont\"><center><b> Torrent Upload </b></center></span></td>\n".
	"    </tr>\n".
	"    <tr>\n".
	"        <td width=\"100%\" class=\"tablea\"><center>\n".
	"            <p>Die Announce-URL des Trackers ist <b>" . $GLOBALS["ANNOUNCE_URLS"][0] . "</b></p>\n".
	"            <p><font color=\"red\">Beachte, dass Du nicht Deine eigene, mit Deinem PassKey versehene URL eintr&auml;gst.<br>Der PassKey des jew. Users wird vom Download-Script automatisch eingef&uuml;gt! Aus diesem Grund MUSST Du den Torrent nach erfolgtem Upload noch einmal vom Tracker runterladen und diesen dann zum Seedenverwenden.</font></p>\n".
	"            <p style=\"color:red;background-color:white;font-weight:bold;border:1px solid red;padding:5px;\">Bitte lies vor dem Benutzen dieses Formulares die <a href=\"rules.php\">Regeln</a> und das <a href=\"faq.php#up\">FAQ</a> zum Thema Uploaden!</p>\n".
	"            <table width=\"680px\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\" class=\"tableinborder\">\n".
	"            <form enctype=\"multipart/form-data\" action=\"takeupload.php\" method=\"post\">\n".
	"			 <input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"" . $GLOBALS["MAX_TORRENT_SIZE"] . "\" />\n";
					tr(".torrent", "<input type=\"file\" name=\"file\" size=\"90\">\n", 1);
					tr("Name", "<input type=\"text\" name=\"name\" size=\"90\" /><br />(Wird keine Name angegeben, wird der Dateiname benutzt. <b>Halte Dich bitte an die Richtlinien.</b>)\n", 1);
					tr("NFO", "<input type=\"file\" name=\"nfo\" size=\"90\"><br>(<b>Wird ben&ouml;tigt.</b> Nur Power User k&ouml;nnen NFO's lesen.)\n", 1);
					tr("Bild 1", "<input type=\"file\" name=\"pic1\" size=\"90\"><br>(Optional. Wird oberhalb der Torrentbeschreibung angezeigt. Max. Größe: ".mksizeint($GLOBALS["MAX_UPLOAD_FILESIZE"]).")\n", 1);
					tr("Bild 2", "<input type=\"file\" name=\"pic2\" size=\"90\"><br>(Optional. Wird oberhalb der Torrentbeschreibung angezeigt. Max. Größe: ".mksizeint($GLOBALS["MAX_UPLOAD_FILESIZE"]).")\n", 1);
					tr("Beschreibung", "<textarea name=\"descr\" rows=\"10\" cols=\"90\"></textarea><br><input type=\"checkbox\" name=\"stripasciiart\" value=\"1\" checked=\"checked\"> ASCII-Art automatisch entfernen<br><br>(HTML ist <b>nicht</b> erlaubt, benutze den <a href=tags.php>BBCode</a> zum Formatieren der Beschreibung)", 1);
					tr("Typ", $s, 1);
					tr("Absenden", "<input type=\"submit\" class=\"btn\" value=\"Upload!\" />\n", 1);

echo "            </form>\n".
	"            </table>\n".
	"        </td>\n".
	"    </tr>\n".
	"</table>\n";

stdfoot();
?>