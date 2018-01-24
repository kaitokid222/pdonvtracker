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

$id = $_GET["id"];
$id = 0 + $id;
if(!isset($id) || !$id)
	die();

$t_query = "SELECT torrents.seeders, torrents.banned, torrents.leechers, torrents.info_hash, ";
$t_query .= "torrents.filename, LENGTH(torrents.nfo) AS nfosz, UNIX_TIMESTAMP() - UNIX_TIMESTAMP(";
$t_query .= "torrents.last_action) AS lastseed, torrents.name, ";
$t_query .= "torrents.owner, torrents.gu_agent, torrents.save_as, torrents.descr, torrents.visible, torrents.size, torrents.activated, ";
$t_query .= "torrents.added, torrents.views, torrents.hits, torrents.times_completed, torrents.id, ";
$t_query .= "torrents.type, torrents.numfiles, torrents.numpics, categories.name AS cat_name, users.username, users.class FROM ";
$t_query .= "torrents LEFT JOIN categories ON torrents.category = categories.id LEFT JOIN users ON ";
$t_query .= "torrents.owner = users.id WHERE torrents.id = :id";

$qry = $GLOBALS['DB']->prepare($t_query);
$qry->bindParam(':id', $id, PDO::PARAM_INT);
$qry->execute();
$row = $qry->Fetch(PDO::FETCH_ASSOC);

$owned = $moderator = 0;
if($CURUSER["id"] == $row["owner"])
	$owned = 1;
elseif(get_user_class() >= UC_MODERATOR || ($row["activated"] == "no" && get_user_class() == UC_GUTEAM && $row["class"] < UC_UPLOADER))
	$owned = $moderator = 1;

if(!$row || ($row["banned"] == "yes" && !$moderator))
	stderr("Fehler", "Es existiert kein Torrent mit der ID " . $id . ".");

if(!$owned && $row["activated"] != "yes")
	stderr("Fehler", "Es existiert kein Torrent mit der ID " . $id . ".");

$rating = new rating($GLOBALS["DB"]);
if($_SERVER["REQUEST_METHOD"] == "POST"){
	if(isset($_POST["rating"])){
		$wantRating = $_POST["rating"];
		if($wantRating > 5 || $wantRating < 1)
			stderr("Fehler","Du kannst nicht mit Null stimmen!");
		else{
			$rating->vote($id,$CURUSER["id"],$wantRating);
			header("Refresh: 0; url=details.php?id=" . $id . "&rated=1");
		}
	}
}


if(isset($_GET["hit"])){
	$sql = "UPDATE torrents SET views = views + 1 WHERE id = :id";
	$qry = $GLOBALS['DB']->prepare($sql);
	$qry->bindParam(':id', $id, PDO::PARAM_INT);
	$qry->execute();
	if(isset($_GET["tocomm"]))
		header("Location: details.php?id=" . $id . "&page=0#startcomments");
	elseif(isset($_GET["filelist"]))
		header("Location: details.php?id=" . $id . "&filelist=1#filelist");
	elseif(isset($_GET["toseeders"]))
		header("Location: details.php?id=" . $id . "&dllist=1#seeders");
	elseif(isset($_GET["todlers"]))
		header("Location: details.php?id=" . $id . "&dllist=1#leechers");
	elseif(isset($_GET["tosnatchers"]))
		header("Location: details.php?id=" . $id . "&snatcher=1#snatcher");
	else
		header("Location: details.php?id=" . $id);
	exit();
}

if(isset($_GET["activate"]) && $moderator && $row["activated"] != "yes"){
	$sql = "UPDATE `torrents` SET `activated`= 'yes',`added`= NOW() WHERE `id`= :id";
	$qry = $GLOBALS['DB']->prepare($sql);
	$qry->bindParam(':id', $row["id"], PDO::PARAM_INT);
	$qry->execute();
	sendPersonalMessage(0, $row["owner"], "Dein Torrent wurde von einem Moderator freigeschaltet!", "Dein Torrent \"[url=" . $DEFAULTBASEURL . "/details.php?id=" . $row["id"] . "]" . $row["name"] . "[/url]\" wurde von [url=" . $DEFAULTBASEURL . "/userdetails.php?id=" . $CURUSER["id"] . "]" . $CURUSER["username"] . "[/url] freigeschaltet. Du musst nun die Torrent-Datei erneut vom Tracker herunterladen, und kannst dann mit dem Seeden beginnen.\n\nBei Fragen lies bitte zuerst das [url=" . $DEFAULBASEURL . "/faq.php]FAQ[/url]!", PM_FOLDERID_SYSTEM);
	if(get_cur_wait_time($row["owner"])){
		$sql = "INSERT INTO `nowait` (user_id, torrent_id, status, grantor, msg) VALUES (:uid, :tid, 'granted', :grantor, 'Automatische Wartezeitaufhebung durch Torrent-Freischaltung')";
		$qry = $GLOBALS['DB']->prepare($sql);
		$qry->bindParam(':uid', $row["owner"], PDO::PARAM_INT);
		$qry->bindParam(':tid', $row["id"], PDO::PARAM_INT);
		$qry->bindParam(':grantor', $CURUSER["id"], PDO::PARAM_INT);;
		$qry->execute();
	}
	write_log("torrentgranted", "Der Torrent <a href=\"details.php?id=" . $row["id"] . "\">" . htmlspecialchars($row["name"]) . "</a> wurde von '<a href=\"userdetails.php?id=$CURUSER[id]\">$CURUSER[username]</a>' freigeschaltet.");
	stderr("Torrent freigeschaltet", "Der Torrent wurde freigeschaltet, und ist nun über die Torrent-Suche auffindbar. Ebenso kann der Besitzer nun beginnen, den Torrent zu seeden. Eine Persönliche Nachricht wurde an den Uploader versendet, die ihn über die Freischaltung informiert.");
}

if(isset($_GET["agenttakeover"]) && $_GET["agenttakeover"] == "acquire" && $row["gu_agent"] == 0 && $moderator && $row["activated"] != "yes") {
	$row["gu_agent"] = $CURUSER["id"];
	$sql = "UPDATE `torrents` SET `gu_agent`= :gua WHERE `id`= :tid";
	$qry = $GLOBALS['DB']->prepare($sql);
	$qry->bindParam(':gua', $CURUSER["id"], PDO::PARAM_INT);
	$qry->bindParam(':tid', $row["id"], PDO::PARAM_INT);
	$qry->execute();
}

if(isset($_GET["agenttakeover"]) && $_GET["agenttakeover"] == "release" && $row["gu_agent"] == $CURUSER["id"] && $moderator && $row["activated"] != "yes") {
	$row["gu_agent"] = 0;
	$sql = "UPDATE `torrents` SET `gu_agent`=0 WHERE `id`= :tid";
	$qry = $GLOBALS['DB']->prepare($sql);
	$qry->bindParam(':tid', $row["id"], PDO::PARAM_INT);
	$qry->execute();
}

if(!isset($_GET["page"])){
	stdhead("Details zu Torrent \"" . $row["name"] . "\"");
	$spacer = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

	if(!isset($_GET["snatcher"]))
		$snatchersmax = "LIMIT 10";
	else
		$snatchersmax = "";

	$sql = "SELECT DISTINCT(user_id) as id, username, class, peers.id as peerid FROM completed,users LEFT JOIN peers ON peers.userid=users.id AND peers.torrent= :id AND peers.seeder='yes' WHERE completed.user_id=users.id AND completed.torrent_id= :id ORDER BY complete_time DESC " . $snatchersmax;
	$qry = $GLOBALS['DB']->prepare($sql);
	$qry->bindParam(':id', $id, PDO::PARAM_INT);
	$qry->execute();
	if($qry->rowCount()){
		$data = $qry->FetchAll(PDO::FETCH_ASSOC);
		$last10users = "";
		foreach($data as $arr){
			if($last10users != "")
				$last10users .= ", ";
			$arr["username"] = "<font class=\"" . get_class_color($arr["class"]) . "\">" . $arr["username"] . "</font>";
			if($arr["peerid"] > 0){
				$arr["username"] = "<b>" . $arr["username"] . "</b>";
			} 
			$last10users .= "<a href=\"userdetails.php?id=" . $arr["id"] . "\">" . $arr["username"] . "</a>";
		} 
		$last10users .= "<br/><br/>(Fettgedruckte User seeden noch)";
	}else{
		$last10users = "Diesen Torrent hat noch niemand fertiggestellt.";
	}


	if(isset($_GET["edited"])){
		begin_frame("Erfolgreich bearbeitet!", false, "650px");
		echo "<p>Der Torrent wurde erfolgreich geändert. Die Änderungen sind sofort für andere sichtbar.</p>";
		if(isset($_GET["returnto"]))
			echo "<p><b>Gehe dorthin zur&uuml;ck, von <a href=\"" . htmlspecialchars($_GET["returnto"]) . "\">wo Du gekommen bist</a>.</b></p>\n";
		end_frame();
	}elseif(isset($_GET["searched"])){
		begin_frame("Suche", false, "650px");
		echo "<p>Deine Suche nach \"" . htmlspecialchars($_GET["searched"]) . "\" hat ein einzelnes Ergebnis zur&uuml;ckgegeben:</p>\n";
		end_frame();
	}elseif(isset($_GET["rated"])){
		begin_frame("Bewertung hinzugef&uuml;gt!", false, "650px");
		echo "<p>Deine Bewertung wurde gespeichert. Du kannst nun die Ergebnisse ansehen.</p>\n";
		end_frame();
	}
	$s = $row["name"];
	$gu_str = (($row["class"] < UC_UPLOADER) ? "<font color=\"red\">(Gast-Upload)</font>" : "");
	echo "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" style=\"width:650px\" class=\"tableinborder\">\n".
		"    <tr>\n".
		"        <td class=\"tabletitle\" width=\"100%\" style=\"text-align:center\"><img src=\"" . $GLOBALS["PIC_BASE_URL"] . "viewmag.png\" width=\"22\" height=\"22\" alt=\"\" style=\"vertical-align: middle;\"> <b>Details zu " . $row["name"] . $gu_str . "</b></td>\n".
		"    </tr>\n".
		"    <tr>\n".
		"        <td width=\"100%\" class=\"tablea\">\n".
		"            <table width=\"750px\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\" class=\"tableinborder\">\n";

	$url = "edit.php?id=" . $row["id"];
	$keepget = "";
	if(isset($_GET["returnto"])){
		$addthis = "&amp;returnto=" . urlencode($_GET["returnto"]);
		$url .= $addthis;
		$keepget .= $addthis;
	}
	$editlink = "a href=\"" . $url . "\" class=\"sublink\"";

	$waittime = get_wait_time($CURUSER["id"], $id);

	if($row["activated"] != "yes"){
		if($moderator){
			echo "                <tr>\n".
				"                    <td class=\"tableb\" width=\"1%\">Freischalten</td>\n".
				"                    <td class=\"tableb\" width=\"99%\" style=\"text-align:left\">";
			if($row["gu_agent"] > 0){
				$sql = "SELECT `username` FROM `users` WHERE `id`= :id";
				$qry = $GLOBALS['DB']->prepare($sql);
				$qry->bindParam(':id', $row["gu_agent"], PDO::PARAM_INT);
				$qry->execute();
				$gu_agent = $qry->Fetch(PDO::FETCH_ASSOC);
				echo "<font color=\"red\">Dieser Gast-Upload wird bereits von <b><a href=\"userdetails.php?id=".$row["gu_agent"]."\">".htmlspecialchars($gu_agent["username"])."</a></b> bearbeitet. ";
				if($row["gu_agent"] == $CURUSER["id"]){
					echo "(<a class=\"index\" href=\"details.php?id=" . $row["id"] . "&amp;agenttakeover=release\">Bearbeitung abgeben</a>)";
				}
				echo "<br>";
			}else{
				echo "<a class=\"index\" href=\"details.php?id=" . $row["id"] . "&amp;agenttakeover=acquire\">Bearbeitung dieses Gastuploads übernehmen</a><br>";
			}
			echo "<a class=\"index\" href=\"details.php?id=" . $row["id"] . "&amp;activate=1\">Torrent freischalten</a></td>\n.".
				"                </tr>\n";
		}else{
			echo "                <tr>\n".
				"                    <td class=\"tableb\" width=\"1%\">Freischalten</td>\n".
				"                    <td class=\"tableb\" width=\"99%\" style=\"text-align:left\">Dieser Torrent muss erst von einem Moderator freigeschaltet werden, bevor er sichtbar wird.</td>\n".
				"                </tr>\n";
		}
	}elseif($waittime){
		echo "                <tr>\n".
			"                    <td class=\"tableb\" width=\"1%\">Herunterladen</td>\n".
			"                    <td class=\"tableb\" width=\"99%\" style=\"text-align:left\">Dieser Torrent kann erst nach Ablauf der Wartezeit heruntergeladen werden.</td>\n".
			"                </tr>\n";
	}else{
		if($GLOBALS["DOWNLOAD_METHOD"] == DOWNLOAD_REWRITE)
			$download_url = "download/" . $id . "/" . rawurlencode($row["filename"]);
		else
			$download_url = "download.php?torrent=" . $id;
		echo "                <tr>\n".
			"                    <td class=\"tableb\" width=\"1%\">Herunterladen</td>\n".
			"                    <td class=\"tableb\" width=\"99%\" style=\"text-align:left\"><a class=\"index\" href=\"" . $download_url . "\">" . htmlspecialchars($row["filename"]) . "</a></td>\n".
			"                </tr>\n";
		if($CURUSER["wgeturl"] == "yes"){
			$wget_url = "wget --header='Cookie: uid=" . $CURUSER["id"] . "; pass=" . $CURUSER["passhash"] . "' '" . $BASEURL . "/" . $download_url . "' -O '" . htmlspecialchars($row["filename"]) . "'";
			echo "                <tr>\n".
				"                    <td class=\"tableb\" width=\"1%\">wget-Kommando</td>\n".
				"                    <td class=\"tableb\" width=\"99%\" style=\"text-align:left\"><input type=\"text\" readonly=\"readonly\" size=\"80\" value=\"" . htmlspecialchars($wget_url) . "\"></td>\n".
				"                </tr>\n";
		}
	}

	echo "                <tr>\n".
		"                    <td class=\"tableb\" width=\"1%\">Hash-Wert</td>\n".
		"                    <td class=\"tableb\" width=\"99%\" style=\"text-align:left\">" . $row["info_hash"] . "</td>\n".
		"                </tr>\n";

	if($waittime){
		echo "                <tr>\n".
			"                    <td class=\"tableb\" width=\"1%\">Hash-Wert</td>\n".
			"                    <td class=\"tableb\" width=\"99%\" style=\"text-align:left\">";
		if($row["activated"] != "yes" && $owned)
			echo "Du benötigst eine Wartezeitaufhebung für diesen Torrent. Diese wird beim Freischalten des Torrents jedoch automatisch erteilt.";
		else{
			if($waittime > 2){
				echo "<form action=\"requestnowait.php\" method=\"post\">Antragsgrund: ".
					"<input type=\"hidden\" name=\"id\" value=\"" . $id . "\">".
					"<input type=\"text\" size=\"60\" maxlength=\"2000\" name=\"msg\">".
					"<input type=\"submit\" value=\"Beantragen\">".
					"</form><br><a href=\"faq.php#dlf\">Bitte beachte die Regeln zur Wartezeitaufhebung!</a>";
			}elseif($waittime){
				echo "Du musst noch min. 3 Stunden Wartezeit übrig haben, um eine Aufhebung beantragen zu können. (<a href=\"faq.php#dlf\">Hilfe</a>)";
			}
		}
		echo "</td>\n".
			"                </tr>\n";
	}

	if(!empty($row["descr"])){
		if($row["numpics"] > 0){
			$img_prev = "[center]";
			for($I = 1; $I <= $row["numpics"]; $I++)
				$img_prev .= "[url=" . $DEFAULTBASEURL . "/" . $GLOBALS["BITBUCKET_DIR"] . "/f-" . $id . "-" . $I . ".jpg][img=" . $DEFAULTBASEURL . "/" . $GLOBALS["BITBUCKET_DIR"] . "/t-" . $id . "-" . $I . ".jpg][/url] ";
			$img_prev .= "[/center]\n\n";
			$row["descr"] = $img_prev . $row["descr"];
		}
		echo "                <tr>\n".
			"                    <td class=\"tableb\" width=\"1%\">Beschreibung</td>\n".
			"                    <td class=\"tableb\" width=\"99%\" style=\"text-align:left\">" . format_comment($row["descr"]) . "</td>\n".
			"                </tr>\n";
	}

	if($row["nfosz"] > 0){
		echo "                <tr>\n".
			"                    <td class=\"tableb\" width=\"1%\">NFO</td>\n".
			"                    <td class=\"tableb\" width=\"99%\" style=\"text-align:left\"><a href=\"viewnfo.php?id=" . $row["id"] . "\"><b>NFO anzeigen</b></a> (" . mksize($row["nfosz"]) . ")</td>\n".
			"                </tr>\n";
	}

	if($row["visible"] == "no"){
		echo "                <tr>\n".
			"                    <td class=\"tableb\" width=\"1%\">Sichtbar</td>\n".
			"                    <td class=\"tableb\" width=\"99%\" style=\"text-align:left\"><b>Nein</b> (Tot)</td>\n".
			"                </tr>\n";
	}

	if($moderator){
		echo "                <tr>\n".
			"                    <td class=\"tableb\" width=\"1%\">Gebannt</td>\n".
			"                    <td class=\"tableb\" width=\"99%\" style=\"text-align:left\">" . $row["banned"] . "</td>\n".
			"                </tr>\n";
	}
	$cname = ((isset($row["cat_name"])) ? $row["cat_name"] : "(nicht ausgewählt)");
	$kg_str = ((isset($keepget)) ? $keepget : "");
	$sna_left_str = ((!isset($_GET["snatcher"])) ? "<a name=\"snatcher\"></a>Letzte 10<br /><a href=\"details.php?id=" . $id . "&amp;snatcher=1" . $kg_str . "#snatcher\" class=\"sublink\">[Alle anzeigen]</a>" : "<a name=\"snatcher\"></a>Fertiggestellt<br />(Benutzer)<br /><a href=\"details.php?id=" . $id . $kg_str . "#snatcher\" class=\"sublink\">[Liste verbergen]</a>" );
	$uprow = ((isset($row["username"])) ? ("<a href=\"userdetails.php?id=" . $row["owner"] . "\"><b>" . htmlspecialchars($row["username"]) . "</b></a>") : "<i>Unbekannt</i>");
	if($owned)
		$uprow .= " " . $spacer . "<" . $editlink . "><b>[Diesen Torrent bearbeiten]</b></a>";
	$keepget = "";
	echo "                <tr>\n".
		"                    <td class=\"tableb\" width=\"15%\">Typ</td>\n".
		"                    <td class=\"tableb\" width=\"85%\" style=\"text-align:left\">" . $cname . "</td>\n".
		"                </tr>\n".
		"                <tr>\n".
		"                    <td class=\"tableb\" width=\"1%\">Letzter Seeder</td>\n".
		"                    <td class=\"tableb\" width=\"99%\" style=\"text-align:left\">Letzte Aktivität ist " . mkprettytime($row["lastseed"]) . " her</td>\n".
		"                </tr>\n".
		"                <tr>\n".
		"                    <td class=\"tableb\" width=\"1%\">Größe</td>\n".
		"                    <td class=\"tableb\" width=\"99%\" style=\"text-align:left\">" . mksize($row["size"]) . " (" . number_format($row["size"]) . " Bytes)" . "</td>\n".
		"                </tr>\n".
		"                <tr>\n".
		"                    <td class=\"tableb\" width=\"1%\">Hinzugefügt</td>\n".
		"                    <td class=\"tableb\" width=\"99%\" style=\"text-align:left\">" . $row["added"] . "</td>\n".
		"                </tr>\n".
		"                <tr>\n".
		"                    <td class=\"tableb\" width=\"1%\">Bewertung</td>\n".
		"                    <td class=\"tableb\" width=\"99%\" style=\"text-align:left\">" . $rating->output($id, $CURUSER["id"]) . "</td>\n".
		"                </tr>\n".
		"                <tr>\n".
		"                    <td class=\"tableb\" width=\"1%\">Aufrufe</td>\n".
		"                    <td class=\"tableb\" width=\"99%\" style=\"text-align:left\">" . $row["views"] . "</td>\n".
		"                </tr>\n".
		"                <tr>\n".
		"                    <td class=\"tableb\" width=\"1%\">Hits</td>\n".
		"                    <td class=\"tableb\" width=\"99%\" style=\"text-align:left\">" . $row["hits"] . "</td>\n".
		"                </tr>\n".
		"                <tr>\n".
		"                    <td class=\"tableb\" width=\"1%\">Fertiggestellt</td>\n".
		"                    <td class=\"tableb\" width=\"99%\" style=\"text-align:left\">" . $row["times_completed"] . " mal</td>\n".
		"                </tr>\n".
		"                <tr>\n".
		"                    <td class=\"tableb\" width=\"1%\">" . $sna_left_str . "</td>\n".
		"                    <td class=\"tableb\" width=\"99%\" style=\"text-align:left\">" . $last10users . "</td>\n".
		"                </tr>\n".
		"                <tr>\n".
		"                    <td class=\"tableb\" width=\"1%\">Hochgeladen von</td>\n".
		"                    <td class=\"tableb\" width=\"99%\" style=\"text-align:left\">" . $uprow . "</td>\n".
		"                </tr>\n";
	if($row["type"] == "multi"){
		if(!isset($_GET["filelist"])){
			echo "                <tr>\n".
				"                    <td class=\"tableb\" width=\"1%\">Anzahl Dateien<br /><a href=\"details.php?id=" . $id . "&amp;filelist=1" . $keepget . "#filelist\" class=\"sublink\">[Liste anzeigen]</a></td>\n".
				"                    <td class=\"tableb\" width=\"99%\" style=\"text-align:left\">" . $row["numfiles"] . " Dateien</td>\n".
				"                </tr>\n";
		}else{
			$sql = "SELECT * FROM files WHERE torrent = :id ORDER BY id";
			$qry = $GLOBALS['DB']->prepare($sql);
			$qry->bindParam(':id', $id, PDO::PARAM_INT);
			$qry->execute();
			$data = $qry->FetchAll(PDO::FETCH_ASSOC);
			$s = "<table class=\"tableinborder\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">";
			$s .= "<tr>"."<td class=\"tablecat\">Pfad</td>"."<td class=\"tablecat\" align=\"right\">Größe</td>"."</tr>";
			foreach($data as $subrow)
				$s .= "<tr>"."<td class=\"tablea\">" . $subrow["filename"] . "</td>"."<td class=\"tableb\" align=\"right\">" . mksize($subrow["size"]) . "</td>"."</tr>";
			$s .= "</table>\n";
			echo "                <tr>\n".
				"                    <td class=\"tableb\" width=\"1%\">Anzahl Dateien</td>\n".
				"                    <td class=\"tableb\" width=\"99%\" style=\"text-align:left\">" . $row["numfiles"] . " Dateien</td>\n".
				"                </tr>\n".
				"                <tr>\n".
				"                    <td class=\"tableb\" width=\"1%\">Anzahl Dateien<br /><a name=\"filelist\">Dateiliste</a><br /><a href=\"details.php?id=" . $id . $keepget . "\" class=\"sublink\">[Liste verbergen]</a></td>\n".
				"                    <td class=\"tableb\" width=\"99%\" style=\"text-align:left\">" . $s . "</td>\n".
				"                </tr>\n";
		}
	}

	if(!isset($_GET["dllist"])){
		echo "                <tr>\n".
			"                    <td class=\"tableb\" width=\"1%\">Peers<br /><a href=\"details.php?id=" . $id . "&amp;dllist=1" . $keepget . "#seeders\" class=\"sublink\">[Liste anzeigen]</a></td>\n".
			"                    <td class=\"tableb\" width=\"99%\" style=\"text-align:left\">" . $row["seeders"] . " Seeder, " . $row["leechers"] . " Leecher = " . ($row["seeders"] + $row["leechers"]) . " Peer(s) gesamt</td>\n".
			"                </tr>\n";
	}else{
		$downloaders = array();
		$seeders = array();
		$sql = "SELECT peer_id, seeder, ip, port, traffic.uploaded AS uploaded, traffic.downloaded AS downloaded, traffic.downloadtime AS downloadtime, traffic.uploadtime AS uploadtime, to_go, UNIX_TIMESTAMP(started) AS st, connectable, agent, UNIX_TIMESTAMP(last_action) AS la, peers.userid FROM peers LEFT JOIN traffic ON peers.userid = traffic.userid AND peers.torrent = traffic.torrentid WHERE torrent = :id";
		$qry = $GLOBALS['DB']->prepare($sql);
		$qry->bindParam(':id', $id, PDO::PARAM_INT);
		$qry->execute();
		$subres = $qry->FetchAll(PDO::FETCH_ASSOC);
		foreach($subres as $subrow){
			if($subrow["seeder"] == "yes")
				$seeders[] = $subrow;
			else
				$downloaders[] = $subrow;
		}
		usort($seeders, "seed_sort");
		usort($downloaders, "leech_sort");
		echo "                <tr>\n".
			"                    <td class=\"tableb\" width=\"1%\"><a name=\"seeders\">Seeder</a><br /><a href=\"details.php?id=" . $id . $keepget . "\" class=\"sublink\">[Liste verbergen]</a></td>\n".
			"                    <td class=\"tableb\" width=\"99%\" style=\"text-align:left\">" . dltable("Seeder", $seeders, $row) . "</td>\n".
			"                </tr>\n".
			"                <tr>\n".
			"                    <td class=\"tableb\" width=\"1%\"><a name=\"leechers\">Leecher</a><br /><a href=\"details.php?id=" . $id . $keepget . "\" class=\"sublink\">[Liste verbergen]</a></td>\n".
			"                    <td class=\"tableb\" width=\"99%\" style=\"text-align:left\">" . dltable("Leecher", $downloaders, $row) . "</td>\n".
			"                </tr>\n";
	}
	echo "            </table>\n".
		"        </td>\n".
		"    </tr>\n".
		"</table><br>\n";
    begin_frame("<img src=\"" . $GLOBALS["PIC_BASE_URL"] . "comments.png\" width=\"22\" height=\"22\" alt=\"\" style=\"vertical-align: middle;\"> Kommentare", false, "750px;");
} else {
    stdhead("Kommentare zu Torrent \"" . $row["name"] . "\"");
    begin_frame("<img src=\"" . $GLOBALS["PIC_BASE_URL"] . "comments.png\" width=\"22\" height=\"22\" alt=\"\" style=\"vertical-align: middle;\"> Kommentare zu <a href=\"details.php?id=" . $id . "\">" . $row["name"] . "</a>", false, "750px;");
} 
echo "<a name=\"startcomments\"></a>\n";
$commentbar = "<table class=\"tableinborder\" border=\"0\" cellpadding=\"4\" cellspacing=\"1\" width=\"100%\">\n".
	"    <tr>\n".
	"        <td class=\"tablea\" width=\"100%\" style=\"text-align: center\"><b><a class=\"index\" href=\"comment.php?action=add&amp;tid=" . $id . "\">"."Kommentar hinzufügen</a></b></td>\n".
	"    </tr>\n".
	"</table><br>\n";
$count = $database->row_count('comments', 'torrent = ' . $id);

if($count == 0){
	echo "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" style=\"width:100%\" class=\"tableinborder\">\n".
		"    <tr>\n".
		"        <td class=\"tabletitle\" width=\"100%\" style=\"text-align:center\"><b>Bisher noch keine Kommentare</b></td>\n".
		"    </tr>\n".
		"</table><br>\n";
}else{
	list($pagertop, $pagerbottom, $limit) = pager(20, $count, "details.php?id=" . $id . "&", array("lastpagedefault" => 1));
	$sql = "SELECT comments.id, text, user, comments.added, editedby, editedat, avatar, warned, username, users.added as uadded, title, enabled, class, donor FROM comments LEFT JOIN users ON comments.user = users.id WHERE torrent = :id ORDER BY comments.id $limit";
	$qry = $GLOBALS['DB']->prepare($sql);
	$qry->bindParam(':id', $id, PDO::PARAM_INT);
	$qry->execute();
	$allrows = $qry->FetchAll(PDO::FETCH_ASSOC);

	echo $commentbar;
	echo $pagertop;
	commenttable($allrows);
	echo $pagerbottom;
}

echo $commentbar;
end_frame();
stdfoot();
?>