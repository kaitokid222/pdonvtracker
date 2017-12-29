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
userlogin();
loggedinorreturn();

if(isset($_GET["id"]) && intval($_GET["id"]) != $CURUSER["id"]){
	$userid = intval($_GET["id"]);
	$qry = $GLOBALS['DB']->prepare('SELECT username,class FROM users WHERE id= :id');
	$qry->bindParam(':id', $userid, PDO::PARAM_INT);
	$qry->execute();
	if($qry->rowCount() > 0)
		$obj = $qry->fetchObject();
	else 
		stderr("Fehler", "Es existiert kein User mit der ID " . $userid . "!");
	if($CURUSER["class"] < UC_MODERATOR || $CURUSER["class"]<=$obj->class)
		stderr("Fehler", "Du hast keine Rechte, den BitBucket-Inhalt dieses Benutzers anzusehen oder zu ändern!");
	$username = $obj->username;
	$userclass = $obj->class;  
}else{
	$userid = $CURUSER["id"];
	$username = $CURUSER["username"];
	$userclass = $CURUSER["class"];
}

if($userclass>=UC_UPLOADER)
	$maxbucketsize = $GLOBALS["MAX_BITBUCKET_SIZE_UPLOADER"];
else
	$maxbucketsize = $GLOBALS["MAX_BITBUCKET_SIZE_USER"];


if(isset($_GET["delete"])){
	$file_id = intval($_GET["delete"]);
	$numfiles = $database->row_count('bitbucket','`id`='.$file_id.' AND `user`='.$userid);
	if($numfiles==1){
		$qry = $GLOBALS['DB']->prepare('SELECT * FROM bitbucket WHERE `id`= :fid');
		$qry->bindParam(':fid', $file_id, PDO::PARAM_INT);
		$qry->execute();
		if($qry->rowCount() > 0)
			$bucketfile = $qry->fetchObject();
		if(!isset($_GET["sure"]))
			stderr("Datei wirklich löschen?", "Bist Du Dir wirklich sicher, dass die Datei '".$bucketfile->originalname."' aus dem BitBucket gelöscht werden soll? Wenn ja, dann <a href=\"bitbucket.php?".(isset($_GET["id"])?"id=$userid&amp;":"")."delete=$file_id&amp;sure=1\">klicke hier</a>.");
		else{
			@unlink($GLOBALS["BITBUCKET_DIR"]."/".$bucketfile->filename);
			$qry = $GLOBALS['DB']->prepare('DELETE FROM bitbucket WHERE `id`= :fid');
			$qry->bindParam(':fid', $file_id, PDO::PARAM_INT);
			$qry->execute();
			if(isset($_GET["id"])){
				$qry = $GLOBALS['DB']->prepare('SELECT modcomment FROM users WHERE id= :id');
				$qry->bindParam(':id', $userid, PDO::PARAM_INT);
				$qry->execute();
				if($qry->rowCount() > 0)
					$obj = $qry->fetchObject();
				$obj->modcomment = date("Y-m-d") . " - Die Datei '".$bucketfile->originalname."' wurde von ".$CURUSER["username"]." aus dem BitBucket gelöscht.\n" . $obj->modcomment;
				$qry = $GLOBALS['DB']->prepare('UPDATE users SET modcomment= :cmt WHERE id= :id');
				$qry->bindParam(':id', $userid, PDO::PARAM_INT);
				$qry->bindParam(':cmt', $obj->modcomment, PDO::PARAM_STR);
				$qry->execute();
			}
			stderr("Erfolg", "<p>Die Datei wurde erfolgreich aus Deinem Bitbucket gel&ouml;scht.</p><p><a href=\"bitbucket.php".(isset($_GET["id"])?"?id=$userid":"")."\">Zur&uuml;ck zum BitBucket</p>");
		}
	}else
		stderr("Fehler", "<p>Diese Datei geh&ouml;rt nicht Ihnen, oder sie wurde bereits gel&ouml;scht.</p><p><a href=\"bitbucket.php".(isset($_GET["id"])?"?id=$userid":"")."\">Zur&uuml;ck zum BitBucket</p>");
}
stdhead("BitBucket von " . $username);
begin_frame("BitBucket von " . $username, FALSE, "650px");
begin_table(TRUE);
if($userid == $CURUSER["id"]){
	echo "    <form method=\"post\" action=\"bitbucket-upload.php" . (isset($_GET["id"]) ? "?id=" . $userid : "") . "\" enctype=\"multipart/form-data\">\n".
		"    <tr>\n".
		"        <td colspan=\"2\" class=\"tablecat\" align=\"left\"><b>Neue Datei hochladen</b> - Maximale Dateigr&ouml;&szlig;e: " . mksize($GLOBALS["MAX_UPLOAD_FILESIZE"]) . "</td>\n".
		"    </tr>\n".
		"    <tr>\n".
		"        <td class=\"tableb\">Datei</td>\n".
		"        <td class=\"tablea\"><input type=\"file\" name=\"file\" size=\"60\"></td>\n".
		"    </tr>\n".
		"    <tr>\n".
		"        <td class=\"tableb\">Avatar</td>\n".
		"        <td class=\"tablea\"><input type=\"checkbox\" id=\"avatar\" name=\"is_avatar\" value=\"1\"><label for=\"avatar\"> Dieses Bild ist ein Avatar, und soll automatisch auf die richtige Gr&ouml;&szlig;e gebracht werden.</label><br><i>(Nur JPEG und PNG)</i></td>\n".
		"    </tr>\n".
		"    <tr>\n".
		"        <td class=\"tablea\" colspan=\"2\" align=\"center\"><input type=\"submit\" value=\"Hochladen\" class=\"btn\"></td>\n".
		"    </tr>\n".
		"    </form>\n";
	end_table();
	echo "<table class=\"main\" width=\"640\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n".
		"    <tr>\n".
		"        <td><font class=\"small\"><b>Hinweis:</b> Die hochgeladenen Dateien m&uuml;ssen mit den Avatar-Regeln konform sein, und d&uuml;rfen keine illegalen, gewaltverherrlichenden oder pornographischen Inhalte enthalten. Lade bitte auch keine Dateien hoch, von denen du nicht m&ouml;chtest, dass diese ein Fremder zu sehen bekommt.</font></td>\n".
		"    </tr>\n".
		"</table>\n".
		"<br>\n";
}else{
	echo "    <tr>\n".
		"        <td class=tablea align=center><a href=\"userdetails.php?id=" . $userid . "\">Zurück zum Profil</a></td>\n".
		"    </tr>";
	end_table();
}

echo "<p>Der BitBucket enth&auml;lt momentan folgende Bilddateien:</p>\n";
begin_table(TRUE);
echo "<colgroup>\n".
	"    <col width=\"1*\">\n".
	"    <col width=\"1*\">\n".
	"    <col width=\"1*\">\n".
	"</colgroup>\n";
$qry = $GLOBALS['DB']->prepare('SELECT SUM(size) FROM bitbucket WHERE user= :id');
$qry->bindParam(':id', $userid, PDO::PARAM_INT);
$qry->execute();
if($qry->rowCount() > 0)
	$bucketsize = $qry->fetchColumn();
	
$bfiles = $GLOBALS['DB']->prepare('SELECT * FROM bitbucket WHERE user= :id');
$bfiles->bindParam(':id', $userid, PDO::PARAM_INT);
$bfiles->execute();

if($database->row_count('bitbucket','`user`='.$userid) == 0){
    echo "    <tr>\n".
		"        <td class=\"tablea\" colspan=\"4\">Es sind zurzeit keine Dateien im BitBucket vorhanden.</td>\n".
		"    </tr>\n";
}else{
	$imgline = "    <tr>\n";
	$descline = "    <tr>\n";
	$cnt = 0;
	foreach($bfiles->fetchAll() as $fileinfo) {
		if($cnt>0 && $cnt%3==0){
			echo $imgline."    </tr>\n";
			echo $descline."    </tr>\n";
			$imgline = "    <tr>\n";
			$descline = "    <tr>\n";	    
		}

		$imgline .= "        <td class=\"tablea\" align=\"center\" valign=\"middle\"><img src=\"".$GLOBALS["BITBUCKET_DIR"]."/".$fileinfo["filename"]."\" width=\"100\" alt=\"".htmlspecialchars($fileinfo["originalname"])."\" title=\"".htmlspecialchars($fileinfo["originalname"])."\"></td>\n";
		$descline .= "        <td class=\"tableb\" align=\"center\" valign=\"top\"><a href=\"".$GLOBALS["BITBUCKET_DIR"]."/".$fileinfo["filename"]."\">".htmlspecialchars($fileinfo["originalname"])."</a><br>(". mksize($fileinfo["size"]).") <a href=\"bitbucket.php?" . (isset($_GET["id"]) ? "id=" . $userid . "&amp;" : "") . "delete=" . $fileinfo["id"] . "\"><img src=\"".$GLOBALS["PIC_BASE_URL"]."/editdelete.png\" width=\"16\" height=\"16\" alt=\"L&ouml;schen\" style=\"border:none;vertical-align:middle;\"></a></td>\n";
		$cnt++;
	}

	if($cnt%3!=0){
		for($I=0; $I<3-$cnt%3; $I++){
			$imgline .= "        <td class=\"tablea\" align=\"center\" valign=\"middle\">&nbsp;</td>\n";
			$descline .= "        <td class=\"tableb\" align=\"center\" valign=\"top\">&nbsp;</td>\n";
		}
	}

	echo $imgline."    </tr>\n";
	echo $descline."    </tr>\n";
}
end_table();

if($userid == $CURUSER["id"]){
	echo "<p><b>Hinweis:</b> Um den Link auf die Datei zu erhalten, klicke mit der rechten Maustaste auf den Dateinamen und w&auml;hle den Eintrag \"Link-Adresse kopieren\" aus dem Men&uuml;. Diesen Link kannst Du dann auf dem Tracker frei benutzen.</p>".
		"<p>Bei Fragen lies bitte die <a class=\"altlink\" href=\"faq.php#usere\">FAQ</a>!";
}

end_frame();
begin_frame("BitBucket Speicherplatznutzung", FALSE, "650px");
echo "<br>\n".
	"<center>\n";
begin_table();
echo "    <tr>\n".
	"        <td style=\"padding: 0px; width: 400px; background-image: url(" . $GLOBALS["PIC_BASE_URL"] . "loadbarbg.gif); background-repeat: repeat-x\">";

$qry = $GLOBALS['DB']->prepare('SELECT SUM(size) FROM bitbucket WHERE user= :id');
$qry->bindParam(':id', $userid, PDO::PARAM_INT);
$qry->execute();
if($qry->rowCount() > 0)
	$size = $qry->fetchColumn();
$percent = min(100,round($size/$maxbucketsize*100));
if($percent <= 70)
	$pic = "loadbargreen.gif";
elseif($percent <= 90)
	$pic = "loadbaryellow.gif";
else
	$pic = "loadbarred.gif";
$width = $percent * 4;
echo "<img src=\"" . $GLOBALS["PIC_BASE_URL"] . $pic."\" height=\"15\" width=\"" . $width . "\" alt=\"" . $percent . "%\"></td>\n".
	"    </tr>\n";
end_table();
echo mksize($size) . " von ".mksize($maxbucketsize)." belegt (" . $percent . "%)\n".
	"</center>\n";
end_frame();
stdfoot();
?>