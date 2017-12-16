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

ob_start("ob_gzhandler");

require_once("include/bittorrent.php");

function dltable($name, $arr, $torrent){
	global $CURUSER;

	$s = "<b>" . count($arr) . " " . $name . "</b>\n";
	if (!count($arr))
		return $s;

	$s .= "\n";
	$s .= "<table width=\"100%\" class=\"tableinborder\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">\n";
	$s .= "    <tr>\n";
	$s .= "        <td class=\"tablecat\">Benutzer/IP</td>\n";
	$s .= "        <td class=\"tablecat\" style=\"text-align:center\">Erreichbar</td>\n";
	$s .= "        <td class=\"tablecat\" style=\"text-align:center\">Hochgeladen</td>\n";
	$s .= "        <td class=\"tablecat\" style=\"text-align:center\">Rate</td>\n";
	$s .= "        <td class=\"tablecat\" style=\"text-align:center\">Runtergeladen</td>\n";
	$s .= "        <td class=\"tablecat\" style=\"text-align:center\">Rate</td>\n";
	$s .= "        <td class=\"tablecat\" style=\"text-align:center\">Ratio</td>\n";
	$s .= "        <td class=\"tablecat\" style=\"text-align:center\">Fertig</td>\n";
	$s .= "        <td class=\"tablecat\" style=\"text-align:center\">Verbunden</td>\n";
	$s .= "        <td class=\"tablecat\" style=\"text-align:center\">Unt&auml;tig</td>\n";
	$s .= "        <td class=\"tablecat\" style=\"text-align:center\">Client</td>";
	$s .= "    </tr>\n";

	$mod = get_user_class() >= UC_MODERATOR;
	$now = time();

	foreach($arr as $e){
		// user/ip/port
		// check if anyone has this ip
		$qry = $GLOBALS['DB']->prepare("SELECT username, privacy, class, donor, enabled, warned, added FROM users WHERE id= :id ORDER BY last_access DESC LIMIT 1");
		$qry->bindParam(':id', $e["userid"], PDO::PARAM_INT);
		$qry->execute();
		if($qry->rowCount())
			$una = $qry->FetchAll(PDO::FETCH_ASSOC);
		//$unr = mysql_query("SELECT username, privacy, class, donor, enabled, warned, added FROM users WHERE id=$e[userid] ORDER BY last_access DESC LIMIT 1") or die;
		//$una = mysql_fetch_array($unr);
		$tdclass = $CURUSER && $e["userid"] == $CURUSER["id"] ? " class=\"inposttable\"": " class=\"tableb\"";

		if ($una["privacy"] == "strong")
			continue;

		$s .= "    <tr>\n";

		if ($una["username"])
			$s .= "        <td" . $tdclass . " nowrap=\"nowrap\"><a href=\"userdetails.php?id=" . $e["userid"] . "\"><font class=\"" . get_class_color($una["class"]) . "\"><b>" . $una["username"] . "</b></font></a>&nbsp;" . get_user_icons($una) . "</td>\n";
		else
			$s .= "        <td" . $tdclass . ">" . ($mod ? $e["ip"] : preg_replace('/\.\d+$/', ".xxx", $e["ip"])) . "</td>\n";

		$revived = $e["revived"] == "yes";
		$s .= "        <td" . $tdclass . " style=\"text-align:center\">" . ($e["connectable"] == "yes" ? "Ja" : "<font color=\"red\">Nein</font>") . "</td>\n";
		$s .= "        <td" . $tdclass . " style=\"text-align:right\">" . mksize($e["uploaded"]) . "</td>\n";
		$s .= "        <td" . $tdclass . " style=\"text-align:right\" nowrap=\"nowrap\">" . mksize($e["uploaded"] / max(1, $e["uploadtime"])) . "/s</td>\n";
		$s .= "        <td" . $tdclass . " style=\"text-align:right\">" . mksize($e["downloaded"]) . "</td>\n";
		$s .= "        <td" . $tdclass . " style=\"text-align:right\" nowrap=\"nowrap\">" . mksize($e["downloaded"] / max(1, $e["downloadtime"])) . "/s</td>\n";

		if($e["downloaded"]){
			$ratio = floor(($e["uploaded"] / $e["downloaded"]) * 1000) / 1000;
			$s .= "        <td" . $tdclass . " style=\"text-align:right\"><font color=\"" . get_ratio_color($ratio) . "\">" . number_format($ratio, 3) . "</font></td>\n";
		}else{
			if($e["uploaded"])
			$s .= "        <td" . $tdclass . " style=\"text-align:right\">Inf.</td>\n";
			else
			$s .= "        <td" . $tdclass . " style=\"text-align:right\">---</td>\n";
		}

		$s .= "        <td" . $tdclass . " style=\"text-align:right\"><div title=\"" . sprintf("%.2f%%", 100 * (1 - ($e["to_go"] / $torrent["size"]))) . "\"style=\"border:1px solid black;padding:0px;width:40px;height:10px;\"><div style=\"border:none;width:" . sprintf("%.2f", 40 * (1 - ($e["to_go"] / $torrent["size"]))) . "px;height:10px;background-image:url(" . $GLOBALS["PIC_BASE_URL"] . "ryg-verlauf-small.png);background-repeat:no-repeat;\"></div></div></td>\n";
		$s .= "        <td" . $tdclass . " nowrap style=\"text-align:right\">" . mkprettytime($now - $e["st"]) . "</td>\n";
		$s .= "        <td" . $tdclass . " style=\"text-align:right\">" . mkprettytime($now - $e["la"]) . "</td>\n";
		$s .= "        <td" . $tdclass . " style=\"text-align:left\">" . htmlspecialchars(getagent($e["agent"], $e["peer_id"])) . "</td>\n";
		$s .= "    </tr>\n";
	}
	$s .= "</table>\n";
	return $s;
}

dbconn(false);

loggedinorreturn();


$id = $_GET["id"];
$id = 0 + $id;
if (!isset($id) || !$id)
    die();

$t_query = "SELECT torrents.seeders, torrents.banned, torrents.leechers, torrents.info_hash, ";
$t_query .= "torrents.filename, LENGTH(torrents.nfo) AS nfosz, UNIX_TIMESTAMP() - UNIX_TIMESTAMP(";
$t_query .= "torrents.last_action) AS lastseed, torrents.numratings, torrents.name, IF(torrents.numratings";
$t_query .= " < " . $GLOBALS["MINVOTES"] . ", NULL, ROUND(torrents.ratingsum / torrents.numratings, 1)) AS rating, ";
$t_query .= "torrents.owner, torrents.gu_agent, torrents.save_as, torrents.descr, torrents.visible, torrents.size, torrents.activated, ";
$t_query .= "torrents.added, torrents.views, torrents.hits, torrents.times_completed, torrents.id, ";
$t_query .= "torrents.type, torrents.numfiles, torrents.numpics, categories.name AS cat_name, users.username, users.class FROM ";
$t_query .= "torrents LEFT JOIN categories ON torrents.category = categories.id LEFT JOIN users ON ";
$t_query .= "torrents.owner = users.id WHERE torrents.id = $id";

$res = mysql_query($t_query) or sqlerr();
$row = mysql_fetch_array($res);

$owned = $moderator = 0;
if ($CURUSER["id"] == $row["owner"])
    $owned = 1;
// We limit access for the GU team on torrents of users below UC_UPLOADER
elseif (get_user_class() >= UC_MODERATOR || ($row["activated"] == "no" && get_user_class() == UC_GUTEAM && $row["class"] < UC_UPLOADER))
    $owned = $moderator = 1;

if (!$row || ($row["banned"] == "yes" && !$moderator))
    stderr("Fehler", "Es existiert kein Torrent mit der ID $id.");

if (!$owned && $row["activated"] != "yes")
    stderr("Fehler", "Es existiert kein Torrent mit der ID $id.");

if ($_GET["hit"]) {
    mysql_query("UPDATE torrents SET views = views + 1 WHERE id = $id");
    if ($_GET["tocomm"])
        header("Location: details.php?id=" . $id . "&page=0#startcomments");
    elseif ($_GET["filelist"])
        header("Location: details.php?id=" . $id . "&filelist=1#filelist");
    elseif ($_GET["toseeders"])
        header("Location: details.php?id=" . $id . "&dllist=1#seeders");
    elseif ($_GET["todlers"])
        header("Location: details.php?id=" . $id . "&dllist=1#leechers");
    elseif ($_GET["tosnatchers"])
        header("Location: details.php?id=" . $id . "&snatcher=1#snatcher");
    else
        header("Location: details.php?id=" . $id);
    exit();
} 

if ($_GET["activate"] && $moderator && $row["activated"] != "yes") {
    mysql_query("UPDATE `torrents` SET `activated`='yes',`added`=NOW() WHERE `id`=" . $row["id"]);
    sendPersonalMessage(0, $row["owner"], "Dein Torrent wurde von einem Moderator freigeschaltet!", "Dein Torrent \"[url=$DEFAULTBASEURL/details.php?id=" . $row["id"] . "]" . $row["name"] . "[/url]\" wurde von [url=$DEFAULTBASEURL/userdetails.php?id=" . $CURUSER["id"] . "]" . $CURUSER["username"] . "[/url] freigeschaltet. Du musst nun die Torrent-Datei erneut vom Tracker herunterladen, und kannst dann mit dem Seeden beginnen.\n\nBei Fragen lies bitte zuerst das [url=$DEFAULBASEURL/faq.php]FAQ[/url]!", PM_FOLDERID_SYSTEM);
    if (get_cur_wait_time($row["owner"]))
        mysql_query("INSERT INTO `nowait` (user_id,torrent_id,status,grantor,msg) VALUES (" . $row["owner"] . "," . $row["id"] . ",'granted'," . $CURUSER["id"] . ",'Automatische Wartezeitaufhebung durch Torrent-Freischaltung')");
    write_log("torrentgranted", "Der Torrent <a href=\"details.php?id=" . $row["id"] . "\">" . htmlspecialchars($row["name"]) . "</a> wurde von '<a href=\"userdetails.php?id=$CURUSER[id]\">$CURUSER[username]</a>' freigeschaltet.");
    stderr("Torrent freigeschaltet", "Der Torrent wurde freigeschaltet, und ist nun über die Torrent-Suche auffindbar. Ebenso kann der Besitzer nun beginnen, den Torrent zu seeden. Eine Persönliche Nachricht wurde an den Uploader versendet, die ihn über die Freischaltung informiert.");
}
if ($_GET["agenttakeover"] == "acquire" && $row["gu_agent"] == 0 && $moderator && $row["activated"] != "yes") {
    $row["gu_agent"] = $CURUSER["id"];
    mysql_query("UPDATE `torrents` SET `gu_agent`=".$CURUSER["id"]." WHERE `id`=".$row["id"]);
} 
if ($_GET["agenttakeover"] == "release" && $row["gu_agent"] == $CURUSER["id"] && $moderator && $row["activated"] != "yes") {
    $row["gu_agent"] = 0;
    mysql_query("UPDATE `torrents` SET `gu_agent`=0 WHERE `id`=".$row["id"]);
} 

if (!isset($_GET["page"])) {
    stdhead("Details zu Torrent \"" . $row["name"] . "\"");

    $spacer = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

    if (!$_GET["snatcher"])
        $snatchersmax = "LIMIT 10";
    else
        $snatchersmax = "";

    $res = mysql_query("SELECT DISTINCT(user_id) as id, username, class, peers.id as peerid FROM completed,users LEFT JOIN peers ON peers.userid=users.id AND peers.torrent=$id AND peers.seeder='yes' WHERE completed.user_id=users.id AND completed.torrent_id=$id ORDER BY complete_time DESC $snatchersmax");

    $last10users = "";
    while ($arr = mysql_fetch_assoc($res)) {
        if ($last10users) $last10users .= ", ";
        $arr["username"] = "<font class=\"" . get_class_color($arr["class"]) . "\">" . $arr["username"] . "</font>";
        if ($arr["peerid"] > 0) {
            $arr["username"] = "<b>" . $arr["username"] . "</b>";
        } 
        $last10users .= "<a href=\"userdetails.php?id=" . $arr["id"] . "\">" . $arr["username"] . "</a>";
    } 

    if ($last10users == "")
        $last10users = "Diesen Torrent hat noch niemand fertiggestellt.";
    else
        $last10users .= "<br/><br/>(Fettgedruckte User seeden noch)";

    if ($_GET["edited"]) {
        begin_frame("Erfolgreich bearbeitet!", false, "650px");
        echo "<p>Der Torrent wurde erfolgreich geändert. Die Änderungen sind sofort für andere sichtbar.</p>";
        if (isset($_GET["returnto"]))
            echo "<p><b>Gehe dorthin zur&uuml;ck, von <a href=\"" . htmlspecialchars($_GET["returnto"]) . "\">wo Du gekommen bist</a>.</b></p>\n";
        end_frame();
    } elseif (isset($_GET["searched"])) {
        begin_frame("Suche", false, "650px");
        echo "<p>Deine Suche nach \"" . htmlspecialchars($_GET["searched"]) . "\" hat ein einzelnes Ergebnis zur&uuml;ckgegeben:</p>\n";
        end_frame();
    } elseif ($_GET["rated"]) {
        begin_frame("Bewertung hinzugef&uuml;gt!", false, "650px");
        echo "<p>Deine Bewertung wurde gespeichert. Du kannst nun die Ergebnisse ansehen.</p>\n";
        end_frame();
    } 

    $s = $row["name"];

    ?>
<table cellpadding="4" cellspacing="1" border="0" style="width:650px" class="tableinborder">
<tr>
<td class="tabletitle" colspan="10" width="100%" style="text-align:center"><img src="<?=$GLOBALS["PIC_BASE_URL"]?>viewmag.png" width="22" height="22" alt="" style="vertical-align: middle;"> <b>Details zu <?=$row["name"]?> <?php if ($row["class"] < UC_UPLOADER) echo '<font color="red">(Gast-Upload)</font>';
    ?></b></td> 
</tr><tr><td width="100%" class="tablea">
<?php

    print("<table width=\"750\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\" class=\"tableinborder\">\n");

    $url = "edit.php?id=" . $row["id"];
    if (isset($_GET["returnto"])) {
        $addthis = "&amp;returnto=" . urlencode($_GET["returnto"]);
        $url .= $addthis;
        $keepget .= $addthis;
    } 
    $editlink = "a href=\"$url\" class=\"sublink\"";

    $waittime = get_wait_time($CURUSER["id"], $id);

    if ($row["activated"] != "yes") {
        if ($moderator) {
            echo "<tr><td class=\"tableb\" width=\"1%\">Freischalten</td><td class=\"tableb\" width=\"99%\" style=\"text-align:left\">";
            if ($row["gu_agent"] > 0) {
                $gu_agent = mysql_fetch_assoc(mysql_query("SELECT `username` FROM `users` WHERE `id`=".$row["gu_agent"]));
                echo "<font color=\"red\">Dieser Gast-Upload wird bereits von <b><a href=\"userdetails.php?id=".$row["gu_agent"]."\">".htmlspecialchars($gu_agent["username"])."</a></b> bearbeitet. ";
                if ($row["gu_agent"] == $CURUSER["id"]) {
                    echo "(<a class=\"index\" href=\"details.php?id=" . $row["id"] . "&amp;agenttakeover=release\">Bearbeitung abgeben</a>)";
                }
                echo "<br>";
            } else {
                echo "<a class=\"index\" href=\"details.php?id=" . $row["id"] . "&amp;agenttakeover=acquire\">Bearbeitung dieses Gastuploads übernehmen</a><br>";
            }
            echo "<a class=\"index\" href=\"details.php?id=" . $row["id"] . "&amp;activate=1\">Torrent freischalten</a></td></tr>";
        } else {
            echo "<tr><td class=\"tableb\" width=\"1%\">Freischalten</td><td class=\"tableb\" width=\"99%\" style=\"text-align:left\">Dieser Torrent muss erst von einem Moderator freigeschaltet werden, bevor er sichtbar wird.</td></tr>";
        } 
    } elseif ($waittime) {
        print("<tr><td class=\"tableb\" width=\"1%\">Herunterladen</td><td class=\"tableb\" width=\"99%\" style=\"text-align:left\">Dieser Torrent kann erst nach Ablauf der Wartezeit heruntergeladen werden.</td></tr>");
    } else {
        if ($GLOBALS["DOWNLOAD_METHOD"] == DOWNLOAD_REWRITE)
            $download_url = "download/" . $id . "/" . rawurlencode($row["filename"]);
        else
            $download_url = "download.php?torrent=" . $id;

        print("<tr><td class=\"tableb\" width=\"1%\">Herunterladen</td><td class=\"tableb\" width=\"99%\" style=\"text-align:left\"><a class=\"index\" href=\"$download_url\">" . htmlspecialchars($row["filename"]) . "</a></td></tr>");

        if ($CURUSER["wgeturl"] == "yes") {
            $wget_url = "wget --header='Cookie: uid=" . $CURUSER["id"] . "; pass=" . $CURUSER["passhash"] . "' '" . $BASEURL . "/" . $download_url . "' -O '" . htmlspecialchars($row["filename"]) . "'";
            print("<tr><td class=\"tableb\" width=\"1%\">wget-Kommando</td><td class=\"tableb\" width=\"99%\" style=\"text-align:left\"><input type=\"text\" readonly=\"readonly\" size=\"80\" value=\"" . htmlspecialchars($wget_url) . "\"></td></tr>");
        } 
    } 

    tr("Hash-Wert", preg_replace_callback('/./s', "hex_esc", hash_pad($row["info_hash"])));

    if ($waittime) {
        if ($row["activated"] != "yes" && $owned)
            tr("Wartezeitaufhebung", "Du benötigst eine Wartezeitaufhebung für diesen Torrent. Diese wird beim Freischalten des Torrents jedoch automatisch erteilt.", 1);
        else {
            if ($waittime > 2)
                tr("Wartezeitaufhebung", "<form action=\"requestnowait.php\" method=\"post\">Antragsgrund: <input type=\"hidden\" name=\"id\" value=\"$id\"><input type=\"text\" size=\"60\" maxlength=\"2000\" name=\"msg\"> <input type=\"submit\" value=\"Beantragen\"></form><br><a href=\"faq.php#dlf\">Bitte beachte die Regeln zur Wartezeitaufhebung!</a>", 1);
            elseif ($waittime)
                tr("Wartezeitaufhebung", "Du musst noch min. 3 Stunden Wartezeit übrig haben, um eine Aufhebung beantragen zu können. (<a href=\"faq.php#dlf\">Hilfe</a>)", 1);
        } 
    } 

    if (!empty($row["descr"])) {
        if ($row["numpics"] > 0) {
            $img_prev = "[center]";
            for ($I = 1; $I <= $row["numpics"]; $I++)
            $img_prev .= "[url=" . $DEFAULTBASEURL . "/" . $GLOBALS["BITBUCKET_DIR"] . "/f-$id-$I.jpg][img=" . $DEFAULTBASEURL . "/" . $GLOBALS["BITBUCKET_DIR"] . "/t-$id-$I.jpg][/url] ";
            $img_prev .= "[/center]\n\n";
            $row["descr"] = $img_prev . $row["descr"];
        } 
        tr("Beschreibung", format_comment($row["descr"]), 1);
    } 

    if ($row["nfosz"] > 0)
        tr("NFO", "<a href=\"viewnfo.php?id=$row[id]\"><b>NFO anzeigen</b></a> (" . mksize($row["nfosz"]) . ")", 1);

    if ($row["visible"] == "no")
        tr("Sichtbar", "<b>Nein</b> (Tot)", 1);

    if ($moderator)
        tr("Gebannt", $row["banned"]);

    if (isset($row["cat_name"]))
        tr("Typ", $row["cat_name"]);
    else
        tr("Typ", "(nicht ausgewählt)");

    tr("Letzter&nbsp;Seeder", "Letzte Aktivität ist " . mkprettytime($row["lastseed"]) . " her");
    tr("Größe", mksize($row["size"]) . " (" . number_format($row["size"]) . " Bytes)");
    tr("Hinzugefügt", $row["added"]);
    tr("Aufrufe", $row["views"]);
    tr("Hits", $row["hits"]);
    tr("Fertiggestellt", $row["times_completed"] . " mal");

    if (!$_GET["snatcher"]) {
        tr("<a name=\"snatcher\"></a>Letzte 10<br /><a href=\"details.php?id=$id&amp;snatcher=1$keepget#snatcher\" class=\"sublink\">[Alle anzeigen]</a>", $last10users, 1);
    } else {
        tr("<a name=\"snatcher\"></a>Fertiggestellt<br />(Benutzer)<br /><a href=\"details.php?id=$id$keepget#snatcher\" class=\"sublink\">[Liste verbergen]</a>", $last10users, 1);
    } 

    $keepget = "";
    $uprow = (isset($row["username"]) ? ("<a href=\"userdetails.php?id=" . $row["owner"] . "\"><b>" . htmlspecialchars($row["username"]) . "</b></a>") : "<i>Unbekannt</i>");

    if ($owned)
        $uprow .= " $spacer<$editlink><b>[Diesen Torrent bearbeiten]</b></a>";

    tr("Hochgeladen&nbsp;von", $uprow, 1);

    if ($row["type"] == "multi") {
        if (!$_GET["filelist"])
            tr("Anzahl Dateien<br /><a href=\"details.php?id=$id&amp;filelist=1$keepget#filelist\" class=\"sublink\">[Liste anzeigen]</a>", $row["numfiles"] . " Dateien", 1);
        else {
            tr("Anzahl Dateien", $row["numfiles"] . " Dateien", 1);

            $s = "<table class=tableinborder border=0 cellspacing=1 cellpadding=4>\n";

            $subres = mysql_query("SELECT * FROM files WHERE torrent = $id ORDER BY id");
            $s .= "<tr><td class=tablecat>Pfad</td><td class=tablecat align=right>Größe</td></tr>\n";
            while ($subrow = mysql_fetch_array($subres)) {
                $s .= "<tr><td class=\"tablea\">" . $subrow["filename"] . "</td><td class=\"tableb\" align=\"right\">" . mksize($subrow["size"]) . "</td></tr>\n";
            } 

            $s .= "</table>\n";
            tr("<a name=\"filelist\">Dateiliste</a><br /><a href=\"details.php?id=$id$keepget\" class=\"sublink\">[Liste verbergen]</a>", $s, 1);
        } 
    } 

    if (!$_GET["dllist"]) {
        tr("Peers<br /><a href=\"details.php?id=$id&amp;dllist=1$keepget#seeders\" class=\"sublink\">[Liste anzeigen]</a>", $row["seeders"] . " Seeder, " . $row["leechers"] . " Leecher = " . ($row["seeders"] + $row["leechers"]) . " Peer(s) gesamt", 1);
    } else {
        $downloaders = array();
        $seeders = array();
        $subres = mysql_query("SELECT peer_id, seeder, ip, port, traffic.uploaded AS uploaded, traffic.downloaded AS downloaded, traffic.downloadtime AS downloadtime, traffic.uploadtime AS uploadtime, to_go, UNIX_TIMESTAMP(started) AS st, connectable, agent, UNIX_TIMESTAMP(last_action) AS la, peers.userid FROM peers LEFT JOIN traffic ON peers.userid=traffic.userid AND peers.torrent=traffic.torrentid WHERE torrent = $id") or sqlerr();
        while ($subrow = mysql_fetch_array($subres)) {
            if ($subrow["seeder"] == "yes")
                $seeders[] = $subrow;
            else
                $downloaders[] = $subrow;
        } 

        function leech_sort($a, $b)
        {
            if (isset($_GET["usort"])) return seed_sort($a, $b);
            $x = $a["to_go"];
            $y = $b["to_go"];
            if ($x == $y)
                return 0;
            if ($x < $y)
                return -1;
            return 1;
        } 

        function seed_sort($a, $b)
        {
            $x = $a["uploaded"];
            $y = $b["uploaded"];
            if ($x == $y)
                return 0;
            if ($x < $y)
                return 1;
            return -1;
        } 

        usort($seeders, "seed_sort");
        usort($downloaders, "leech_sort");

        tr("<a name=\"seeders\">Seeder</a><br /><a href=\"details.php?id=$id$keepget\" class=\"sublink\">[Liste verbergen]</a>", dltable("Seeder", $seeders, $row), 1);
        tr("<a name=\"leechers\">Leecher</a><br /><a href=\"details.php?id=$id$keepget\" class=\"sublink\">[Liste verbergen]</a>", dltable("Leecher", $downloaders, $row), 1);
    } 

    print("</table></td></tr></table><br>\n");
    begin_frame("<img src=\"" . $GLOBALS["PIC_BASE_URL"] . "comments.png\" width=\"22\" height=\"22\" alt=\"\" style=\"vertical-align: middle;\"> Kommentare", false, "750px;");
} else {
    stdhead("Kommentare zu Torrent \"" . $row["name"] . "\"");
    begin_frame("<img src=\"" . $GLOBALS["PIC_BASE_URL"] . "comments.png\" width=\"22\" height=\"22\" alt=\"\" style=\"vertical-align: middle;\"> Kommentare zu <a href=\"details.php?id=$id\">" . $row["name"] . "</a>", false, "750px;");
} 

print("<a name=\"startcomments\"></a>\n");

$commentbar = "<table class=\"tableinborder\" border=\"0\" cellpadding=\"4\" cellspacing=\"1\" width=\"100%\">"
 . "<tr><td class=\"tablea\" colspan=\"10\" width=\"100%\" style=\"text-align: center\">"
 . "<b><a class=\"index\" href=\"comment.php?action=add&amp;tid=$id\">"
 . "Kommentar hinzufügen</a></b></td></tr></table><br>";

$subres = mysql_query("SELECT COUNT(*) FROM comments WHERE torrent = $id");
$subrow = mysql_fetch_array($subres);
$count = $subrow[0];

if (!$count) {

    ?>
<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
    <tr>
        <td class="tabletitle" width="100%" style="text-align:center"><b>Bisher noch keine Kommentare</b></td> 
    </tr>
</table>
<br>
<?php
} else {
    list($pagertop, $pagerbottom, $limit) = pager(20, $count, "details.php?id=$id&", array(lastpagedefault => 1));

    $subres = mysql_query("SELECT comments.id, text, user, comments.added, editedby, editedat, avatar, warned, " . "username, title, class, donor FROM comments LEFT JOIN users ON comments.user = users.id WHERE torrent = " . "$id ORDER BY comments.id $limit") or sqlerr(__FILE__, __LINE__);
    $allrows = array();

    while ($subrow = mysql_fetch_array($subres))
    $allrows[] = $subrow;

    print($commentbar);
    print($pagertop);

    commenttable($allrows);

    print($pagerbottom);
} 

print($commentbar);
end_frame();

stdfoot();

hit_end();

?>
