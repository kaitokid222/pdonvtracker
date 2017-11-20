<?php
require_once("include/bittorrent.php");
require_once("include/benc.php");

dbconn(false);

function err($msg) {
   return benc_resp_raw("d".benc_str("failure reason").benc_str($msg)."e");
}

function benc_resp_raw($x){
    header("Content-Type: text/plain");
    header("Pragma: no-cache");
    print($x);
}

$ip = $_SERVER['REMOTE_ADDR'];
$result = mysql_query("select lastAccess from scrape_lastlog where ipAddress='$ip'");
$lastlog = mysql_fetch_assoc($result);

$now = time();
$then = strtotime($lastlog['lastAccess']);

if (($then + $GLOBALS["SCRAPE_INTERVAL"]) >= $now) {
	sleep(10);
	err("Weniger oft Scrapen!");
}
mysql_query("delete from scrape_lastlog where ipAddress='$ip'") or die(mysql_error());
mysql_query("insert into scrape_lastlog(ipAddress) values('$ip')") or die(mysql_error());

$lastInteresting = time() - $GLOBALS["SCRAPE_INTERVAL"] - 300 ;
$lastIntSQL = date( 'Y-m-d H:i:s', $lastInteresting);
mysql_query("delete from scrape_lastlog where lastAccess < '$lastIntSQL'");


$r = "d" . benc_str("files") . "d";

$fields = "info_hash, times_completed, seeders, leechers";

if (!isset($_GET["info_hash"]))
	$query = "SELECT $fields FROM torrents ORDER BY info_hash";
else
	$query = "SELECT $fields FROM torrents WHERE " . hash_where("info_hash", unesc($_GET["info_hash"]));

$res = mysql_query($query);

while ($row = mysql_fetch_assoc($res)) {
	$r .= "20:" . hash_pad($row["info_hash"]) . "d" .
		benc_str("complete") . "i" . $row["seeders"] . "e" .
		benc_str("downloaded") . "i" . $row["times_completed"] . "e" .
		benc_str("incomplete") . "i" . $row["leechers"] . "e" .
		"e";
}

$r .= "ee";

header("Content-Type: text/plain");
print($r);
?>
