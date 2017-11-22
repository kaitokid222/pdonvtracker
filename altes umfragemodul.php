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