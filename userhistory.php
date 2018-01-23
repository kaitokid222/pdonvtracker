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

require "include/bittorrent.php";

dbconn(false);
loggedinorreturn();

$userid = $_GET["id"];

if(!is_valid_id($userid))
	stderr("Error", "Invalid ID");

if(get_user_class() < UC_POWER_USER || ($CURUSER["id"] != $userid && get_user_class() < UC_MODERATOR))
	stderr("Fehler", "Zugriff verweigert");

//$page = $_GET["page"];
$action = $_GET["action"];
// -------- Global variables
$perpage = 25;
// -------- Action: View posts
if($action == "viewposts"){
	$select_is = "COUNT(DISTINCT p.id)";
	$from_is = "posts AS p JOIN topics as t ON p.topicid = t.id JOIN forums AS f ON t.forumid = f.id";
	$where_is = "p.userid = " . $userid . " AND f.minclassread <= " . $CURUSER['class'];
	$order_is = "p.id DESC";
	$query = "SELECT " . $select_is . " FROM " . $from_is . " WHERE " . $where_is;
	$res = mysql_query($query) or sqlerr(__FILE__, __LINE__);
	$arr = mysql_fetch_row($res) or stderr("Error", "No posts found");
	$postcount = $arr[0]; 
	// ------ Make page menu
	list($pagertop, $pagerbottom, $limit) = pager($perpage, $postcount, $_SERVER["PHP_SELF"] . "?action=viewposts&id=$userid&"); 
	// ------ Get user data
	$res = mysql_query("SELECT username, added, donor, warned, enabled FROM users WHERE id=$userid") or sqlerr(__FILE__, __LINE__);
	if(mysql_num_rows($res) == 1){
		$arr = mysql_fetch_assoc($res);
		$subject = "<a href=userdetails.php?id=$userid><b>$arr[username]</b></a>" . get_user_icons($arr, true);
	}else
		$subject = "unknown[$userid]"; 
	// ------ Get posts
	$from_is = "posts AS p JOIN topics as t ON p.topicid = t.id JOIN forums AS f ON t.forumid = f.id LEFT JOIN readposts as r ON p.topicid = r.topicid AND p.userid = r.userid";
	$select_is = "f.id AS f_id, f.name, t.id AS t_id, t.subject, t.lastpost, r.lastpostread, p.*";
	$query = "SELECT $select_is FROM $from_is WHERE $where_is ORDER BY $order_is $limit";
	$res = mysql_query($query) or sqlerr(__FILE__, __LINE__);
	if(mysql_num_rows($res) == 0)
		stderr("Error", "No posts found");
	stdhead("Beitrags-History");
	if($postcount > $perpage)
		echo $pagertop; 
	// ------ Print table
	begin_main_frame();
	begin_frame("Beitrags-History", false, "650px");
	while($arr = mysql_fetch_assoc($res)){
		$postid = $arr["id"];
		$posterid = $arr["userid"];
		$topicid = $arr["t_id"];
		$topicname = stripslashes($arr["subject"]);
		$forumid = $arr["f_id"];
		$forumname = stripslashes($arr["name"]);
		$newposts = ($arr["lastpostread"] < $arr["lastpost"]) && $CURUSER["id"] == $userid;
		$added = $arr["added"] . " (Vor " . (get_elapsed_time(sql_timestamp_to_unix_timestamp($arr["added"]))) . ")";
		begin_table(TRUE);
		print("<tr><td class=tableb>$added<br><b>Forum:&nbsp;</b><a href=/forums.php?action=viewforum&forumid=$forumid>$forumname</a>&nbsp;--&nbsp;<b>Thema:&nbsp;</b><a href=/forums.php?action=viewtopic&topicid=$topicid>$topicname</a>&nbsp;--&nbsp;<b>Beitrag:&nbsp;</b>#<a href=/forums.php?action=viewtopic&topicid=$topicid&page=p$postid#$postid>$postid</a>" . ($newposts ? " &nbsp;<b>(<font color=red>NEU!</font>)</b>" : "") . "</td></tr>\n");
		$body = format_comment($arr["body"]);
		if(is_valid_id($arr['editedby'])){
			$subres = mysql_query("SELECT username FROM users WHERE id=$arr[editedby]");
			if(mysql_num_rows($subres) == 1){
				$subrow = mysql_fetch_assoc($subres);
				$body .= "<p><font size=1 class=smallfont>Zuletzt bearbeitet von <a href=userdetails.php?id=$arr[editedby]><b>$subrow[username]</b></a> am $arr[editedat]</font></p>\n";
			}
		}
		print("<tr valign=top><td class=tablea>$body</td></tr>\n");
		end_table();
	}

	end_frame();
	end_main_frame();
	if($postcount > $perpage)
		echo $pagerbottom;
	stdfoot();
} 
// -------- Action: View comments
if ($action == "viewcomments") {
    $select_is = "COUNT(*)"; 
    // LEFT due to orphan comments
    $from_is = "comments AS c LEFT JOIN torrents as t
	            ON c.torrent = t.id";

    $where_is = "c.user = $userid";
    $order_is = "c.id DESC";

    $query = "SELECT $select_is FROM $from_is WHERE $where_is ORDER BY $order_is";
    $res = mysql_query($query) or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_row($res) or stderr("Error", "No comments found");
    $commentcount = $arr[0]; 
    // ------ Make page menu
    list($pagertop, $pagerbottom, $limit) = pager($perpage, $commentcount, $_SERVER["PHP_SELF"] . "?action=viewcomments&id=$userid&"); 
    // ------ Get user data
    $res = mysql_query("SELECT username, donor, warned, enabled FROM users WHERE id=$userid") or sqlerr(__FILE__, __LINE__);
    if (mysql_num_rows($res) == 1) {
        $arr = mysql_fetch_assoc($res);
        $subject = "<a href=userdetails.php?id=$userid><b>$arr[username]</b></a>" . get_user_icons($arr, true);
    } else
        $subject = "unknown[$userid]"; 
    // ------ Get comments
    $select_is = "t.name, c.torrent AS t_id, c.id, c.added, c.text";
    $query = "SELECT $select_is FROM $from_is WHERE $where_is ORDER BY $order_is $limit";
    $res = mysql_query($query) or sqlerr(__FILE__, __LINE__);
    if (mysql_num_rows($res) == 0) stderr("Error", "No comments found");
    stdhead("Kommentar-History");
    if ($commentcount > $perpage) echo $pagertop; 
    // ------ Print table
    begin_main_frame();
    begin_frame("Kommentar-History", FALSE, "650px");
    while ($arr = mysql_fetch_assoc($res)) {
        $commentid = $arr["id"];
        $torrent = $arr["name"]; 
        // make sure the line doesn't wrap
        if (strlen($torrent) > 55) $torrent = substr($torrent, 0, 52) . "...";

        $torrentid = $arr["t_id"]; 
        // find the page; this code should probably be in details.php instead
        $subres = mysql_query("SELECT COUNT(*) FROM comments WHERE torrent = $torrentid AND id < $commentid")
        or sqlerr(__FILE__, __LINE__);
        $subrow = mysql_fetch_row($subres);
        $count = $subrow[0];
        $comm_page = floor($count / 20);
        $page_url = $comm_page?"&page=$comm_page":"";

        $added = $arr["added"] . " (Vor " . (get_elapsed_time(sql_timestamp_to_unix_timestamp($arr["added"]))) . ")";

        begin_table(TRUE);
        print("<tr><td class=tableb>" . "$added<br><b>Torrent:&nbsp;</b>" .
            ($torrent?("<a href=/details.php?id=$torrentid&tocomm=1>$torrent</a>"):" [Deleted] ") . "&nbsp;--&nbsp;<b>Kommentar:&nbsp;</b>#<a href=/details.php?id=$torrentid&tocomm=1$page_url>$commentid</a>
	  </td></tr>\n");

        $body = format_comment($arr["text"]);

        print("<tr valign=top><td class=tablea>$body</td></tr>\n");

        end_table();
    } 

    end_frame();

    end_main_frame();

    if ($commentcount > $perpage) echo $pagerbottom;

    stdfoot();

    die;
} 
// -------- Handle unknown action
if ($action != "")
    stderr("History Error", "Unknown action '$action'.");
// -------- Any other case
stderr("History Error", "Invalid or no query.");

?>