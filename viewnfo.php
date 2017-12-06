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

require_once "include/bittorrent.php";

//dbconn(false);
userlogin();
loggedinorreturn();

if(isset($_GET['id'])){
	if(is_valid_id($_GET['id']))
		$id = $_GET["id"];
	else
		die("Ungültige NFO-ID!");
}else
	die("ID nicht lesbar!");
/*$id = $_GET["id"];
if (!is_valid_id($id))
    die("Ungültige NFO-ID!");*/

if($_GET["dl"]=="1"){
	$qry = $GLOBALS['DB']->prepare('SELECT nfo FROM torrents WHERE id = :id');
	$qry->bindParam(':id', $id, PDO::PARAM_INT);
	$qry->execute();
	$a = $qry->fetchAll()[0];
	$nfo = $a["nfo"];
    /*$r = mysql_query("SELECT nfo FROM torrents WHERE id=$id") or sqlerr();
    $a = mysql_fetch_assoc($r) or die("Puke");
    $nfo = $a["nfo"];*/
    header("Pragma: private");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");            
    header("Content-Type: text/plain");
    header("Content-Length: ".strlen($nfo));
    
    echo $nfo;
    die();
}

$qry = $GLOBALS['DB']->prepare('SELECT name FROM torrents WHERE id = :id');
$qry->bindParam(':id', $id, PDO::PARAM_INT);
$qry->execute();
$a = $qry->fetchAll()[0];
/*$r = mysql_query("SELECT name FROM torrents WHERE id=$id") or sqlerr();
$a = mysql_fetch_assoc($r) or die("Puke");*/

stdhead();
begin_frame("NFO zu <a href=details.php?id=" . $id . ">" . $a['name'] . "</a>\n", FALSE, "500px");
begin_table(TRUE);
print("<tr>\n".
	"    <td class=tableb style=\"text-align: center\">\n".
	"        <a href=\"viewnfo.php?id=" . $id . "&amp;dl=1\">Download NFO</a>\n".
	"    </td>\n".
	"</tr>\n".
	"<tr>\n".
	"    <td class=tablea style=\"text-align: center\">\n".
	"        <div style=\"padding:5px;background-color:white;\">\n".
	"            <img src=\"".$GLOBALS["BITBUCKET_DIR"]."/nfo-" . $id . ".png\" alt=\"NFO zu " . $a['name'] . "\">\n".
	"        </div>\n".
	"    </td>\n".
	"</tr>\n");
end_table();
end_frame();
stdfoot();
?>