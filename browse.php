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

userlogin();
loggedinorreturn();

$cats = genrelist();
$searchstr = unesc(((isset($_GET["search"]) && $_GET["search"] != "") ? $_GET["search"] : ""));
$cleansearchstr = searchfield($searchstr);
if(empty($cleansearchstr))
	unset($cleansearchstr);
$addparam = "";
$wherea = array("`activated`='yes'");
$wherecatina = array();
if(isset($_GET["incldead"]) && $_GET["incldead"] == 1){
    $addparam .= "incldead=1&amp;";
    if (!isset($CURUSER) || get_user_class() < UC_ADMINISTRATOR)
        $wherea[] = "banned != 'yes'";
}elseif(isset($_GET["incldead"]) && $_GET["incldead"] == 2) {
    $addparam .= "incldead=2&amp;";
    $wherea[] = "visible = 'no'";
}else
    $wherea[] = "visible = 'yes'";
$category = intval(((isset($_GET["cat"]) ? $_GET["cat"] : 0)));
$all = ((isset($_GET["all"])) ? $_GET["all"] : false);
if($all === false){
    if(!$_GET && $CURUSER["notifs"]){
        $all = true;
        foreach($cats as $cat){
            $all &= $cat["id"];
            if(strpos($CURUSER["notifs"], "[cat" . $cat["id"] . "]") !== false){
                $wherecatina[] = $cat["id"];
                $addparam .= "c" . $cat["id"] . "=1&amp;";
            } 
        } 
    }elseif($category > 0){
        if(!is_valid_id($category))
            stderr("Error", "Invalid category ID " . $category . ".");
        $wherecatina[] = $category;
        $addparam .= "cat=" . $category . "&amp;";
    }else{
        $all = true;
        foreach($cats as $cat){
			$_GET["c" . $cat["id"]] = ((isset($_GET["c" . $cat["id"]])) ? $_GET["c" . $cat["id"]] : 0);
            $all &= $_GET["c" . $cat["id"]];
            if($_GET["c" . $cat["id"]] > 0){
                $wherecatina[] = $cat["id"];
                $addparam .= "c" . $cat["id"] . "=1&amp;";
            } 
        } 
    } 
} 
$orderby = "ORDER BY ";
if(isset($_GET["orderby"]) && $_GET["orderby"] != ""){
	$addparam .= "orderby=" . urlencode($_GET["orderby"]) . "&amp;";
	switch($_GET["orderby"]){
		case "name":
			$orderby .= "torrents.name";
			break;
		case "type":
			$orderby .= "categories.name";
			break;
		case "files":
			$orderby .= "torrents.numfiles";
			break;
		case "comments":
			$orderby .= "torrents.comments";
			break;
		case "added":
			$orderby .= "torrents.added";
			break;
		case "size":
			$orderby .= "torrents.size";
			break;
		case "snatched":
			$orderby .= "torrents.times_completed";
			break;
		case "seeds":
			$orderby .= "torrents.seeders";
			break;
		case "leeches":
			$orderby .= "torrents.leechers";
			break;
		case "uppedby":
			$orderby .= "users.username";
			break;
	}
}else{
	$_GET["orderby"] = "added";
	$orderby .= "torrents.added";
}
$orderby_sel = array("name" => "Torrent-Name",
    "type" => "Kategorie",
    "files" => "Anzahl Dateien",
    "comments" => "Anzahl Kommentare",
    "added" => "Upload-Datum",
    "size" => "Gesamtgröße",
    "snatched" => "Anzahl heruntergeladen",
    "seeds" => "Anzahl Seeder",
    "leeches" => "Anzahl Leecher",
    "uppedby" => "Uploader"
);

if(isset($_GET["sort"]) && $_GET["sort"] == "asc"){
	$orderby .= " ASC";
	$addparam .= "sort=asc&amp;";
}else{
	$addparam .= "sort=desc&amp;";
	$orderby .= " DESC";
}

if($all != false){
	$wherecatina = array();
	$addparam = "";
}

if(isset($_GET["showsearch"]) && $_GET["showsearch"] == 1)
	user::showSearch($CURUSER["id"]);
elseif(isset($_GET["showsearch"]) && $_GET["showsearch"] == 0)
	user::hideSearch($CURUSER["id"]);

if(count($wherecatina) > 1){
	$wherecatin = implode(",", $wherecatina);
}elseif(count($wherecatina) == 1){
    $wherea[] = "category = " . $wherecatina[0];
	$wherecatin = "";
}else
	$wherecatin = "";
if($CURUSER["hideuseruploads"]=="yes")
    $wherea[] = "users.class >= ".UC_UPLOADER;
$wherebase = $wherea;
if(isset($cleansearchstr)){
	$wherea[] = "MATCH (search_text, ori_descr) AGAINST ('" . $searchstr . "')"; 
	$addparam .= "search=" . urlencode($searchstr) . "&amp;"; 
} 
$where = implode(" AND ", $wherea);
if($wherecatin != "")
	$where .= ($where ? " AND " : "") . "category IN(" . $wherecatin . ")";
if($where != "")
    $where = "WHERE " . $where;

$sql = "SELECT COUNT(*) FROM torrents LEFT JOIN users ON torrents.owner=users.id " . $where;
$qry = $GLOBALS["DB"]->prepare($sql);
$qry->execute();
$count = $qry->fetchColumn(0);
if(!$count && isset($cleansearchstr)) {
    $wherea = $wherebase;
    $orderby = "ORDER BY added DESC";
    $searcha = explode(" ", $cleansearchstr);
    $sc = 0;
    foreach ($searcha as $searchss) {
        if (strlen($searchss) <= 1)
            continue;
        $sc++;
        if ($sc > 5)
            break;
        $ssa = array();
        foreach (array("search_text", "ori_descr") as $sss)
        $ssa[] = $sss . " LIKE '%" . sqlwildcardesc($searchss) . "%'";
        $wherea[] = "(" . implode(" OR ", $ssa) . ")";
    } 
    if($sc){
        $where = implode(" AND ", $wherea);
        if ($where != "")
            $where = "WHERE " . $where;
		$sql = "SELECT COUNT(*) FROM torrents LEFT JOIN users ON torrents.owner=users.id " . $where;
		$qry = $GLOBALS["DB"]->prepare($sql);
		$qry->execute();
		$count = $qry->fetchColumn(0);
    } 
} 
$torrentsperpage = $CURUSER["torrentsperpage"];
if (!$torrentsperpage)
    $torrentsperpage = 15;
if($count){
    list($pagertop, $pagerbottom, $limit) = pager($torrentsperpage, $count, "browse.php?" . $addparam);
    $query = "SELECT torrents.*, IF(torrents.nfo <> '', 1, 0) as nfoav, categories.name AS cat_name, categories.image AS cat_pic, users.username, users.class AS uploaderclass 
		FROM torrents 
		LEFT JOIN categories ON category = categories.id 
		LEFT JOIN users ON torrents.owner = users.id $where $orderby $limit";
	$qry = $GLOBALS['DB']->prepare($query);
	$qry->execute();
	$res = $qry->FetchAll(PDO::FETCH_ASSOC);
}else
	unset($res);

if(isset($cleansearchstr))
	stdhead("Suchergebnisse für \"$searchstr\"");
else
	stdhead();

echo "<form method=\"get\" action=\"browse.php\">\n".
	"<input type=\"hidden\" name=\"showsearch\" value=\"1\">\n".
	"<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" style=\"width:100%\" class=\"tableinborder\">\n".
	"    <tr>\n".
	"        <td class=\"tabletitle\" style=\"width:100%;text-align:center;\">";

$page_str = ((isset($_GET["page"]) && $_GET["page"] > 0) ? "&amp;page=" . intval($_GET["page"]) : "");
if ($CURUSER["displaysearch"] == "yes"){
	echo "<a href=\"browse.php?" . $addparam . "showsearch=0" . $page_str . "\"><img src=\"" . $GLOBALS["PIC_BASE_URL"].$GLOBALS["ss_uri"] . "/minus.gif\" alt=\"+\" title=\"Zuklappen\" border=\"0\"> <b>Kategorien / Suchen</b></a></td>\n". 
		"    </tr>\n". 
		"    <tr>\n". 
		"        <td width=\"100%\" style=\"width:100%;\" class=\"tablea\">\n".
		"        <center>\n". 
		"            <table border=\"0\" cellspacing=\"1\" cellpadding=\"5\" class=\"tableinborder\">\n". 
		"                <tr>\n". 
		"                    <td colspan=\"" . $GLOBALS["BROWSE_CATS_PER_ROW"] . "\" class=\"tablecat\" align=\"center\"><b>Kategorien</b></td>\n". 
		"                </tr>\n". 
		"                <tr>\n";

    $i = 0;
	foreach($cats as $cat){
		echo (($i && $i % $GLOBALS["BROWSE_CATS_PER_ROW"] == 0) ? "                </tr>\n                <tr>\n" : "") . "                    <td class=\"tablea\" style=\"padding-bottom: 2px;padding-left: 7px;white-space: nowrap\"><input name=\"c" . $cat["id"] . "\" type=\"checkbox\" " . (in_array($cat["id"], $wherecatina) ? "checked=\"checked\" " : "") . "value=\"1\"><a class=\"catlink\" href=\"browse.php?cat=" . $cat["id"] . "&amp;showsearch=1\">" . htmlspecialchars($cat["name"]) . "</a></td>\n";
		$i++;
	}
	$alllink = "<a href=\"browse.php?all=1&amp;showsearch=1\"><b>Alle anzeigen</b></a>";
	$ncats = count($cats);
	$nrows = ceil($ncats / $GLOBALS["BROWSE_CATS_PER_ROW"]);
	$lastrowcols = $ncats % $GLOBALS["BROWSE_CATS_PER_ROW"];
	if($lastrowcols != 0){
		if($GLOBALS["BROWSE_CATS_PER_ROW"] - $lastrowcols != 1){
			echo "                    <td class=\"tablea\" rowspan=\"" . ($GLOBALS["BROWSE_CATS_PER_ROW"] - $lastrowcols - 1) . "\">&nbsp;</td>\n";
		}
		echo "                    <td class=\"tablea\" style=\"padding-left: 5px\">" . $alllink . "</td>\n";
	}
	if($ncats % $GLOBALS["BROWSE_CATS_PER_ROW"] == 0)
		echo "                <tr>\n                    <td class=\"tablea\" colspan=\"" . $GLOBALS["BROWSE_CATS_PER_ROW"] . "\" style=\"text-align:center;vertical-align:middle\">" . $alllink . "</td>\n";

	echo "                </tr>\n".
		"                <tr>\n".
		"                    <td colspan=\"" . $GLOBALS["BROWSE_CATS_PER_ROW"] . "\" class=\"tablea\" style\"white-space: nowrap\">Suchen:&nbsp;<input type=\"text\" name=\"search\" size=\"30\" value=\"" . htmlspecialchars($searchstr) . "\" /> in ".
		"<select name=\"incldead\">".
		"<option value=\"0\">aktiven</option>".
		"<option value=\"1\"" . ((isset($_GET["incldead"]) && $_GET["incldead"] == 1) ? " selected=\"selected\"" : "") . ">allen</option>".
		"<option value=\"2\"" . ((isset($_GET["incldead"]) && $_GET["incldead"] == 2) ? " selected=\"selected\"" : "") . ">toten</option>".
		"</select> Torrents</td>\n".
		"                </tr>\n".
		"                <tr>\n".
		"                    <td colspan=\"" . $GLOBALS["BROWSE_CATS_PER_ROW"] . "\" class=\"tablea\" style=\"white-space: nowrap\">Sortieren nach <select name=\"orderby\" size=\"1\">";

	foreach($orderby_sel as $orderparam => $description){
		echo "<option value=\"" . $orderparam . "\"";
		if($orderparam == $_GET["orderby"])
			echo " selected=\"selected\"";
		echo ">" . $description . "</option>";
	}
	echo "</select>".
		" in <select name=\"sort\" size=\"1\">";
	if(isset($_GET["sort"]) && $_GET["sort"] == "asc"){
		$order = "asc";
	}else{
		$order = "desc";
	}

	echo "<option value=\"desc\"" . (($order == "asc") ? "" : " selected=\"selected\"") . ">absteigender</option>".
		"<option value=\"asc\"" . (($order == "desc") ? "" : " selected=\"selected\"") . ">aufsteigender</option>".
		"</select> Reihenfolge</td>\n".
		"                </tr>\n".
		"                <tr>\n".
		"                    <td colspan=\"" . $GLOBALS["BROWSE_CATS_PER_ROW"] . "\" class=\"tablea\" style=\"text-align:center;white-space: nowrap\"><input type=\"submit\" value=\"Suchen / Aktualisieren\"/></td>\n".
		"                </tr>\n".
		"            </table>\n".
		"            </center>\n";
}else{
	echo "<a href=\"browse.php?" . $addparam . "showsearch=1" . $page_str . "\"><img src=\"" . $GLOBALS["PIC_BASE_URL"].$GLOBALS["ss_uri"] . "/plus.gif\" alt=\"+\" title=\"Aufklappen\" border=\"0\"> <b>Kategorien / Suchen</b></a>";
} 
echo "        </td>\n".
	"    </tr>\n".
	"</table>\n".
	"</form>\n";

if(isset($cleansearchstr)){
	echo "<br>\n".
		"<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" style=\"width:100%\" class=\"tableinborder\">\n".
		"    <tr class=\"tabletitle\" width=\"100%\">\n".
		"        <td width=\"100%\" style=\"text-align:center;\"><b>Suchergebnisse zu \"" . htmlspecialchars($searchstr) . "\" </b></td>\n".
		"    </tr>\n".
		"</table>\n";
}

if($count){
	echo $pagertop;
	torrenttable($res, "index", $addparam);
	echo $pagerbottom;
}else{
	if(isset($cleansearchstr)){
		echo "<br>\n".
			"<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" style=\"width:100%\" class=\"tableinborder\">\n".
			"    <tr class=\"tabletitle\" width=\"100%\">\n".
			"        <td width=\"100%\" style=\"text-align:center;\"><b>Nichts gefunden!</b></td>\n".
			"    </tr>\n".
			"    <tr>\n".
			"        <td width=\"100%\" class=\"tablea\">Es existieren keine Torrents, die Deinen Suchkriterien entsprechen! Bitte versuche es noch einmal mit einem anderen Suchbegriff.</td>\n".
			"    </tr>\n".
			"</table>\n";
	}else{
		echo "<br>\n".
			"<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" style=\"width:100%\" class=\"tableinborder\">\n".
			"    <tr class=\"tabletitle\" width=\"100%\">\n".
			"        <td width=\"100%\" style=\"text-align:center;\"><b>Keine Torrents vorhanden</b></td>\n".
			"    </tr>\n".
			"    <tr>\n".
			"        <td width=\"100%\" class=\"tablea\">Es existieren keine Torrents, die Deinen Kriterien entsprechen!</td>\n".
			"    </tr>\n".
			"</table>\n";
	}
}
stdfoot();
?>