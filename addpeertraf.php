<?php

require_once("include/bittorrent.php");

// wozu dient diese php-datei eigentlich?

//dbconn(false);
userlogin();
//$res = mysql_query("SELECT userid,torrent,UNIX_TIMESTAMP(started) AS started,finishedat,uploaded,downloaded FROM peers");
$qry = $GLOBALS['DB']->prepare('SELECT userid,torrent,UNIX_TIMESTAMP(started) AS started,finishedat,uploaded,downloaded FROM peers');
$qry->execute();
if(!$qry->rowCount())
	stderr("Fehler", "pdo select failed");
else
	$res = $qry->FetchAll();
foreach($res as $peer){
//while ($peer = mysql_fetch_assoc($res)) {
    $uptime = time() - $peer["started"];
    if ($peer["finishedat"] > 0)
        $dntime = $peer["finishedat"] - $peer["started"];
    else
        $dntime = $uptime;
    $downloaded = $peer["downloaded"];
    $uploaded = $peer["uploaded"];
    $userid = $peer["userid"];
    $torrentid = $peer["torrent"];
        
	$qry = $GLOBALS['DB']->prepare('INSERT INTO `traffic` (`userid`,`torrentid`,`downloaded`,`uploaded`,`downloadtime`,`uploadtime`) VALUES (:uid,:tid,:dld,:uld,:dntime,:uptime)');
	$qry->bindParam(':uid', $userid, PDO::PARAM_INT);
	$qry->bindParam(':tid', $torrentid, PDO::PARAM_INT);
	$qry->bindParam(':dld', $downloaded, PDO::PARAM_STR);
	$qry->bindParam(':uld', $uploaded, PDO::PARAM_STR);
	$qry->bindParam(':dntime', $dntime, PDO::PARAM_STR);
	$qry->bindParam(':uptime', $uptime, PDO::PARAM_STR);
	$qry->execute();
	if(!$qry->rowCount())
        stderr("Fehler", "Daten konnten nicht eingefügt werden.");
	else
    //mysql_query("INSERT INTO `traffic` (`userid`,`torrentid`,`downloaded`,`uploaded`,`downloadtime`,`uploadtime`) VALUES ('$userid','$torrentid','$downloaded','$uploaded','$dntime','$uptime')");
		echo "$userid, $torrentid, $downloaded, $uploaded, $interval   OK<br>\n";
}

?>