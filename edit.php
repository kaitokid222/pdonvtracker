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

if($_SERVER["REQUEST_METHOD"] == "POST"){
	if(isset($_POST["action"], $_POST["id"])){
		$action = $_POST["action"];
		$id = $_POST["id"];
		if($action == "edit"){
			if(isset($_POST["name"], $_POST["descr"], $_POST["type"])){
				$name = $_POST["name"];
				$descr = $_POST["descr"];
				$type = $_POST["type"];
				$tupload = new tupload($GLOBALS["DB"]);
				$tupload->setUser($CURUSER);

				$sql = "SELECT torrents.owner, torrents.numpics, torrents.filename, torrents.save_as, torrents.activated, users.class FROM torrents LEFT JOIN users ON torrents.owner=users.id WHERE torrents.id = :id";
				$qry = $GLOBALS['DB']->prepare($sql);
				$qry->bindParam(':id', $id, PDO::PARAM_INT);
				$qry->execute();
				if($qry->rowCount())
					$row = $qry->Fetch(PDO::FETCH_ASSOC);
				else
					die();

				if ($CURUSER["id"] != $row["owner"] && !(get_user_class() >= UC_MODERATOR || ($row["activated"] == "no" && get_user_class() == UC_GUTEAM && $row["class"] < UC_UPLOADER)))
					stderr("Fehler","Das ist nicht Dein Torrent! Wie konnte das passieren?\n");

				$updateset = array();

				$fname = $row["filename"];
				preg_match('/^(.+)\.torrent$/si', $fname, $matches);
				$shortfname = $matches[1];
				$dname = $row["save_as"];

				$nfoaction = $_POST['nfoaction'];
				if ($nfoaction == "update"){
					$nfofile = $_FILES['nfo'];
					if(!$nfofile)
						die("No data " . var_dump($_FILES));
					if($nfofile['size'] > 65535)
						stderr("Fehler","NFO is too big! Max 65,535 bytes.");
					$nfofilename = $nfofile['tmp_name'];
					if(@is_uploaded_file($nfofilename) && @filesize($nfofilename) > 0){
						$nfo = str_replace("\x0d\x0d\x0a", "\x0d\x0a", file_get_contents($nfofilename));
						$updateset[] = "nfo = '" . $nfo . "'";
						// Create NFO image
						gen_nfo_pic($nfo, $GLOBALS["BITBUCKET_DIR"]."/nfo-" . $id . ".png");
					}
				}elseif($nfoaction == "remove"){
					$nfo = "";
					$updateset[] = "nfo = '" . $nfo . "'";
				}

				$picaction = $_POST['picaction'];
				if($picaction == "update"){
					if($row["numpics"] >0){
						for($I=1; $I<=$row["numpics"]; $I++){
							@unlink($GLOBALS["BITBUCKET_DIR"]."/t-" . $id . "-" . $I . ".jpg");
							@unlink($GLOBALS["BITBUCKET_DIR"]."/f-" . $id . "-" . $I . ".jpg");
						}
					}

					$picnum = 0;
					if($_FILES["pic1"]["name"] != ""){
						if($tupload->torrent_image_upload($_FILES["pic1"], $id, $picnum+1))
							$picnum++;
					}
						
					if($_FILES["pic2"]["name"] != ""){
						if($tupload->torrent_image_upload($_FILES["pic2"], $id, $picnum+1))
							$picnum++;
					}
					$updateset[] = "numpics = '" . $picnum . "'";
				}

				if(isset($_POST["stripasciiart"]) && $_POST["stripasciiart"] == "1")
					$descr = strip_ascii_art($descr);

				$updateset[] = "name = '" . $name . "'";
				$updateset[] = "search_text = '" . searchfield($shortfname . $dname . $fname) . "'";
				$updateset[] = "descr = '" . $descr . "'";
				$updateset[] = "ori_descr = '" . $descr . "'";
				$updateset[] = "category = '" . (0 + $type) . "'";
				if(get_user_class() >= UC_MODERATOR){
					if(isset($_POST["banned"]) && $_POST["banned"] > 0){
						$updateset[] = "banned = 'yes'";
						$_POST["visible"] = 0;
					}
					else
						$updateset[] = "banned = 'no'";
				}

				if($row["activated"] == "yes")
					$updateset[] = "visible = '" . (($_POST["visible"] > 0) ? "yes" : "no") . "'";

				$sql = "UPDATE torrents SET " . join(",", $updateset) . " WHERE id = :id";
				$qry = $GLOBALS['DB']->prepare($sql);
				$qry->bindParam(':id', $id, PDO::PARAM_INT);
				$qry->execute();

				write_log("torrentedit", "Der Torrent <a href=\"details.php?id=" . $id . "\">" . $id . " (" . $name . ")</a> wurde von '<a href=\"userdetails.php?id=" . $CURUSER["id"] . "\">" . $CURUSER["username"] . "</a>' bearbeitet.");

				if($tupload->get_uploaderrors_pic() !== false) {
					$o = "<p>Beim Hochladen der Vorschaubilder sind Fehler aufgetreten:</p><ul>";
					foreach($tupload->get_uploaderrors_pic() as $pic)
						foreach($pic as $error)
							$o .= "<li>" . $error . "</li>";
					$o .= "<p>Alle anderen Änderungen wurden jedoch übernommen. Bitte bearbeite den Torrent erneut, um neue Vorschaubilder hochzuladen.</p>";
					$o .= "<p><a href=\"details.php?id=" . $id . "&edited=1\">Weiter zur Detailansicht des Torrents</p>";
					$o .= "</ul>\n";
					stderr("Fehler beim Bilderupload", $o);
				}

				$returl = "details.php?id=" . $id . "&edited=1";
				if(isset($_POST["returnto"]))
					$returl .= "&returnto=" . urlencode($_POST["returnto"]);
				header("Refresh: 0; url=" . $returl);
			}else
				stderr("Fehler","Es fehlen Daten!");
		}elseif($action == "delete"){
			$qry = $GLOBALS['DB']->prepare('SELECT torrents.name,torrents.owner,torrents.seeders,torrents.activated,users.class FROM torrents LEFT JOIN users ON torrents.owner=users.id WHERE torrents.id = :id');
			$qry->bindParam(':id', $id, PDO::PARAM_INT);
			$qry->execute();
			if(!$qry->rowCount())
				stderr("Fehler","Ungültiger Query oder es gibt keinen Torrent mit der angegebenen Id");
			else
				$row = $qry->FetchAll()[0];

			if($CURUSER["id"] != $row["owner"] && !(get_user_class() >= UC_MODERATOR || ($row["activated"] == "no" && get_user_class() == UC_GUTEAM && $row["class"] < UC_UPLOADER)))
				stderr("Fehler","Dir gehört der Torrent nicht! Wie konnte das passieren?\n");

			if(isset($_POST["reasontype"]))
				$rt = 0 + $_POST["reasontype"];
			else
				$rt = false;

			if(!is_int($rt) || $rt < 1 || $rt > 5)
				stderr("Fehler","Ungültiger Grund (" . $rt . ").");

			if(!isset($_POST["reason"]) && $rt > 1)
				stderr("Fehler","Es wurde kein Grund angegeben");
			else
				$reason = $_POST["reason"];

			if($rt == 1)
				$reasonstr = "Tot: 0 Seeder, 0 Leecher = 0 Peers gesamt";
			elseif($rt == 2)
				$reasonstr = "Doppelt" . ($reason[0] ? (": " . trim($reason[0])) : "!");
			elseif($rt == 3)
				$reasonstr = "Nuked" . ($reason[1] ? (": " . trim($reason[1])) : "!");
			elseif ($rt == 4){
				if(!$reason[2])
					stderr("Fehler","Bitte beschreibe, welche Regel verletzt wurde.");
				$reasonstr = "NetVision Regeln verletzt: " . trim($reason[2]);
			}else{
				if(!$reason[3])
					stderr("Fehler","Bitte gebe einen Grund an, warum dieser Torrent gelöscht werden soll.");
				$reasonstr = trim($reason[3]);
			}
			deletetorrent($id, $row["owner"], $reasonstr);
			write_log("torrentdelete","Der Torrent " . $id . " (" . $row['name'] . ") wurde von '<a href=\"userdetails.php?id=" . $CURUSER['id'] . "\">" . $CURUSER['username'] . "</a>' gelöscht (" . $reasonstr . ")\n");
			stdhead("Torrent gelöscht!");
			if(isset($_POST["returnto"]))
				$ret = "<a href=\"" . htmlspecialchars($_POST["returnto"]) . "\">Gehe dorthin zurück, von wo Du kamst</a>";
			else
				$ret = "<a href=\"./\">Zurück zum Index</a>";
			echo "<h2>Torrent gel&ouml;scht!</h2>\n".
				"<p>" . $ret . "</p>\n";
			stdfoot();
		}else
			stderr("Fehler","unbekannte Aktion!");
	}else
		stderr("Fehler","Es fehlen Daten!");
}else{
	if(isset($_GET["id"]))
		$id = 0 + $_GET["id"];
	if(!$id)
		die();

	$sql = "SELECT torrents.*,users.class FROM torrents LEFT JOIN users ON torrents.owner=users.id WHERE torrents.id = :id";
	$qry = $GLOBALS['DB']->prepare($sql);
	$qry->bindParam(':id', $id, PDO::PARAM_INT);
	$qry->execute();
	if($qry->rowCount())
		$row = $qry->Fetch(PDO::FETCH_ASSOC);
	else
		die();

	$type = "<select name=\"type\">\n";
	$cats = genrelist();
	foreach($cats as $subrow){
		$type .= "<option value=\"" . $subrow["id"] . "\"";
		if($subrow["id"] == $row["category"])
			$type .= " selected=\"selected\"";
		$type .= ">" . htmlspecialchars($subrow["name"]) . "</option>\n";
	}
	$type .= "</select>\n";

	stdhead("Torrent \"" . $row["name"] . "\" bearbeiten");
	if(!isset($CURUSER) || !($CURUSER["id"] == $row["owner"] || get_user_class() >= UC_MODERATOR || ($row["activated"] == "no" && get_user_class() == UC_GUTEAM && $row["class"] < UC_UPLOADER))){
		echo "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" style=\"width:650px\" class=\"tableinborder\">\n".
			"    <tr class=\"tabletitle\" width=\"100%\">\n".
			"        <td width=\"100%\"><span class=\"normalfont\"><center><b>Du darfst diesen Torrent nicht bearbeiten </b></center></span></td> \n".
			"    </tr>\n".
			"    <tr>\n".
			"        <td width=\"100%\" class=\"tablea\">Du bist nicht der rechtm&auml;&szlig;ige Besitzer, oder Du bist nicht korrekt <a href=\"login.php?returnto=\"" . urlencode($_SERVER["REQUEST_URI"]) . "&amp;nowarn=1\">eingeloggt</a>. </td>\n".
			"    </tr>\n".
			"</table>\n";
	}else{
		echo "<form method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "\" enctype=\"multipart/form-data\">\n".
			"<input type=\"hidden\" name=\"action\" value=\"edit\">\n".
			"<input type=\"hidden\" name=\"id\" value=\"" . $id . "\">\n";
		if(isset($_GET["returnto"]))
			echo "<input type=\"hidden\" name=\"returnto\" value=\"" . htmlspecialchars($_GET["returnto"]) . "\" />\n";
		echo "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" style=\"width:650px\" class=\"tableinborder\">\n".
			"    <tr class=\"tabletitle\" width=\"100%\">\n".
			"        <td colspan=\"2\" width=\"100%\"><span class=\"normalfont\"><center><b>Torrent bearbeiten</b></center></span></td>\n".
			"    </tr>\n"; 
		tr("Torrent Name", "<input type=\"text\" name=\"name\" value=\"" . htmlspecialchars($row["name"]) . "\" size=\"80\" />", 1);
		tr("NFO Datei", "<input type=\"radio\" name=\"nfoaction\" value=\"keep\" checked=\"checked\">Aktuelle beibehalten<br><input type=\"radio\" name=\"nfoaction\" value=\"update\">Ändern:<br><input type=\"file\" name=\"nfo\" size=\"60\">", 1);
		tr("Bilder", "<input type=\"radio\" name=\"picaction\" value=\"keep\" checked=\"checked\">Aktuelle beibehalten<br><input type=\"radio\" name=\"picaction\" value=\"update\">Ändern (leer lassen, um Bilder zu löschen):<br><input type=\"file\" name=\"pic1\" size=\"80\"><br>(Optional. Wird oberhalb der Torrentbeschreibung angezeigt. Max. Größe: ".mksizeint($GLOBALS["MAX_UPLOAD_FILESIZE"]).")<br><br>\n<input type=\"file\" name=\"pic2\" size=\"80\"><br>(Optional. Wird oberhalb der Torrentbeschreibung angezeigt. Max. Größe: ".mksizeint($GLOBALS["MAX_UPLOAD_FILESIZE"]).")\n", 1);
		tr("Beschreibung", "<textarea name=\"descr\" rows=\"15\" cols=\"80\">" . htmlspecialchars($row["ori_descr"]) . "</textarea><br><input type=\"checkbox\" name=\"stripasciiart\" value=\"1\"> ASCII-Art automatisch entfernen<br>(HTML ist <b>nicht</b> erlaubt. Klick <a href=\"tags.php\">hier</a>, f&uuml;r die Ansicht des BB-Codes.)", 1);
		tr("Type", $type, 1);
		tr("Visible", "<input type=\"checkbox\" name=\"visible\"" . (($row["visible"] == "yes") ? " checked=\"checked\"" : "" ) . " value=\"1\" /> Visible on main page<br /><table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"420\"><tr><td class=\"embedded\">Note that the torrent will automatically become visible when there's a seeder, and will become automatically invisible (dead) when there has been no seeder for a while. Use this switch to speed the process up manually. Also note that invisible (dead) torrents can still be viewed or searched for, it's just not the default.</td></tr></table>", 1);
		if ($CURUSER["class"] >= UC_MODERATOR)
			tr("Gebannt", "<input type=\"checkbox\" name=\"banned\"" . (($row["banned"] == "yes") ? " checked=\"checked\"" : "" ) . " value=\"1\" /> Diesen Torrent bannen", 1);
		echo "    <tr>\n".
			"        <td class=\"tablea\" colspan=\"2\" align=\"center\"><input type=\"submit\" value=\"&Auml;ndern\" style=\"height: 25px; width: 100px\"> <input type=\"reset\" value=\"Verwerfen!\" style=\"height: 25px; width: 100px\"></td>\n".
			"    </tr>\n".
			"</table>\n".
			"</form>\n".
			"<br>\n".
			"<form method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "\">\n";
		if(isset($_GET["returnto"]))
			echo "<input type=\"hidden\" name=\"returnto\" value=\"" . htmlspecialchars($_GET["returnto"]) . "\" />\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"delete\">\n".
			"<input type=\"hidden\" name=\"id\" value=\"" . $id . "\">\n".
			"<table border=\"0\" cellspacing=\"1\" cellpadding=\"4\" style=\"width:650px;\" class=\"tableinborder\">\n".
			"    <tr class=\"tabletitle\">\n".
			"        <td colspan=\"2\"><span class=\"normalfont\"><center><b>Torrent löschen.</b> Grund:</b></center></span></td>\n".
			"    </tr>".
			"    <tr>".
			"        <td class=\"tableb\"><input name=\"reasontype\" type=\"radio\" value=\"1\">&nbsp;Tot </td><td class=\"tablea\"> 0 Seeder, 0 Leecher = 0 Peers gesamt</td>\n".
			"    </tr>\n".
			"    <tr>\n".
			"        <td class=\"tableb\"><input name=\"reasontype\" type=\"radio\" value=\"2\">&nbsp;Doppelt</td>\n".
			"        <td class=\"tablea\"><input type=\"text\" size=\"60\" name=\"reason[]\"></td>\n".
			"    </tr>\n".
			"    <tr>\n".
			"        <td class=\"tableb\"><input name=\"reasontype\" type=\"radio\" value=\"3\">&nbsp;Nuked</td>\n".
			"        <td class=\"tablea\"><input type=\"text\" size=\"60\" name=\"reason[]\"></td>\n".
			"    </tr>\n".
			"    <tr>\n".
			"        <td class=\"tableb\"><input name=\"reasontype\" type=\"radio\" value=\"4\">&nbsp;Regelbruch</td>\n".
			"        <td class=\"tablea\"><input type=\"text\" size=\"60\" name=\"reason[]\">(req)</td>\n".
			"    </tr>".
			"    <tr>\n".
			"        <td class=\"tableb\"><input name=\"reasontype\" type=\"radio\" value=\"5\" checked>&nbsp;Anderer</td>\n".
			"        <td class=\"tablea\"><input type=\"text\" size=\"60\" name=\"reason[]\">(req)</td>\n".
			"    </tr>\n".
			"    <tr>\n".
			"        <td class=\"tablea\" colspan=\"2\" align=\"center\"><input type=\"submit\" value=\"L&ouml;schen!\" style=\"height: 25px\"></td>\n".
			"    </tr>\n".
			"</table>\n".
			"</form>\n".
			"</p>\n";
	}
}
stdfoot();
?>