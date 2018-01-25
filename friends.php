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

$userid = $_GET['id'];
$action = $_GET['action'];

if (!$userid)
    $userid = $CURUSER['id'];

if (!is_valid_id($userid))
    stderr("Fehler", "Ung&uuml;ltige User-ID $userid!");

if ($userid != $CURUSER["id"])
    stderr("Fehler", "Zugriff verweigert!");

$res = mysql_query("SELECT * FROM users WHERE id=$userid") or sqlerr(__FILE__, __LINE__);
$user = mysql_fetch_array($res) or stderr("Fehler", "Es gibt keinen User mit der ID $userid!");
// action: add -------------------------------------------------------------
if ($action == 'add') {
    $targetid = $_GET['targetid'];
    $type = $_GET['type'];

    if (!is_valid_id($targetid))
        stderr("Fehler", "Ung&uuml;ltige User-ID $$targetid.");

    if ($type == 'friend') {
        $table_is = $frag = 'friends';
        $field_is = 'friendid';
    } elseif ($type == 'block') {
        $table_is = $frag = 'blocks';
        $field_is = 'blockid';
    } else
        stderr("Fehler", "Unbekannter Typ $type");

    $r = mysql_query("SELECT id FROM $table_is WHERE userid=$userid AND $field_is=$targetid") or sqlerr(__FILE__, __LINE__);
    if (mysql_num_rows($r) == 1)
        stderr("Fehler", "User-ID $targetid ist bereits in Deiner $table_is Liste.");

    mysql_query("INSERT INTO $table_is VALUES (0,$userid, $targetid)") or sqlerr(__FILE__, __LINE__);
    header("Location: $BASEURL/friends.php?id=$userid&".SID."#$frag");
    die;
} 
// action: delete ----------------------------------------------------------
if ($action == 'delete') {
    $targetid = $_GET['targetid'];
    $sure = $_GET['sure'];
    $type = $_GET['type'];

    if (!is_valid_id($targetid))
        stderr("Fehler", "Ung&uuml;ltige User-ID $userid.");

    if ($type=="block")
        $readtype = "blockierten Benutzer";
    else 
        $readtype = "Freund";
    
    if (!$sure)
        stderr("L&ouml;sche $readtype", "Möchtest Du wirklich einen $readtype aus der Liste entfernen? Klicke\n" . "<a href=?id=$userid&action=delete&type=$type&targetid=$targetid&sure=1>hier</a> wenn du Dir sicher bist.");

    if ($type == 'friend') {
        mysql_query("DELETE FROM friends WHERE userid=$userid AND friendid=$targetid") or sqlerr(__FILE__, __LINE__);
        if (mysql_affected_rows() == 0)
            stderr("Error", "No friend found with ID $targetid");
        $frag = "friends";
    } elseif ($type == 'block') {
        mysql_query("DELETE FROM blocks WHERE userid=$userid AND blockid=$targetid") or sqlerr(__FILE__, __LINE__);
        if (mysql_affected_rows() == 0)
            stderr("Fehler", "Kein blockierter Benutzer mit der ID $targetid gefunden");
        $frag = "blocks";
    } else
        stderr("Fehler", "Unknown type $type");

    header("Location: $BASEURL/friends.php?id=$userid&".SID."#$frag");
    die;
} 
// main body  -----------------------------------------------------------------
stdhead("Buddyliste f&uuml;r " . $user['username']);

if ($user["donor"] == "yes") $donor = "<td class=embedded><img src=\"".$GLOBALS["PIC_BASE_URL"]."starbig.gif\" alt='Donor' style='margin-left: 4pt'></td>";
if ($user["warned"] == "yes") $warned = "<td class=embedded><img src=\"".$GLOBALS["PIC_BASE_URL"]."warnedbig.gif\" alt='Warned' style='margin-left: 4pt'></td>";

?>
<table cellpadding="4" cellspacing="1" border="0" style="width:750px" class="tableinborder">
<tr class="tabletitle" width="100%">
<td colspan="10" width="100%"><span class="smallfont"><center>
<b>Buddyliste f&uuml;r <?=$user["username"]?></b> <?=$donor . $warned . $country?>
</center></span></td></tr><tr><td width="100%" class="tablea">
<table cellpadding="4" cellspacing="1" border="0" style="width:750px" class="tableinborder">
<tr><td colspan="2" width="100%" class="tablecat" align="center"><b>Freunde</b></td></tr>
<?php

$i = 0;

$res = mysql_query("SELECT f.friendid as id, u.username AS name, u.class, u.avatar, u.title, u.donor, u.warned, u.enabled, u.last_access FROM friends AS f JOIN users as u ON f.friendid = u.id WHERE userid=$userid ORDER BY name") or sqlerr(__FILE__, __LINE__);

if (mysql_num_rows($res) == 0)
    $friends = "<em>Deine Buddyliste ist leer.</em>";
else
    while ($friend = mysql_fetch_array($res)) {
    $year = substr($friend['last_access'], 0, 4);
    $month = substr($friend['last_access'], 5, 2);
    $day = substr($friend['last_access'], 8, 2);
    $hour = substr($friend['last_access'], 11, 2);
    $mins = substr($friend['last_access'], 14, 2);

    $date_last_access = $day . "." . $month . "." . $year;

    $name_day = date("l", mktime(0, 0, 0, $month, $day, $year));

    if ($name_day == "Monday") $name_day = "Montag";
    if ($name_day == "Tuesday") $name_day = "Dienstag";
    if ($name_day == "Wednesday") $name_day = "Mittwoch";
    if ($name_day == "Thursday") $name_day = "Donnerstag";
    if ($name_day == "Friday") $name_day = "Freitag";
    if ($name_day == "Saturday") $name_day = "Samstag";
    if ($name_day == "Sunday") $name_day = "Sonntag";

    $title = $friend["title"];
    if (!$title)
        $title = get_user_class_name($friend["class"]);
    $body1 = "<a href=userdetails.php?id=" . $friend['id'] . "><b>" . $friend['name'] . "</b></a>" .
    get_user_icons($friend) . " ($title)<br><br>Zuletzt gesehen am<br>" . $name_day . ", den " . $date_last_access . " " . substr($friend['last_access'], 11, 5) . " Uhr (vor " . get_elapsed_time(sql_timestamp_to_unix_timestamp($friend[last_access])) . ")";
    $body2 = "<br><a href=friends.php?id=$userid&action=delete&type=friend&targetid=" . $friend['id'] . ">L&ouml;schen</a>" . "<br><br><a href=messages.php?action=send&amp;receiver=" . $friend['id'] . ">PN&nbsp;schicken</a>";
    $avatar = ($CURUSER["avatars"] == "yes" ? htmlspecialchars($friend["avatar"]) : "");
    if (!$avatar)
        $avatar = $GLOBALS["PIC_BASE_URL"]."default_avatar.gif";
    if ($i % 2 == 0)
        print("<tr><td class=tablea style='padding: 5px' width=50% align=center>");
    else
        print("<td class=tablea style='padding: 5px' width=50% align=center>");
    print("<table class=main width=100% height=75px>");
    print("<tr valign=top><td width=75 align=center style='padding: 0px'>" .
        ($avatar ? "<div style='width:75px;height:75px;overflow: hidden'><img width=75px src=\"$avatar\"></div>" : "") . "</td><td>\n");
    print("<table class=main>");
    print("<tr><td class=embedded style='padding: 5px' width=80%>$body1</td>\n");
    print("<td class=embedded style='padding: 5px' width=20%>$body2</td></tr>\n");
    print("</table>");
    print("</td></tr>");
    print("</td></tr></table>\n");
    if ($i % 2 == 1)
        print("</td></tr>\n");
    else
        print("</td>\n");
    $i++;
} 
if ($i % 2 == 1)
    print("<td class=tablea width=50%>&nbsp;\n");
print($friends);
print("</td></tr></table>\n");
?>
<br>
<table cellpadding="4" cellspacing="1" border="0" style="width:750px" class="tableinborder">
<colgroup>
  <col width="20%">
  <col width="20%">
  <col width="20%">
  <col width="20%">
  <col width="20%">
</colgroup>
<tr><td colspan="6" width="100%" class="tablecat" align="center"><b>Blockierte Benutzer</b></td></tr>
<tr>
<?php
$res = mysql_query("SELECT b.blockid as id, u.username AS name, u.donor, u.warned, u.enabled, u.last_access FROM blocks AS b JOIN users as u ON b.blockid = u.id WHERE userid=$userid ORDER BY name") or sqlerr(__FILE__, __LINE__);
if (mysql_num_rows($res) == 0)
    $blocks = "<td colspan=\"6\" class=\"tablea\" align=\"center\"><em>Du hast keine anderen Mitglieder blockiert.</em></td>";
else {
    $I = 0;
    while ($block = mysql_fetch_array($res)) {
        if (($I>0) && ($I%6 == 0))
            $blocks .= "</tr><tr>";
        $blocks .= "<td class=\"tablea\">[<a href=friends.php?id=$userid&action=delete&type=block&targetid=" . $block['id'] . ">D</a>]&nbsp;<a href=userdetails.php?id=" . $block['id'] . "><b>" . $block['name'] . "</b></a>" .
        get_user_icons($block) . "</td>";
        $I++;
    }
    if ($I%6)
        $blocks .= "<td class=\"tablea\" colspan=\"".(6-($I%6))."\">&nbsp;</td>";
} 
print("$blocks\n");
print("</tr></table></td></tr></table>\n");

stdfoot();

?>