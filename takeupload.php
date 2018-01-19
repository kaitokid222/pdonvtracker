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

require_once("include/benc.php");
require_once("include/bittorrent.php");

// ACHTUNG: Nicht immer erlaubt! Bitte notfalls in der VHost-Config via:
//   php_admin_value upload_max_filesize WERT
// setzen. WERT kann z.B. 12M, 1000K oder eine Zahl in Bytes sein.
ini_set("upload_max_filesize", $GLOBALS["MAX_TORRENT_SIZE"] + 2 * $GLOBALS["MAX_UPLOAD_FILESIZE"]);

userlogin();
loggedinorreturn();

$tupload = new tupload($GLOBALS["DB"]);
$tupload->setUser($CURUSER);
if($tupload->canUpload() == false)
	stderr("Keine Uploadrechte!", "Du hast kein Recht, auf diesem Tracker Torrents hochzuladen, da diese Funktion für Deinen Account von einem Moderator deaktiviert wurde.");
if($tupload->checkForm() == false)
	stderr("Fehlende Formulardaten", "Die übergebenen Daten sind unvollständig. Bitte benutze das Upload-Formular, und fülle alle nötigen Felder aus!");
$activated = ($tupload->isUploader() == false) ? "no" : "yes";



function abort($msg){
    end_table();
    end_frame();
    begin_frame("Torrent-Upload fehlgeschlagen!", FALSE, "650px");
    echo "<p>Beim Upload ist ein schwerwiegender Fehler aufgetreten:</p><p style=\"color:red\">" . $msg . "</p><p>Bitte korrigiere den angezeigten Fehler, und versuche es erneut!</p>";
    end_frame();
    stdfoot();
    die();
}

stdhead();
begin_frame("Überprüfe Upload...", FALSE, "650px");
begin_table(TRUE);

tr_msg("Dateiname der Torrent-Metadatei");
if($tupload->checkMetafile($_FILES["file"]) === false){
    tr_status("err");
    abort("Die Pr&uuml;fung der Torrentdatei ist gescheitert!");
}
if(!preg_match('/^(.+)\.torrent$/si', $_FILES["file"]["name"], $matches)) {
    tr_status("err");
    abort("Der Torrent-Dateiname muss mit \".torrent\" enden.");
}
tr_status("ok");

tr_msg("Max. Größe der Torrent-Metadatei");
if($tupload->checkMetafileSize($_FILES["file"]["size"]) === false){
    tr_status("err");
    abort("Die Pr&uuml;fung der Torrentdatei ist gescheitert!");
}
tr_status("ok");

tr_msg("Dateiname der NFO-Datei");
$nfofile = $_FILES['nfo'];
if($nfofile['name'] == ''){
    tr_status("err");
    abort("Die NFO hat keinen Dateinamen oder es wurde keine NFO-Datei hochgeladen!");
}
tr_status("ok");

tr_msg("Größe der NFO-Datei");
if($tupload->checknfofileSize($nfofile['size']) === false){
    tr_status("err");
    abort("Die Gr&ouml;ssenpr&uuml;fung der NFO-datei ist gescheitert!");
}
tr_status("ok");

$nfofilename = $nfofile['tmp_name'];
tr_msg("Uploadstatus der NFO-Datei");
if (@!is_uploaded_file($nfofilename)) {
    tr_status("err");
    abort("NFO-Upload fehlgeschlagen");
}
tr_status("ok");

tr_msg("Torrent-Beschreibung");
$descr = unesc($_POST["descr"]);
if($tupload->checkTorrentDescr($descr) === false){
    tr_status("err");
    abort("Die Torrentbeschreibung ist fehlerhaft");
}
tr_status("ok");

if($_POST["stripasciiart"] == "1"){
    $descr = strip_ascii_art($descr);
}

tr_msg("Kategorie-Zuordnung");
$catid = (0 + $_POST["type"]);
if (!is_valid_id($catid)) {
    tr_status("err");
    abort("Du musst eine Kategorie angeben, welcher der Torrent zugeordnet werden soll.");
}
tr_status("ok");


$shortfname = $torrent = $matches[1];
if (!empty($_POST["name"]))
    $torrent = unesc($_POST["name"]);

	
tr_msg("Torrent-Metadatei dekodieren und prüfen");
$dict = bdec_file($_FILES["file"]["tmp_name"]);
if(!isset($dict)){
    tr_status("err");
    abort("Was zum Teufel hast du da hochgeladen? Das ist jedenfalls keine gültige Torrent-Datei!");
}
tr_status("ok");
$dname = $dict['info']['name'];
tr_msg("Announce-URL");
if(!in_array($dict['announce'], $GLOBALS["ANNOUNCE_URLS"], 1)){
    tr_status("err");
    $errstr = "Ungültige Announce-URL! Muss eine der Folgenden sein:</p><ul>";
    sort($GLOBALS["ANNOUNCE_URLS"]);
    foreach ($GLOBALS["ANNOUNCE_URLS"] as $aurl)
        $errstr .= "<li>".htmlspecialchars($aurl)."</li>";
    abort($errstr . "</ul><p>");
}
tr_status("ok");

tr_msg("Plausibilitätsprüfung und Einlesen der Dateiliste");
if(isset($dict['info']['length']))
	$totallen = $dict['info']['length'];
else
	$totallen = 0;

$filelist = array();
if($totallen > 0){
	$filelist[] = array($dname, $totallen);
	$type = "single";
}else{
	$flist = $dict['info']['files'];
	if(!isset($flist)){
		tr_status("err");
		abort("Es fehlen sowohl der \"length\"- als auch der \"files\"-Schlüssel im Info-Dictionary!");
	}
	if(!count($flist)){
		tr_status("err");
		abort("Der Torrent enthält keine Dateien");
	}
	$totallen = 0;
	foreach($flist as $fn){
		$ll = $fn['length'];
		$ff = $fn['path'];
		$totallen += $ll;
		$ffe = implode("/", $ff);
		$filelist[] = array($ffe, $ll);
	} 
	$type = "multi";
}
tr_status("ok");

tr_msg("Plausibilitätsprüfung der Piece-Hashes");
if(strlen($dict['info']['pieces']) % 20 != 0){
	tr_status("err");
	abort("Die Länge der Piece-Hashes ist kein Vielfaches von 20!");
}
$numpieces = strlen($dict['info']['pieces'])/20;
if($numpieces != ceil($totallen/$dict['info']['piece length'])){
	tr_status("err");
	abort("Die Anzahl Piecehashes stimmt nicht mit der Torrentlänge überein (".$numpieces." ungleich ".ceil($totallen/$dict['info']['piece length']).")!");
}
tr_status("ok");

$dict["private"] = 1;
$dict["info"]["unique id"] = mksecret();

$infohash = pack("H*", sha1(benc($dict["info"])));
$torrent = str_replace("_", " ", $torrent);

// save info_hash as hex
// leichteres abgleichen! :D
$infohash_hex = bin2hex($infohash);


tr_msg("Torrent-Informationen in die Datenbank schreiben");
$nfo = str_replace("\x0d\x0d\x0a", "\x0d\x0a", @file_get_contents($nfofilename));
$searchfield_v = searchfield($shortfname . $dname . $torrent);
$visible_qv = "no";
$file_c = count($filelist);
$cat_qv = 0 + $_POST["type"];
$dt_qv = get_date_time();
$dtla_qv = get_date_time();
$fname = $tupload->get_fname();
/*$ret = mysql_query("INSERT INTO torrents (search_text, filename, owner, visible, info_hash, name, size, numfiles, type, descr, ori_descr, category, save_as, added, last_action, nfo, activated) VALUES (" .
    implode(",", array_map("sqlesc", array(
	searchfield("$shortfname $dname $torrent"),
	$fname,
	$CURUSER["id"],
	"no",
	$infohash_hex,
	$torrent,
	$totallen,
	count($filelist),
	$type,
	$descr,
	$descr,
	0 + $_POST["type"],
	$dname)
    )) . ", '" . get_date_time() . "', '" . get_date_time() . "', ".sqlesc($nfo).", '$activated')");*/
	
$qry = $GLOBALS["DB"]->prepare("INSERT INTO torrents (search_text, filename, owner, visible, info_hash, name, size, numfiles, type, descr, ori_descr, category, save_as, added, last_action, nfo, activated) VALUES (:searchfield, :fname, :owner, :visible, :hexhash, :tname, :size, :fcount, :ttype, :descr, :odescr, :cat, :saveas, :dt, :dtla, :nfo, :activated)");
$qry->bindParam(':searchfield', $searchfield_v, PDO::PARAM_STR);
$qry->bindParam(':fname', $fname, PDO::PARAM_STR);
$qry->bindParam(':owner', $CURUSER["id"], PDO::PARAM_INT);
$qry->bindParam(':visible', $visible_qv, PDO::PARAM_STR);
$qry->bindParam(':hexhash', $infohash_hex, PDO::PARAM_STR);
$qry->bindParam(':tname', $torrent, PDO::PARAM_STR);
$qry->bindParam(':size', $totallen, PDO::PARAM_INT);
$qry->bindParam(':fcount', $file_c, PDO::PARAM_INT);
$qry->bindParam(':ttype', $type, PDO::PARAM_STR);
$qry->bindParam(':descr', $descr, PDO::PARAM_STR);
$qry->bindParam(':odescr', $descr, PDO::PARAM_STR);
$qry->bindParam(':cat', $cat_qv, PDO::PARAM_INT);
$qry->bindParam(':saveas', $dname, PDO::PARAM_STR);
$qry->bindParam(':dt', $dt_qv, PDO::PARAM_STR);
$qry->bindParam(':dtla', $dtla_qv, PDO::PARAM_STR);
$qry->bindParam(':nfo', $nfo, PDO::PARAM_STR);
$qry->bindParam(':activated', $activated, PDO::PARAM_STR);
$qry->execute();
if(!$qry->rowCount()){
	tr_status("err");
	//print_r($qry->errorInfo());
	abort("Torrent konnte nicht in die DB geschrieben werden.");
} 
$id = $GLOBALS['DB']->lastInsertId();
$qry = $GLOBALS["DB"]->prepare("DELETE FROM files WHERE torrent = :id");
$qry->bindParam(':id', $id, PDO::PARAM_INT);
$qry->execute();
foreach($filelist as $file){
	$qry = $GLOBALS["DB"]->prepare("INSERT INTO files (torrent, filename, size) VALUES (:id, :fn, :fz)");
	$qry->bindParam(':id', $id, PDO::PARAM_INT);
	$qry->bindParam(':fn', $file[0], PDO::PARAM_STR);
	$qry->bindParam(':fz', $file[1], PDO::PARAM_INT);
	$qry->execute();
} 
tr_status("ok");

tr_msg("Torrent-Datei auf dem Server speichern");
// We don't move the file anymore, we rather write the changed,
// bencoded version of our dictionary.
$fhandle = fopen($GLOBALS["TORRENT_DIR"]."/" . $id . ".torrent", "w");
if ($fhandle) {
    fwrite($fhandle, benc($dict));
    fclose($fhandle);
} else {
    tr_status("err");
    abort("Fehler beim Öffnen der Torrent-Datei auf dem Server (Schreibzugriff verweigert) - bitte SysOp benachrichtigen!");
} 
tr_status("ok");

write_log("torrentupload", "Der Torrent <a href=\"details.php?id=" . $id . "\">" . $id . "(" . $torrent . ")</a> wurde von '<a href=\"userdetails.php?id=" . $CURUSER["id"] . "\">" . $CURUSER["username"] . "</a>' hochgeladen.");

// Handle picture uploads
$picnum = 0;
if($_FILES["pic1"]["name"] != "") {
    tr_msg("Vorschaubild ".($picnum+1)." verkleinern und ablegen");
	if($tupload->torrent_image_upload($_FILES["pic1"], $id, $picnum+1)){
		$picnum++;
		tr_status("ok");
	}else
		tr_status("err");
} 

if($_FILES["pic2"]["name"] != ""){    
    tr_msg("Vorschaubild ".($picnum+1)." verkleinern und ablegen");
    if($tupload->torrent_image_upload($_FILES["pic2"], $id, $picnum+1)){
		$picnum++;
		tr_status("ok");
	}else
		tr_status("err");
} 

if($picnum > 0){
	$qry = $GLOBALS["DB"]->prepare("UPDATE torrents SET numpics= :np WHERE id= :id");
	$qry->bindParam(':np', $picnum, PDO::PARAM_INT);
	$qry->bindParam(':id', $id, PDO::PARAM_INT);
	$qry->execute();
}

// Create NFO image
tr_msg("NFO-Bild erzeugen");
if (gen_nfo_pic($nfo, $GLOBALS["BITBUCKET_DIR"]."/nfo-" . $id . ".png") == 0)
    tr_status("err");
else
    tr_status("ok");

/* RSS feeds */
// Now dynamically handled by rss.php!

if ($activated == "no") {
    tr_msg("Gastuploader-Team und Moderatoren benachrichtigen");
    $mod_msg = "[b]Der Benutzer [url=".$DEFAULTBASEURL."/userdetails.php?id=".$CURUSER["id"]."]".$CURUSER["username"]."[/url] hat einen Torrent hochgeladen:[/b]\n\n[url=".$DEFAULTBASEURL."/details.php?id=".$id."]".$torrent."[/url] (".$id.")\n\nBitte überprüfen und freischalten/löschen.";
	$qry = $GLOBALS["DB"]->prepare("SELECT `id` FROM `users` WHERE `class` = :class");
	$qry->bindParam(':class', UC_GUTEAM, PDO::PARAM_INT);
	$qry->execute();
	if($qry->rowCount()){
		$uids = $qry->FetchAll(PDO::FETCH_ASSOC);
		foreach($uids as $u)
			sendPersonalMessage(0, $u["id"], "Der Benutzer ".$CURUSER["username"]." hat einen Torrent hochgeladen.", $mod_msg);
	}else{
		sendPersonalMessage(0, 0, "Der Benutzer ".$CURUSER["username"]." hat einen Torrent hochgeladen.", $mod_msg, PM_FOLDERID_MOD, 0, "open");
	}
    tr_status("ok");
}

end_table();
end_frame();
begin_frame("Torrent-Upload war erfolgreich!", FALSE, "650px");
echo "<p>Dein Torrent wurde erfolgreich hochgeladen. <b>Beachte</b> dass Dein Torrent erst".
	"sichtbar wird, wenn der erste Seeder verfügbar ist!</p>\n";

if ($tupload->get_uploaderrors_pic() !== false) {
	echo "<p>Beim Upload des Torrents ist mindestens ein unkritischer Fehler aufgetreten:</p>\n".
		"<ul>\n";
	foreach($tupload->get_uploaderrors_pic() as $pic)
		foreach($pic as $error)
			echo "    <li>" . $error . "</li>";
	echo "</ul>\n";
}

if ($activated == "no") {
	echo "<p><b>Da Du kein Uploader bist, wurde Dein Torrent als Gastupload gewertet, und muss ".
		"zuerst von einem Gastupload-Betreuer &uuml;berpr&uuml;ft und freigeschaltet werden. ".
		"Erst dann kannst Du den Torrent zum Seeden herunterladen.</b> Bitte sende uns keine ".
		"Nachrichten mit der Bitte um Freischaltung. Das Team wurde bereits per PN &uuml;ber ".
		"Deinen Upload benachrichtigt, und wird sich baldm&ouml;glichst darum k&uuml;mmern.</p>\n";
}
echo "<p><b>Wichtiger Hinweis:</b><br>Bevor Du den Torrent seeden kannst, musst Du den Torrent ".
	"erneut vom Tracker herunterladen, da beim Upload einige Änderungen an der Torrent-Datei ".
	"vorgenommen wurden. Dadurch hat der Torrent einen neuen Info-Hash erhalten, und beim ".
	"Download wird ebenfalls Dein PassKey in die Announce-URL eingefügt. <b>Das ".
	"&Auml;ndern der Announce-URL in Deiner soeben hochgeladenen Torrent-Metadatei gen&uuml;gt ".
	"nicht!</b></p>\n".
	"<p style=\"text-align:center\"><a href=\"details.php?id=" . $id . "\">Weiter zu den Details Deines Torrents</a></p>\n";

end_frame();
stdfoot();
?>