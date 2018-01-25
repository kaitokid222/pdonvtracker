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

function docleanup()
{
    set_time_limit(0);
    ignore_user_abort(1);

    while (1) {
        // Collect all torrent ids from database
        $res = mysql_query("SELECT id FROM torrents");
        $all_torrents = array();
        while ($row = mysql_fetch_array($res)) {
            $id = $row[0];
            $all_torrents[$id] = 1;
        }
    
        // Open torrent directory for scanning
        $dp = @opendir($GLOBALS["TORRENT_DIR"]);
        if (!$dp)
            break;
    
        // Collect all .torrent files matching "id.torrent" filename pattern
        $all_files = array();
        while (($file = readdir($dp)) !== false) {
            if (!preg_match('/^(\d+)\.torrent$/', $file, $m))
                continue;
            $id = $m[1];
            $all_files[$id] = 1;
            
            // If no database entry exists, delete the orphaned file
            if (!isset($all_torrents[$id]) || $all_torrents[$id] == 0)
                unlink($GLOBALS["TORRENT_DIR"] . "/$file");
        }
        closedir($dp);
    
        // Open bitbucket directory for scanning
        $dp = @opendir($GLOBALS["BITBUCKET_DIR"]);
        if (!$dp)
            break;
    
        // Collect all NFO files matching "nfo-id.png" filename pattern
        while (($file = readdir($dp)) !== false) {
            if (!preg_match('/^nfo-(\d+)\.png$/', $file, $m))
                continue;
            $id = $m[1];
            
            // If no database entry exists, delete the orphaned file
            if (!isset($all_torrents[$id]) || $all_torrents[$id] == 0)
                unlink($GLOBALS["BITBUCKET_DIR"] . "/$file");
        }
        closedir($dp);
        
        // No torrents or files to consider
        if (!count($all_torrents) && !count($all_files))
            break;
    
        // Enumerate and delete torrents which have no according .torrent file
        $delids = array();
        foreach (array_keys($all_torrents) as $k) {
            if (isset($all_files[$k]) && $all_files[$k])
                continue;
            $delids[] = $k;
            unset($all_torrents[$k]);
        }
        if (count($delids))
            mysql_query("DELETE FROM torrents WHERE id IN (" . join(",", $delids) . ")");
    
        // Enumerate and delete peers which have no according torrent in the DB
        $res = mysql_query("SELECT torrent FROM peers GROUP BY torrent");
        $delids = array();
        while ($row = mysql_fetch_array($res)) {
            $id = $row[0];
            if (isset($all_torrents[$id]) && $all_torrents[$id])
                continue;
            $delids[] = $id;
        }
        if (count($delids))
            mysql_query("DELETE FROM peers WHERE torrent IN (" . join(",", $delids) . ")");
    
        // Enumerate and delete file entries which have no according torrent in the DB
        $res = mysql_query("SELECT torrent FROM files GROUP BY torrent");
        $delids = array();
        while ($row = mysql_fetch_array($res)) {
            $id = $row[0];
            if (isset($all_torrents[$id]))
                continue;
            $delids[] = $id;
        }
        if (count($delids))
            mysql_query("DELETE FROM files WHERE torrent IN (" . join(",", $delids) . ")");
    
        // Enumerate and delete wait time overrides which have no according torrent in the DB
        $res = mysql_query("SELECT torrent_id FROM nowait GROUP BY torrent_id");
        $delids = array();
        while ($row = mysql_fetch_array($res)) {
            $id = $row[0];
            if ($all_torrents[$id])
                continue;
            $delids[] = $id;
        }
        if (count($delids))
            mysql_query("DELETE FROM nowait WHERE torrent_id IN (" . join(",", $delids) . ")");
            
        break;
    }
    
    // Delete inactive peers
    $deadtime = deadtime();
    mysql_query("DELETE FROM peers WHERE last_action < FROM_UNIXTIME($deadtime)");

    // Mark inactive torrents dead
    $deadtime -= $GLOBALS["MAX_DEAD_TORRENT_TIME"] * 86400;
    mysql_query("UPDATE torrents SET visible='no' WHERE visible='yes' AND last_action < FROM_UNIXTIME($deadtime)");

    // Delete newly registered user accounts which were not activated
    $deadtime = time() - $GLOBALS["SIGNUP_TIMEOUT"] * 3600;
    mysql_query("DELETE FROM users WHERE status = 'pending' AND added < FROM_UNIXTIME($deadtime) AND last_login < FROM_UNIXTIME($deadtime) AND last_access < FROM_UNIXTIME($deadtime)");

    // Update torrent stats (leechers, seeders, comments)
    $torrents = array();
    $res = mysql_query("SELECT torrent, seeder, COUNT(*) AS c FROM peers GROUP BY torrent, seeder");
    while ($row = mysql_fetch_assoc($res)) {
        if ($row["seeder"] == "yes")
            $key = "seeders";
        else
            $key = "leechers";
        $torrents[$row["torrent"]][$key] = $row["c"];
    } 

    $res = mysql_query("SELECT torrent, COUNT(*) AS c FROM comments GROUP BY torrent");
    while ($row = mysql_fetch_assoc($res)) {
        $torrents[$row["torrent"]]["comments"] = $row["c"];
    } 

    $fields = explode(":", "comments:leechers:seeders");
    $res = mysql_query("SELECT id, seeders, leechers, comments FROM torrents");
    while ($row = mysql_fetch_assoc($res)) {
        $id = $row["id"];
        $torr = $torrents[$id];
        foreach ($fields as $field) {
            if (!isset($torr[$field]))
                $torr[$field] = 0;
        } 
        $update = array();
        foreach ($fields as $field) {
            if ($torr[$field] != $row[$field])
                $update[] = "$field = " . $torr[$field];
        } 
        if (count($update))
            mysql_query("UPDATE torrents SET " . implode(",", $update) . " WHERE id = $id");
    } 
    
    // delete disabled and inactive user accounts
    $secs = $GLOBALS["INACTIVE_TIMEOUT"] * 86400;
    $dt = sqlesc(get_date_time(time() - $secs));
    $secs = $GLOBALS["DISABLED_TIMEOUT"] * 86400;
    $dit = sqlesc(get_date_time(time() - $secs));
    $maxclass = UC_POWER_USER;
    $res = mysql_query("SELECT id FROM users WHERE status='confirmed' AND ((enabled='yes' AND class <= $maxclass AND last_access < $dt) OR (enabled='no' AND last_access < $dit))");
    while ($arr = mysql_fetch_assoc($res))
        delete_acct($arr["id"]); 
    
    // lock topics where last post was made more than x days ago
    if ($GLOBALS["THREAD_LOCK_TIMEOUT"]) {
        $secs = $GLOBALS["THREAD_LOCK_TIMEOUT"] * 86400;
        $res = mysql_query("SELECT topics.id FROM topics JOIN posts ON topics.lastpost = posts.id AND topics.sticky = 'no' WHERE " . time() . " - UNIX_TIMESTAMP(posts.added) > $secs") or sqlerr(__FILE__, __LINE__);
        while ($arr = mysql_fetch_assoc($res))
            mysql_query("UPDATE topics SET locked='yes' WHERE id=$arr[id]") or sqlerr(__FILE__, __LINE__);
    } 
    
    // remove expired warnings
    $res = mysql_query("SELECT id,username FROM users WHERE warned='yes' AND warneduntil < NOW() AND warneduntil <> '0000-00-00 00:00:00'") or sqlerr(__FILE__, __LINE__);
    if (mysql_num_rows($res) > 0) {
        $dt = sqlesc(get_date_time());
        $msg = "Deine Verwarnung wurde automatisch entfernt, und die Moderatoren darüber informiert, um evtl. gestellte Bedingungen erneut zu prüfen.\n";
        while ($arr = mysql_fetch_assoc($res)) {
            $mod_msg = "[b]Eine Verwarnung ist abgelaufen![/b]\n\nBenutzer [url=userdetails.php?id=$arr[id]]" . $arr["username"] . "[/url] (".$GLOBALS["DEFAULTBASEURL"]."/userdetails.php?id=$arr[id])\n\nBitte die Verwarnungsbedingungen erneut prüfen und entsprechend reagieren.";
            mysql_query("UPDATE users SET warned = 'no', warneduntil = '0000-00-00 00:00:00' WHERE id = $arr[id]") or sqlerr(__FILE__, __LINE__);
            sendPersonalMessage(0, $arr["id"], "Deine Verwarnung ist abgelaufen", $msg, PM_FOLDERID_SYSTEM, 0);
            sendPersonalMessage(0, 0, "Die Verwarnung für '$arr[username]' ist abgelaufen", $mod_msg, PM_FOLDERID_MOD, 0, "open");
            write_log("remwarn", "Die Verwarnung für Benutzer '<a href=\"userdetails.php?id=$arr[id]\">$arr[username]</a>' ist abgelaufen und wurde vom System zurückgenommen.");
            write_modcomment($arr["id"], 0, "Verwarnung abgelaufen.");
        } 
    } 
    
    // promote power users
    $limit = 25 * 1024 * 1024 * 1024;
    $minratio = 1.05;
    $maxdt = sqlesc(get_date_time(time() - 86400 * 28));
    $res = mysql_query("SELECT id,username FROM users WHERE class = 0 AND uploaded >= $limit AND uploaded / downloaded >= $minratio AND added < $maxdt") or sqlerr(__FILE__, __LINE__);
    if (mysql_num_rows($res) > 0) {
        $dt = sqlesc(get_date_time());
        $msg = sqlesc("Glückwunsch, Du wurdest automatisch zum [b]Power User[/b] befördert. :)\nDu kannst Dir nun NFOs ansehen.\n");
        while ($arr = mysql_fetch_assoc($res)) {
            mysql_query("UPDATE users SET class = 1 WHERE id = $arr[id]") or sqlerr(__FILE__, __LINE__);
            sendPersonalMessage(0, $arr["id"], "Du wurdest zum 'Power User' befördert", $msg, PM_FOLDERID_SYSTEM, 0);
            write_log("promotion", "Der Benutzer '<a href=\"userdetails.php?id=$arr[id]\">$arr[username]</a>' wurde automatisch zum Power User befördert.");
            write_modcomment($arr["id"], 0, "Automatische Beförderung zum Power User.");
        } 
    } 
    
    // demote power users
    $minratio = 0.95;
    $res = mysql_query("SELECT id,username FROM users WHERE class = 1 AND uploaded / downloaded < $minratio") or sqlerr(__FILE__, __LINE__);
    if (mysql_num_rows($res) > 0) {
        $dt = sqlesc(get_date_time());
        $msg = sqlesc("Du wurdest automatisch vom [b]Power User[/b] zum [b]User[/b] degradiert, da Deine Share-Ratio unter $minratio gefallen ist.\n");
        while ($arr = mysql_fetch_assoc($res)) {
            mysql_query("UPDATE users SET class = 0 WHERE id = $arr[id]") or sqlerr(__FILE__, __LINE__);
            sendPersonalMessage(0, $arr["id"], "Du wurdest zum 'User' degradiert", $msg, PM_FOLDERID_SYSTEM, 0);
            write_log("demotion", "Der Benutzer '<a href=\"userdetails.php?id=$arr[id]\">$arr[username]</a>' wurde automatisch zum User degradiert.");
            write_modcomment($arr["id"], 0, "Automatische Degradierung zum User.");
        } 
    } 
    // Update stats
    $seeders = pdo_row_count("peers", "seeder='yes'");
    $leechers = pdo_row_count("peers", "seeder='no'");
    mysql_query("UPDATE avps SET value_u=$seeders WHERE arg='seeders'") or sqlerr(__FILE__, __LINE__);
    mysql_query("UPDATE avps SET value_u=$leechers WHERE arg='leechers'") or sqlerr(__FILE__, __LINE__); 
    // update forum post/topic count
    $forums = mysql_query("select id from forums");
    while ($forum = mysql_fetch_assoc($forums)) {
        $postcount = 0;
        $topiccount = 0;
        $topics = mysql_query("select id from topics where forumid=$forum[id]");
        while ($topic = mysql_fetch_assoc($topics)) {
            $res = mysql_query("select count(*) from posts where topicid=$topic[id]");
            $arr = mysql_fetch_row($res);
            $postcount += $arr[0];
            ++$topiccount;
        } 
        mysql_query("update forums set postcount=$postcount, topiccount=$topiccount where id=$forum[id]");
    } 
    
    // Delete old/dead and not activated torrents
    if ($GLOBALS["MAX_TORRENT_TTL"]) {
        $days = $GLOBALS["MAX_TORRENT_TTL"];
        $dt = sqlesc(get_date_time(time() - ($days * 86400)));
        $deadtime = deadtime() - $GLOBALS["MAX_DEAD_TORRENT_TIME"] * 86400;
        $res = mysql_query("SELECT id, name, owner FROM torrents WHERE (added < $dt OR `activated`='no') AND `last_action` < FROM_UNIXTIME($deadtime)");
        while ($arr = mysql_fetch_assoc($res)) {
            deletetorrent($arr["id"]);
            $msg = sqlesc("Dein Torrent '$arr[name]' wurde automatisch vom System gelöscht. (Inaktiv und älter als $days Tage).\n");
            sendPersonalMessage(0, $arr["owner"], "Einer Deiner Torrents wurde gelöscht", $msg, PM_FOLDERID_SYSTEM, 0);
            write_log("torrentdelete", "Torrent $arr[id] ($arr[name]) wurde vom System gelöscht (Inaktiv und älter als $days Tage)");
        } 
    } 
    
    // Delete old entries from start/stop log
    mysql_query("DELETE FROM startstoplog WHERE UNIX_TIMESTAMP(`datetime`) < (UNIX_TIMESTAMP()-864000)");
    
    // Delete orphaned entries from completed list (either torrent or user doesn't exist anymore)
    $res = mysql_query("SELECT completed.id FROM completed LEFT JOIN torrents ON completed.torrent_id=torrents.id LEFT JOIN users ON completed.user_id=users.id WHERE torrents.id IS NULL OR users.id IS NULL");
    $idlist = "";
    while ($id = mysql_fetch_assoc($res)) {
    	if ($idlist != "") $idlist .= ",";
        $idlist .= $id["id"];
    }
    if ($idlist != "") mysql_query("DELETE FROM completed WHERE id IN ($idlist)");
    
    // Delete orphaned friends
    $query = "SELECT `friends`.`id` AS `id` FROM `friends` LEFT JOIN `users` AS `myuser` ON `friends`.`userid`=`myuser`.`id` LEFT JOIN `users` AS `myfriend` ON `friends`.`userid`=`myfriend`.`id` WHERE `myuser`.`username` IS NULL OR `myfriend`.`username` IS NULL";
    $res = mysql_query($query);
    $idlist = "";
    while ($id = mysql_fetch_assoc($res)) {
    	if ($idlist != "") $idlist .= ",";
        $idlist .= $id["id"];
    }
    if ($idlist != "") mysql_query("DELETE FROM `friends` WHERE `id` IN ($idlist)");
    
    // Delete orphaned mod comments
    $res = mysql_query("SELECT `modcomments`.`userid` AS `id` FROM `modcomments` LEFT JOIN `users` ON `modcomments`.`userid`=`users`.`id` WHERE `users`.`id` IS NULL");
    $idlist = "";
    while ($id = mysql_fetch_assoc($res)) {
    	if ($idlist != "") $idlist .= ",";
        $idlist .= $id["id"];
    }
    if ($idlist != "") mysql_query("DELETE FROM `modcomments` WHERE `userid` IN ($idlist)");
    
} 

?>