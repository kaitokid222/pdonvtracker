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
dbconn();

session_start();


    $res = mysql_query("SELECT COUNT(*) FROM users") or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_row($res);
    if ($arr[0] >= $GLOBALS["MAX_USERS"]) {
            $_SESSION["proofcode"] = "";
            stderr("Sorry", "Das aktuelle Benutzerlimit (" . number_format($GLOBALS["MAX_USERS"]) . ") wurde erreicht. Inactive Accounts werden regelmäßig gelöscht, versuche es also einfach später nochmal...");
    }


stdhead("Anmeldung");


?>
<form method="post" action="takesignup.php">
<input type="hidden" name="proofcode" value="<?=$_SESSION["proofcode"]?>" />
<?php

    begin_frame("Neuen Account erstellen", FALSE, "650px");
?>
<p>Hinweis: Du musst Cookies akzeptieren, um Dich Anmelden und Einloggen zu k&ouml;nnen!</p>
<table border="0" cellspacing="1" cellpadding="4" class="tableinborder">
<tr><td align="right" class="tableb">Gew&uuml;nschter Benutzername:</td><td class="tablea" align=left><input type="text" size="20" name="wantusername" /></td></tr>
<tr><td align="right" class="tableb">W&auml;hle ein Passwort:</td><td class="tablea" align=left><input type="password" size="20" name="wantpassword" /></td></tr>
<tr><td align="right" class="tableb">Gebe Dein Passwort erneut ein:</td><td class="tablea" align=left><input type="password" size="20" name="passagain" /></td></tr>
<tr valign=top><td align="right" class="tableb">E-Mail Adresse:</td><td class="tablea" align=left><input type="text" size="30" name="email" />
<table width=350 border=0 cellspacing=0 cellpadding=0><tr><td><font class=small>Die angegebene E-Mail Adresse muss g&uuml;ltig sein.
Du wirst eine Best&auml;tigungsmail von uns erhalten, auf die Du antworten musst. Deine E-Mail Adresse wird nirdgendwo auf dem Tracker &ouml;ffentlich angezeigt.</td></tr>
</font></td></tr></table>
</td></tr>
<tr><td align="right" class="tableb"></td><td class="tablea" align=left><input type=checkbox name=rulesverify value=yes> Ich habe die Regeln gelesen.<br>
<input type=checkbox name=faqverify value=yes> Ich werde die FAQ lesen, bevor ich Fragen an einen Moderator oder Admin stelle.<br>
<input type=checkbox name=ageverify value=yes> Ich bin mindestens 13 Jahre alt.</td></tr>

<p style="text-align:center"><input type=submit value="Anmelden! (NUR EINMAL KLICKEN)" style='height: 25px'></p>
</table>
<?php
end_frame();
?>
</form>
<?php
stdfoot();
?>
