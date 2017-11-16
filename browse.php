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

//ob_start("ob_gzhandler");

require_once("include/bittorrent.php");

//hit_start();

dbconn(false);
//userlogin();
loggedinorreturn();
//hit_count();
//stdhead();
$cats = genrelist();

if(isset($_GET['search']))
	$searchstr = intval($_GET['search']);
else
	$searchstr = "";
$cleansearchstr = searchfield($searchstr);
if (empty($cleansearchstr))
    unset($cleansearchstr);

$addparam = "";
$wherea = array("activated = 'yes'");
$wherecatina = array();

if(isset($_GET["incldead"])){
	if ($_GET["incldead"] == 1) {
		$addparam .= "incldead=1&amp;";
		if (!isset($CURUSER) || get_user_class() < UC_ADMINISTRATOR)
			$wherea[] = "banned != 'yes'";
	} elseif ($_GET["incldead"] == 2) {
		$addparam .= "incldead=2&amp;";
		$wherea[] = "visible = 'no'";
	} else
		$wherea[] = "visible = 'yes'";
}
if(isset($_GET['cat']))
	$category = intval($_GET['cat']);
else
	$category = 0;
	
if(isset($_GET['all']))
	$all = intval($_GET['all']);
else
	$all = 0;

if (!$all) {
    if (!$_GET && $CURUSER["notifs"]) {
        $all = true;
        foreach ($cats as $cat) {
            $all &= $cat['id'];
            if (strpos($CURUSER["notifs"], "[cat" . $cat['id'] . "]") !== false) {
                $wherecatina[] = $cat['id'];
                $addparam .= "c" . $cat['id'] . "&amp;";
            } 
        } 
    } elseif ($category) {
        if (!is_valid_id($category))
            stderr("Error", "Invalid category ID " . $category . ".");
        $wherecatina[] = $category;
        $addparam .= "cat=" . $category . "&amp;";
    } else {
        $all = true;
        foreach ($cats as $cat) {
			if(isset($_GET["c" . $cat['id']])){
				$all &= $_GET["c" . $cat['id']];
				if ($_GET["c" . $cat['id']]) {
					$wherecatina[] = $cat['id'];
					$addparam .= "c" . $cat['id'] . "=1&amp;";
				}
			}
        } 
    } 
} 

$orderby = "ORDER BY ";

if(isset($_GET["orderby"])){
	if ($_GET["orderby"] != "")
		$addparam .= "orderby=" . urlencode($_GET["orderby"]) . "&amp;";

	switch ($_GET["orderby"]) {
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
		default:
			$_GET["orderby"] = "added";
			$orderby .= "torrents.added";
			break;
	} 
}else
	$_GET["orderby"] = "added";

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

if(!isset($_GET["sort"])){
	$_GET["sort"] = "desc";
	switch ($_GET["sort"]) {
		case "asc":
			$orderby .= " ASC";
			$addparam .= "sort=asc&amp;";
			break;

		default:
		case "desc":
			$addparam .= "sort=desc&amp;";
			$orderby .= " DESC";
			break;
	}
}

if ($all) {
    $wherecatina = array();
    $addparam = "";
} 

if(!isset($_GET["showsearch"])){
	$_GET["showsearch"] = 1;
	if ($_GET["showsearch"] == 1) {
		$CURUSER["displaysearch"] = "yes";
		//mysql_query("UPDATE users SET displaysearch='yes' WHERE id=".$CURUSER["id"]);
		$_SESSION["userdata"]["displaysearch"] = "yes";
	} elseif (isset($_GET["showsearch"]) && $_GET["showsearch"] == 0) {
		$CURUSER["displaysearch"] = "no";
		//mysql_query("UPDATE users SET displaysearch='no' WHERE id=".$CURUSER["id"]);
		$_SESSION["userdata"]["displaysearch"] = "no";
	}
}

if (count($wherecatina) > 1)
    $wherecatin = implode(",", $wherecatina);
elseif (count($wherecatina) == 1)
    $wherea[] = "category = $wherecatina[0]";

if ($CURUSER["hideuseruploads"]=="yes")
    $wherea[] = "users.class >= ".UC_UPLOADER;

$wherebase = $wherea;

if (isset($cleansearchstr)) {
    $wherea[] = "MATCH (search_text, ori_descr) AGAINST (" . sqlesc($searchstr) . ")"; 
    // $wherea[] = "0";
    $addparam .= "search=" . urlencode($searchstr) . "&amp;"; 
    // $orderby = "";
} 

$where = implode(" AND ", $wherea);
if (isset($wherecatin))
    $where .= ($where ? " AND " : "") . "category IN(" . $wherecatin . ")";

//WHERE is in pdo_row_count();
if ($where != ""){
	$owhere = $where;
    $where = 'WHERE ' . $where;
}
//echo '<br><br>Condition: ' . $where;
//$res = pdo_row_count('torrents',$owhere);
$res = mysql_query("SELECT COUNT(*) FROM torrents LEFT JOIN users ON torrents.owner=users.id $where") or die(mysql_error());
$row = mysql_fetch_array($res);
//$count = $res;
$count = $row[0];

if (!$count && isset($cleansearchstr)) {
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
        $ssa[] = "$sss LIKE '%" . sqlwildcardesc($searchss) . "%'";
        $wherea[] = "(" . implode(" OR ", $ssa) . ")";
    } 
    if ($sc) {
        $where = implode(" AND ", $wherea);
        if ($where != "")
            $where = "WHERE $where";
        $res = mysql_query("SELECT COUNT(*) FROM torrents LEFT JOIN users ON torrents.owner=users.id $where");
        $row = mysql_fetch_array($res);
        $count = $row[0];
    } 
} 

$torrentsperpage = $CURUSER["torrentsperpage"];
if (!$torrentsperpage)
    $torrentsperpage = 15;

if ($count) {
    list($pagertop, $pagerbottom, $limit) = pager($torrentsperpage, $count, "browse.php?" . $addparam);
    $query = "
	SELECT torrents.id, torrents.category, torrents.leechers, 
	torrents.seeders, torrents.name, torrents.times_completed, 
	torrents.size, torrents.added, torrents.type, torrents.last_action, torrents.visible,
	torrents.comments,torrents.numfiles,torrents.filename,torrents.owner,
	IF(torrents.nfo <> '', 1, 0) as nfoav," . 
    //"IF(torrents.numratings < ".$GLOBALS["MINVOTES"].", NULL, ROUND(torrents.ratingsum / torrents.numratings, 1)) AS rating, categories.name AS cat_name, categories.image AS cat_pic, users.username FROM torrents LEFT JOIN categories ON category = categories.id LEFT JOIN users ON torrents.owner = users.id $where $orderby $limit";
    "categories.name AS cat_name, categories.image AS cat_pic, 
	users.username, users.class AS uploaderclass FROM torrents 
	LEFT JOIN categories ON category = categories.id LEFT JOIN users 
	ON torrents.owner = users.id $where $orderby $limit";
	echo '<br><br>QRY: ' . $query .' <br>';
   // $res = mysql_query($query) or die(mysql_error());
    $res = mysql_query($query);
} else
    unset($res);
if (isset($cleansearchstr))
    stdhead("Suchergebnisse für \"$searchstr\"");
else
   stdhead();

?>
<form method="get" action="browse.php">
<input type="hidden" name="showsearch" value="1">
<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
 <tr>
  <td class="tabletitle" colspan="10" style="width:100%;text-align:center;"> 
<?php if ($CURUSER["displaysearch"] == "yes") {

    ?>
<a href="browse.php?<?=$addparam?>showsearch=0<?php if ($_GET["page"] > 0) echo "&amp;page=" . intval($_GET["page"]);

    ?>"><img src="<?=$GLOBALS["PIC_BASE_URL"].$GLOBALS["ss_uri"]?>/minus.gif" alt="+" title="Zuklappen" border="0"> <b>Kategorien / Suchen</b></a></td> 
 </tr><tr><td width="100%" style="width:100%;" class="tablea"><center>
 <table border="0" cellspacing="1" cellpadding="5" class="tableinborder">
  <tr>
    <td colspan="<?=$GLOBALS["BROWSE_CATS_PER_ROW"]?>" class="tablecat" align="center"><b>Kategorien</b></td>
  </tr>
  <tr>
<?php
    $i = 0;
    foreach ($cats as $cat) {
        print(($i && $i % $GLOBALS["BROWSE_CATS_PER_ROW"] == 0) ? "</tr><tr>" : "");
        print("<td class=\"tablea\" style=\"padding-bottom: 2px;padding-left: 7px\" nowrap><input name=\"c$cat[id]\" type=\"checkbox\" " . (in_array($cat['id'], $wherecatina) ? "checked " : "") . "value=\"1\"><a class=\"catlink\" href=\"browse.php?cat=$cat[id]&amp;showsearch=1\">" . htmlspecialchars($cat['name']) . "</a></td>\n");
        $i++;
    } 

    $alllink = "<a href=\"browse.php?all=1&amp;showsearch=1\"><b>Alle anzeigen</b></a>";

    $ncats = count($cats);
    $nrows = ceil($ncats / $GLOBALS["BROWSE_CATS_PER_ROW"]);
    $lastrowcols = $ncats % $GLOBALS["BROWSE_CATS_PER_ROW"];

    if ($lastrowcols != 0) {
        if ($GLOBALS["BROWSE_CATS_PER_ROW"] - $lastrowcols != 1) {
            print("<td class=\"tablea\" rowspan=\"" . ($GLOBALS["BROWSE_CATS_PER_ROW"] - $lastrowcols - 1) . "\">&nbsp;</td>");
        }
        print("<td class=\"tablea\" style=\"padding-left: 5px\">$alllink</td>\n");
    } 

    if ($ncats % $GLOBALS["BROWSE_CATS_PER_ROW"] == 0)
        print("<tr><td class=\"tablea\" colspan=\"" . $GLOBALS["BROWSE_CATS_PER_ROW"] . "\" style=\"text-align:center;vertical-align:middle\">$alllink</td>\n");

    ?>
  </tr>
  <tr>
    <td colspan="<?=$GLOBALS["BROWSE_CATS_PER_ROW"]?>" class="tablea" nowrap="nowrap">
	<?php
	if($searchstr == 0)
		$searchstr == "";
	?>
      Suchen:&nbsp;<input type="text" name="search" size="30" value="<?= htmlspecialchars($searchstr) ?>" />
       in <select name="incldead">
<option value="0">aktiven</option>
<?php
if(isset($_GET["incldead"]) && $_GET["incldead"] == 1)
	$inkdeadall == " selected";
?>
<option value="1"<?php !isset($inkdeadall) ?: $inkdeadall = ""; ?>>allen</option>
<?php
if(isset($_GET["incldead"]) && $_GET["incldead"] == 2)
	$inkdead == " selected";
?>
<option value="2"<?php !isset($inkdead) ?: $inkdead = ""; ?>>toten</option>
		</select> Torrents
     </td>
  </tr>
  <tr>
    <td colspan="<?=$GLOBALS["BROWSE_CATS_PER_ROW"]?>" class="tablea" nowrap="nowrap">Sortieren nach <select name="orderby" size="1">
    <?php
    foreach ($orderby_sel as $orderparam => $description) {
        echo "<option value=\"$orderparam\"";
        if ($orderparam == $_GET["orderby"])
            echo " selected=\"selected\"";
        echo ">$description</option>\n";
    } 

    ?>
    </select> in <select name="sort" size="1">
      <option value="desc"<?php if ($_GET["sort"] == "desc") echo " selected=\"selected\"";

    ?>>absteigender</option>
      <option value="asc"<?php if ($_GET["sort"] == "asc") echo " selected=\"selected\"";

    ?>>aufsteigender</option>
      </select> Reihenfolge</td>
  </tr>
  <tr>
     <td colspan="<?=$GLOBALS["BROWSE_CATS_PER_ROW"]?>" class="tablea" nowrap="nowrap" style="text-align:center;"><input type="submit" value="Suchen / Aktualisieren"/></td>  	
  </tr>
  </table></center>
<?php } else {

    ?>
<a href="browse.php?<?=$addparam?>showsearch=1<?php if ($_GET["page"] > 0) echo "&amp;page=" . intval($_GET["page"]);

    ?>"><img src="<?=$GLOBALS["PIC_BASE_URL"].$GLOBALS["ss_uri"]?>/plus.gif" alt="+" title="Aufklappen" border="0"> <b>Kategorien / Suchen</b></a>
<?php } 

?>
</td>
</tr>
</table>
</form>

<?php

if (isset($cleansearchstr)) {

    ?>
<br>
<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
 <tr class="tabletitle" width="100%">
  <td colspan="10" width="100%" style="text-align:center;"><b>
  Suchergebnisse zu "<?=htmlspecialchars($searchstr)?>" </b></td></tr></table>
<?php
} 

if ($count) {
    print($pagertop);

    torrenttable($res, "index", $addparam);

    print($pagerbottom);
} else {
    if (isset($cleansearchstr)) {

        ?>
<br>
<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
 <tr class="tabletitle" width="100%">
  <td colspan="10" width="100%" style="text-align:center;"><b>Nichts gefunden!</b></td> 
 </tr><tr><td width="100%" class="tablea">Es existieren keine Torrents, die Deinen Suchkriterien entsprechen! Bitte versuche es noch einmal mit einem anderen Suchbegriff.</td></tr></table>
<?php
    } else {

        ?>
<br>
<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
 <tr class="tabletitle" width="100%">
  <td colspan="10" width="100%" style="text-align:center;"><b>Keine Torrents vorhanden</b></td> 
 </tr><tr><td width="100%" class="tablea">Es existieren keine Torrents, die Deinen Kriterien entsprechen!</td></tr></table>
     <?php
    } 
} 

stdfoot();

hit_end();

?>