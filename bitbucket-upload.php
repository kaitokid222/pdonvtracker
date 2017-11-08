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
//dbconn();
userlogin();
loggedinorreturn();

$maxfilesize = $GLOBALS["MAX_UPLOAD_FILESIZE"];

if ($CURUSER["class"]>=UC_UPLOADER) {
    $maxbucketsize = $GLOBALS["MAX_BITBUCKET_SIZE_UPLOADER"];
} else {
    $maxbucketsize = $GLOBALS["MAX_BITBUCKET_SIZE_USER"];
}

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
	$file = $_FILES["file"];
	if (!isset($file) || $file["size"] < 1)
		stderr("Upload fehlgeschlagen", "Es wurden keine Daten empfangen!");
    if ($_POST["is_avatar"] != "1") {
    	if ($file["size"] > $maxfilesize)
    		stderr("Upload fehlgeschlagen", "Sorry, diese Datei ist zu gro&szlig; f&uuml;r den BitBucket.");

		$qry = $GLOBALS['DB']->prepare('SELECT SUM(size) FROM bitbucket WHERE user= :id');
		$qry->bindParam(':id', $GLOBALS["CURUSER"]["id"], PDO::PARAM_INT);
		$qry->execute();
		if($qry->rowCount() > 0){
			$bucketsize = $qry->fetchColumn();
		}
    	if ($bucketsize+$file["size"]>$maxbucketsize)
    		stderr("Upload fehlgeschlagen", "Sorry, Dein BitBucket is zu voll, um diese Datei aufnehmen zu k&ouml;nnen. Bitte l&ouml;sche erst eine oder mehrere Dateien, bevor Du eine weitere hochl&auml;dst.");	
    }
	$filename = md5_file($file["tmp_name"]);
	if (file_exists($tgtfile))
		stderr("Upload fehlgeschlagen", "Sorry, eine Datei mit dem Namen <b>" . htmlspecialchars($filename) . "</b> existiert bereits im BitBucket.");
	$it = exif_imagetype($file["tmp_name"]);
	if ($it != IMAGETYPE_GIF && $it != IMAGETYPE_JPEG && $it != IMAGETYPE_PNG)
		stderr("Upload fehlgeschlagen", "Sorry, die hochgeladene Datei konnte nicht als g&uuml;tige Bilddatei verifiziert werden.");

	$i = strrpos($file["name"], ".");
	if ($i !== false)
	{
		$ext = strtolower(substr($file["name"], $i));
		if (($it == IMAGETYPE_GIF && $ext != ".gif") || ($it == IMAGETYPE_JPEG && $ext != ".jpg") || ($it == IMAGETYPE_PNG && $ext != ".png"))
			stderr("Fehler", "Ung&uuml;tige Dateinamenerweiterung: <b>$ext</b>");
		$filename .= $ext;
	}
	else
		stderr("Fehler", "Die Datei muss eine Dateinamenerweiterung besitzen.");
	$tgtfile = $GLOBALS["BITBUCKET_DIR"]."/".$filename;
    
    if ($_POST["is_avatar"] == "1") {
        $img = resize_image($file["name"], $file["tmp_name"], $tgtfile);
        if (!$img)
            stderr("Fehler", "Das hochgeladene Bild konnte nicht verarbeitet werden. Bitte ändere die Größe selbständig auf Avatargröße, und versuche den Upload ohne die Option \"automatisch anpassen\". Die Automatik akzeptiert nur die Formate JPEG und PNG. Animierte GIFs werden nicht unterstützt (ergeben ein statisches Bild).");
        imagedestroy($img);
        $file["size"] = filesize($tgtfile);
        if ($bucketsize+filesize($tgtfile)>$maxbucketsize) {
            unlink($tgtfile);
            stderr("Upload fehlgeschlagen", "Sorry, Dein BitBucket ist zu voll, um diese Datei aufnehmen zu k&ouml;nnen. Bitte l&ouml;sche erst eine oder mehrere Dateien, bevor Du eine weitere hochl&auml;dst.");	
        }
    } else {
        move_uploaded_file($file["tmp_name"], $tgtfile) or stderr("Fehler", "Interner Fehler #2.");
    }
        
    $url = str_replace(" ", "%20", htmlspecialchars($GLOBALS["BITBUCKET_DIR"]."/".$filename));
	$qry = $GLOBALS['DB']->prepare('INSERT INTO bitbucket (`user`,`filename`,`size`,`originalname`) VALUES (:id,:fn,:fz,:fnn)');
	$qry->bindParam(':id', $CURUSER["id"], PDO::PARAM_INT);
	$qry->bindParam(':fn', $filename, PDO::PARAM_STR);
	$qry->bindParam(':fz', $file["size"], PDO::PARAM_STR);
	$qry->bindParam(':fnn', $file["name"], PDO::PARAM_STR);
	$qry->execute();
	
    header("Location: bitbucket.php");
}

stdhead("BitBucket Upload");
begin_frame("BitBucket Upload", FALSE, "650px");
begin_table(TRUE);
?>
<tr><td class="tablea" align="center"><a href="bitbucket.php">Zur&uuml;ck zum BitBucket-Inhalt</a></td></tr></table></br>
<form method=post action="bitbucket-upload.php" enctype="multipart/form-data">
<p><b>Maximale Dateigr&ouml;&szlig;e: <?=number_format($maxfilesize); ?> Bytes.</b></p>
<?php begin_table(TRUE); ?>
<tr><td class=tableb>Datei</td><td class=tablea><input type=file name=file size=60></td></tr>
<tr><td class=tablea colspan=2 align=center><input type=submit value="Upload" class=btn></td></tr>
</table>
</form>
<p>
<table class=main width=640 border=0 cellspacing=0 cellpadding=0><tr><td class=embedded>
<font class=small><b>Hinweis:</b> Die hochgeladenen Dateien m&uuml;ssen mit den Avatar-Regeln konform sein, und d&uuml;rfen keine illegalen, gewaltverherrlichenden oder pronographischen Inhalte enthalten. Lade bitte auch keine Dateien hoch, von denen du nicht m&ouml;chtest, dass diese ein Fremder zu sehen bekommt.</font>
</td></tr></table>
<?php end_frame(); ?>

<?php
stdfoot();
?>