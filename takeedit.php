<?

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

hit_start();

function bark($msg) {
	genbark($msg, "Edit failed!");
}

function tr_status($status)
{
    //DUMMY
}

if (!mkglobal("id:name:descr:type"))
	bark("Fehlende Formulardaten!");

$id = 0 + $id;
if (!$id)
	die();

dbconn();

hit_count();

loggedinorreturn();

$GLOBALS["uploaderrors"] = Array();

$res = mysql_query("SELECT torrents.owner, torrents.numpics, torrents.filename, torrents.save_as, torrents.activated, users.class FROM torrents LEFT JOIN users ON torrents.owner=users.id WHERE torrents.id = $id");
$row = mysql_fetch_array($res);
if (!$row)
	die();

if ($CURUSER["id"] != $row["owner"] && !(get_user_class() >= UC_MODERATOR || ($row["activated"] == "no" && get_user_class() == UC_GUTEAM && $row["class"] < UC_UPLOADER)))
	bark("Das ist nicht Dein Torrent! Wie konnte das passieren?\n");

$updateset = array();

$fname = $row["filename"];
preg_match('/^(.+)\.torrent$/si', $fname, $matches);
$shortfname = $matches[1];
$dname = $row["save_as"];

$nfoaction = $_POST['nfoaction'];
if ($nfoaction == "update")
{
  $nfofile = $_FILES['nfo'];
  if (!$nfofile) die("No data " . var_dump($_FILES));
  if ($nfofile['size'] > 65535)
    bark("NFO is too big! Max 65,535 bytes.");
  $nfofilename = $nfofile['tmp_name'];
  if (@is_uploaded_file($nfofilename) && @filesize($nfofilename) > 0) {
    $nfo = str_replace("\x0d\x0d\x0a", "\x0d\x0a", file_get_contents($nfofilename));
    $updateset[] = "nfo = " . sqlesc($nfo);
    // Create NFO image
    gen_nfo_pic($nfo, $GLOBALS["BITBUCKET_DIR"]."/nfo-$id.png");
  }
}
else
  if ($nfoaction == "remove")
    $updateset[] = "nfo = ''";

$picaction = $_POST['picaction'];

if ($picaction == "update") {
    
    if ($row["numpics"] >0) {
        for ($I=1; $I<=$row["numpics"]; $I++) {
            @unlink($GLOBALS["BITBUCKET_DIR"]."/t-$id-$I.jpg");
            @unlink($GLOBALS["BITBUCKET_DIR"]."/f-$id-$I.jpg");
        }
    }
    
    // Handle picture uploads
    $picnum = 0;
    if ($_FILES["pic1"]["name"] != "") {
        if (torrent_image_upload($_FILES["pic1"], $id, $picnum+1))
            $picnum++;
    }
    
    if ($_FILES["pic2"]["name"] != "") {
        if (torrent_image_upload($_FILES["pic2"], $id, $picnum+1))
            $picnum++;
    }
    
    $updateset[] = "numpics = " . $picnum;
}

if ($_POST["stripasciiart"] == "1") {
    $descr = strip_ascii_art($descr);
}

$updateset[] = "name = " . sqlesc($name);
$updateset[] = "search_text = " . sqlesc(searchfield("$shortfname $dname $torrent"));
$updateset[] = "descr = " . sqlesc($descr);
$updateset[] = "ori_descr = " . sqlesc($descr);
$updateset[] = "category = " . (0 + $type);
if ($CURUSER["admin"] == "yes") {
	if ($_POST["banned"]) {
		$updateset[] = "banned = 'yes'";
		$_POST["visible"] = 0;
	}
	else
		$updateset[] = "banned = 'no'";
}

// Only allow torrent to be visible/alive if activated
if ($row["activated"] == "yes")
    $updateset[] = "visible = '" . ($_POST["visible"] ? "yes" : "no") . "'";

mysql_query("UPDATE torrents SET " . join(",", $updateset) . " WHERE id = $id");

write_log("torrentedit", "Der Torrent <a href=\"details.php?id=$id\">$id ($name)</a> wurde von '<a href=\"userdetails.php?id=$CURUSER[id]\">$CURUSER[username]</a>' bearbeitet.");

if (count($GLOBALS["uploaderrors"])) {
    $errstr = "<p>Beim Hochladen der Vorschaubilder sind Fehler aufgetreten:</p><ul>";
    foreach ($GLOBALS["uploaderrors"] as $error)
        $errstr .= "<li>$error</li>\n";
    $errstr .= "</ul><p>Alle anderen Änderungen wurden jedoch übernommen. Bitte bearbeite den Torrent erneut, um ";
    $errstr .= "neue Vorschaubilder hochzuladen.</p>";
    $errstr .= "<p><a href=\"details.php?id=$id&edited=1\">Weiter zur Detailansicht des Torrents</p>";
    stderr("Fehler beim Bilderupload", $errstr);
}

$returl = "details.php?id=$id&edited=1";
if (isset($_POST["returnto"]))
	$returl .= "&returnto=" . urlencode($_POST["returnto"]);
header("Refresh: 0; url=$returl");

hit_end();

?>
