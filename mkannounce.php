<html>
<body>
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
require_once("include/benc.php");

function bark($msg) { die($msg); }

function dict_check($d, $s) {
	if ($d["type"] != "dictionary")
		bark("not a dictionary");
	$a = explode(":", $s);
	$dd = $d["value"];
	$ret = array();
	foreach ($a as $k) {
		unset($t);
		if (preg_match('/^(.*)\((.*)\)$/', $k, $m)) {
			$k = $m[1];
			$t = $m[2];
		}
		if (!isset($dd[$k]))
			bark("dictionary is missing key(s)");
		if (isset($t)) {
			if ($dd[$k]["type"] != $t)
				bark("invalid entry in dictionary");
			$ret[] = $dd[$k]["value"];
		}
		else
			$ret[] = $dd[$k];
	}
	return $ret;
}

function dict_get($d, $k, $t) {
	if ($d["type"] != "dictionary")
		bark("not a dictionary");
	$dd = $d["value"];
	if (!isset($dd[$k]))
		return;
	$v = $dd[$k];
	if ($v["type"] != $t)
		bark("invalid dictionary entry type");
	return $v["value"];
}


if (isset($_FILES["torrent"])) {
    $fn = $_FILES["torrent"]["tmp_name"];
    $torrent_data = bdec_file($fn, filesize($fn));   

    list($ann, $info) = dict_check($torrent_data, "announce(string):info");
    $infohash = pack("H*", sha1($info["string"]));
    $peer_id = "-AZ2202-" . mksecret(12);

    $totallen = dict_get($info, "length", "integer");
    if (isset($totallen)) {
    	$filelist[] = array($dname, $totallen);
    	$type = "single";
    }
    else {
    	$flist = dict_get($info, "files", "list");
    	if (!isset($flist))
    		bark("missing both length and files");
    	if (!count($flist))
    		bark("no files");
    	$totallen = 0;
    	foreach ($flist as $fn) {
    		list($ll, $ff) = dict_check($fn, "length(integer):path(list)");
    		$totallen += $ll;
    		$ffa = array();
    		foreach ($ff as $ffe) {
    			if ($ffe["type"] != "string")
    				bark("filename error");
    			$ffa[] = $ffe["value"];
    		}
    		if (!count($ffa))
    			bark("filename error");
    		$ffe = implode("/", $ffa);
    		$filelist[] = array($ffe, $ll);
    	}
    	$type = "multi";
    }
    
    $url  = $ann;
    $url .= "?info_hash=" . urlencode($infohash);
    $url .= "&peer_id=" . urlencode($peer_id);
    $url .= "&port=" . rand(10000,30000);
    $url .= "&compact=1";
    $url .= "&numwant=160";
    $url .= "&uploaded=0";
    $url .= "&downloaded=0";
    $url .= "&left=" . $totallen;
    $url .= "&event=started";
    
    echo "<p>URL: $url</p>";
}
?>
<form action="mkannounce.php" enctype="multipart/form-data" method="post">
<input type="file" name="torrent"> <input type="submit" value="Make Announce URL">
</form>
</body>
</html>