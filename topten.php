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

ob_start("ob_gzhandler");

  require "include/bittorrent.php";
  dbconn(false);
  loggedinorreturn();

/*
  function donortable($res, $frame_caption)
  {
    begin_frame($frame_caption, true);
    begin_table();
?>
<tr>
<td class=colhead style="text-align:right">Rank</td>
<td class=colhead>User</td>
<td class=colhead style="text-align:right">Donated</td>
</tr>
<?
    $num = 0;
    while ($a = mysql_fetch_assoc($res))
    {
        ++$num;
		$this = $a["donated"];
		if ($this == $last)
			$rank = "";
		else
		{
		  $rank = $num;
		}
	if ($rank && $num > 10)
    	break;
      print("<tr><td>$rank</td><td align=left><a href=userdetails.php?id=$a[id]><b>$a[username]" .
         "</b></a></td><td style=\"text-align:right\">$$this</td></tr>");
		$last = $this;
    }
    end_table();
    end_frame();
  }
*/

  function usertable($res, $frame_caption)
  {
  	global $CURUSER;
    begin_frame($frame_caption, true);
    begin_table(TRUE);
?>
<tr>
<td class=tablecat>Rang</td>
<td class=tablecat align=left>Benutzer</td>
<td class=tablecat>Uploaded</td>
<td class=tablecat align=left>Geschw.</td>
<td class=tablecat>Downloaded</td>
<td class=tablecat align=left>Geschw.</td>
<td class=tablecat align=right>Ratio</td>
<td class=tablecat align=left>Registriert</td>

</tr>
<?
    $num = 0;
    while ($a = mysql_fetch_assoc($res))
    {
      ++$num;
      $highlight = $CURUSER["id"] == $a["userid"] ? " bgcolor=#BBAF9B" : "";
      if ($a["downloaded"])
      {
        $ratio = $a["uploaded"] / $a["downloaded"];
        $color = get_ratio_color($ratio);
        $ratio = number_format($ratio, 2);
        if ($color)
          $ratio = "<font color=$color>$ratio</font>";
      }
      else
        $ratio = "Inf.";
      print("<tr$highlight><td class=tablea>$num</td><td class=tableb align=left$highlight><a href=userdetails.php?id=" .
      		$a["userid"] . "><b><font class=".get_class_color($a["class"]).">" . $a["username"] . "</font></b> " .
      		get_user_icons($a) .
      		"</td><td class=tablea style=\"text-align:right\"$highlight>" . mksize($a["uploaded"]) .
			"</td><td class=tableb style=\"text-align:right\" align=right$highlight>" . mksize($a["upspeed"]) . "/s" .
         	"</td><td class=tablea style=\"text-align:right\" align=right$highlight>" . mksize($a["downloaded"]) .
      		"</td><td class=tableb style=\"text-align:right\" align=right$highlight>" . mksize($a["downspeed"]) . "/s" .
      		"</td><td class=tablea style=\"text-align:right\" align=right$highlight>" . $ratio .
      		"</td><td class=tableb>" . date("Y-m-d",strtotime($a["added"])) . " (Vor " .
      		get_elapsed_time(sql_timestamp_to_unix_timestamp($a["added"])) . ")</td></tr>");
    }
    end_table();
    end_frame();
  }

  function _torrenttable($res, $frame_caption)
  {
    begin_frame($frame_caption, true);
    begin_table();
?>
<tr>
<td class=tablecat style="text-align:right">Rank</td>
<td class=tablecat>Name</td>
<td class=tablecat style="text-align:right">Sna.</td>
<td class=tablecat style="text-align:right">Data</td>
<td class=tablecat style="text-align:right">Se.</td>
<td class=tablecat style="text-align:right">Le.</td>
<td class=tablecat style="text-align:right">To.</td>
<td class=tablecat style="text-align:right">Ratio</td>
</tr>
<?
    $num = 0;
    while ($a = mysql_fetch_assoc($res))
    {
      ++$num;
      if ($a["leechers"])
      {
        $r = $a["seeders"] / $a["leechers"];
        $ratio = "<font color=" . get_ratio_color($r) . ">" . number_format($r, 2) . "</font>";
      }
      else
        $ratio = "Inf.";
      print("<tr><td class=tablea style=\"text-align:right\">$num" .
        "</td><td class=tableb><a href=details.php?id=" . $a["id"] . "&hit=1><b>" . $a["name"] . "</b></a>" .
        "</td><td class=tablea style=\"text-align:right\">" . number_format($a["times_completed"]) .
		"</td><td class=tableb style=\"text-align:right\">" . mksize($a["data"]) .
        "</td><td class=tablea style=\"text-align:right\">" . number_format($a["seeders"]) .
        "</td><td class=tableb style=\"text-align:right\">" . number_format($a["leechers"]) .
        "</td><td class=tablea style=\"text-align:right\">" . ($a["leechers"] + $a["seeders"]) .
        "</td><td class=tableb style=\"text-align:right\">$ratio</td>\n");
    }
    end_table();
    end_frame();
  }

  function countriestable($res, $frame_caption, $what)
  {
    global $CURUSER;
    begin_frame($frame_caption, true);
    begin_table();
?>
<tr>
<td class=tablecat style="text-align:right">Rang</td>
<td class=tablecat>Land</td>
<td class=tablecat style="text-align:right"><?=$what?></td>
</tr>
<?
  	$num = 0;
		while ($a = mysql_fetch_assoc($res))
		{
	    ++$num;
	    if ($what == "Benutzer")
	      $value = number_format($a["num"]);
	    elseif ($what == "Hochgeladen")
	      $value = mksize($a["ul"]);
	    elseif ($what == "Durchschnitt")
	    	$value = mksize($a["ul_avg"]);
 	    elseif ($what == "Ratio")
 	    	$value = number_format($a["r"],2);
	    print(
          "<tr><td class=tablea style=\"text-align:right\">$num</td>".
          "<td class=tableb><table border=0 cellspacing=0 cellpadding=0><tr><td>".
	      "<img align=center src=\"".$GLOBALS["PIC_BASE_URL"]."flag/$a[flagpic]\"></td>".
          "<td style='padding-left: 5px'><b>$a[name]</b></td>".
	      "</tr></table></td><td class=tablea style=\"text-align:right\">$value</td></tr>\n");
	  }
    end_table();
    end_frame();
  }

    function peerstable($res, $frame_caption)
    {
        begin_frame($frame_caption, true);
        begin_table();

?>
<tr>
<td class=tablecat>Rang</td>
<td class=tablecat>Benutzername</td>
<td class=tablecat>Upload-Rate</td>
<td class=tablecat>Download-Rate</td>
</tr>
<?

        $n = 1;
        while ($arr = mysql_fetch_assoc($res))
        {
            $highlight = $CURUSER["id"] == $arr["userid"] ? " class=tablecat" : " class=tablea";
            print(
              "<tr><td$highlight style=\"text-align:right\">$n</td>".
              "<td$highlight><a href=userdetails.php?id=" . $arr["userid"] . "><b><font class=".get_class_color($arr["class"]).">" . $arr["username"] . "</font></b> ".get_user_icons($arr)."</td>".
              "<td$highlight style=\"text-align:right\">" . mksize($arr["uprate"]) . "/s</td>".
              "<td$highlight style=\"text-align:right\">" . mksize($arr["downrate"]) . "/s</td></tr>\n");
            ++$n;
        }

        end_table();
        end_frame();
    }

  stdhead("Top 10");
  begin_main_frame();
//  $r = mysql_query("SELECT * FROM users ORDER BY donated DESC, username LIMIT 100") or die;
//  donortable($r, "Top 10 Donors");
	$type = 0 + $_GET["type"];
	if (!in_array($type,array(1,2,3,4)))
		$type = 1;
	$limit = 0 + $_GET["lim"];
	$subtype = $_GET["subtype"];
?>
<table cellpadding="4" cellspacing="1" border="0" style="width:750px" class="tableinborder">
<tr class="tabletitle" width="100%">
<td colspan="10" width="100%"><span class="smallfont"><center>
<?
    print(($type == 1 && !$limit ? "<b>Benutzer</b>" : "<a href=topten.php?type=1>Benutzer</a>") .    " | " .
          ($type == 2 && !$limit ? "<b>Torrents</b>" : "<a href=topten.php?type=2>Torrents</a>") . " | " .
          ($type == 3 && !$limit ? "<b>Länder</b>" : "<a href=topten.php?type=3>Länder</a>") . " | " .
          ($type == 4 && !$limit ? "<b>Peers</b>" : "<a href=topten.php?type=4>Peers</a>"));
?>
 </center></span></td></tr></table><br>
<?
	$pu = get_user_class() >= UC_POWER_USER;

  if (!$pu)
  	$limit = 10;

  if ($type == 1)
  {
    $mainquery = "SELECT id as userid, username, class, added, uploaded, downloaded, uploaded / (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(added)) AS upspeed, downloaded / (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(added)) AS downspeed, donor, enabled, warned FROM users WHERE enabled = 'yes'";

  	if (!$limit || $limit > 250)
  		$limit = 10;

  	if ($limit == 10 || $subtype == "ul")
  	{
			$order = "uploaded DESC";
			$r = mysql_query($mainquery . $extrawhere . " ORDER BY $order " . " LIMIT $limit") or sqlerr();
	  	usertable($r, "Top $limit Uploader" . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=1&amp;lim=100&amp;subtype=ul>Top 100</a>] - [<a href=topten.php?type=1&amp;lim=250&amp;subtype=ul>Top 250</a>]</font>" : ""));
	  }

    if ($limit == 10 || $subtype == "dl")
  	{
			$order = "downloaded DESC";
		  $r = mysql_query($mainquery . $extrawhere . " ORDER BY $order " . " LIMIT $limit") or sqlerr();
		  usertable($r, "Top $limit Downloader" . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=1&amp;lim=100&amp;subtype=dl>Top 100</a>] - [<a href=topten.php?type=1&amp;lim=250&amp;subtype=dl>Top 250</a>]</font>" : ""));
	  }

    if ($limit == 10 || $subtype == "uls")
  	{
			$order = "upspeed DESC";
			$r = mysql_query($mainquery . $extrawhere . " ORDER BY $order " . " LIMIT $limit") or sqlerr();
	  	usertable($r, "Top $limit schnellste Uploader <font class=small>(Durchschnitt, inklusive Inaktivität)</font>" . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=1&amp;lim=100&amp;subtype=uls>Top 100</a>] - [<a href=topten.php?type=1&amp;lim=250&amp;subtype=uls>Top 250</a>]</font>" : ""));
	  }

    if ($limit == 10 || $subtype == "dls")
  	{
			$order = "downspeed DESC";
			$r = mysql_query($mainquery . $extrawhere . " ORDER BY $order " . " LIMIT $limit") or sqlerr();
	  	usertable($r, "Top $limit schnellste Downloader <font class=small>(Durchschnitt, inklusive Inaktivität)</font>" . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=1&amp;lim=100&amp;subtype=dls>Top 100</a>] - [<a href=topten.php?type=1&amp;lim=250&amp;subtype=dls>Top 250</a>]</font>" : ""));
	  }

    if ($limit == 10 || $subtype == "bsh")
  	{
			$order = "uploaded / downloaded DESC";
			$extrawhere = " AND downloaded > 1073741824";
	  	$r = mysql_query($mainquery . $extrawhere . " ORDER BY $order " . " LIMIT $limit") or sqlerr();
	  	usertable($r, "Top $limit Best Sharers <font class=small>(mit min. 1 GB Download)</font>" . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=1&amp;lim=100&amp;subtype=bsh>Top 100</a>] - [<a href=topten.php?type=1&amp;lim=250&amp;subtype=bsh>Top 250</a>]</font>" : ""));
		}

    if ($limit == 10 || $subtype == "wsh")
  	{
			$order = "uploaded / downloaded ASC, downloaded DESC";
  		$extrawhere = " AND downloaded > 1073741824";
	  	$r = mysql_query($mainquery . $extrawhere . " ORDER BY $order " . " LIMIT $limit") or sqlerr();
	  	 usertable($r, "Top $limit schlechteste Sharer <font class=small>(mit min. 1 GB Download)</font>" . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=1&amp;lim=100&amp;subtype=wsh>Top 100</a>] - [<a href=topten.php?type=1&amp;lim=250&amp;subtype=wsh>Top 250</a>]</font>" : ""));
	  }
  }

  elseif ($type == 2)
  {
   	if (!$limit || $limit > 50)
  		$limit = 10;

   	if ($limit == 10 || $subtype == "act")
  	{
		  $r = mysql_query("SELECT t.*, (t.size * t.times_completed + SUM(p.downloaded)) AS data FROM torrents AS t LEFT JOIN peers AS p ON t.id = p.torrent WHERE p.seeder = 'no' GROUP BY t.id ORDER BY seeders + leechers DESC, seeders DESC, added ASC LIMIT $limit") or sqlerr();
		  _torrenttable($r, "Top $limit aktivste Torrents" . ($limit == 10 && $pu ? " <font class=smallfont> - [<a href=topten.php?type=2&amp;lim=25&amp;subtype=act>Top 25</a>] - [<a href=topten.php?type=2&amp;lim=50&amp;subtype=act>Top 50</a>]</font>" : ""));
	  }

   	if ($limit == 10 || $subtype == "sna")
   	{
	  	$r = mysql_query("SELECT t.*, (t.size * t.times_completed + SUM(p.downloaded)) AS data FROM torrents AS t LEFT JOIN peers AS p ON t.id = p.torrent WHERE p.seeder = 'no' GROUP BY t.id ORDER BY times_completed DESC LIMIT $limit") or sqlerr();
		  _torrenttable($r, "Top $limit am häufigsten runtergeladene Torrents" . ($limit == 10 && $pu ? " <font class=smallfont> - [<a href=topten.php?type=2&amp;lim=25&amp;subtype=sna>Top 25</a>] - [<a href=topten.php?type=2&amp;lim=50&amp;subtype=sna>Top 50</a>]</font>" : ""));
	  }

   	if ($limit == 10 || $subtype == "mdt")
   	{
		  $r = mysql_query("SELECT t.*, (t.size * t.times_completed + SUM(p.downloaded)) AS data FROM torrents AS t LEFT JOIN peers AS p ON t.id = p.torrent WHERE p.seeder = 'no' AND leechers >= 5 AND times_completed > 0 GROUP BY t.id ORDER BY data DESC, added ASC LIMIT $limit") or sqlerr();
		  _torrenttable($r, "Top $limit Torrents mit größten Datentransfer" . ($limit == 10 && $pu ? " <font class=smallfont> - [<a href=topten.php?type=2&amp;lim=25&amp;subtype=mdt>Top 25</a>] - [<a href=topten.php?type=2&amp;lim=50&amp;subtype=mdt>Top 50</a>]</font>" : ""));
		}

   	if ($limit == 10 || $subtype == "bse")
   	{
		  $r = mysql_query("SELECT t.*, (t.size * t.times_completed + SUM(p.downloaded)) AS data FROM torrents AS t LEFT JOIN peers AS p ON t.id = p.torrent WHERE p.seeder = 'no' AND seeders >= 5 GROUP BY t.id ORDER BY seeders / leechers DESC, seeders DESC, added ASC LIMIT $limit") or sqlerr();
	  	_torrenttable($r, "Top $limit am besten geseedete Torrents <font class=smallfont>(mit mindestens 5 Seedern)</font>" . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=2&amp;lim=25&amp;subtype=bse>Top 25</a>] - [<a href=topten.php?type=2&amp;lim=50&amp;subtype=bse>Top 50</a>]</font>" : ""));
    }

   	if ($limit == 10 || $subtype == "wse")
   	{
		  $r = mysql_query("SELECT t.*, (t.size * t.times_completed + SUM(p.downloaded)) AS data FROM torrents AS t LEFT JOIN peers AS p ON t.id = p.torrent WHERE p.seeder = 'no' AND leechers >= 5 AND times_completed > 0 GROUP BY t.id ORDER BY seeders / leechers ASC, leechers DESC LIMIT $limit") or sqlerr();
		  _torrenttable($r, "Top $limit miserabel geseedete Torrents <font class=smallfont>(mit min. 5 Leechern, ausg. unfertige Torrents)</font>" . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=2&amp;lim=25&amp;subtype=wse>Top 25</a>] - [<a href=topten.php?type=2&amp;lim=50&amp;subtype=wse>Top 50</a>]</font>" : ""));
		}
  }
  elseif ($type == 3)
  {
  	if (!$limit || $limit > 25)
  		$limit = 10;

   	if ($limit == 10 || $subtype == "us")
   	{
		  $r = mysql_query("SELECT name, flagpic, COUNT(users.country) as num FROM countries JOIN users ON users.country = countries.id GROUP BY name ORDER BY num DESC LIMIT $limit") or sqlerr();
		  countriestable($r, "Top $limit Länder<font class=small> (Anzahl Benutzer)</font>" . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=3&amp;lim=25&amp;subtype=us>Top 25</a>]</font>" : ""),"Benutzer");
    }

   	if ($limit == 10 || $subtype == "ul")
   	{
	  	$r = mysql_query("SELECT c.name, c.flagpic, sum(u.uploaded) AS ul FROM users AS u JOIN countries AS c ON u.country = c.id WHERE u.enabled = 'yes' GROUP BY c.name ORDER BY ul DESC LIMIT $limit") or sqlerr();
		  countriestable($r, "Top $limit Länder<font class=small> (Gesamt hochgeladen)</font>" . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=3&amp;lim=25&amp;subtype=ul>Top 25</a>]</font>" : ""),"Hochgeladen");
    }

		if ($limit == 10 || $subtype == "avg")
		{
		  $r = mysql_query("SELECT c.name, c.flagpic, sum(u.uploaded)/count(u.id) AS ul_avg FROM users AS u JOIN countries AS c ON u.country = c.id WHERE u.enabled = 'yes' GROUP BY c.name HAVING sum(u.uploaded) > 107374182400 AND count(u.id) >= 10 ORDER BY ul_avg DESC LIMIT $limit") or sqlerr();
		  countriestable($r, "Top $limit Länder<font class=small> (Durchschn. Upload pro User, min. 100GB uploaded und 10 Benutzern)</font>" . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=3&amp;lim=25&amp;subtype=avg>Top 25</a>]</font>" : ""),"Durchschnitt");
    }

		if ($limit == 10 || $subtype == "r")
		{
		  $r = mysql_query("SELECT c.name, c.flagpic, sum(u.uploaded)/sum(u.downloaded) AS r FROM users AS u JOIN countries AS c ON u.country = c.id WHERE u.enabled = 'yes' GROUP BY c.name HAVING sum(u.uploaded) > 107374182400 AND sum(u.downloaded) > 107374182400 AND count(u.id) >= 10 ORDER BY r DESC LIMIT $limit") or sqlerr();
		  countriestable($r, "Top $limit Länder<font class=small> (Ratio, mit min. 100GB Upload, 100GB Download und 10 Benutzern)</font>" . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=3&amp;lim=25&amp;subtype=r>Top 25</a>]</font>" : ""),"Ratio");
	  }
  }
	elseif ($type == 4)
	{
//		print("<h1 align=center><font color=red>Under construction!</font></h1>\n");
  	if (!$limit || $limit > 250)
  		$limit = 10;

	    if ($limit == 10 || $subtype == "ul")
  		{
//				$r = mysql_query("SELECT users.id AS userid, peers.id AS peerid, username, peers.uploaded, peers.downloaded, peers.uploaded / (UNIX_TIMESTAMP(NOW()) - (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(last_action)) - UNIX_TIMESTAMP(started)) AS uprate, peers.downloaded / (UNIX_TIMESTAMP(NOW()) - (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(last_action)) - UNIX_TIMESTAMP(started)) AS downrate FROM peers JOIN users ON peers.userid = users.id ORDER BY uprate DESC LIMIT $limit") or sqlerr();
//				peerstable($r, "Top $limit Fastest Uploaders" . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=4&amp;lim=100&amp;subtype=ul>Top 100</a>] - [<a href=topten.php?type=4&amp;lim=250&amp;subtype=ul>Top 250</a>]</font>" : ""));

//				$r = mysql_query("SELECT users.id AS userid, peers.id AS peerid, username, peers.uploaded, peers.downloaded, (peers.uploaded - peers.uploadoffset) / (UNIX_TIMESTAMP(last_action) - UNIX_TIMESTAMP(started)) AS uprate, (peers.downloaded - peers.downloadoffset) / (UNIX_TIMESTAMP(last_action) - UNIX_TIMESTAMP(started)) AS downrate FROM peers JOIN users ON peers.userid = users.id ORDER BY uprate DESC LIMIT $limit") or sqlerr();
//				peerstable($r, "Top $limit Fastest Uploaders (timeout corrected)" . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=4&amp;lim=100&amp;subtype=ul>Top 100</a>] - [<a href=topten.php?type=4&amp;lim=250&amp;subtype=ul>Top 250</a>]</font>" : ""));

				$r = mysql_query( "SELECT users.donor AS donor, users.enabled AS enabled, users.warned AS warned, users.id AS userid, username, (peers.uploaded - peers.uploadoffset) / (UNIX_TIMESTAMP(last_action) - UNIX_TIMESTAMP(started)) AS uprate, IF(seeder = 'yes',(peers.downloaded - peers.downloadoffset)  / (finishedat - UNIX_TIMESTAMP(started)),(peers.downloaded - peers.downloadoffset) / (UNIX_TIMESTAMP(last_action) - UNIX_TIMESTAMP(started))) AS downrate FROM peers JOIN users ON peers.userid = users.id ORDER BY uprate DESC LIMIT $limit") or sqlerr();
				peerstable($r, "Top $limit schnellste Uploader" . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=4&amp;lim=100&amp;subtype=ul>Top 100</a>] - [<a href=topten.php?type=4&amp;lim=250&amp;subtype=ul>Top 250</a>]</font>" : ""));
	  	}

	    if ($limit == 10 || $subtype == "dl")
  		{
//				$r = mysql_query("SELECT users.id AS userid, peers.id AS peerid, username, peers.uploaded, peers.downloaded, (peers.uploaded - peers.uploadoffset) / (UNIX_TIMESTAMP(last_action) - UNIX_TIMESTAMP(started)) AS uprate, (peers.downloaded - peers.downloadoffset) / (UNIX_TIMESTAMP(last_action) - UNIX_TIMESTAMP(started)) AS downrate FROM peers JOIN users ON peers.userid = users.id ORDER BY downrate DESC LIMIT $limit") or sqlerr();
//				peerstable($r, "Top $limit Fastest Downloaders (timeout corrected)" . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=4&amp;lim=100&amp;subtype=dl>Top 100</a>] - [<a href=topten.php?type=4&amp;lim=250&amp;subtype=dl>Top 250</a>]</font>" : ""));

				$r = mysql_query("SELECT users.donor AS donor, users.enabled AS enabled, users.warned AS warned, users.id AS userid, peers.id AS peerid, username, peers.uploaded, peers.downloaded,(peers.uploaded - peers.uploadoffset) / (UNIX_TIMESTAMP(last_action) - UNIX_TIMESTAMP(started)) AS uprate, IF(seeder = 'yes',(peers.downloaded - peers.downloadoffset)  / (finishedat - UNIX_TIMESTAMP(started)),(peers.downloaded - peers.downloadoffset) / (UNIX_TIMESTAMP(last_action) - UNIX_TIMESTAMP(started))) AS downrate FROM peers JOIN users ON peers.userid = users.id ORDER BY downrate DESC LIMIT $limit") or sqlerr();
				peerstable($r, "Top $limit schnellste Downloader" . ($limit == 10 && $pu ? " <font class=small> - [<a href=topten.php?type=4&amp;lim=100&amp;subtype=dl>Top 100</a>] - [<a href=topten.php?type=4&amp;lim=250&amp;subtype=dl>Top 250</a>]</font>" : ""));
	  	}
	}
  end_main_frame();
?>
<table cellpadding="4" cellspacing="1" border="0" style="width:750px" class="tableinborder">
 <tr class="tabletitle" width="100%">
  <td colspan="10" width="100%"><span class="smallfont"><center>
  Transferaufzeichnungen am 2003-08-31 begonnen
 </tr></table>
<?  
  print("");
  stdfoot();
?>


