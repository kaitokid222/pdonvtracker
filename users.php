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

$search = ((isset($_GET['search'])) ? trim($_GET['search']) : "");
$class = ((isset($_GET['class'])) ? $_GET['class'] : "");
$letter = ((isset($_GET["letter"])) ? trim($_GET["letter"]) : "");
$page = ((isset($_GET['page'])) ? $_GET['page'] : 0);
$class_options = "";
$browsemenu = "";
$pagemenu = "";
$q = "";
$perpage = 100;

if($class == '-' || !is_valid_id($class))
	$class = '';

if($search != '' || $class){
	$query = "username LIKE '%" . $search . "%' AND status='confirmed'";
	if($search)
		$q = "search=" . htmlspecialchars($search);
}else{
	if(strlen($letter) > 1)
		die;
	if($letter == "" || strpos("abcdefghijklmnopqrstuvwxyz", $letter) === false)
		$letter = "a";
	$query = "username LIKE '" . $letter . "%' AND status='confirmed'";
	$q = "letter=" . $letter;
}

if($class != ""){
	$query .= " AND class=" . $class;
	$q .= ($q ? "&amp;" : "") . "class=" . $class;
}

for($i = 0;$i<=UC_SYSOP;++$i){
	if($c = get_user_class_name($i))
		$class_options .= "<option value=\"" . $i . ($class && $class == $i ? " selected" : "") . "\">" . $c . "</option>";
}

$c = $database->row_count("users", $query);
$pages = floor($c / $perpage);

if($pages * $perpage < $c)
	++$pages;

if($page < 1)
	$page = 1;
elseif($page > $pages)
	$page = $pages;

for($i = 1; $i <= $pages; ++$i){
	if($i == $page)
		$pagemenu .= "<b>" . $i . "</b>\n";
	else
		$pagemenu .= "<a href=" . $_SERVER["PHP_SELF"] . "?" . $q . "&page=" . $i . "><b>" . $i . "</b></a>\n";
}

if($page == 1)
	$browsemenu .= "<b>&lt;&lt; Zurück</b>";
else
	$browsemenu .= "<a href=" . $_SERVER["PHP_SELF"] . "?" . $q . "&page=" . ($page - 1) . "><b>&lt;&lt; Zurück</b></a>";
$browsemenu .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

if($page == $pages)
	$browsemenu .= "<b>Weiter &gt;&gt;</b>";
else
	$browsemenu .= "<a href=" . $_SERVER["PHP_SELF"] . "?" . $q . "&page=" . ($page + 1) . "><b>Weiter &gt;&gt;</b></a>";

$offset = ($page * $perpage) - $perpage;

$sql = "SELECT u.*, c.name as cname, c.flagpic as cflagpic FROM users as u LEFT JOIN countries as c ON u.country = c.id WHERE " . $query . " ORDER BY username LIMIT " . $offset . "," . $perpage;
$qry = $GLOBALS['DB']->prepare($sql);
$qry->execute();
$user_num = $qry->rowCount();
$user_data = $qry->FetchAll();

stdhead("Benutzer");
begin_frame("<img src=\"".$GLOBALS["PIC_BASE_URL"]."user.png\" width=\"22\" height=\"22\" alt=\"\" style=\"vertical-align: middle;\"> Mitglieder", FALSE, "650px");
begin_table(TRUE);
echo "<tr>\n".
	"    <td class=\"tableb\" align=\"left\"><form method=\"get\" action=\"?\">Suchen: <input type=\"text\" size=\"30\" name=\"search\"> ".
	"<select name=\"class\">".
	"<option value=\"-\">(beliebige Klasse)</option>".
	$class_options .
	"</select> ".
	"<input type=\"submit\" value=\"Okay\"></form></td>\n".
	"</tr>\n".
	"<tr>\n".
	"    <td class=\"tablea\" align=\"center\">";
for($i = 97; $i < 123; ++$i){
	$l = chr($i);
	$L = chr($i - 32);
	if($l == $letter)
		echo "<b>" . $L . "</b> ";
	else
		echo "<a href=" . $_SERVER["PHP_SELF"] . "?letter=" . $l . "><b>" . $L . "</b></a> ";
}
echo "    </td>\n".
	"</tr>\n".
	"<tr>\n".
	"    <td class=\"tablea\" align=\"center\">" . $browsemenu . "<br>" . $pagemenu . "</td>\n".
	"</tr>\n";
end_table();
begin_table(TRUE);
echo "<tr>\n".
	"    <td class=\"tablecat\" align=\"left\">Benutzername</td>\n".
	"    <td class=\"tablecat\">Registriert</td>\n".
	"    <td class=\"tablecat\">Letzter Zugriff</td>\n".
	"    <td class=\"tablecat\" align=\"left\">Klasse</td>\n".
	"    <td class=\"tablecat\">Land</td>\n".
	"</tr>\n";
for($i = 0; $i < $user_num; ++$i){
	$arr = $user_data[$i];
	if($arr['country'] > 0)
		$country = "    <td class=\"tablea\" style=\"padding: 0px\" align=\"center\"><img src=\"" . $GLOBALS["PIC_BASE_URL"] . "flag/" . $arr["cflagpic"] . "\" alt=\"" . $arr["cname"] . "\"></td>\n";
	else
		$country = "    <td class=\"tablea\" align=\"center\">&nbsp;</td>\n";
	if($arr['added'] == '0000-00-00 00:00:00')
		$arr['added'] = '-';
	if($arr['last_access'] == '0000-00-00 00:00:00')
		$arr['last_access'] = '-';
	echo "<tr>\n".
		"    <td class=\"tablea\" align=\"left\"><a href=\"userdetails.php?id=" . $arr["id"] . "\"><font class=\"".get_class_color($arr["class"])."\"><b>" . $arr["username"] . "</b></font></a>" .get_user_icons($arr)."</td>\n".
		"    <td class=\"tableb\">" . $arr["added"] . "</td>\n".
		"    <td class=\"tablea\">" . $arr["last_access"] . "</td>\n".
		"    <td class=\"tableb\" align=\"left\">" . (($arr["title"] != "") ? htmlspecialchars($arr["title"]) : get_user_class_name($arr["class"])) . "</td>\n" . $country .
		"</tr>\n";
}
end_table();
begin_table(TRUE);
echo "<tr>\n".
	"    <td class=\"tablea\" align=\"center\">" . $browsemenu . "<br>" . $pagemenu . "</td>\n".
	"</tr>";
end_table();
stdfoot();
?>