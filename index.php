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

require "include/bittorrent.php";
//hit_start();

dbconn();

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    $choice = $_POST["choice"];
    if ($CURUSER && $choice != "" && $choice < 256 && $choice == floor($choice))
    {
        $res = mysql_query("SELECT * FROM polls ORDER BY added DESC LIMIT 1") or sqlerr();
        $arr = mysql_fetch_assoc($res) or die("No poll");
        $pollid = $arr["id"];
        $userid = $CURUSER["id"];
        $res = mysql_query("SELECT * FROM pollanswers WHERE pollid=$pollid && userid=$userid") or sqlerr();
        $arr = mysql_fetch_assoc($res);
        if ($arr)
            die("Dupe vote");
        mysql_query("INSERT INTO pollanswers VALUES(0, $pollid, $userid, $choice)") or sqlerr();
        if (mysql_affected_rows() != 1)
            stderr("Error", "Ein Fehler ist passiert! Deine Stimme konnte nicht gez&auml;hlt werden.");
        header("Location: $BASEURL/?".SID);
        die;
    }
    else
    stderr("Error", "Bitte w&auml;hle eine Option aus.");
}    

$registered = number_format(pdo_row_count('users'));
$unverified = number_format(pdo_row_count("users", "status='pending'"));
$inactive = number_format(pdo_row_count("users", "enabled='no'"));
$torrents = pdo_row_count("torrents");
$dead = pdo_row_count("torrents", "visible='no'");

$r = mysql_query("SELECT value_u FROM avps WHERE arg='seeders'") or sqlerr(__FILE__, __LINE__);
$a = mysql_fetch_row($r);
$seeders = 0 + $a[0];
$r = mysql_query("SELECT value_u FROM avps WHERE arg='leechers'") or sqlerr(__FILE__, __LINE__);
$a = mysql_fetch_row($r);
$leechers = 0 + $a[0];
$r = mysql_query("SELECT SUM(downloaded) FROM users WHERE enabled='yes'") or sqlerr(__FILE__, __LINE__);
$a = mysql_fetch_row($r);
$totaldown = mksize(0 + $a[0]);
$r = mysql_query("SELECT SUM(uploaded) FROM users WHERE enabled='yes'") or sqlerr(__FILE__, __LINE__);
$a = mysql_fetch_row($r);
$totalup = mksize(0 + $a[0]);

if ($leechers == 0)
    $ratio = 0;
else
    $ratio = round($seeders / $leechers * 100);
    
$peers = number_format($seeders + $leechers);
$seeders = number_format($seeders);
$leechers = number_format($leechers);

$dt = time() - 200;
$dt = sqlesc(get_date_time($dt));
$maxdt = get_date_time(time() - 21600*28);
$res = mysql_query("SELECT id, username, class, donor, warned, added, enabled FROM users WHERE last_access >= $dt AND last_access <= NOW() ORDER BY class DESC,username") or print(mysql_error());
$activeusers_no = mysql_num_rows($res);
$activeusers = "";
while ($arr = mysql_fetch_assoc($res))
{
	
    if ($activeusers) $activeusers .= ",\n";
    $arr["username"] = "<font class=".get_class_color($arr["class"]).">" . $arr["username"] . "</font>";
    if ($CURUSER)
        $activeusers .= "<a href=userdetails.php?id=" . $arr["id"] . "><b>" . $arr["username"] . "</b></a>";
    else
        $activeusers .= "<b>$arr[username]</b>";
    
    $activeusers .= "&nbsp;".get_user_icons($arr);
}

if (!$activeusers)
    $activeusers = "Keine aktiven Mitglieder in den letzten 15 Minuten.";

stdhead();

?>
<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
 <tr class="tabletitle" width="100%">
  <td colspan="10" width="100%"><span class="normalfont"><center><b> <img src="<?=$GLOBALS["PIC_BASE_URL"]?>star16.gif"> <a href="donate.php">Spende, um den Tracker zu erhalten!</a> <img src="<?=$GLOBALS["PIC_BASE_URL"]?>star16.gif"></b></center></span></td> 
 </tr></table>
<br>
<?php
	$pollsnew = new polls(false);
	$pollsnew->getData();
	//print("<pre>");
	//var_dump($pollsnew->data);
	highlight_string("<?php\n\$pollsnew->data =\n" . var_export($pollsnew->data, true) . ";\n?>");
?>

<script type="text/javascript">

function expandCollapse(newsId)
{
    var plusMinusImg = document.getElementById("plusminus"+newsId);
    var detailRow = document.getElementById("details"+newsId);

    if (detailRow.style.display == "none") {
        plusMinusImg.src = "<?=$GLOBALS["PIC_BASE_URL"].$GLOBALS["ss_uri"]?>/minus.gif";
        detailRow.style.display = "table-row";
    } else {
        plusMinusImg.src = "<?=$GLOBALS["PIC_BASE_URL"].$GLOBALS["ss_uri"]?>/plus.gif";
        detailRow.style.display = "none";
    }
}

</script>
<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
 <tr  class="tabletitle" width="100%">
        <td colspan="10" width="100%"><span class="normalfont"><center><img src="<?=$GLOBALS["PIC_BASE_URL"]?>newsticker.png" width="22" height="22" alt="" style="vertical-align: middle;"> <b>Neuigkeiten 
<?php


if (get_user_class() >= UC_ADMINISTRATOR)
        print(" <a href=\"news.php\"><img src=\"".$GLOBALS["PIC_BASE_URL"]."news_add.png\" width=\"22\" height=\"22\" alt=\"News hinzufügen\" title=\"News hinzufügen\" style=\"vertical-align: middle;border:none\"></a>");
?>

</b></center></span></td> 
 </tr><tr><td width="100%" class="tablea">
 <?php
$res = mysql_query("SELECT * FROM news WHERE ADDDATE(added, INTERVAL 45 DAY) > NOW() ORDER BY added DESC LIMIT 10") or sqlerr(__FILE__, __LINE__);

if (mysql_num_rows($res) > 0)
{
    $first = TRUE;
    begin_table(TRUE);
    while($array = mysql_fetch_array($res))
    {
        $user_id=$array['userid'];
        $res_username=mysql_query("SELECT username FROM users WHERE id=$user_id") or sqlerr(__FILE__, __LINE__);
        $username=mysql_fetch_array($res_username);
    
        $news_date=date("Y-m-d",strtotime($array['added']));
        $news_year=substr($news_date,0,4);
        $news_month=substr($news_date,5,2);
        $news_day=substr($news_date,8,2);
    
        $news_date=$news_day . "." . $news_month . "." . $news_year;
        $news_day=date("l",mktime(0,0,0,$news_month,$news_day,$news_year));
    
        if ($news_day == "Monday")
            $news_day="Montag";
        if ($news_day == "Tuesday")
            $news_day="Dienstag";
            if ($news_day == "Wednesday")
            $news_day="Mittwoch";
        if ($news_day == "Thursday")
            $news_day="Donnerstag";
        if ($news_day == "Friday")
            $news_day="Freitag";
        if ($news_day == "Saturday")
            $news_day="Samstag";
        if ($news_day == "Sunday")
            $news_day="Sonntag";


        echo "<tr><td class=tablecat align=left>";
        if ($first)
        print("<a href=\"javascript:expandCollapse('" . $array['id'] . "');\"><img id=\"plusminus" . $array['id'] . "\" src=\"".$GLOBALS["PIC_BASE_URL"].$GLOBALS["ss_uri"]."/minus.gif\" alt=\"Auf-/Zuklappen\" border=\"0\"></a>\n");
        else
        print("<a href=\"javascript:expandCollapse('" . $array['id'] . "');\"><img id=\"plusminus" . $array['id'] . "\" src=\"".$GLOBALS["PIC_BASE_URL"].$GLOBALS["ss_uri"]."/plus.gif\" alt=\"Auf-/Zuklappen\" border=\"0\"></a>\n");
        print("<b>".htmlspecialchars($array["title"])."</b> ");
        print("(Von <a class=altlink href=userdetails.php?id=$user_id>" . $username['username'] . "</a>, " . $news_day . ", " . $news_date . ") ");
        if (get_user_class() >= UC_ADMINISTRATOR)
        {
    print(" <font class=middle><a class=altlink href=news.php?action=edit&newsid=" . $array['id'] . "&returnto=" . urlencode($_SERVER['PHP_SELF']) . "><img src=\"".$GLOBALS["PIC_BASE_URL"]."edit.png\" width=\"16\" height=\"16\" alt=\"Bearbeiten\" title=\"Bearbeiten\" border=\"0\" style=\"vertical-align:bottom\"></font>");
            print(" <font class=middle><a class=altlink href=news.php?action=delete&newsid=" . $array['id'] . "&returnto=" . urlencode($_SERVER['PHP_SELF']) . "><img src=\"".$GLOBALS["PIC_BASE_URL"]."editdelete.png\" width=\"16\" height=\"16\" alt=\"L&ouml;schen\" title=\"L&ouml;schen\" border=\"0\" style=\"vertical-align:bottom\"></a></font>");
        }
    
        if ($first)
            print("<tr id=\"details" . $array['id'] . "\" style=\"display:table-row;\">");
        else
        print("<tr id=\"details" . $array['id'] . "\" style=\"display:none;\">");
        print("<td class=\"tablea\" align=\"left\"><div align=\"justify\">" . stripslashes($array['body']));
        print("</td></tr>");
        $first = FALSE;
    }
    end_table();
}

?>
</td></tr></table>
<br>
<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
 <tr class="tabletitle" width="100%">
        <td colspan="10" width="100%"><span class="normalfont"><center><img src="<?=$GLOBALS["PIC_BASE_URL"]?>user.png" width="22" height="22" alt="" style="vertical-align: middle;"> <b>Momentan aktive Mitglieder (<?=$activeusers_no?>) </b></center></span></td> 
 </tr><tr><td width="100%" class="tablea"><?=$activeusers?></td></tr></table>
<br>
<?php

if ($CURUSER)
{
    echo "<table cellspacing=\"5\" cellpadding=\"0\" border=\"0\" style=\"width:100%\"><tr>";
    // get current poll
    $res = mysql_query("SELECT * FROM polls ORDER BY added DESC LIMIT 1") or sqlerr();
    $arr = mysql_fetch_assoc($res);
    if (is_array($arr)) {
        $pollid = $arr["id"];
        $userid = $CURUSER["id"];
        $question = $arr["question"];
        $o = array($arr["option0"], $arr["option1"], $arr["option2"], $arr["option3"], $arr["option4"],
            $arr["option5"], $arr["option6"], $arr["option7"], $arr["option8"], $arr["option9"],
            $arr["option10"], $arr["option11"], $arr["option12"], $arr["option13"], $arr["option14"],
            $arr["option15"], $arr["option16"], $arr["option17"], $arr["option18"], $arr["option19"]);
    
        // check if user has already voted
        $res = mysql_query("SELECT * FROM pollanswers WHERE pollid=$pollid AND userid=$userid") or sqlerr();
        $arr2 = mysql_fetch_assoc($res);
    
?><td valign="top" width="50%">
<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
 <tr class="tabletitle" width="100%">
  <td colspan="10" width="100%"><span class="normalfont"><center><b> Aktuelle Umfrage<?php

    if (get_user_class() >= UC_MODERATOR)
    {
        print("<font class=middle>");
        print(" - [<a class=altlink href=makepoll.php?returnto=main><b>Neu</b></a>]\n");
        print(" - [<a class=altlink href=makepoll.php?action=edit&pollid=$arr[id]&returnto=main><b>&Auml;ndern</b></a>]\n");
        print(" - [<a class=altlink href=polls.php?action=delete&pollid=$arr[id]&returnto=main><b>L&ouml;schen</b></a>]");
        print("</font>");
    }
?> </b></center></span></td> 
 </tr><tr><td width="100%" class="tablea">
 <?php
    print("<p align=center><b>$question</b></p>\n");
  
    $voted = $arr2;
  
    if ($voted)
    {
    
        // display results
        if ($arr["selection"])
            $uservote = $arr["selection"];
        else
            $uservote = -1;
        // we reserve 255 for blank vote.
        $res = mysql_query("SELECT selection FROM pollanswers WHERE pollid=$pollid AND selection < 20") or sqlerr();
        $tvotes = mysql_num_rows($res);
        $vs = array();
        $os = array();

        // count votes
        while ($arr2 = mysql_fetch_row($res))
            $vs[$arr2[0]] += 1;

        reset($o);
        for ($i = 0; $i < count($o); ++$i)
            if ($o[$i])
                $os[$i] = array($vs[$i], $o[$i]);

        function srt($a,$b)
        {
            if ($a[0] > $b[0]) return -1;
            if ($a[0] < $b[0]) return 1;
            return 0;
        }

        // now os is an array like this: array(array(123, "Option 1"), array(45, "Option 2"))
        if ($arr["sort"] == "yes")
            usort($os, srt);

        print("<center><table border=0 cellspacing=0 cellpadding=2>\n");
        $i = 0;
        while ($a = $os[$i])
        {
            if ($i == $uservote)
                $a[1] .= "&nbsp;*";
            if ($tvotes == 0)
                $p = 0;
            else
                $p = round($a[0] / $tvotes * 100);

            print("<tr><td nowrap align=left>" . $a[1] . "&nbsp;&nbsp;</td><td align=left>" .
        "<img src=\"".$GLOBALS["PIC_BASE_URL"].$GLOBALS["ss_uri"]."/vote_left".(($i%5)+1).".gif\"><img src=\"".$GLOBALS["PIC_BASE_URL"].$GLOBALS["ss_uri"]."/vote_middle".(($i%5)+1).".gif\" height=9 width=" . ($p * 5) .
        "><img src=\"".$GLOBALS["PIC_BASE_URL"].$GLOBALS["ss_uri"]."/vote_right".(($i%5)+1).".gif\"> $p%</td></tr>\n");
            $i++;
        }
        print("</table></center>\n");
        $tvotes = number_format($tvotes);
        print("<p align=center>Abgebene Stimmen: $tvotes</p>\n");
    }
    else
    {
        print("<form method=post action=index.php>\n");
        $i = 0;
        while ($a = $o[$i])
        {
            print("<input type=radio name=choice value=$i>$a<br>\n");
            ++$i;
        }
    print("<br>");
    print("<input type=radio name=choice value=255>Ich will keine Stimme abgeben, ich m&ouml;chte nur das Ergebnis sehen!<br>\n");
    print("<p align=center><input type=submit value='Vote!' class=btn></p>");
    }

if ($voted)
    print("<p align=center><a href=polls.php>Ältere Umfragen</a></p>\n");

?>
</td></tr></table>
</td>
<?php
    }
    
    if ($GLOBALS["ENABLESHOUTCAST"]) {
        echo "<td valign=\"top\" width=\"50%\">";
        sc_infobox();
        echo "</td>\n";
    }
    echo "</tr></table><br>";
}

?>
<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
 <tr class="tabletitle" width="100%">
  <td colspan="10" width="100%"><span class="normalfont"><center><b> Statistik (Tracker) </b></center></span></td> 
 </tr><tr><td width="100%" class="tablea">
<center>
<table border="0" cellspacing="1" cellpadding="5" class="tableinborder">
        <tr><td class="tableb" align="left">Max. Mitgliederzahl</td><td align="right" class="tablea"><?php $max=$GLOBALS["MAX_USERS"]/1000; echo number_format($max,3); ?></td></tr>
        <?php if ($CURUSER) { ?>
<tr><td class="tableb" align="left">Registrierte Mitglieder</td><td align="right" class="tablea"><?=$registered?></td></tr>
<tr><td class="tableb" align="left">&nbsp;&nbsp;Unbest&auml;tigte Mitglieder</td><td align="right" class="tablea"><?=$unverified?></td></tr>
<tr><td class="tableb" align="left">&nbsp;&nbsp;Deaktivierte Accounts</td><td align="right" class="tablea"><?=$inactive?></td></tr>
        <?php } ?>
<tr><td class="tableb" align="left">Torrents</td><td align="right" class="tablea"><?=number_format($torrents)?></td></tr>
<tr><td class="tableb" align="left">&nbsp;&nbsp;&nbsp;Aktive Torrents</td><td align="right" class="tablea"><?=number_format($torrents-$dead)?></td></tr>
<tr><td class="tableb" align="left">&nbsp;&nbsp;&nbsp;Inaktive Torrents</td><td align="right" class="tablea"><?=number_format($dead)?></td></tr>
<?php if (isset($peers)) { ?>
<tr><td class="tableb" align="left">Peers</td><td align="right" class="tablea"><?=$peers?></td></tr>
<tr><td class="tableb" align="left">&nbsp;&nbsp;&nbsp;Seeders</td><td align="right" class="tablea"><?=$seeders?></td></tr>
<tr><td class="tableb" align="left">&nbsp;&nbsp;&nbsp;Leechers</td><td align="right" class="tablea"><?=$leechers?></td></tr>
<tr><td class="tableb" align="left">&nbsp;&nbsp;&nbsp;Seeder/Leecher Ratio (%)</td><td align="right" class="tablea"><?=$ratio?></td></tr>
<tr><td class="tableb" align="left">Total Runtergeladen</td><td align="right" class="tablea"><?=$totaldown?></td></tr>
<tr><td class="tableb" align="left">Total Hochgeladen</td><td align="right" class="tablea"><?=$totalup?></td></tr>
<?php } ?>
</table>
</center>
</td></tr></table>
<br>
<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
 <tr class="tabletitle" width="100%">
  <td colspan="10" width="100%"><span class="normalfont"><center><b> Serverauslastung </b></center></span></td> 
 </tr><tr><td width="100%" class="tablea">
 <center>
 <p>Webserver-Prozesse:</p>
 <table class="tableinborder" border="0" cellpadding="0" cellspacing="1" width="402">
   <tr>
        <td align="left" style="padding: 0px; background-image: url('<?=$GLOBALS["PIC_BASE_URL"]?>loadbarbg.gif'); background-repeat: repeat-x">

<?php 

    $percent = min(100, round(exec('ps ax | grep -c apache2') / 150 * 100));
    if ($percent <= 70) $pic = "loadbargreen.gif";
    elseif ($percent <= 90) $pic = "loadbaryellow.gif";
    else $pic = "loadbarred.gif";
    $width = $percent * 4;
    print("<img height=15 width=$width src=\"".$GLOBALS["PIC_BASE_URL"].$pic."\" alt='$percent%'>");


?>
      </td>
    </tr>
  </table>
  <p>Systemauslastung (Durchschnittswerte): <?php
  $loadavg = explode(" ", exec("cat /proc/loadavg"));
  echo $loadavg[0]*100, "% (1min) - ", $loadavg[1]*100, "% (5min) - ", $loadavg[2]*100, "% (15min)";
  
  ?></p>
  <p>Diese Seite wurde in <?php
    $now = gettimeofday();
    $runtime = ($now["sec"] - $RUNTIME_START["sec"]) + ($now["usec"] - $RUNTIME_START["usec"]) / 1000000;
    echo $runtime;
  ?> Sekunden erstellt.</p>
  </center>
</td></tr></table>

<?php
// stdfoot();

hit_end();
?>
