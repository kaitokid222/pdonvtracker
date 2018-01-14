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

userlogin();
loggedinorreturn();

if(isset($_GET["id"]))
	$id = intval($_GET["id"]);
else
	$id = $CURUSER["id"];

if(!is_valid_id($id))
	bark("Bad ID " . $id . ".");

$qry = $GLOBALS['DB']->prepare("SELECT users.* FROM users WHERE id= :id");
$qry->bindParam(':id', $id, PDO::PARAM_INT);
$qry->execute();
if($qry->rowCount() > 0)
	$user = $qry->Fetch(PDO::FETCH_ASSOC);
else
	bark("Es existiert kein User mit der ID " . $id . ".");

if($user["status"] == "pending")
	bark("Der Account wurde noch nicht freigeschaltet.");
	
$qry = $GLOBALS['DB']->prepare("SELECT torrents.id, torrents.name, torrents.added, torrents.seeders, torrents.leechers, torrents.category, categories.name as catname, categories.image as catimage FROM torrents LEFT JOIN categories ON categories.id = torrents.category WHERE `activated`='yes' AND owner= :id ORDER BY added DESC");
$qry->bindParam(':id', $id, PDO::PARAM_INT);
$qry->execute();
if($qry->rowCount() > 0){
	$ownTorrents = $qry->FetchAll(PDO::FETCH_ASSOC);
	$torrents = "<table class=\"tableinborder\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\" width=\"100%\">\n".
		"    <tr>\n".
		"        <td class=\"tablecat\">Typ</td>\n".
		"        <td class=\"tablecat\" width=\"100%\">Name</td>\n".
		"        <td class=\"tablecat\">Hochgeladen</td>\n".
		"        <td class=\"tablecat\">Seeder</td>\n".
		"        <td class=\"tablecat\">Leecher</td>\n".
		"    </tr>\n";
	foreach($ownTorrents as $a){
        $cat = "<img src=\"" . $GLOBALS["PIC_BASE_URL"] . $a["catimage"] . "\" alt=\"" . $a["catname"] . "\" title=\"" . $a["catname"] . "\">";
        $torrents .= "    <tr>\n".
			"        <td class=\"tableb\" style=\"padding: 0px\">" . $cat . "</td>\n".
			"        <td class=\"tablea\"><a href=\"details.php?id=" . $a["id"] . "&hit=1\"><b>" . htmlspecialchars($a["name"]) . "</b></a></td>\n".
			"        <td class=\"tableb\" align=\"center\">" . str_replace(" ", "<br />", date("d.m.Y H:i:s", sql_timestamp_to_unix_timestamp($a["added"]))) . "</td>\n".
			"        <td class=\"tablea\" align=\"right\">" . $a["seeders"] . "</td>\n".
			"        <td class=\"tableb\" align=\"right\">" . $a["leechers"] . "</td>\n".
			"    </tr>\n";
	}
	$torrents .= "</table>";
}else
	$torrents = "";

if($GLOBALS["CLIENT_AUTH"] == CLIENT_AUTH_PASSKEY)
	$announceurl = preg_replace("/\\{KEY\\}/", preg_replace_callback('/./s', "hex_esc", str_pad($user["passkey"], 8)), $GLOBALS["PASSKEY_ANNOUNCE_URL"]);
else
	$announceurl = $GLOBALS["ANNOUNCE_URLS"][0];

$addr = "";
$peer_addr = "";
if($user["ip"] && (get_user_class() >= UC_MODERATOR || $user["id"] == $CURUSER["id"])){
	$ip = $user["ip"];
	$addr = get_domain($ip);
	$qry = $GLOBALS['DB']->prepare("SELECT DISTINCT(ip) AS ip FROM peers WHERE userid= :id");
	$qry->bindParam(':id', $id, PDO::PARAM_INT);
	$qry->execute();
	if($qry->rowCount() > 0){
		$p = $qry->FetchAll(PDO::FETCH_ASSOC);
		foreach($p as $peer_ip){
			if($peer_addr != "")
				$peer_addr .= "<br>";
			$peer_addr .= get_domain($peer_ip["ip"]);
		}
	}
}

if($user["added"] == "0000-00-00 00:00:00"){
	$joindate = 'N/A';
	$down_per_day = "";
	$upped_per_day = "";
}else{
	$joindate = "" . $user["added"] . " (Vor " . get_elapsed_time(sql_timestamp_to_unix_timestamp($user["added"])) . ")";
	$days_regged = round((time() - sql_timestamp_to_unix_timestamp($user["added"])) / 86400);
	if($days_regged){
		$down_per_day = "(" . mksize(floor($user["downloaded"] / $days_regged)) . " / Tag)";
		$upped_per_day = "(" . mksize(floor($user["uploaded"] / $days_regged)) . " / Tag)";
	}else{
		$down_per_day = "(0,00 KB / Tag)";
		$upped_per_day = "(0,00 KB / Tag)";
	}
}
$lastseen = $user["last_access"];
if($lastseen == "0000-00-00 00:00:00")
	$lastseen = "nie";
else
	$lastseen .= " (Vor " . get_elapsed_time(sql_timestamp_to_unix_timestamp($lastseen)) . ")";

$torrentcomments = $database->row_count('comments', 'user = ' . $user['id']);
$forumposts = $database->row_count('posts', 'userid = ' . $user['id']);

$qry = $GLOBALS['DB']->prepare("SELECT name, flagpic FROM countries WHERE id= :c LIMIT 1");
$qry->bindParam(':c', $user["country"], PDO::PARAM_INT);
$qry->execute();
if($qry->rowCount() == 1){
	$arr = $qry->Fetch(PDO::FETCH_ASSOC);
	$country = "<img src=\"" . $GLOBALS["PIC_BASE_URL"] . "flag/" . $arr["flagpic"] . "\" alt=\"" . $arr["name"] . "\" style=\"margin-left: 8pt;vertical-align: middle;\">";
}else
	$country = "";

$leeching = "";
$qry = $GLOBALS['DB']->prepare("SELECT torrent,added,traffic.uploaded,traffic.downloaded,torrents.name as torrentname,categories.name as catname,size,image,category,seeders,leechers FROM peers JOIN traffic ON peers.userid = traffic.userid AND peers.torrent = traffic.torrentid JOIN torrents ON peers.torrent = torrents.id JOIN categories ON torrents.category = categories.id WHERE peers.userid= :id AND seeder='no'");
$qry->bindParam(':id', $id, PDO::PARAM_INT);
$qry->execute();
if($qry->rowCount() > 0){
	$arr = $qry->FetchAll(PDO::FETCH_ASSOC);
	$leeching = maketable($arr);
}

$seeding = "";
$qry = $GLOBALS['DB']->prepare("SELECT torrent,added,traffic.uploaded,traffic.downloaded,torrents.name as torrentname,categories.name as catname,size,image,category,seeders,leechers FROM peers JOIN traffic ON peers.userid = traffic.userid AND peers.torrent = traffic.torrentid JOIN torrents ON peers.torrent = torrents.id JOIN categories ON torrents.category = categories.id WHERE peers.userid= :id AND seeder='yes'");
$qry->bindParam(':id', $id, PDO::PARAM_INT);
$qry->execute();
if($qry->rowCount() > 0){
	$arr = $qry->FetchAll(PDO::FETCH_ASSOC);
	$seeding = maketable($arr);
}

$completed = "";
if(get_user_class() >= UC_MODERATOR || (isset($CURUSER) && $CURUSER["id"] == $user["id"])){
	if(!isset($_GET["allcompleted"]))
		$limit_comp = " LIMIT 3";
	else
		$limit_comp = "";
	$sql = "SELECT torrent_id as torrent,torrent_name,complete_time,torrents.seeders as seeders,torrents.leechers as leechers,torrents.id as torrent_origid,categories.name as catname,image,traffic.uploaded,traffic.downloaded,traffic.uploadtime,traffic.downloadtime FROM completed LEFT JOIN traffic ON completed.torrent_id = traffic.torrentid AND completed.user_id = traffic.userid LEFT JOIN torrents ON completed.torrent_id = torrents.id LEFT JOIN categories ON completed.torrent_category = categories.id WHERE user_id= :id ORDER BY complete_time DESC" . $limit_comp;
	$qry = $GLOBALS['DB']->prepare($sql);
	$qry->bindParam(':id', $id, PDO::PARAM_INT);
	$qry->execute();
	if($qry->rowCount() > 0){
		$data = $qry->FetchAll(PDO::FETCH_ASSOC);
		$completed = makecomptable($data);
	}
}

$qry = $GLOBALS['DB']->prepare("SELECT `baduser` FROM `accounts` WHERE `userid`= :id");
$qry->bindParam(':id', $id, PDO::PARAM_INT);
$qry->execute();
$acctdata = $qry->Fetch(PDO::FETCH_ASSOC);
$baduser = $acctdata["baduser"];

$enabled = $user["enabled"] == 'yes';
$acceptrules = $user["accept_rules"] == 'no';
$allowupload = $user["allowupload"] == 'yes';

stdhead("Benutzerprofil von " . $user["username"]);
begin_frame("<img src=\"" . $GLOBALS["PIC_BASE_URL"] . "personal.png\" width=\"22\" height=\"22\" alt=\"\" style=\"vertical-align: middle;\">  Benutzerprofil von " . $user["username"] . get_user_icons($user, true) . "&nbsp;" . $country, false, "750px");
begin_table(true);
if(!$enabled){
	echo "    <tr>\n".
		"        <td colspan=\"2\" class=\"tablea\"><b>Dieser Account wurde gesperrt</b></td>\n".
		"    </tr>\n";
}

if($CURUSER["id"] != $user["id"] && $enabled){
	$friend = $database->row_count("friends", "userid= " . $CURUSER["id"] . " AND friendid= " . $id . "");
	if($friend == 0)
		$block = $database->row_count("blocks", "userid= " . $CURUSER["id"] . " AND blockid= " . $id . "");

	if($friend > 0){
		echo "    <tr>\n".
			"        <td colspan=\"2\" class=\"tablea\" align=\"center\"><a href=\"friends.php?action=delete&type=friend&targetid=" . $id . "\">Von Freundesliste entfernen</a></td>\n".
			"    </tr>\n";
	}elseif($block > 0){
		echo "    <tr>\n".
			"        <td colspan=\"2\" class=\"tablea\" align=\"center\"><a href=\"friends.php?action=delete&type=block&targetid=" . $id . "\">Von Blockliste entfernen</a></td>\n".
			"    </tr>\n";
	}else{
		echo "    <tr>\n".
			"        <td class=\"tablea\" align=\"center\"><a href=\"friends.php?action=add&type=friend&targetid=" . $id . "\">Zu Freunden hinzufügen</a></td>\n".
			"        <td class=\"tablea\" align=\"center\"><a href=\"friends.php?action=add&type=block&targetid=" . $id . "\">Zu Blockliste hinzufügen</a></td>\n".
			"    </tr>\n";
	}
}
end_table();

echo "<table cellspacing=\"1\" cellpadding=\"5\" border=\"0\" class=\"tableinborder\" style=\"width: 100%\">\n".
	"    <tr>\n".
	"        <td class=\"tableb\" width=\"1%\">Registriert</td>\n".
	"        <td class=\"tablea\" align=\"left\" width=\"99%\">" . $joindate . "</td>\n".
	"    </tr>\n".
	"    <tr>\n".
	"        <td class=\"tableb\">Zuletzt aktiv</td>\n".
	"        <td class=\"tablea\" align=\"left\">" . $lastseen . "</td>\n".
	"    </tr>\n";
if(get_user_class() >= UC_MODERATOR){
	echo "    <tr>\n".
		"        <td class=\"tableb\">E-Mail</td>\n".
		"        <td class=\"tablea\" align=\"left\"><a href=\"mailto:" . $user["email"] . "\">" . $user["email"] . "</a></td>\n".
		"    </tr>\n";
}

if($addr != ""){
	echo "    <tr>\n".
		"        <td class=\"tableb\">Adresse</td>\n".
		"        <td class=\"tablea\" align=\"left\">" . $addr . "</td>\n".
		"    </tr>\n";
}

if ($peer_addr){
	echo "    <tr>\n".
		"        <td class=\"tableb\">Peer-Adressen</td>\n".
		"        <td class=\"tablea\" align=\"left\">" . $peer_addr . "</td>\n".
		"    </tr>\n";
}

if ($CURUSER["id"] == $user["id"] || get_user_class() == UC_SYSOP){
	echo "    <tr>\n".
		"        <td class=\"tableb\">Announce-URL</td>\n".
		"        <td class=\"tablea\" align=\"left\">" . $announceurl . "</td>\n".
		"    </tr>\n";
}

echo "    <tr>\n".
	"        <td class=\"tableb\">Hochgeladen</td>\n".
	"        <td class=\"tablea\" align=\"left\">" . mksize($user["uploaded"]) . " " . $upped_per_day . "</td>\n".
	"    </tr>\n".
	"    <tr>\n".
	"        <td class=\"tableb\">Runtergeladen</td>\n".
	"        <td class=\"tablea\" align=\"left\">" . mksize($user["downloaded"]) . " " . $down_per_day . "</td>\n".
	"    </tr>\n";

if($user["downloaded"] > 0){
	$sr = $user["uploaded"] / $user["downloaded"];
	if($sr >= 10)
		$s = "pirate2";
	elseif($sr >= 7.5)
		$s = "bow";
	elseif($sr >= 5)
		$s = "yikes";
	elseif($sr >= 3.5)
		$s = "w00t";
	elseif($sr >= 2)
		$s = "grin";
	elseif($sr >= 1)
		$s = "smile1";
	elseif($sr >= 0.9)
		$s = "innocent";
	elseif($sr >= 0.5)
		$s = "noexpression";
	elseif($sr >= 0.25)
		$s = "sad";
	elseif($sr >= 0.1)
		$s = "cry";
	else
		$s = "shit";
	$sr = floor($sr * 1000) / 1000;
	$sr = "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">"."<tr>"."<td class=\"embedded\"><font color=\"" . get_ratio_color($sr) . "\">" . number_format($sr, 3) . "</font></td>"."<td class=\"embedded\">&nbsp;&nbsp;<img src=\"" . $GLOBALS["PIC_BASE_URL"] . "smilies/" . $s . ".gif\"></td>"."</tr>"."</table>";
	echo "    <tr>\n".
		"        <td class=\"tableb\" style=\"vertical-align: middle\">Ratio</td>\n".
		"        <td class=\"tablea\" align=\"left\" valign=\"center\" style=\"padding-top: 1px; padding-bottom: 0px\">" . $sr . "</td>\n".
		"    </tr>\n";

	if(file_exists($GLOBALS["BITBUCKET_DIR"] . "/rstat-" . $user["id"] . ".png")){
		echo "    <tr>\n".
			"        <td class=\"tableb\" style=\"vertical-align: middle\">Ratio-Histogramm</td>\n".
			"        <td class=\"tablea\" align=\"left\" valign=\"center\" style=\"padding-top: 1px; padding-bottom: 0px\"><img src=\"" . $GLOBALS["BITBUCKET_DIR"] . "/rstat-" . $user["id"] . ".png\"></td>\n".
			"    </tr>\n";
	}
}

if($user["avatar"]){
	echo "    <tr>\n".
		"        <td class=\"tableb\">Avatar</td>\n".
		"        <td class=\"tablea\" align=\"left\"><img src=\"" . htmlspecialchars($user["avatar"]) . "\"></td>\n".
		"    </tr>\n";
}
echo "    <tr>\n".
	"        <td class=\"tableb\">Rang</td>\n".
	"        <td class=\"tablea\" align=\"left\">" . get_user_class_name($user["class"]) . "</td>\n".
	"    </tr>\n".
	"    <tr>\n".
	"        <td class=\"tableb\">Kommentare</td>";
if($torrentcomments && (($user["class"] >= UC_POWER_USER && $user["id"] == $CURUSER["id"]) || get_user_class() >= UC_MODERATOR)){
	echo "        <td class=\"tablea\" align=\"left\"><a href=\"userhistory.php?action=viewcomments&id=" . $id . "\">" . $torrentcomments . "</a></td>\n".
		"    </tr>\n";
}else{
	echo "        <td class=\"tablea\" align=\"left\">" . $torrentcomments . "</td>\n".
		"    </tr>\n";
}
echo "    <tr>\n".
	"        <td class=\"tableb\">Forumbeiträge</td>\n";
if($forumposts && (($user["class"] >= UC_POWER_USER && $user["id"] == $CURUSER["id"]) || get_user_class() >= UC_MODERATOR))
	echo "        <td class=\"tablea\" align=\"left\"><a href=\"userhistory.php?action=viewposts&id=" . $id . "\">" . $forumposts . "</a></td>\n".
		"    </tr>\n";
else
	echo "        <td class=\"tablea\" align=\"left\">" . $forumposts . "</td>\n".
		"    </tr>\n";

if($torrents){
	echo "    <tr valign=\"top\">\n".
		"        <td class=\"tableb\">Hochgeladen</td>\n".
		"        <td class=\"tablea\" align=\"left\">" . $torrents . "\n</td>\n".
		"    </tr>\n";
}

if($seeding){
	echo "    <tr valign=\"top\">\n".
		"        <td class=\"tableb\">Seedet&nbsp;momentan</td>\n".
		"        <td class=\"tablea\" align=\"left\">" . $seeding . "\n</td>\n".
		"    </tr>\n";
}

if($leeching){
	echo "    <tr valign=\"top\">\n".
		"        <td class=\"tableb\">Leecht&nbsp;momentan</td>\n".
		"        <td class=\"tablea\" align=\"left\">" . $leeching . "\n</td>\n".
		"    </tr>\n";
}

if($completed){
	echo "    <tr valign=\"top\">\n".
		"        <td class=\"tableb\"><a name=\"completed\"></a>";
	if(!$_GET["allcompleted"]){
		echo "Letzte 3 fertige Torrents<br><a class=\"sublink\" href=\"userdetails.php?id=" . $id . "&amp;allcompleted=1#completed\">[Alle anzeigen]</a></td>\n".
			"        <td class=\"tablea\" align=\"left\">" . $completed . "\n</td>\n".
			"    </tr>\n";
	}else{
		echo "Alle fertigen Torrents<br><a class=\"sublink\" href=\"userdetails.php?id=" . $id . "#completed\">[Liste verbergen]</a></td>\n".
			"        <td class=\"tablea\" align=\"left\">" . $completed . "\n</td>\n".
			"    </tr>\n";
	}
}

if($user["info"]){
	echo "    <tr valign=\"top\">\n".
		"        <td class=\"inposttable\" align=\"left\" colspan=\"2\" class=\"text\" bgcolor=\"#F4F4F0\">" . format_comment($user["info"]) . "</td>\n".
		"    </tr>\n";
}

if($CURUSER["id"] != $user["id"]){
	if(get_user_class() >= UC_GUTEAM){
		$showpmbutton = true;
		$showemailbutton = true;
	}else{
		if($user["acceptpms"] == "yes"){
			$b = $database->row_count("blocks", "userid= " . $user["id"] . " AND blockid= " . $CURUSER["id"] . "");
			$showpmbutton = (($b == 1) ? false : true);
		}elseif($user["acceptpms"] == "friends"){
			$f = $database->row_count("friends", "userid= " . $user["id"] . " AND friendid= " . $CURUSER["id"] . "");
			$showpmbutton = (($f == 1) ? true : false);
		}

		if($user["accept_email"] == "yes"){
			$b = $database->row_count("blocks", "userid= " . $user["id"] . " AND blockid= " . $CURUSER["id"] . "");
			$showemailbutton = (($b == 1) ? false : true);
		}elseif($user["accept_email"] == "friends"){
			$f = $database->row_count("friends", "userid= " . $user["id"] . " AND friendid= " . $CURUSER["id"] . "");
			$showemailbutton = (($f == 1) ? true : false);
		}
	}
}else{
	$showpmbutton = false;
	$showemailbutton = false;
}

if($showpmbutton || $showemailbutton){
	echo "    <tr>\n".
		"        <td class=\"tablea\" colspan=\"2\" style=\"text-align:center\">";
	if($showpmbutton){
		echo "<form method=\"get\" action=\"messages.php\" style=\"display:inline\">".
			"<input type=\"hidden\" name=\"action\" value=\"send\">".
			"<input type=\"hidden\" name=\"receiver\" value=\"" . $user["id"] . "\">".
			"<input type=\"submit\" value=\"Nachricht senden\" style=\"height: 23px\">".
			"</form>&nbsp;&nbsp;";
	}
	if($showemailbutton){
		echo "<form method=\"get\" action=\"email-gateway.php\" style=\"display:inline\">".
			"<input type=\"hidden\" name=\"id\" value=\"" . $user["id"] . "\">".
			"<input type=\"submit\" value=\"E-Mail senden\" style=\"height: 23px\">".
			"</form>";
	}
	echo "        </td>\n".
		"    </tr>\n";
} 

echo "</table>\n".
	"</td></tr></table><br>\n";

if((get_user_class() >= UC_MODERATOR && $user["class"] < get_user_class()) || get_user_class() == UC_SYSOP){
	echo "<script type=\"text/javascript\">\n".
		"    function togglediv(){\n".
		"        var mySelect = document.getElementById(\"tselect\");\n".
		"        var myDiv = document.getElementById(\"tlimitdiv\");\n".
		"        if(mySelect.options[mySelect.selectedIndex].value == \"manual\")\n".
		"            myDiv.style.visibility = \"visible\";\n".
		"        else\n".
		"            myDiv.style.visibility = \"hidden\";\n".
		"    }\n".
		"</script>\n";
    $bbfilecount = $database->row_count('bitbucket','`user`='.$id);
	$bb_str = ($bbfilecount > 0) ? "    <tr>\n        <td class=\"tableb\">BitBucket</td>\n        <td class=\"tablea\" colspan=\"2\" align=\"left\"><a href=\"bitbucket.php?id=" . $id . "\">BitBucket-Inhalt dieses Benutzers anzeigen / bearbeiten</a> (" . $bbfilecount . " Datei(en))</td>\n    </tr>\n" : "";
	$avatar = htmlspecialchars($user["avatar"]);
    begin_frame("Profil bearbeiten", false, "750px");
	echo "    <form method=\"post\" action=\"modtask.php\">\n".
		"    <input type=\"hidden\" name=\"action\" value=\"edituser\">\n".
		"    <input type=\"hidden\" name=\"userid\" value=\"" . $id . "\">\n".
		"    <input type=\"hidden\" name=\"returnto\" value=\"userdetails.php?id=" . $id . "\">\n".
		"    <table class=\"tableinborder\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\" style=\"width:100%;\">\n".
		"    <tr>\n".
		"        <td class=\"tableb\">Titel</td>\n".
		"        <td class=\"tablea\" colspan=\"2\" align=\"left\"><input type=\"text\" size=\"60\" name=\"title\" value=\"" . htmlspecialchars($user["title"]) . "\"></td>\n".
		"    </tr>\n".
		"    <tr>\n".
		"        <td class=\"tableb\">Avatar&nbsp;URL</td>\n".
		"        <td class=\"tablea\" colspan=\"2\" align=\"left\"><input type=\"text\" size=\"60\" name=\"avatar\" value=\"" . $avatar . "\"></td>\n".
		"    </tr>\n".
		"    <tr>\n".
		"        <td class=\"tableb\">Start/Stop&nbsp;Events</td>\n".
		"        <td class=\"tablea\" colspan=\"2\" align=\"left\"><a href=\"startstoplog.php?op=user&amp;uid=" . $user["id"] . "\">Ereignisse anzeigen</a></td>\n".
		"    </tr>\n".
		$bb_str;
	if($CURUSER["class"] < UC_ADMINISTRATOR)
		echo "    <input type=\"hidden\" name=\"donor\" value=\"" . $user["donor"] . "\">\n";
	else{
		echo "    <tr>\n".
			"        <td class=\"tableb\">Gespendet</td>\n".
			"        <td class=\"tablea\" colspan=\"2\" align=\"left\">".
			"<input type=\"radio\" name=\"donor\" value=\"yes\"" . ($user["donor"] == "yes" ? " checked" : "") . ">Ja ".
			"<input type=\"radio\" name=\"donor\" value=\"no\"" . ($user["donor"] == "no" ? " checked" : "") . ">Nein</td>\n".
			"    </tr>\n";
	}
	if(get_user_class() == UC_MODERATOR && $user["class"] > UC_VIP)
		echo "<input type=\"hidden\" name=\"class\" value=\"" . $user["class"] . "\"\n";
	else{
		echo "    <tr>\n".
			"        <td class=\"tableb\">Klasse</td>\n".
			"        <td class=\"tablea\" colspan=\"2\" align=\"left\">\n".
			"        <select name=\"class\">\n";
		if(get_user_class() == UC_MODERATOR)
			$maxclass = UC_VIP;
		elseif(get_user_class() == UC_SYSOP)
			$maxclass = UC_SYSOP;
		else
			$maxclass = get_user_class() - 1;
		for($i = 0; $i <= $maxclass; ++$i)
			if(get_user_class_name($i) != "")
				echo "            <option value=\"" . $i . "\"" . ($user["class"] == $i ? " selected=\"selected\"" : "") . ">" . get_user_class_name($i) . "\n";
		echo "        </select>\n".
			"        </td>\n".
			"    </tr>\n";
	}
	echo "    <tr>\n".
		"        <td class=\"tableb\">Torrentbegrenzung</td>\n".
		"        <td class=\"tablea\" colspan=\"2\" align=\"left\">\n".
		"        <select id=\"tselect\" name=\"limitmode\" size=\"1\" onchange=\"togglediv();\">\n".
		"            <option value=\"auto\"" . (($user["tlimitall"] == 0) ? " selected=\"selected\"" : "") . ">Automatisch</option>\n".
		"            <option value=\"unlimited\"" . (($user["tlimitall"] == -1) ? " selected=\"selected\"" : "") . ">Unbegrenzt</option>\n".
		"            <option value=\"manual\"" . (($user["tlimitall"] > 0) ? " selected=\"selected\"" : "") . ">Manuell</option>\n".
		"        </select>\n".
		"        <div id=\"tlimitdiv\" style=\"display: inline;" . (($user["tlimitall"] <= 0) ? "visibility:hidden;" : "") . "\">&nbsp;&nbsp;&nbsp;".
		" Seeds: <input type=\"text\" size=\"2\" maxlength=\"2\" name=\"maxseeds\" value=\"" . (($user["tlimitseeds"] > 0) ? $user["tlimitseeds"] : "") . "\">".
		" Leeches: <input type=\"text\" size=\"2\" maxlength=\"2\" name=\"maxleeches\" value=\"" . (($user["tlimitleeches"] > 0) ? $user["tlimitleeches"] : "") . "\">".
		" Gesamt: <input type=\"text\" size=\"2\" maxlength=\"2\" name=\"maxtotal\" value=\"" . (($user["tlimitall"] > 0) ? $user["tlimitall"] : "") . "\">".
		"</div>\n".
		"        </td>\n".
		"    </tr>\n";
	$qry = $GLOBALS['DB']->prepare("SELECT msg,name,torrent_id,`status` FROM nowait LEFT JOIN torrents ON torrents.id=torrent_id WHERE user_id= :id");
	$qry->bindParam(':id', $id, PDO::PARAM_INT);
	$qry->execute();
	if($qry->rowCount() > 0){
		$data = $qry->FetchAll(PDO::FETCH_ASSOC);
		echo "    <tr>\n".
			"        <td class=\"tableb\">Wartezeit aufheben</td>\n".
			"        <td class=\"tablea\" colspan=\"2\" align=\"left\">\n".
			"            <table class=\"tableinborder\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\" width=\"100%\">\n".
			"                <tr>\n".
			"                    <td class=\"tablecat\" width=\"100%\">Torrent / Grund</td>\n".
			"                    <td class=\"tablecat\">Status</td>\n".
			"                </tr>\n";
		foreach($data as $arr){
			echo "                <tr>\n".
				"                    <td class=\"tablea\"><p><b><a href=\"details.php?id=" . $arr["torrent_id"] . "\">" . htmlspecialchars($arr["name"]) . "</a></b></p><p>" . htmlspecialchars($arr["msg"]) . "</td>\n";
			if($arr["status"] == "pending"){
				echo "                    <td class=\"tableb\" valign=\"middle\" nowrap=\"nowrap\">".
					"<input type=\"radio\" name=\"wait[" . $arr["torrent_id"] . "]\" value=\"yes\"" . (($arr["status"] == "granted") ? " checked=\"checked\"" : "") . "> Akzeptieren<br/>".
					"<input type=\"radio\" name=\"wait[" . $arr["torrent_id"] . "]\" value=\"no\"" . (($arr["status"] == "rejected") ? " checked=\"checked\"" : "") . "> Ablehnen<br/>\n".
					"<input type=\"radio\" name=\"wait[" . $arr["torrent_id"] . "]\" value=\"\"" . (($arr["status"] == "pending") ? " checked=\"checked\"" : "") . "> Nichts tun".
					"</td>\n".
					"                </tr>\n";
			}else{
				echo "                    <td class=\"tableb\" valign=\"middle\" align=\"center\">" . (($arr["status"] == "granted") ? "Akzeptiert" : "Abgelehnt") . "</td>\n".
					"                </tr>\n";
			}
		}
		echo "            </table>\n".
			"        </td>\n".
			"    </tr>\n";
	}
    
	echo "    <tr>\n".
		"        <td class=tableb>Kommentare</td>\n".
		"        <td class=tablea colspan=2 align=left>\n".
		"            <div style=\"width:580px;height:100px;overflow:auto;\">\n";
    begin_table(TRUE);

	$qry = $GLOBALS['DB']->prepare("SELECT `modcomments`.`added`,`modcomments`.`userid`,`modcomments`.`moduid`,`modcomments`.`txt`,`users`.`username` FROM `modcomments` LEFT JOIN `users` ON `users`.`id`=`modcomments`.`moduid` WHERE `userid`= :id ORDER BY `added` DESC");
	$qry->bindParam(':id', $id, PDO::PARAM_INT);
	$qry->execute();
	$data = $qry->FetchAll(PDO::FETCH_ASSOC);
	foreach($data as $comment){
		$comment["added"] = str_replace(" ", "&nbsp;", $comment["added"]);
		echo "                <tr>\n".
			"                    <td class=\"tablea\" valign=\"top\" width=\"130px\">".$comment["added"]."</td>\n".
			"                    <td class=\"tableb\" valign=\"top\">";
		if($comment["moduid"] == 0)
			echo "System";
		elseif ($comment["username"] == "")
			echo "<i>Gelöscht</i>";
		else
			echo "<a href=\"userdetails.php?id=".$comment["moduid"]."\">".$comment["username"]."</a>";
		echo "</td>\n".
			"                    <td class=\"tablea\" valign=\"top\">".format_comment(stripslashes($comment["txt"]))."</td>\n".
			"                </tr>\n";
	}   
	echo "</table>\n".
		"            </div><br>Hinzufügen: <input type=\"text\" size=\"50\" name=\"modcomment\"></td>\n".
		"    </tr>\n";

    $warned = $user["warned"] == "yes";
    echo "    <tr>\n".
		"        <td class=\"tableb\"" . ((!$warned) ? " rowspan=\"2\"" : "") . ">Verwarnt</td>\n".
		"        <td class=\"tablea\" align=\"left\" width=\"20%\">" . (($warned) ? "<input name=\"warned\" value=\"yes\" type=\"radio\" checked=\"checked\">Ja <input name=\"warned\" value=\"no\" type=\"radio\">Nein" : "Nein") . "</td>\n";

	if($warned){
		$warneduntil = $user['warneduntil'];
		if($warneduntil == '0000-00-00 00:00:00'){
			echo "        <td class=\"tablea\" align=\"center\">(willkürliche Dauer)</td>\n".
				"    </tr>\n";
		}else{
			echo "        <td class=\"tablea\" align=\"center\">Bis " . $warneduntil . " (noch " . mkprettytime(strtotime($warneduntil) - time()) . ")</td>\n".
				"    </tr>\n";
		}
	}else{
		echo "        <td class=\"tablea\">Verwarnen für \n".
			"            <select name=\"warnlength\">\n".
			"                <option value=\"0\">------</option>\n".
			"                <option value=\"1\">1 Woche</option>\n".
			"                <option value=\"2\">2 Wochen</option>\n".
			"                <option value=\"4\">4 Wochen</option>\n".
			"                <option value=\"8\">8 Wochen</option>\n".
			"                <option value=\"255\">Unbefristet</option>\n".
			"            </select>\n".
			"        </td>\n".
			"    </tr>\n".
			"    <tr>\n".
			"        <td class=\"tablea\" colspan=\"2\" align=\"left\">PM Kommentar (BBCode erlaubt):<br><textarea cols=\"60\" rows=\"4\" name=\"warnpm\"></textarea><br><input id=\"addwarnratio\" type=\"checkbox\" name=\"addwarnratio\" value=\"yes\"><label for=\"addwarnratio\">&nbsp;Ratiostats zu Mod-Kommentar hinzufügen</label></td>\n".
			"    </tr>";
	}
    echo "    <tr>\n".
		"        <td class=\"tableb\">Muss Regeln bestätigen</td>\n".
		"        <td class=\"tablea\" colspan=\"2\" align=\"left\"><input name=\"acceptrules\" value=\"no\" type=\"radio\"" . (($acceptrules) ? " checked=\"checked\"" : "") . ">Ja <input name=\"acceptrules\" value=\"yes\" type=\"radio\"" . ((!$acceptrules) ? " checked=\"checked\"" : "") . ">Nein</td>\n".
		"    </tr>\n".
		"    <tr>\n".
		"        <td class=\"tableb\">Torrent-Upload sperren</td>\n".
		"        <td class=\"tablea\" align=\"left\" colspan=\"2\"><input name=\"denyupload\" value=\"yes\" type=\"radio\"" . ((!$allowupload) ? " checked=\"checked\"" : "") . ">Ja <input name=\"denyupload\" value=\"no\" type=\"radio\"" . (($allowupload) ? " checked=\"checked\"" : "") . ">Nein</td>\n".
		"    </tr>\n".
		"    <tr>\n".
		"        <td class=\"tableb\">Bad User <img src=\"" . $GLOBALS["PIC_BASE_URL"] . "help.png\" style=\"vertical-align:middle;\" title=\"Bewirkt, dass dieser Benutzer nur ungültige Peer-IPs erhält\" alt=\"Bewirkt, dass dieser Benutzer nur ungültige Peer-IPs erhält\"></td>\n".
		"        <td class=\"tablea\" align=\"left\"><input name=\"baduser\" value=\"yes\" type=\"radio\"" . (($baduser) ? " checked=\"checked\"" : "") . ">Ja <input name=\"baduser\" value=\"no\" type=\"radio\"" . ((!$baduser) ? " checked=\"checked\"" : "") . ">Nein</td>\n".
		"        <td class=\"tablea\" align=\"left\"><a href=\"startstoplog.php?op=acclist&amp;id=" . $id . "\">Liste ehem. Accounts anzeigen</a></td>\n".
		"    </tr>\n".
		"    <tr>\n".
		"        <td class=\"tableb\">Aktiviert</td>\n".
		"        <td class=\"tablea\" align=\"left\"><input name=\"enabled\" value=\"yes\" type=\"radio\"" . (($enabled) ? " checked=\"checked\"" : "") . ">Ja <input name=\"enabled\" value=\"no\" type=\"radio\"" . ((!$enabled) ? " checked=\"checked\"" : "") . ">Nein</td>\n".
		"        <td class=\"tablea\" align=\"left\">Grund: <input type=\"text\" name=\"disablereason\" size=\"40\"></td>\n".
		"    </tr>\n".
		"    <tr>\n".
		"        <td class=\"tablea\" colspan=\"3\" align=\"center\"><input type=\"submit\" class=\"btn\" value=\"Okay\"></td>\n".
		"    </tr>\n".
		"    </table>\n".
		"    </form>\n";
    end_frame();
}
end_main_frame();
echo "</center>\n";
stdfoot();

function bark($msg){
	stdhead();
	stdmsg("Error", $msg);
	stdfoot();
	exit;
} 

function get_domain($ip){
	$dom = @gethostbyaddr($ip);
	if($dom == $ip || @gethostbyname($dom) != $ip)
		return "<a href=\"whois.php?ip=" . $ip . "\" target=\"nvwhois\">" . $ip . "</a>";
	else{
		$dom = strtoupper($dom);
		return "<a href=\"whois.php?ip=" . $ip . "\" target=\"nvwhois\">" . $ip . "</a> (" . $dom . ")";
	}
}

function maketable($data){
	$ret = "\n<table class=\"tableinborder\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\" width=\"100%\">\n".
		"    <tr>\n".
		"        <td class=\"tablecat\" align=\"center\">Typ</td>\n".
		"        <td class=\"tablecat\" width=\"100%\">Name</td>\n".
		"        <td class=\"tablecat\" align=\"center\">TTL</td>\n".
		"        <td class=\"tablecat\" align=\"center\">Größe</td>\n".
		"        <td class=\"tablecat\" align=\"right\">Se.</td>\n".
		"        <td class=\"tablecat\" align=\"right\">Le.</td>\n".
		"        <td class=\"tablecat\" align=\"center\">Hochgel.</td>\n".
		"        <td class=\"tablecat\" align=\"center\">Runtergel.</td>\n".
		"        <td class=\"tablecat\" align=\"center\">Ratio</td>\n".
		"    </tr>\n";
	foreach($data as $arr){
		if ($arr["downloaded"] > 0){
			$ratio = number_format($arr["uploaded"] / $arr["downloaded"], 3);
			$ratio = "<font color=" . get_ratio_color($ratio) . ">" . $ratio . "</font>";
		}else{
			if($arr["uploaded"] > 0)
				$ratio = "Inf.";
			else
				$ratio = "---";
		}
		$catimage = htmlspecialchars($arr["image"]);
		$catname = htmlspecialchars($arr["catname"]);
		$ttl = (28 * 24) - floor((time() - sql_timestamp_to_unix_timestamp($arr["added"])) / 3600);
		if($ttl == 1)
			$ttl .= "<br>hour";
		else
			$ttl .= "<br>hours";
		$size = str_replace(" ", "<br>", mksize($arr["size"]));
		$uploaded = str_replace(" ", "<br>", mksize($arr["uploaded"]));
		$downloaded = str_replace(" ", "<br>", mksize($arr["downloaded"]));
		$seeders = number_format($arr["seeders"]);
		$leechers = number_format($arr["leechers"]);
		$ret .= "    <tr>\n".
			"        <td class=\"tableb\" style=\"padding: 0px\"><img src=\"" . $GLOBALS["PIC_BASE_URL"] . $catimage . "\" alt=\"" . $catname . "\" title=\"" . $catname . "\" width=\"42\" height=\"42\"></td>\n". 
			"        <td class=\"tablea\"><a href=\"details.php?id=" . $arr["torrent"] . "&amp;hit=1\"><b>" . htmlspecialchars($arr["torrentname"]) . "</b></a></td>\n".
			"        <td class=\"tableb\" align=\"center\">" . $ttl . "</td>\n".
			"        <td class=\"tablea\" align=\"center\">" . $size . "</td>\n".
			"        <td class=\"tableb\" align=\"right\">" . $seeders . "</td>\n".
			"        <td class=\"tablea\" align=\"right\">" . $leechers . "</td>\n".
			"        <td class=\"tableb\" align=\"center\">" . $uploaded . "</td>\n".
			"        <td class=\"tablea\" align=\"center\">" . $downloaded . "</td>\n".
			"        <td class=\"tableb\" align=\"center\">" . $ratio . "</td>\n".
			"    </tr>\n";
	}
	$ret .= "</table>\n";
	return $ret;
}

function makecomptable($data){
	$ret = "\n<table class=\"tableinborder\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\" width=\"100%\">\n".
		"    <tr>\n".
		"        <td class=\"tablecat\" style=\"text-align:center\">Typ</td>\n".
		"        <td class=\"tablecat\" width=\"100%\">Name</td>\n".
		"        <td class=\"tablecat\" style=\"text-align:center\">Fertiggestellt</td>\n".
		"        <td class=\"tablecat\">Se.</td>\n".
		"        <td class=\"tablecat\">Le.</td>\n".
		"        <td class=\"tablecat\">Hochgel.</td>\n".
		"        <td class=\"tablecat\">Runtergel.</td>\n".
		"    </tr>\n";
	foreach($data as $arr){
		$catimage = htmlspecialchars($arr["image"]);
		$catname = htmlspecialchars($arr["catname"]);
		$ret .= "    <tr>\n".
			"        <td class=\"tableb\" style=\"padding: 0px\"><img src=\"" . $GLOBALS["PIC_BASE_URL"] . $catimage . "\" alt=\"" . $catname . "\" title=\"" . $catname . "\" width=\"42\" height=\"42\"></td>\n".
			"        <td class=\"tablea\">";
		if($arr["torrent_origid"] > 0){
			$seeders = number_format($arr["seeders"]);
			$leechers = number_format($arr["leechers"]);
			$ret .= "<a href=\"details.php?id=" . $arr["torrent"] . "&amp;hit=1\"><b>" . htmlspecialchars($arr["torrent_name"]) . "</b></a></td>\n".
				"        <td class=\"tableb\" style=\"text-align:center\">" . str_replace(" ", "<br />", date("d.m.Y H:i:s", sql_timestamp_to_unix_timestamp($arr["complete_time"]))) . "</td>\n".
				"        <td class=\"tablea\" style=\"text-align:right\">" . $seeders . "</td>\n".
				"        <td class=\"tableb\" style=\"text-align:right\">" . $leechers . "</td>\n".
				"        <td class=\"tablea\" style=\"text-align:right\" nowrap=\"nowrap\">" . mksize($arr["uploaded"]) . "<br>@ " . mksize($arr["uploaded"] / max(1, $arr["uploadtime"])) . "/s</td>\n".
				"        <td class=\"tablea\" style=\"text-align:right\" nowrap=\"nowrap\">" . mksize($arr["downloaded"]) . "<br>@ " . mksize($arr["downloaded"] / max(1, $arr["downloadtime"])) . "/s</td>\n".
				"    </tr>\n";
		}else{
			$ret .= "<b>" . htmlspecialchars($arr["torrent_name"]) . "</b></td>\n".
				"        <td class=\"tableb\" style=\"text-align:center\">" . str_replace(" ", "<br />", date("d.m.Y H:i:s", sql_timestamp_to_unix_timestamp($arr["complete_time"]))) . "</td>\n".
				"        <td class=\"tablea\" style=\"text-align:center\" colspan=\"4\">Gelöscht</td>\n".
				"    </tr>\n";
		}
	}
	$ret .= "</table>\n";
	return $ret;
}
?>