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
userlogin();

stdhead();
echo "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" style=\"width:100%\" class=\"tableinborder\">\n".
	"    <tr class=\"tabletitle\" width=\"100%\">\n".
	"        <td colspan=\"10\" width=\"100%\">\n".
	"            <span class=\"normalfont\"><center>\n".
	"            <b><img src=\"" . $GLOBALS["PIC_BASE_URL"] . "star16.gif\"> <a href=\"donate.php\">Spende, um den Tracker zu erhalten!</a> <img src=\"" . $GLOBALS["PIC_BASE_URL"] . "star16.gif\"></b>\n".
	"            </center></span>\n".
	"        </td>\n". 
	"    </tr>\n".
	"</table>\n".
	"<br>\n". // Start Newsmodul
	"<script language='JavaScript' src='js/expandCollapse.js' type='text/javascript'></script>".
	"<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" style=\"width:100%\" class=\"tableinborder\">\n".
	"    <tr class=\"tabletitle\" width=\"100%\">\n".
	"        <td colspan=\"10\" width=\"100%\"><span class=\"normalfont\">\n".
	"            <center>\n".
	"            <img src=\"" . $GLOBALS["PIC_BASE_URL"] . "newsticker.png\" width=\"22\" height=\"22\" alt=\"\" style=\"vertical-align: middle;\"> <b>Neuigkeiten\n";
if(get_user_class() >= UC_ADMINISTRATOR)
	echo " <a href=\"news.php\"><img src=\"" . $GLOBALS["PIC_BASE_URL"] . "news_add.png\" width=\"22\" height=\"22\" alt=\"News hinzufügen\" title=\"News hinzufügen\" style=\"vertical-align: middle;border:none\"></a>";
echo "            </b>\n".
	"            </center>\n".
	"        </span></td> \n".
	"    </tr>\n".
	"    <tr>\n".
	"        <td width=\"100%\" class=\"tablea\">\n";
$qry = $GLOBALS['DB']->prepare('SELECT news.*, users.username as pname FROM news LEFT JOIN users ON users.id = news.userid WHERE ADDDATE(news.added, INTERVAL 45 DAY) > NOW() ORDER BY added DESC LIMIT 10');
$qry->execute();
if($qry->rowCount() > 0){
	$data = $qry->FetchAll();
	$first = TRUE;
	begin_table(TRUE);
	foreach($data as $array){
		$news_date=date("Y-m-d",strtotime($array['added']));
		$news_year=substr($news_date,0,4);
		$news_month=substr($news_date,5,2);
		$news_day=substr($news_date,8,2);
		$news_date=$news_day . "." . $news_month . "." . $news_year;
		$news_day=date("l",mktime(0,0,0,$news_month,$news_day,$news_year));
		if($news_day == "Monday")
			$news_day="Montag";
		if($news_day == "Tuesday")
			$news_day="Dienstag";
		if($news_day == "Wednesday")
			$news_day="Mittwoch";
		if($news_day == "Thursday")
			$news_day="Donnerstag";
		if($news_day == "Friday")
			$news_day="Freitag";
		if($news_day == "Saturday")
			$news_day="Samstag";
		if($news_day == "Sunday")
			$news_day="Sonntag";

		echo "<tr>\n".
			"    <td class=tablecat align=left>\n";
		if ($first)
			echo "<a href=\"javascript:expandCollapse('" . $array['id'] . "');\"><img id=\"plusminus" . $array['id'] . "\" src=\"".$GLOBALS["PIC_BASE_URL"].$GLOBALS["ss_uri"]."/minus.gif\" alt=\"Auf-/Zuklappen\" border=\"0\"></a>\n";
		else
			echo "<a href=\"javascript:expandCollapse('" . $array['id'] . "');\"><img id=\"plusminus" . $array['id'] . "\" src=\"".$GLOBALS["PIC_BASE_URL"].$GLOBALS["ss_uri"]."/plus.gif\" alt=\"Auf-/Zuklappen\" border=\"0\"></a>\n";
		echo "<b>".htmlspecialchars($array["title"])."</b> ".
			"(Von <a class=\"altlink\" href=\"userdetails.php?id=" . $array['userid'] . "\">" . $array['pname'] . "</a>, " . $news_day . ", " . $news_date . ") ";
		if (get_user_class() >= UC_ADMINISTRATOR){
			echo " <font class=\"middle\"><a class=\"altlink\" href=\"news.php?action=edit&newsid=" . $array['id'] . "&returnto=" . urlencode($_SERVER['PHP_SELF']) . "\"><img src=\"".$GLOBALS["PIC_BASE_URL"]."edit.png\" width=\"16\" height=\"16\" alt=\"Bearbeiten\" title=\"Bearbeiten\" border=\"0\" style=\"vertical-align:bottom\"></font>".
				" <font class=\"middle\"><a class=\"altlink\" href=\"news.php?action=delete&newsid=" . $array['id'] . "&returnto=" . urlencode($_SERVER['PHP_SELF']) . "\"><img src=\"".$GLOBALS["PIC_BASE_URL"]."editdelete.png\" width=\"16\" height=\"16\" alt=\"L&ouml;schen\" title=\"L&ouml;schen\" border=\"0\" style=\"vertical-align:bottom\"></a></font>";
		}
		if ($first)
			echo "<tr id=\"details" . $array['id'] . "\" style=\"display:table-row;\">";
		else
			echo "<tr id=\"details" . $array['id'] . "\" style=\"display:none;\">";
		echo "    <td class=\"tablea\" align=\"left\">\n".
			"<div align=\"justify\">" . stripslashes($array['body']);
		echo "    </td>".
			"</tr>";
		$first = FALSE;
	}
	end_table();
}
echo "        </td>\n".
	"    </tr>\n".
	"</table>\n";
// eof newsmodul

// start active users
$dt = time() - 200;
$dt = get_date_time($dt);
$qry = $GLOBALS['DB']->prepare('SELECT id, username, class, donor, warned, added, enabled FROM users WHERE last_access >= :dt AND last_access <= NOW() ORDER BY class DESC,username');
$qry->bindParam(':dt', $dt, PDO::PARAM_INT);
$qry->execute();
if(!$qry->rowCount())
	$activeusers_no = 0;
else{
	$activeusers_no = $qry->rowCount();
	$data = $qry->FetchAll();
}

$activeusers = "";
if($activeusers_no > 0){
	foreach($data as $arr){
		if ($activeusers)
			$activeusers .= ",\n";
		$arr['username'] = "<font class=" . get_class_color($arr['class']) . ">" . $arr['username'] . "</font>";
		if ($CURUSER)
			$activeusers .= "<a href=userdetails.php?id=" . $arr['id'] . "><b>" . $arr['username'] . "</b></a>";
		else
			$activeusers .= "<b>" . $arr['username'] . "</b>";
		$activeusers .= "&nbsp;".get_user_icons($arr);
	}
}else
    $activeusers = "Keine aktiven Mitglieder in den letzten 15 Minuten.";

echo "<br>\n".
	"<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" style=\"width:100%\" class=\"tableinborder\">\n".
	"    <tr class=\"tabletitle\" width=\"100%\">\n".
	"        <td colspan=\"10\" width=\"100%\">\n".
	"            <span class=\"normalfont\">\n".
	"            <center>\n".
	"                <img src=\"" . $GLOBALS["PIC_BASE_URL"] . "user.png\" width=\"22\" height=\"22\" alt=\"\" style=\"vertical-align: middle;\"><b>Momentan aktive Mitglieder (" . $activeusers_no . ") </b>\n".
	"            </center>\n".
	"            </span>\n".
	"        </td>\n".
	"    </tr>\n".
	"    <tr>\n".
	"        <td width=\"100%\" class=\"tablea\">" . $activeusers . "</td>\n".
	"    </tr>\n".
	"</table>\n".
	"<br>\n";
// eof active users

if($CURUSER){
	if($GLOBALS["ENABLESHOUTCAST"]){
		echo "<td valign=\"top\" width=\"50%\">";
		sc_infobox();
		echo "</td>\n";
	}

	// start umfragemodul
	$polls = (new polls)->getData();
	$latest = $polls->data;

	foreach($latest as $p){
		$poll = $p;
	}
	$check = $polls->has_answered($poll['id'],$CURUSER['id']);
	$tvotes = $polls->get_answer_count($poll['id']);

	begin_table(); 
	echo "<table cellspacing=\"5\" cellpadding=\"0\" border=\"0\" style=\"width:100%\">\n".
		"    <tr>\n".
		"        <td valign=\"top\" width=\"50%\">\n".
		"            <table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" style=\"width:100%\" class=\"tableinborder\">\n".
		"                <tr class=\"tabletitle\" width=\"100%\">\n".
		"                    <td colspan=\"10\" width=\"100%\"><span class=\"normalfont\">\n".
		"                        <center><b> Aktuelle Umfrage</b></center></span>\n".
		"                    </td>\n".
		"                </tr>\n".
		"                <tr>\n".
		"                    <td width=\"100%\" class=\"tablea\">\n".
		"                    <p align=center><b>" . $poll['question'] . "</b></p>\n";
	if($check){
		echo "<center><table border=\"0\" cellspacing=\"0\" cellpadding=\"2\">\n";
		foreach($poll['result'] as $answerid => $users){
			if($users[0] == "")
				$count = 0;
			else
				$count = count($users);
			if($count == 0)
				$p = 0;
			else
				$p = round($count / $tvotes * 100);

			echo "<tr>".
				"    <td nowrap align=\"left\">" . $poll['answers'][$answerid] . "&nbsp;&nbsp;</td>".
				"    <td align=\"left\">".
				"        <img src=\"".$GLOBALS["PIC_BASE_URL"].$GLOBALS["ss_uri"]."/vote_left" . (($answerid%5)+1) . ".gif\">".
				"        <img src=\"".$GLOBALS["PIC_BASE_URL"].$GLOBALS["ss_uri"]."/vote_middle" . (($answerid%5)+1) . ".gif\" height=9 width=" . (($p * 5)+1) .">".
				"        <img src=\"".$GLOBALS["PIC_BASE_URL"].$GLOBALS["ss_uri"]."/vote_right" . (($answerid%5)+1) . ".gif\"> " . $p . "%".
				"    </td>".
				"</tr>\n";
		}
		echo "<p align=\"center\">Abgebene Stimmen: " . $tvotes . "</p>\n".
			"</table></center>\n";
	}else{
		echo "<form method=\"post\" action=\"polls.php?action=vote\"><center>\n";
		foreach($poll['answers'] as $aid => $a){
			echo "<input type=\"radio\" name=\"choice\" value=" . $aid . ">" . $a . "<br>\n";
		}
		echo "<input type=\"hidden\" name=\"userid\" value=" . $CURUSER['id'] . " />\n".
			"<input type=\"hidden\" name=\"pollid\" value=" . $poll['id'] . " />\n".
			"<br><p align=\"center\"><input type=\"submit\" value=\"'Vote!'\" class=\"btn\"></p></center>\n";
	}
	if ($check)
		echo "<p align=center><a href=\"polls.php\">Alle Umfragen</a> <a href=\"polls.php?action=revoke&pollid=" . $poll['id'] . "\">Stimme zurückziehen</a></p>\n";
	echo "        </td>\n".
		"    </tr>\n".
		"</table>\n";
	end_table();
	// eof umfrage
}

// Shoutbox-Modul
function textbbcode_edit($text, $aktive = TRUE){
	global $BASEURL, $CURUSER;
	$button = $BASEURL . "/" . $GLOBALS["PIC_BASE_URL"] . "editor";
	$png = "true";
	$button .= "/default";
    print("\n<div id=\"loading-layer\" style=\"display:none; font-family: Verdana; font-size: 11px; width:200px; height:50px; background:#FFFFFF; padding:10px; text-align:center; border:1px solid #000000;\">\n" .
          "    <div style=\"font-weight:bold;\" id=\"loading-layer-text\">Senden. Bitte warten ...</div><br />\n" .
          "    <img src=\"" . $BASEURL . "/pic/loading.gif\" border=\"0\" />\n" .
          "</div>\n" .
          "<div style=\"text-align: left; width: 650px;\">\n" .
          "  <script type=\"text/javascript\" src=\"" . $BASEURL . "/js/editor.js\"></script>\n" .
          "  <script type=\"text/javascript\">edToolbar('" . $text . "','" . $button . "','" . $png . "','no');</script>\n");

    if ($aktive){
        print("  <input name=\"" . $text . "\" id=\"" . $text . "\" value=\"\" size=\"80\" />\n" .
              "  <button name=\"button\" id=\"button\">Absenden</button>\n");
    }

    print("</div>\n" .
          "<br style=\"clear: left;\" />");
}

$shoutbox = new shoutbox($database);
echo "<br>\n".
	"<table align=\"center\" width=\"100%\">\n".
	"    <tr>\n".
	"        <td>\n";
if(date('m') != 12 && date('m') != 1)
	echo "            <script type=\"text/javascript\" src=\"/js/jquery-3.2.1.min.js\"></script>\n";
echo "            <script type=\"text/javascript\" src=\"/js/shoutbox.js\"></script>\n".
	"            <script type=\"text/javascript\" src=\"/js/ajax.js\"></script>\n".
	"            <link rel=\"stylesheet\" href=\"css/shoutbox.css\" type=\"text/css\">\n".
	"            <table summary=\"\" cellpadding=\"4\" cellspacing=\"1\" border=\"0\" style=\"width:97%\" class=\"tableinborder\">\n".
	"                <tr>\n".
	"                    <td class=\"tabletitle\" width=\"100%\" style=\"text-align: center; font-weight: bold;\" colspan=\"2\">.: Ajax-Chat v0.6 :.</td>\n".
	"                </tr>\n".
	"                <tr>\n".
	"                    <td width=\"12%\" class=\"tablea\" valign=\"top\">\n".
	"                        <center>\n".
	"                            <table summary=\"\" cellSpacing=\"1\" cellPadding=\"3\" class=\"tableinborder\" border=\"0\">\n";
$zeile = 0;
reset($privatesmilies);
while(list($code, $url) = each($privatesmilies)){
	if ($zeile == 0)
		echo "                                <tr>\n";
	echo "                                    <td class=\"tablea\" style=\"padding: 3px; margin: 1px\"><center>".
		"<img border=\"0\" src=\"" . $BASEURL . "/" . $GLOBALS["PIC_BASE_URL"] . "/smilies/" . $url . "\" onclick=\"javascript: em('" . $code . "')\" alt=\"\" /></center></td>\n";
	$zeile++;
	if($zeile == 4){
		echo "                                </tr>\n";
		$zeile = 0;
	}
}
if (($zeile != 4) && ($zeile != 0)){
	echo "                                    <td class=\"tablea\" colspan=\"" . (4 - $zeile) . "\">&nbsp;</td>\n" .
		"                                </tr>\n";
}
echo "                                <tr>\n" .
	"                                    <td class=\"tablea\" colspan=\"4\"><center><a href=\"smilies.php\" target=\"_black\">Mehr Smilies</a></center></td>\n" .
	"                                </tr>\n";

/*if (get_user_class() >= UC_ADMIN)
{
  print("         <tr>\n" .
        "           <td class=\"tabletitle\" colspan=\"10\" width=\"100%\" style=\"text-align: center\"><a href=\"ajax_chat_history.php?history=1\" target=\"_blank\">[History]</a></td>\n" .
        "         </tr>\n");
}*/

/*print("         <tr>\n" .
      "           <td class=\"tablea\" colspan=\"4\" width=\"100%\" style=\"text-align: center\">\n" .
      "             <a href=\"" . $BASEURL . "/shoutcast.php\">\n" .
      "               <img src=\"" . $BASEURL . "/" . $GLOBALS["PIC_BASE_URL"] . "radio_index.gif\" border=\"0\">\n" .
      "             </a></td>\n" .
      "         </tr>\n");*/

echo "                            </table>\n".
	"                        </center>\n".
	"                    </td>\n".
	"                    <td class=\"tablea\">\n".
	"                        <table summary=\"\" class=\"tableinborder\"  border=\"0\" cellspacing=\"1\" cellpadding=\"5\" width=\"100%\">\n".
	"                            <tr>\n".
	"                                <td class=\"tablea\">\n".
	"                                    <div style=\"width:100%; height:330px; background: #404953;\" id=\"frame\">\n".
	"                                        <div style=\"padding: 2px;\" id=\"rahmen\">\n".
	"                                            <div id=\"screen\" style=\"width:100%; height:330px; overflow:auto; text-align:left; font-size:12px; font-family:Verdana;\"></div>\n".
	"                                        </div>\n".
	"                                    </div>\n".
	"                                </td>\n".
	"                            </tr>\n".
	"                            <tr>\n".
	"                                <td class=\"tablea\">\n";
textbbcode_edit("message");
echo "                                </td>\n".
	"                            </tr>\n".
	"                        </table>\n".
	"                    </td>\n".
	"                </tr>\n".
	"            </table>\n".
	"        </td>\n".
	"    </tr>\n".
	"</table>\n".
	"<br>\n";
// ende shoutbox

// start stats
$a = $GLOBALS['DB']->query("SELECT value_u FROM avps WHERE arg='seeders'")->fetchAll()[0];
$seeders = 0 + $a['value_u'];
$a = $GLOBALS['DB']->query("SELECT value_u FROM avps WHERE arg='leechers'")->fetchAll()[0];
$leechers = 0 + $a['value_u'];

if ($leechers == 0)
	$ratio = 0;
else
	$ratio = round($seeders / $leechers * 100);
    
$peers = number_format($seeders + $leechers);
$seeders = number_format($seeders);
$leechers = number_format($leechers);

$max = $GLOBALS["MAX_USERS"]/1000;
echo "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" style=\"width:100%\" class=\"tableinborder\">\n".
	"    <tr class=\"tabletitle\" width=\"100%\">\n".
	"        <td colspan=\"10\" width=\"100%\"><span class=\"normalfont\"><center><b> Statistik (Tracker) </b></center></span></td>\n".
	"    </tr>\n".
	"    <tr>\n".
	"        <td width=\"100%\" class=\"tablea\">\n".
	"            <center>\n".
	"                <table border=\"0\" cellspacing=\"1\" cellpadding=\"5\" class=\"tableinborder\">\n".
	"                    <tr>\n".
	"                        <td class=\"tableb\" align=\"left\">Max. Mitgliederzahl</td>\n".
	"                        <td align=\"right\" class=\"tablea\">" . number_format($max,3) . "</td>\n".
	"                    </tr>\n";
if($CURUSER){
	$registered = number_format($database->row_count('users'));
	$unverified = number_format($database->row_count("users", "status='pending'"));
	$inactive = number_format($database->row_count("users", "enabled='no'"));
	echo "                    <tr>\n".
		"                        <td class=\"tableb\" align=\"left\">Registrierte Mitglieder</td>\n".
		"                        <td align=\"right\" class=\"tablea\">" . $registered . "</td>\n".
		"                    </tr>\n".
		"                    <tr>\n".
		"                        <td class=\"tableb\" align=\"left\">&nbsp;&nbsp;Unbest&auml;tigte Mitglieder</td>\n".
		"                        <td align=\"right\" class=\"tablea\">" . $unverified . "</td>\n".
		"                    </tr>\n".
		"                    <tr>\n".
		"                        <td class=\"tableb\" align=\"left\">&nbsp;&nbsp;Deaktivierte Accounts</td>\n".
		"                        <td align=\"right\" class=\"tablea\">" . $inactive . "</td>\n".
		"                    </tr>\n";
}
$torrents = $database->row_count("torrents");
$dead = $database->row_count("torrents", "visible='no'");
echo "                    <tr>\n".
	"                        <td class=\"tableb\" align=\"left\">Torrents</td>\n".
	"                        <td align=\"right\" class=\"tablea\">" . number_format($torrents) . "</td>\n".
	"                    </tr>\n".
	"                    <tr>\n".
	"                        <td class=\"tableb\" align=\"left\">&nbsp;&nbsp;&nbsp;Aktive Torrents</td>\n".
	"                        <td align=\"right\" class=\"tablea\">" . number_format($torrents-$dead) . "</td>\n".
	"                    </tr>\n".
	"                    <tr>\n".
	"                        <td class=\"tableb\" align=\"left\">&nbsp;&nbsp;&nbsp;Inaktive Torrents</td>\n".
	"                        <td align=\"right\" class=\"tablea\">" . number_format($dead) . "</td>\n".
	"                    </tr>\n";
if(isset($peers)){
	$a = $GLOBALS['DB']->query("SELECT SUM(downloaded) as res FROM users WHERE enabled='yes'")->fetchAll()[0];
	$totaldown = mksize(0 + $a['res']);

	$a = $GLOBALS['DB']->query("SELECT SUM(uploaded) as res FROM users WHERE enabled='yes'")->fetchAll()[0];
	$totalup = mksize(0 + $a['res']);
	echo "                    <tr>\n".
		"                        <td class=\"tableb\" align=\"left\">Peers</td>\n".
		"                        <td align=\"right\" class=\"tablea\">" . $peers . "</td>\n".
		"                    </tr>\n".
		"                    <tr>\n".
		"                        <td class=\"tableb\" align=\"left\">&nbsp;&nbsp;&nbsp;Seeders</td>\n".
		"                        <td align=\"right\" class=\"tablea\">" . $seeders . "</td></tr>\n".
		"                    <tr>\n".
		"                        <td class=\"tableb\" align=\"left\">&nbsp;&nbsp;&nbsp;Leechers</td>\n".
		"                        <td align=\"right\" class=\"tablea\">" . $leechers . "</td></tr>\n".
		"                    <tr>\n".
		"                        <td class=\"tableb\" align=\"left\">&nbsp;&nbsp;&nbsp;Seeder/Leecher Ratio (%)</td>\n".
		"                        <td align=\"right\" class=\"tablea\">" . $ratio . "</td></tr>\n".
		"                    <tr>\n".
		"                        <td class=\"tableb\" align=\"left\">Total Runtergeladen</td>\n".
		"                        <td align=\"right\" class=\"tablea\">" . $totaldown . "</td></tr>\n".
		"                    <tr>\n".
		"                        <td class=\"tableb\" align=\"left\">Total Hochgeladen</td>\n".
		"                        <td align=\"right\" class=\"tablea\">" . $totalup . "</td>\n".
		"                    </tr>\n";
}
echo "                </table>\n".
	"            </center>\n".
	"        </td>\n".
	"    </tr>\n".
	"</table>\n".// eof stats
	"<br>\n";
// start serverload
echo "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" style=\"width:100%\" class=\"tableinborder\">\n".
	"    <tr class=\"tabletitle\" width=\"100%\">\n".
	"        <td colspan=\"10\" width=\"100%\">\n".
	"            <span class=\"normalfont\"><center><b>Serverauslastung</b></center></span>\n".
	"        </td>\n".
	"    </tr>\n".
	"    <tr>\n".
	"        <td width=\"100%\" class=\"tablea\">\n".
	"            <center><p>Webserver-Prozesse:</p>\n".
	"            <table class=\"tableinborder\" border=\"0\" cellpadding=\"0\" cellspacing=\"1\" width=\"402\">\n".
	"                <tr>\n".
	"                    <td align=\"left\" style=\"padding: 0px; background-image: url('" . $GLOBALS["PIC_BASE_URL"] . "loadbarbg.gif'); background-repeat: repeat-x\">";
$percent = min(100, round(exec('ps ax | grep -c apache2') / 150 * 100));
if($percent <= 70)
	$pic = "loadbargreen.gif";
elseif($percent <= 90)
	$pic = "loadbaryellow.gif";
else
	$pic = "loadbarred.gif";
$width = $percent * 4;
echo "                        <img height=\"15\" width=\"" . $width . "\" src=\"".$GLOBALS["PIC_BASE_URL"].$pic."\" alt=\"" . $percent . "%\">\n".
	"                    </td>\n".
	"                </tr>\n".
	"            </table>\n".
	"            <p>Systemauslastung (Durchschnittswerte): ";
$loadavg = explode(" ", exec("cat /proc/loadavg"));
$time = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
echo $loadavg[0]*100, "% (1min) - ", $loadavg[1]*100, "% (5min) - ", $loadavg[2]*100, "% (15min)".
	"            </p>\n".
	"            <p>Diese Seite wurde in " . round($time, 4) . " Sekunden erstellt.</p>\n".
	"            </center>\n".
	"        </td>\n".
	"    </tr>\n".
	"</table>\n";
	
//phpinfo();
// eof serverload
stdfoot();
?>