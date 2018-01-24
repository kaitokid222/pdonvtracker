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
if (get_user_class() < UC_MODERATOR)
    stderr("Fehler", "Dir ist der Zugang zu dieser Seite nicht gestattet!");

stdhead("BitBucket Gallerie");
begin_frame("BitBucket Gallerie", FALSE, "750px");

// Alle Dateien im BitBucket holen
$count = $GLOBALS['DB']->query('SELECT COUNT(DISTINCT(user)) AS cnt FROM bitbucket')->fetch();
list($pagertop, $pagerbottom, $limit) = pager(10, $count["cnt"], "bitbucket-gallery.php?");
//$qry = $GLOBALS['DB']->prepare('SELECT DISTINCT(bitbucket.user) AS id,users.username AS username FROM bitbucket JOIN users ON bitbucket.user=users.id ORDER BY users.username ASC :limit'); // buggy
$qry = $GLOBALS['DB']->prepare('SELECT DISTINCT(bitbucket.user) AS id,users.username AS username FROM bitbucket JOIN users ON bitbucket.user=users.id ORDER BY users.username ASC');
//$qry->bindParam(':limit', $limit, PDO::PARAM_STR);
$qry->execute();
if(!$qry->rowCount())
	echo "<p>Es wurde bislang in keinen BitBucket etwas hochgeladen!</p>";
else{
	$userres = $qry->FetchAll();
	echo $pagertop;
	//while ($user = mysql_fetch_assoc($userres)) {
	foreach($userres as $user){
		$qry = $GLOBALS['DB']->prepare("SELECT * FROM bitbucket WHERE user = :user");
		$qry->bindParam(':user', $user["id"], PDO::PARAM_INT);
		$qry->execute();
		$res = $qry->FetchAll();
		//$res = mysql_query("SELECT * FROM bitbucket WHERE user=".$user["id"]);
		begin_table(TRUE);
		echo "    <colgroup>\n".
			"        <col width=\"25%\">\n".
			"        <col width=\"25%\">\n".
			"        <col width=\"25%\">\n".
			"        <col width=\"25%\">\n".
			"    </colgroup>\n".
			"    <tr>\n".
			"        <td class=\"tablecat\" align=\"center\" colspan=\"4\"><b>".htmlspecialchars($user["username"])."</b> [<a href=\"bitbucket.php?id=".$user["id"]."\">BitBucket bearbeiten</a>]</td>\n".
			"    </tr>";
		$I=0;
		foreach($res as $pics){
		//while($pics = mysql_fetch_assoc($res)){
			if ($I>0 && $I%4==0)
				echo "    </tr>\n".
					"    <tr>";
			$pics["originalname"] = htmlspecialchars($pics["originalname"]);
			echo "        <td class=\"tablea\" align=\"center\" valign=\"middle\"><a href=\"".$GLOBALS["BITBUCKET_DIR"]."/".$pics["filename"]."\" data-lightbox=\"preview\"><img src=\"".$GLOBALS["BITBUCKET_DIR"]."/".$pics["filename"]."\" width=\"150\" border=\"0\" alt=\"".$pics["originalname"]."\" title=\"".$pics["originalname"]."\"></a></td>\n";
			$I++;
		}
		if($I%4){
			for($J=0; $J<=(4-$I%4-1); $J++){
				echo "        <td class=tablea>&nbsp;</td>\n";
			}
		}
		end_table();
	}
	echo $pagerbottom;
}
end_frame();
stdfoot();
?>