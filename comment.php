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

if(isset($_GET["action"]) && $_GET["action"] != "")
	$action = $_GET["action"];
else
	$action = "";

if($action == "add"){
	if($_SERVER["REQUEST_METHOD"] == "POST"){
		if(isset($_POST["tid"], $_POST["text"]) && $_POST["tid"] != 0 && $_POST["text"] != ""){
			$text = trim($_POST["text"]);
			$torrentid = $_POST["tid"];
		}else
			stderr("Fehler", "Eingabe ungültig!");
		$dt = get_date_time();
		$sql = "INSERT INTO comments (user, torrent, added, text, ori_text) VALUES (:uid, :tid, :dt, :text, :otext)";
		$qry = $GLOBALS["DB"]->prepare($sql);
		$qry->bindParam(':uid', $CURUSER["id"], PDO::PARAM_INT);
		$qry->bindParam(':tid', $torrentid, PDO::PARAM_INT);
		$qry->bindParam(':dt', $dt, PDO::PARAM_STR);
		$qry->bindParam(':text', $text, PDO::PARAM_STR);
		$qry->bindParam(':otext', $text, PDO::PARAM_STR);
		$qry->execute();
		$newid = $GLOBALS['DB']->lastInsertId();
		$sql = "UPDATE torrents SET comments = comments + 1 WHERE id = :tid";
		$qry = $GLOBALS["DB"]->prepare($sql);
		$qry->bindParam(':tid', $torrentid, PDO::PARAM_INT);
		$qry->execute();
		header("Refresh: 0; url=details.php?id=" . $torrentid . "&viewcomm=" . $newid . "#comm" . $newid);
	}else{
		$torrentid = 0 + $_GET["tid"];
		if(!is_valid_id($torrentid))
			stderr("Fehler", "Ungültige ID " . $torrentid . ".");

		$sql = "SELECT name FROM torrents WHERE id = :id";
		$qry = $GLOBALS['DB']->prepare($sql);
		$qry->bindParam(':id', $torrentid, PDO::PARAM_INT);
		$qry->execute();
		if(!$qry->rowCount())
			stderr("Fehler", "Kein Torrent mit der ID " . $torrentid . " vorhanden.");
		else
			$arr = $qry->Fetch(PDO::FETCH_ASSOC);
		if(strlen($arr["name"])>50)
			$arr["name"] = substr($arr["name"], 0, 50)."...";
		
		stdhead("Einen Kommentar für \"" . $arr["name"] . "\" hinzufügen");
		begin_frame("Einen Kommentar für \"" . htmlspecialchars($arr["name"]) . "\" hinzufügen", FALSE, "500px");
		echo "<form method=\"post\" action=\"comment.php?action=add\">\n".
			"<p>\n".
			"    <input type=\"hidden\" name=\"tid\" value=\"" . $torrentid . "\"/>\n".
			"    <textarea name=\"text\" rows=\"10\" cols=\"80\"></textarea>\n".
			"</p>\n".
			"<p align=\"center\">\n".
			"    <input type=\"button\" value=\"Smilie-Legende\" onclick=\"window.open('smilies.php','smilies','')\">\n".
			"    <input type=\"submit\" class=\"btn\" value=\"Und ab!\" />\n".
			"</p>\n".
			"</form>\n";
		end_frame();

		$sql = "SELECT comments.id, text, user, comments.added, editedby, editedat, avatar, warned, username, users.added as uadded, title, enabled, class, donor FROM comments LEFT JOIN users ON comments.user = users.id WHERE torrent = :id ORDER BY comments.id DESC LIMIT 5";
		$qry = $GLOBALS['DB']->prepare($sql);
		$qry->bindParam(':id', $torrentid, PDO::PARAM_INT);
		$qry->execute();
		if($qry->rowCount()){
			$allrows = $qry->FetchAll(PDO::FETCH_ASSOC);
			begin_frame("Neueste Kommentare zuerst, in umgekehrter Reihenfolge");
			commenttable($allrows);
			end_frame();
		}
		stdfoot();
	}
}elseif($action == "edit"){
	$commentid = 0 + $_GET["cid"];
	if(!is_valid_id($commentid))
		stderr("Fehler", "Ungültige ID " . $commentid . ".");

	$sql = "SELECT c.*, t.id as tid, t.name FROM comments AS c JOIN torrents AS t ON c.torrent = t.id WHERE c.id= :id";
	$qry = $GLOBALS['DB']->prepare($sql);
	$qry->bindParam(':id', $commentid, PDO::PARAM_INT);
	$qry->execute();
	if($qry->rowCount())
		$arr = $qry->Fetch(PDO::FETCH_ASSOC);
	else
		stderr("Fehler", "Ungültige ID " . $commentid . ".");

	if($arr["user"] != $CURUSER["id"] && get_user_class() < UC_MODERATOR)
		stderr("Fehler", "Zugriff verweigert.");

	if($_SERVER["REQUEST_METHOD"] == "POST"){
		$text = ((isset($_POST["text"])) ? $_POST["text"] : "");
		$returnto =((isset($_POST["returnto"])) ? $_POST["returnto"] : "");

		if($text == "")
			stderr("Fehler", "Der Kommentar-Text darf nicht leer sein!");
		$editedat = get_date_time();

		$sql = "UPDATE comments SET text= :txt, editedat= :editedat, editedby= :eby WHERE id= :id";
		$qry = $GLOBALS['DB']->prepare($sql);
		$qry->bindParam(':txt', $text, PDO::PARAM_STR);
		$qry->bindParam(':editedat', $editedat, PDO::PARAM_STR);
		$qry->bindParam(':eby', $CURUSER["id"], PDO::PARAM_INT);
		$qry->bindParam(':id', $commentid, PDO::PARAM_INT);
		$qry->execute();

		if($returnto != "")
			header("Location: " . $returnto);
		else
			header("Location: " . $BASEURL . "/" . $_SERVER['PHP_SELF'] . "?tid=" . $arr["tid"]);
	}else{
		stdhead("Kommentar für \"" . $arr["name"] . "\" bearbeiten");
		begin_frame("Kommentar für \"" . htmlspecialchars($arr["name"]) . "\" bearbeiten", FALSE, "500px");
		echo "<form method=\"post\" action=\"comment.php?action=edit&amp;cid=" . $commentid . "\">\n".
			"    <input type=\"hidden\" name=\"returnto\" value=\"" . $_SERVER["HTTP_REFERER"] . "\" />\n".
			"    <input type=\"hidden\" name=\"cid\" value=\"" . $commentid . "\" />\n".
			"    <textarea name=\"text\" rows=\"10\" cols=\"80\">" . htmlspecialchars(stripslashes($arr["text"])) . "</textarea></p>\n".
			"    <p align=\"center\">\n".
			"        <input type=\"button\" value=\"Smilie-Legende\" onclick=\"window.open('smilies.php','smilies','')\">&nbsp;<input type=\"submit\" class=\"btn\" value=\"Und ab!\" />\n".
			"    </p>\n".
			"</form>\n";
		end_frame();
		stdfoot();
	}
}elseif($action == "delete"){
	if(get_user_class() < UC_MODERATOR)
		stderr("Fehler", "Zugriff verweigert.");

	$commentid = 0 + $_GET["cid"];
	if(!is_valid_id($commentid))
		stderr("Fehler", "Ungültige ID " . $commentid . ".");

	$sure = ((isset($_GET["sure"])) ? $_GET["sure"] : 0);
	if($sure < 1){
		$referer = $_SERVER["HTTP_REFERER"];
		stderr("Kommentar löschen", "Du bist im Begriff, einen Kommentar zu Löschen. Klicke <a href=\"?action=delete&cid=" . $commentid . "&sure=1" . ($referer ? "&returnto=" . urlencode($referer) : "") . "\">hier</a> wenn Du Dir sicher bist.");
	}

	$sql = "SELECT torrent FROM comments WHERE id= :id";
	$qry = $GLOBALS['DB']->prepare($sql);
	$qry->bindParam(':id', $commentid, PDO::PARAM_INT);
	$qry->execute();
	$arr = $qry->Fetch(PDO::FETCH_ASSOC);
	$torrentid = $arr["torrent"];

	$sql = "DELETE FROM comments WHERE id= :id";
	$qry = $GLOBALS['DB']->prepare($sql);
	$qry->bindParam(':id', $commentid, PDO::PARAM_INT);
	$qry->execute();
	if($torrentid > 0 && $qry->rowCount()){
		$sql = "UPDATE torrents SET comments = comments - 1 WHERE id = :id";
		$qry = $GLOBALS['DB']->prepare($sql);
		$qry->bindParam(':id', $torrentid, PDO::PARAM_INT);
		$qry->execute();
	}

	$returnto = ((isset($_GET["returnto"])) ? $_GET["returnto"] : $_SERVER['PHP_SELF']);
	if($returnto)
		header("Location: " . $returnto);
	else
		header("Location: " . $BASEURL . "/" . $_SERVER['PHP_SELF'] . "?tid=" . $torrentid);
}elseif($action == "vieworiginal"){
	if(get_user_class() < UC_MODERATOR)
		stderr("Fehler", "Zugriff verweigert.");

	$commentid = 0 + $_GET["cid"];
	if(!is_valid_id($commentid))
		stderr("Fehler", "Ungültige ID " . $commentid . ".");

	$sql = "SELECT c.*, t.name FROM comments AS c JOIN torrents AS t ON c.torrent = t.id WHERE c.id= :id";
	$qry = $GLOBALS['DB']->prepare($sql);
	$qry->bindParam(':id', $commentid, PDO::PARAM_INT);
	$qry->execute();
	if($qry->rowCount())
		$arr = $qry->Fetch(PDO::FETCH_ASSOC);
	else
		stderr("Fehler", "Ungültige ID " . $commentid . ".");

	$returnto = ((isset($_GET["returnto"])) ? $_GET["returnto"] : $_SERVER['PHP_SELF']);
	if($returnto)
		$rt_str = "<br><br><center>(<a href=" . $returnto . ">Zurück</a>)</center>\n";
	else
		$rt_str = "";

	stdhead("Originaler Kommentar");
	begin_frame("Ursprünglicher Inhalt des Kommentars #" . $commentid, FALSE, "500px");
	begin_table();
	echo "<center>" . htmlspecialchars(stripslashes($arr["ori_text"])) . "</center>\n" . $rt_str;
	end_table();
	end_frame();

	$returnto = $_SERVER["HTTP_REFERER"];
	// $returnto = "details.php?id=$torrentid&amp;viewcomm=$commentid#$commentid";
	if($returnto)
		print("");

	stdfoot();
	die;
}else
	stderr("Fehler", "Unbekannte Aktion " . $action);
die;
?>