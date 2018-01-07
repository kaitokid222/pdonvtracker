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

dbconn(false);

loggedinorreturn();

function bark($msg)
{
    stdhead();
    stdmsg("Error", $msg);
    stdfoot();
    exit;
} 

function get_domain($ip)
{
    $dom = @gethostbyaddr($ip);
    if ($dom == $ip || @gethostbyname($dom) != $ip)
        return "<a href=\"whois.php?ip=$ip\" target=\"nvwhois\">$ip</a>";
    else {
        $dom = strtoupper($dom);
        return "<a href=\"whois.php?ip=$ip\" target=\"nvwhois\">$ip</a> ($dom)";
    } 
} 

function maketable($res)
{
    $ret = "<table class=tableinborder border=0 cellspacing=1 cellpadding=4 width=\"100%\">" . "<tr><td class=tablecat align=center>Typ</td><td class=tablecat width=\"100%\">Name</td><td class=tablecat align=center>TTL</td><td class=tablecat align=center>Größe</td><td class=tablecat align=right>Se.</td><td class=tablecat align=right>Le.</td><td class=tablecat align=center>Hochgel.</td>\n" . "<td class=tablecat align=center>Runtergel.</td><td class=tablecat align=center>Ratio</td></tr>\n";
    while ($arr = mysql_fetch_assoc($res)) {
        if ($arr["downloaded"] > 0) {
            $ratio = number_format($arr["uploaded"] / $arr["downloaded"], 3);
            $ratio = "<font color=" . get_ratio_color($ratio) . ">$ratio</font>";
        } else
        if ($arr["uploaded"] > 0)
            $ratio = "Inf.";
        else
            $ratio = "---";
        $catimage = htmlspecialchars($arr["image"]);
        $catname = htmlspecialchars($arr["catname"]);
        $ttl = (28 * 24) - floor((time() - sql_timestamp_to_unix_timestamp($arr["added"])) / 3600);
        if ($ttl == 1) $ttl .= "<br>hour";
        else $ttl .= "<br>hours";
        $size = str_replace(" ", "<br>", mksize($arr["size"]));
        $uploaded = str_replace(" ", "<br>", mksize($arr["uploaded"]));
        $downloaded = str_replace(" ", "<br>", mksize($arr["downloaded"]));
        $seeders = number_format($arr["seeders"]);
        $leechers = number_format($arr["leechers"]);
        $ret .= "<tr><td class=tableb style='padding: 0px'><img src=\"" . $GLOBALS["PIC_BASE_URL"] . $catimage . "\" alt=\"$catname\" title=\"$catname\" width=42 height=42></td>\n" . "<td class=tablea><a href=details.php?id=$arr[torrent]&amp;hit=1><b>" . htmlspecialchars($arr["torrentname"]) . "</b></a></td><td class=tableb align=center>$ttl</td><td class=tablea align=center>$size</td><td class=tableb align=right>$seeders</td><td class=tablea align=right>$leechers</td><td class=tableb align=center>$uploaded</td>\n" . "<td class=tablea align=center>$downloaded</td><td class=tableb align=center>$ratio</td></tr>\n";
    } 
    $ret .= "</table>\n";
    return $ret;
} 

function makecomptable($res)
{
    $ret = "<table class=tableinborder border=0 cellspacing=1 cellpadding=4 width=\"100%\">" . "<tr><td class=tablecat style=\"text-align:center\">Typ</td><td class=tablecat width=\"100%\">Name</td>\n" . "<td class=tablecat style=\"text-align:center\">Fertiggestellt</td><td class=tablecat>Se.</td><td class=tablecat>Le.</td><td class=tablecat>Hochgel.</td><td class=tablecat>Runtergel.</td></tr>\n";
    while ($arr = mysql_fetch_assoc($res)) {
        $catimage = htmlspecialchars($arr["image"]);
        $catname = htmlspecialchars($arr["catname"]);
        $ret .= "<tr><td class=\"tableb\" style=\"padding: 0px\"><img src=\"" . $GLOBALS["PIC_BASE_URL"] . $catimage . "\" alt=\"$catname\" title=\"$catname\" width=42 height=42></td>\n<td class=tablea>";
        if ($arr["torrent_origid"] > 0) {
            $seeders = number_format($arr["seeders"]);
            $leechers = number_format($arr["leechers"]);
            $ret .= "<a href=\"details.php?id=$arr[torrent]&amp;hit=1\"><b>" . htmlspecialchars($arr["torrent_name"]) . "</b></a></td>";
            $ret .= "<td class=\"tableb\" style=\"text-align:center\">" . str_replace(" ", "<br />", date("d.m.Y H:i:s", sql_timestamp_to_unix_timestamp($arr["complete_time"]))) . "</td>";
            $ret .= "<td class=\"tablea\" style=\"text-align:right\">$seeders</td>";
            $ret .= "<td class=\"tableb\" style=\"text-align:right\">$leechers</td>";
            $ret .= "<td class=\"tablea\" style=\"text-align:right\" nowrap=\"nowrap\">" . mksize($arr["uploaded"]) . "<br>@ " . mksize($arr["uploaded"] / max(1, $arr["uploadtime"])) . "/s</td>";
            $ret .= "<td class=\"tablea\" style=\"text-align:right\" nowrap=\"nowrap\">" . mksize($arr["downloaded"]) . "<br>@ " . mksize($arr["downloaded"] / max(1, $arr["downloadtime"])) . "/s</td>";
            $ret .= "</tr>\n";
        } else {
            $ret .= "<b>" . htmlspecialchars($arr["torrent_name"]) . "</b>";
            $ret .= "</td><td class=\"tableb\" style=\"text-align:center\">" . str_replace(" ", "<br />", date("d.m.Y H:i:s", sql_timestamp_to_unix_timestamp($arr["complete_time"]))) . "</td>";
            $ret .= "<td class=\"tablea\" style=\"text-align:center\" colspan=\"4\">Gelöscht</td></tr>\n";
        } 
    } 

    $ret .= "</table>\n";
    return $ret;
} 

$id = intval($_GET["id"]);

if (!is_valid_id($id))
    bark("Bad ID $id.");

$r = @mysql_query("SELECT * FROM users WHERE id=$id") or sqlerr();
$user = mysql_fetch_array($r) or bark("No user with ID $id.");
if ($user["status"] == "pending") die;
$r = mysql_query("SELECT id, name, added, seeders, leechers, category FROM torrents WHERE `activated`='yes' AND owner=$id ORDER BY added DESC") or sqlerr();
if (mysql_num_rows($r) > 0) {
    $torrents = "<table class=tableinborder border=0 cellspacing=1 cellpadding=4 width=\"100%\">\n" . "<tr><td class=tablecat>Typ</td><td class=tablecat width=\"100%\">Name</td><td class=tablecat>Hochgeladen</td><td class=tablecat>Seeder</td><td class=tablecat>Leecher</td></tr>\n";
    while ($a = mysql_fetch_assoc($r)) {
        $r2 = mysql_query("SELECT name, image FROM categories WHERE id=$a[category]") or sqlerr(__FILE__, __LINE__);
        $a2 = mysql_fetch_assoc($r2);
        $cat = "<img src=\"" . $GLOBALS["PIC_BASE_URL"] . $a2["image"] . "\" alt=\"$a2[name]\" title=\"$a2[name]\">";
        $torrents .= "<tr><td class=tableb style='padding: 0px'>$cat</td><td class=tablea><a href=details.php?id=" . $a["id"] . "&hit=1><b>" . htmlspecialchars($a["name"]) . "</b></a></td>" . "<td class=tableb align=center>" . str_replace(" ", "<br />", date("d.m.Y H:i:s", sql_timestamp_to_unix_timestamp($a["added"]))) . "</td>" . "<td class=tablea align=right>$a[seeders]</td><td class=tableb align=right>$a[leechers]</td></tr>\n";
    } 
    $torrents .= "</table>";
} 

if ($GLOBALS["CLIENT_AUTH"] == CLIENT_AUTH_PASSKEY)
    $announceurl = preg_replace("/\\{KEY\\}/", preg_replace_callback('/./s', "hex_esc", str_pad($user["passkey"], 8)), $GLOBALS["PASSKEY_ANNOUNCE_URL"]);
else
    $announceurl = $GLOBALS["ANNOUNCE_URLS"][0];

$addr = "";
$peer_addr = "";
if ($user["ip"] && (get_user_class() >= UC_MODERATOR || $user["id"] == $CURUSER["id"])) {
    $ip = $user["ip"];
    $addr = get_domain($ip);

    $res = mysql_query("SELECT DISTINCT(ip) AS ip FROM peers WHERE userid=$id");
    if (mysql_num_rows($res)) {
        while ($peer_ip = mysql_fetch_assoc($res)) {
            if ($peer_addr != "")
                $peer_addr .= "<br>";
            $peer_addr .= get_domain($peer_ip["ip"]);
        } 
    } 
} 
if ($user["added"] == "0000-00-00 00:00:00") {
    $joindate = 'N/A';
    $down_per_day = "";
    $upped_per_day = "";
} else {
    $joindate = "$user[added] (Vor " . get_elapsed_time(sql_timestamp_to_unix_timestamp($user["added"])) . ")";
    $days_regged = round((time() - sql_timestamp_to_unix_timestamp($user["added"])) / 86400);
    if ($days_regged) {
        $down_per_day = "(" . mksize(floor($user["downloaded"] / $days_regged)) . " / Tag)";
        $upped_per_day = "(" . mksize(floor($user["uploaded"] / $days_regged)) . " / Tag)";
    } else{
        $down_per_day = "(0,00 KB / Tag)";
        $upped_per_day = "(0,00 KB / Tag)";
	}
} 
$lastseen = $user["last_access"];
if ($lastseen == "0000-00-00 00:00:00")
    $lastseen = "nie";
else {
    $lastseen .= " (Vor " . get_elapsed_time(sql_timestamp_to_unix_timestamp($lastseen)) . ")";
}

//SELECT COUNT(*) FROM comments WHERE user= {$user['id']}');
$torrentcomments = $database->row_count('comments', 'user = ' . $user['id']);
$forumposts = $database->row_count('posts', 'userid = ' . $user['id']);


// if ($user['donated'] > 0)
// $don = "<img src=\"".$GLOBALS["PIC_BASE_URL"]."starbig.gif\">";
$res = mysql_query("SELECT name,flagpic FROM countries WHERE id=$user[country] LIMIT 1") or sqlerr();
if (mysql_num_rows($res) == 1) {
    $arr = mysql_fetch_assoc($res);
    $country = "<img src=\"" . $GLOBALS["PIC_BASE_URL"] . "flag/" . $arr["flagpic"] . "\" alt=\"" . $arr["name"] . "\" style=\"margin-left: 8pt;vertical-align: middle;\">";
} 
// if ($user["donor"] == "yes") $donor = "<td class=embedded><img src=\"".$GLOBALS["PIC_BASE_URL"]."starbig.gif\" alt='Donor' style='margin-left: 4pt'></td>";
// if ($user["warned"] == "yes") $warned = "<td class=embedded><img src=\"".$GLOBALS["PIC_BASE_URL"]."warnedbig.gif\" alt='Warned' style='margin-left: 4pt'></td>";
$res = mysql_query("SELECT torrent,added,traffic.uploaded,traffic.downloaded,torrents.name as torrentname,categories.name as catname,size,image,category,seeders,leechers FROM peers JOIN traffic ON peers.userid = traffic.userid AND peers.torrent = traffic.torrentid JOIN torrents ON peers.torrent = torrents.id JOIN categories ON torrents.category = categories.id WHERE peers.userid=$id AND seeder='no'") or sqlerr();
$leeching = "";
if (mysql_num_rows($res) > 0)
    $leeching = maketable($res);
$res = mysql_query("SELECT torrent,added,traffic.uploaded,traffic.downloaded,torrents.name as torrentname,categories.name as catname,size,image,category,seeders,leechers FROM peers JOIN traffic ON peers.userid = traffic.userid AND peers.torrent = traffic.torrentid JOIN torrents ON peers.torrent = torrents.id JOIN categories ON torrents.category = categories.id WHERE peers.userid=$id AND seeder='yes'") or sqlerr();
$seeding = "";
if (mysql_num_rows($res) > 0)
    $seeding = maketable($res);
$completed = "";
if (get_user_class() >= UC_MODERATOR || (isset($CURUSER) && $CURUSER["id"] == $user["id"])) {
    if (!isset($_GET["allcompleted"]))
        $limit_comp = " LIMIT 3";
    else
        $limit_comp = "";
    $res = mysql_query("SELECT torrent_id as torrent,torrent_name,complete_time,torrents.seeders as seeders,torrents.leechers as leechers,torrents.id as torrent_origid,categories.name as catname,image,traffic.uploaded,traffic.downloaded,traffic.uploadtime,traffic.downloadtime FROM completed LEFT JOIN traffic ON completed.torrent_id = traffic.torrentid AND completed.user_id = traffic.userid LEFT JOIN torrents ON completed.torrent_id = torrents.id LEFT JOIN categories ON completed.torrent_category = categories.id WHERE user_id=$id ORDER BY complete_time DESC$limit_comp");
    if (mysql_num_rows($res) > 0)
        $completed = makecomptable($res);
} 

stdhead("Benutzerprofil von " . $user["username"]);
$enabled = $user["enabled"] == 'yes';
$acceptrules = $user["accept_rules"] == 'no';
$allowupload = $user["allowupload"] == 'yes';

$acctdata = mysql_fetch_assoc(mysql_query("SELECT `baduser` FROM `accounts` WHERE `userid`=$id"));
$baduser = $acctdata["baduser"];

begin_frame("<img src=\"" . $GLOBALS["PIC_BASE_URL"] . "personal.png\" width=\"22\" height=\"22\" alt=\"\" style=\"vertical-align: middle;\">  Benutzerprofil von " . $user["username"] . get_user_icons($user, true) . "&nbsp;" . $country, false, "750px");
begin_table(true);

if (!$enabled)
    print("<tr><td colspan=\"2\" class=\"tablea\"><b>Dieser Account wurde gesperrt</b></td></tr>\n");
elseif ($CURUSER["id"] <> $user["id"]) {
    $r = mysql_query("SELECT id FROM friends WHERE userid=$CURUSER[id] AND friendid=$id") or sqlerr(__FILE__, __LINE__);
    $friend = mysql_num_rows($r);
    $r = mysql_query("SELECT id FROM blocks WHERE userid=$CURUSER[id] AND blockid=$id") or sqlerr(__FILE__, __LINE__);
    $block = mysql_num_rows($r);

    if ($friend)
        print("<tr><td colspan=\"2\" class=\"tablea\" align=\"center\"><a href=friends.php?action=delete&type=friend&targetid=$id>Von Freundesliste entfernen</a></td></tr>\n");
    elseif ($block)
        print("<tr><td colspan=\"2\" class=\"tablea\" align=\"center\"><a href=friends.php?action=delete&type=block&targetid=$id>Von Blockliste entfernen</a></td></tr>\n");
    else {
        print("<tr><td class=\"tablea\" align=\"center\"><a href=friends.php?action=add&type=friend&targetid=$id>Zu Freunden hinzufügen</a></td>");
        print("<td class=\"tablea\" align=\"center\"><a href=friends.php?action=add&type=block&targetid=$id>Zu Blockliste hinzufügen</a></td></tr>\n");
    } 
} 

end_table();

?>
<table cellspacing="1" cellpadding="5" border="0" class="tableinborder" style="width: 100%">
<tr><td class="tableb" width=1%>Registriert</td><td class=tablea align=left width=99%><?=$joindate?></td></tr>
<tr><td class="tableb">Zuletzt&nbsp;aktiv</td><td class=tablea align=left><?=$lastseen?></td></tr>
<?php
if (get_user_class() >= UC_MODERATOR)
    print("<tr><td class=tableb>E-Mail</td><td class=tablea align=left><a href=mailto:$user[email]>$user[email]</a></td></tr>\n");
if ($addr)
    print("<tr><td class=tableb>Adresse</td><td class=tablea align=left>$addr</td></tr>\n");
if ($peer_addr)
    print("<tr><td class=tableb>Peer-Adressen</td><td class=tablea align=left>$peer_addr</td></tr>\n");
if ($CURUSER["id"] == $user["id"] || get_user_class() == UC_SYSOP)
    print("<tr><td class=tableb>Announce-URL</td><td class=tablea align=left>$announceurl</td></tr>");
// if ($user["id"] == $CURUSER["id"] || get_user_class() >= UC_MODERATOR)
// {
?>
<tr><td class=tableb>Hochgeladen</td><td class=tablea align=left><?=mksize($user["uploaded"])?> <?=$upped_per_day?></td></tr>
<tr><td class=tableb>Runtergeladen</td><td class=tablea align=left><?=mksize($user["downloaded"])?> <?=$down_per_day?></td></tr>
<?php
if ($user["downloaded"] > 0) {
    $sr = $user["uploaded"] / $user["downloaded"];
    if ($sr >= 10)
        $s = "pirate2";
    else if ($sr >= 7.5)
        $s = "bow";
    else if ($sr >= 5)
        $s = "yikes";
    else if ($sr >= 3.5)
        $s = "w00t";
    else if ($sr >= 2)
        $s = "grin";
    else if ($sr >= 1)
        $s = "smile1";
    else if ($sr >= 0.9)
        $s = "innocent";
    else if ($sr >= 0.5)
        $s = "noexpression";
    else if ($sr >= 0.25)
        $s = "sad";
    else if ($sr >= 0.1)
        $s = "cry";
    else
        $s = "shit";
    $sr = floor($sr * 1000) / 1000;
    $sr = "<table border=0 cellspacing=0 cellpadding=0><tr><td class=embedded><font color=" . get_ratio_color($sr) . ">" . number_format($sr, 3) . "</font></td><td class=embedded>&nbsp;&nbsp;<img src=\"" . $GLOBALS["PIC_BASE_URL"] . "smilies/$s.gif\"></td></tr></table>";
    print("<tr><td class=tableb style='vertical-align: middle'>Ratio</td><td class=tablea align=left valign=center style='padding-top: 1px; padding-bottom: 0px'>$sr</td></tr>\n");

    if (file_exists($GLOBALS["BITBUCKET_DIR"] . "/rstat-" . $user["id"] . ".png"))
        print("<tr><td class=tableb style='vertical-align: middle'>Ratio-Histogramm</td><td class=tablea align=left valign=center style='padding-top: 1px; padding-bottom: 0px'><img src=\"" . $GLOBALS["BITBUCKET_DIR"] . "/rstat-" . $user["id"] . ".png\"></td></tr>\n");
} 
// }
// if ($user['donated'] > 0 && (get_user_class() >= UC_MODERATOR || $CURUSER["id"] == $user["id"]))
// print("<tr><td class=tableb>Donated</td><td align=left>$$user[donated]</td></tr>\n");
if ($user["avatar"])
    print("<tr><td class=tableb>Avatar</td><td class=tablea align=left><img src=\"" . htmlspecialchars($user["avatar"]) . "\"></td></tr>\n");
print("<tr><td class=tableb>Rang</td><td class=tablea align=left>" . get_user_class_name($user["class"]) . "</td></tr>\n");
print("<tr><td class=tableb>Kommentare</td>");
if ($torrentcomments && (($user["class"] >= UC_POWER_USER && $user["id"] == $CURUSER["id"]) || get_user_class() >= UC_MODERATOR))
    print("<td class=tablea align=left><a href=userhistory.php?action=viewcomments&id=$id>$torrentcomments</a></td></tr>\n");
else
    print("<td class=tablea align=left>$torrentcomments</td></tr>\n");
print("<tr><td class=tableb>Forumbeiträge</td>");
if ($forumposts && (($user["class"] >= UC_POWER_USER && $user["id"] == $CURUSER["id"]) || get_user_class() >= UC_MODERATOR))
    print("<td class=tablea align=left><a href=userhistory.php?action=viewposts&id=$id>$forumposts</a></td></tr>\n");
else
    print("<td class=tablea align=left>$forumposts</td></tr>\n");

if ($torrents)
    print("<tr valign=top><td class=tableb>Hochgeladen</td><td class=tablea align=left>$torrents</td></tr>\n");
if ($seeding)
    print("<tr valign=top><td class=tableb>Seedet&nbsp;momentan</td><td class=tablea align=left>$seeding</td></tr>\n");
if ($leeching)
    print("<tr valign=top><td class=tableb>Leecht&nbsp;momentan</td><td class=tablea align=left>$leeching</td></tr>\n");
if ($completed) {
    print("<tr valign=top><td class=tableb><a name=\"completed\"></a>");
    if (!$_GET["allcompleted"])
        print("Letzte 3 fertige Torrents<br><a class=\"sublink\" href=\"userdetails.php?id=$id&amp;allcompleted=1#completed\">[Alle anzeigen]</a></td><td class=tablea align=left>$completed</td></tr>\n");
    else
        print("Alle fertigen Torrents<br><a class=\"sublink\" href=\"userdetails.php?id=$id#completed\">[Liste verbergen]</a></td><td class=tablea align=left>$completed</td></tr>\n");
} 
if ($user["info"])
    print("<tr valign=top><td class=inposttable align=left colspan=2 class=text bgcolor=#F4F4F0>" . format_comment($user["info"]) . "</td></tr>\n");

if ($CURUSER["id"] != $user["id"]) {
    if (get_user_class() >= UC_GUTEAM) {
        $showpmbutton = true;
        $showemailbutton = true;
    } else {
        if ($user["acceptpms"] == "yes") {
            $r = mysql_query("SELECT id FROM blocks WHERE userid=$user[id] AND blockid=$CURUSER[id]") or sqlerr(__FILE__, __LINE__);
            $showpmbutton = (mysql_num_rows($r) == 1 ? false : true);
        } elseif ($user["acceptpms"] == "friends") {
            $r = mysql_query("SELECT id FROM friends WHERE userid=$user[id] AND friendid=$CURUSER[id]") or sqlerr(__FILE__, __LINE__);
            $showpmbutton = (mysql_num_rows($r) == 1 ? true : false);
        } 

        if ($user["accept_email"] == "yes") {
            $r = mysql_query("SELECT id FROM blocks WHERE userid=$user[id] AND blockid=$CURUSER[id]") or sqlerr(__FILE__, __LINE__);
            $showemailbutton = (mysql_num_rows($r) == 1 ? false : true);
        } elseif ($user["accept_email"] == "friends") {
            $r = mysql_query("SELECT id FROM friends WHERE userid=$user[id] AND friendid=$CURUSER[id]") or sqlerr(__FILE__, __LINE__);
            $showemailbutton = (mysql_num_rows($r) == 1 ? true : false);
        } 
    } 
} 
if ($showpmbutton || $showemailbutton) {
    print("<tr><td class=\"tablea\" colspan=\"2\" style=\"text-align:center\">");

    if ($showpmbutton)
        print('<form method="get" action="messages.php" style="display:inline"><input type="hidden" name="action" value="send"><input type="hidden" name="receiver" value="' . $user["id"] . '"><input type="submit" value="Nachricht senden" style="height: 23px"></form>&nbsp;&nbsp;');

    if ($showemailbutton)
        print('<form method="get" action="email-gateway.php" style="display:inline"><input type="hidden" name="id" value="' . $user["id"] . '"><input type="submit" value="E-Mail senden" style="height: 23px"></form>');

    print ("</td></tr>");
} 

print("</table></td></tr></table><br>\n");

if ((get_user_class() >= UC_MODERATOR && $user["class"] < get_user_class()) || get_user_class() == UC_SYSOP) {

    ?>
<script type="text/javascript">
function togglediv()
{
    var mySelect = document.getElementById('tselect');
    var myDiv = document.getElementById('tlimitdiv');

    if (mySelect.options[mySelect.selectedIndex].value == "manual")
        myDiv.style.visibility = 'visible';
    else
        myDiv.style.visibility = 'hidden';
    
}
</script>
<?php
    $bbfilecount = mysql_fetch_assoc(mysql_query("SELECT COUNT(*) AS cnt FROM bitbucket WHERE user=$id"));

    begin_frame("Profil bearbeiten", false, "750px");
    print("<form method=post action=modtask.php>\n");
    print("<input type=hidden name='action' value='edituser'>\n");
    print("<input type=hidden name='userid' value='$id'>\n");
    print("<input type=hidden name='returnto' value='userdetails.php?id=$id&" . SID . "'>\n");
    print("<table class=tableinborder border=0 cellspacing=1 cellpadding=4 style=\"width:100%;\">\n");
    print("<tr><td class=tableb>Titel</td><td class=tablea colspan=2 align=left><input type=text size=60 name=title value=\"" . htmlspecialchars($user['title']) . "\"></tr>\n");
    $avatar = htmlspecialchars($user["avatar"]);
    print("<tr><td class=tableb>Avatar&nbsp;URL</td><td class=tablea colspan=2 align=left><input type=text size=60 name=avatar value=\"$avatar\"></tr>\n");
    print("<tr><td class=tableb>Start/Stop&nbsp;Events</td><td class=tablea colspan=2 align=left><a href=\"startstoplog.php?op=user&amp;uid=" . $user["id"] . "\">Ereignisse anzeigen</a></td></tr>\n");
    if ($bbfilecount["cnt"]) {
        print("<tr><td class=tableb>BitBucket</td><td class=tablea colspan=2 align=left><a href=\"bitbucket.php?id=$id\">BitBucket-Inhalt dieses Benutzers anzeigen / bearbeiten</a> (" . $bbfilecount["cnt"] . " Datei(en))</tr>\n");
    } 
    // we do not want mods to be able to change user classes or amount donated...
    if ($CURUSER["class"] < UC_ADMINISTRATOR)
        print("<input type=hidden name=donor value=$user[donor]>\n");
    else {
        print("<tr><td class=tableb>Gespendet</td><td class=tablea colspan=2 align=left><input type=radio name=donor value=yes" . ($user["donor"] == "yes" ? " checked" : "") . ">Ja <input type=radio name=donor value=no" . ($user["donor"] == "no" ? " checked" : "") . ">Nein</td></tr>\n");
    } 

    if (get_user_class() == UC_MODERATOR && $user["class"] > UC_VIP)
        printf("<input type=hidden name=class value=$user[class]\n");
    else {
        print("<tr><td class=tableb>Klasse</td><td class=tablea colspan=2 align=left><select name=class>\n");
        if (get_user_class() == UC_MODERATOR)
            $maxclass = UC_VIP;
        elseif (get_user_class() == UC_SYSOP)
            $maxclass = UC_SYSOP;
        else
            $maxclass = get_user_class() - 1;
        for ($i = 0; $i <= $maxclass; ++$i)
        if (get_user_class_name($i) != "")
            print("<option value=" . $i . ($user["class"] == $i ? " selected" : "") . ">" . get_user_class_name($i) . "\n");
            //print("<option value=$i" . ($user["class"] == $i ? " selected" : "") . ">$prefix" . get_user_class_name($i) . "\n");
        print("</select></td></tr>\n");
    } 

    print("<tr><td class=tableb>Torrentbegrenzung</td><td class=tablea colspan=2 align=left><select id=\"tselect\" name=\"limitmode\" size=\"1\" onchange=\"togglediv();\">");
    print("<option value=\"auto\"" . ($user["tlimitall"] == 0?" selected=\"selected\"":"") . ">Automatisch</option>\n");
    print("<option value=\"unlimited\"" . ($user["tlimitall"] == -1?" selected=\"selected\"":"") . ">Unbegrenzt</option>\n");
    print("<option value=\"manual\"" . ($user["tlimitall"] > 0?" selected=\"selected\"":"") . ">Manuell</option>\n");
    print("</select><div id=\"tlimitdiv\" style=\"display: inline;" . ($user["tlimitall"] <= 0?"visibility:hidden;":"") . "\">&nbsp;&nbsp;&nbsp;");
    print(" Seeds: <input type=\"text\" size=\"2\" maxlength=\"2\" name=\"maxseeds\" value=\"" . ($user["tlimitseeds"] > 0?$user["tlimitseeds"]:"") . "\">");
    print(" Leeches: <input type=\"text\" size=\"2\" maxlength=\"2\" name=\"maxleeches\" value=\"" . ($user["tlimitleeches"] > 0?$user["tlimitleeches"]:"") . "\">");
    print(" Gesamt: <input type=\"text\" size=\"2\" maxlength=\"2\" name=\"maxtotal\" value=\"" . ($user["tlimitall"] > 0?$user["tlimitall"]:"") . "\">");
    print("</div></td></tr>\n");

    $res = mysql_query("SELECT msg,name,torrent_id,`status` FROM nowait LEFT JOIN torrents ON torrents.id=torrent_id WHERE user_id=$id");

    if (mysql_num_rows($res)) {
        print("<tr><td class=tableb>Wartezeit&nbsp;aufheben</td><td class=tablea colspan=2 align=left><table");
        print("<table class=tableinborder border=0 cellspacing=1 cellpadding=4 width=\"100%\">");
        print("<tr><td class=tablecat width=\"100%\">Torrent / Grund</td><td class=tablecat>Status</td></tr>\n");
        while ($arr = mysql_fetch_assoc($res)) {
            print("<tr><td class=tablea><p><b><a href=\"details.php?id=$arr[torrent_id]\">" . htmlspecialchars($arr["name"]) . "</a></b></p><p>" . htmlspecialchars($arr["msg"]) . "</td>");
            if ($arr["status"] == "pending") {
                print("<td class=tableb valign=\"middle\" nowrap=\"nowrap\"><input type=\"radio\" name=\"wait[$arr[torrent_id]]\" value=\"yes\"" . ($arr["status"] == "granted"?" checked=\"checked\"":"") . "> Akzeptieren<br/>");
                print("<input type=\"radio\" name=\"wait[$arr[torrent_id]]\" value=\"no\"" . ($arr["status"] == "rejected"?" checked=\"checked\"":"") . "> Ablehnen<br/>\n");
                print("<input type=\"radio\" name=\"wait[$arr[torrent_id]]\" value=\"\"" . ($arr["status"] == "pending"?" checked=\"checked\"":"") . "> Nichts tun</td></tr>\n");
            } else {
                print("<td class=tableb valign=\"middle\" align=\"center\">" . ($arr["status"] == "granted"?"Akzeptiert":"Abgelehnt") . "</td></tr>\n");
            } 
        } 
        print("</table></td></tr>\n");
    } 
    
    print("<tr><td class=tableb>Kommentare</td><td class=tablea colspan=2 align=left><div style=\"width:500px;height:100px;overflow:auto;\">");
    begin_table(TRUE);
    $modcommentres = mysql_query("SELECT `modcomments`.`added`,`modcomments`.`userid`,`modcomments`.`moduid`,`modcomments`.`txt`,`users`.`username` FROM `modcomments` LEFT JOIN `users` ON `users`.`id`=`modcomments`.`moduid` WHERE `userid`=$id ORDER BY `added` DESC");
    while ($comment = mysql_fetch_assoc($modcommentres)) {
        $comment["added"] = str_replace(" ", "&nbsp;", $comment["added"]);
        print("<tr><td class=\"tablea\" valign=\"top\">".$comment["added"]."</td>\n<td class=\"tableb\" valign=\"top\">");
    	if ($comment["moduid"] == 0)
            print("System");
        elseif ($comment["username"] == "")
            print("<i>Gelöscht</i>");
        else
            print("<a href=\"userdetails.php?id=".$comment["moduid"]."\">".$comment["username"]."</a>");
        print("</td>\n");
        print("<td class=\"tablea\" valign=\"top\">".format_comment(stripslashes($comment["txt"]))."</td></tr>\n");
    }    
    print("</table></div><br>Hinzufügen:&nbsp;&nbsp;<input type=\"text\" size=\"50\" name=\"modcomment\"></td></tr>\n");
    
    $warned = $user["warned"] == "yes";
    print("<tr><td class=tableb" . (!$warned ? " rowspan=2": "") . ">Verwarnt</td>
 	<td class=tablea align=left width=20%>" . ($warned ? "<input name=warned value='yes' type=radio checked>Ja<input name=warned value='no' type=radio>Nein" : "Nein") . "</td>");

    if ($warned) {
        $warneduntil = $user['warneduntil'];
        if ($warneduntil == '0000-00-00 00:00:00')
            print("<td class=tablea align=center>(willkürliche Dauer)</td></tr>\n");
        else {
            print("<td class=tablea align=center>Bis $warneduntil");
            print(" (noch " . mkprettytime(strtotime($warneduntil) - time()) . ")</td></tr>\n");
        } 
    } else {
        print("<td class=tablea>Verwarnen für <select name=warnlength>\n");
        print("<option value=0>------</option>\n");
        print("<option value=1>1 Woche</option>\n");
        print("<option value=2>2 Wochen</option>\n");
        print("<option value=4>4 Wochen</option>\n");
        print("<option value=8>8 Wochen</option>\n");
        print("<option value=255>Unbefristet</option>\n");
        print("</select></td></tr>\n");
        print("<tr><td class=tablea colspan=2 align=left>PM Kommentar (BBCode erlaubt):<br><textarea cols=\"60\" rows=\"4\" name=\"warnpm\"></textarea><br>");
        print("<input id=\"addwarnratio\" type=\"checkbox\" name=\"addwarnratio\" value=\"yes\"><label for=\"addwarnratio\">&nbsp;Ratiostats zu Mod-Kommentar hinzufügen</label></td></tr>");
    }
    print("<tr><td class=tableb>Muss Regeln bestätigen</td><td class=tablea colspan=2 align=left><input name=acceptrules value='no' type=radio" . ($acceptrules ? " checked" : "") . ">Ja <input name=acceptrules value='yes' type=radio" . (!$acceptrules ? " checked" : "") . ">Nein</td></tr>\n");
    print("<tr><td class=tableb>Torrent-Upload sperren</td><td class=tablea align=left colspan=2><input name=denyupload value='yes' type=radio" . (!$allowupload ? " checked" : "") . ">Ja <input name=denyupload value='no' type=radio" . ($allowupload ? " checked" : "") . ">Nein</td></tr>\n");
    print("<tr><td class=tableb>Bad User <img src=\"" . $GLOBALS["PIC_BASE_URL"] . "help.png\" style=\"vertical-align:middle;\" title=\"Bewirkt, dass dieser Benutzer nur ungültige Peer-IPs erhält\" alt=\"Bewirkt, dass dieser Benutzer nur ungültige Peer-IPs erhält\"></td><td class=tablea align=left><input name=baduser value='yes' type=radio" . ($baduser ? " checked" : "") . ">Ja <input name=baduser value='no' type=radio" . (!$baduser ? " checked" : "") . ">Nein</td><td class=tablea align=left><a href=\"startstoplog.php?op=acclist&amp;id=$id\">Liste ehem. Accounts anzeigen</a></td></tr>\n");
    print("<tr><td class=tableb>Aktiviert</td><td class=tablea align=left><input name=enabled value='yes' type=radio" . ($enabled ? " checked" : "") . ">Ja <input name=enabled value='no' type=radio" . (!$enabled ? " checked" : "") . ">Nein</td><td class=tablea align=left>Grund: <input type=text name=disablereason size=40></td></tr>\n");

    print("</td></tr>");
    print("<tr><td class=tablea colspan=3 align=center><input type=submit class=btn value='Okay'></td></tr>\n");
    print("</table>\n");
    print("</form>\n");
    end_frame();
} 
end_main_frame();
echo "</center>";
stdfoot();

?>