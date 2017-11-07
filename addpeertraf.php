<?php

require_once("include/bittorrent.php");

dbconn(false);

$res = mysql_query("SELECT userid,torrent,UNIX_TIMESTAMP(started) AS started,finishedat,uploaded,downloaded FROM peers");

while ($peer = mysql_fetch_assoc($res)) {
    $uptime = time() - $peer["started"];
    if ($peer["finishedat"] > 0)
        $dntime = $peer["finishedat"] - $peer["started"];
    else
        $dntime = $uptime;
    $downloaded = $peer["downloaded"];
    $uploaded = $peer["uploaded"];
    $userid = $peer["userid"];
    $torrentid = $peer["torrent"];
    
    mysql_query("INSERT INTO `traffic` (`userid`,`torrentid`,`downloaded`,`uploaded`,`downloadtime`,`uploadtime`) VALUES ('$userid','$torrentid','$downloaded','$uploaded','$dntime','$uptime')");
    echo "$userid, $torrentid, $downloaded, $uploaded, $interval   OK<br>\n";
}

?>