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

dbconn();

loggedinorreturn();

$search = trim($_GET['search']);
$class = $_GET['class'];
if ($class == '-' || !is_valid_id($class))
  $class = '';

if ($search != '' || $class)
{
  $query = "username LIKE " . sqlesc("%$search%") . " AND status='confirmed'";
	if ($search)
		  $q = "search=" . htmlspecialchars($search);
}
else
{
	$letter = trim($_GET["letter"]);
  if (strlen($letter) > 1)
    die;

  if ($letter == "" || strpos("abcdefghijklmnopqrstuvwxyz", $letter) === false)
    $letter = "a";
  $query = "username LIKE '$letter%' AND status='confirmed'";
  $q = "letter=$letter";
}

if ($class)
{
  $query .= " AND class=$class";
  $q .= ($q ? "&amp;" : "") . "class=$class";
}

stdhead("Benutzer");

begin_frame("<img src=\"".$GLOBALS["PIC_BASE_URL"]."user.png\" width=\"22\" height=\"22\" alt=\"\" style=\"vertical-align: middle;\"> Mitglieder", FALSE, "650px");

begin_table(TRUE);
print("<tr><td class=tableb align=left><form method=get action=?>Suchen: <input type=text size=30 name=search>\n");
print("<select name=class>\n");
print("<option value='-'>(beliebige Klasse)</option>\n");
for ($i = 0;$i<=UC_SYSOP;++$i)
{
	if ($c = get_user_class_name($i))
	  print("<option value=$i" . ($class && $class == $i ? " selected" : "") . ">$c</option>\n");
}
print("</select>\n");
print("<input type=submit value='Okay'></form></td></tr>\n");
print("<tr><td class=tablea align=center>\n");

for ($i = 97; $i < 123; ++$i)
{
	$l = chr($i);
	$L = chr($i - 32);
	if ($l == $letter)
    print("<b>$L</b>\n");
	else
    print("<a href=?letter=$l><b>$L</b></a>\n");
}

print("</td></tr>\n");

$page = $_GET['page'];
$perpage = 100;

$res = mysql_query("SELECT COUNT(*) FROM users WHERE $query") or sqlerr();
$arr = mysql_fetch_row($res);
$pages = floor($arr[0] / $perpage);
if ($pages * $perpage < $arr[0])
  ++$pages;

if ($page < 1)
  $page = 1;
else
  if ($page > $pages)
    $page = $pages;

for ($i = 1; $i <= $pages; ++$i)
  if ($i == $page)
    $pagemenu .= "<b>$i</b>\n";
  else
    $pagemenu .= "<a href=?$q&page=$i><b>$i</b></a>\n";

if ($page == 1)
  $browsemenu .= "<b>&lt;&lt; Zurück</b>";
else
  $browsemenu .= "<a href=?$q&page=" . ($page - 1) . "><b>&lt;&lt; Zurück</b></a>";

$browsemenu .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

if ($page == $pages)
  $browsemenu .= "<b>Weiter &gt;&gt;</b>";
else
  $browsemenu .= "<a href=?$q&page=" . ($page + 1) . "><b>Weiter &gt;&gt;</b></a>";

print("<tr><td class=tablea align=center>$browsemenu<br>$pagemenu</td></tr>");
end_table();

$offset = ($page * $perpage) - $perpage;

$res = mysql_query("SELECT * FROM users WHERE $query ORDER BY username LIMIT $offset,$perpage") or sqlerr();
$num = mysql_num_rows($res);

begin_table(TRUE);
print("<tr><td class=tablecat align=left>Benutzername</td><td class=tablecat>Registriert</td><td class=tablecat>Letzter Zugriff</td><td class=tablecat align=left>Klasse</td><td class=tablecat>Land</td></tr>\n");
for ($i = 0; $i < $num; ++$i)
{
  $arr = mysql_fetch_assoc($res);
  if ($arr['country'] > 0)
  {
    $cres = mysql_query("SELECT name,flagpic FROM countries WHERE id=$arr[country]");
    if (mysql_num_rows($cres) == 1)
    {
      $carr = mysql_fetch_assoc($cres);
      $country = "<td class=tablea style='padding: 0px' align=center><img src=\"".$GLOBALS["PIC_BASE_URL"]."flag/$carr[flagpic]\" alt=\"$carr[name]\"></td>";
    }
  }
  else
    $country = "<td class=tablea align=center>&nbsp;</td>";
  if ($arr['added'] == '0000-00-00 00:00:00')
    $arr['added'] = '-';
  if ($arr['last_access'] == '0000-00-00 00:00:00')
    $arr['last_access'] = '-';
  print("<tr><td class=tablea align=left><a href=userdetails.php?id=$arr[id]><font class=\"".get_class_color($arr["class"])."\"><b>$arr[username]</b></font></a>" .get_user_icons($arr)."</td>" .
  "<td class=tableb>$arr[added]</td><td class=tablea>$arr[last_access]</td>".
    "<td class=tableb align=left>" . ($arr["title"]!="" ? htmlspecialchars($arr["title"]) : get_user_class_name($arr["class"])) . "</td>$country</tr>\n");
}
end_table();

begin_table(TRUE);
print("<tr><td class=tablea align=center>$browsemenu<br>$pagemenu</td></tr>");
end_table();

stdfoot();
die;

?>