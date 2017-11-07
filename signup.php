<?

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

if ($_SESSION["proofcode"] != "" && $_POST["proofcode"] != "" && strtolower($_POST["proofcode"]) == strtolower($_SESSION["proofcode"]))
    $code_ok = TRUE;
else
    $code_ok = FALSE;

if ($code_ok) {
    $res = mysql_query("SELECT COUNT(*) FROM users") or sqlerr(__FILE__, __LINE__);
    $arr = mysql_fetch_row($res);
    if ($arr[0] >= $GLOBALS["MAX_USERS"]) {
            $_SESSION["proofcode"] = "";
            stderr("Sorry", "Das aktuelle Benutzerlimit (" . number_format($GLOBALS["MAX_USERS"]) . ") wurde erreicht. Inactive Accounts werden regelmäßig gelöscht, versuche es also einfach später nochmal...");
    }
} else {
    /* Zufallsgenerator initialisieren */
    srand(microtime()*360000);
    
    /* Im Code benutzte Zeichen */
    $chars = "ABCDEFGHIJKLMNOPSTUWXYZ";
    
    /* Zeichenfolge generieren */
    $_SESSION["proofcode"] = "";
    for ($I=0; $I<6; $I++) $_SESSION["proofcode"] .= $chars[rand(0, strlen($chars)-1)];
}

stdhead("Anmeldung");

if ($code_ok) {
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
</table>
<?php
end_frame();
begin_frame("Fragebogen zu FAQ und Regeln", FALSE, "650px");

// Sieben Fragen per Zufall auswählen
$questions = mysql_query("SELECT * FROM `test` ORDER BY RAND() LIMIT 7");

?>
<p>Um die Registrierung abschließen zu können, musst Du noch einige Fragen zu
den <a href="rules.php">Regeln</a> und den <a href="faq.php">FAQ</a> beantworten.
Da Du auf diesem Tracker wegen Missachtung der Regeln gebannt werden kannst,
solltest Du Dir die Regeln in eigenem Interesse durchlesen. Das FAQ enthält auch
nützliche Informationen zu den meistgestellten Fragen.</p>
<?

while ($qdata = mysql_fetch_assoc($questions)) {
    begin_table(true);
    $antworten = unserialize($qdata["answers"]);
    // Auch Antworten in zufälliger Reihenfolge zeigen
    shuffle($antworten);
    echo "<tr><td class=\"tablecat\"><b>" . htmlspecialchars($qdata["question"]) . "</b></td></tr>\n";
    echo "<tr><td class=\"tablea\">\n<table cellspacing=\"2\" cellpadding=\"0\" border=\"0\">\n";
    for ($I=0; $I<count($antworten); $I++) {
        echo "<tr>\n<td>" . ($I+1) . ".&nbsp;&nbsp;\n";
        if ($qdata["type"] == "radio")
            echo "<td><input type=\"" . $qdata["type"] . "\" id=\"ch" . $qdata["id"] . "x" . ($I+1) . "\" name=\"choice[" .
                $qdata["id"] . "]\" value=\"" . $antworten[$I]["id"] . "\"></td>\n";
        else
            echo "<td><input type=\"" . $qdata["type"] . "\" id=\"ch" . $qdata["id"] . "x" . ($I+1) . "\" name=\"choice[" .
                $qdata["id"] . "][" . $antworten[$I]["id"] . "]\" value=\"1\"></td>\n";
        echo "<td><label for=\"ch" . $qdata["id"] . "x" . ($I+1) . "\">" . htmlspecialchars($antworten[$I]["answer"]) .
            "</label></td>\n</tr>\n";
    }
    echo "</table>\n</td></tr>\n";
    end_table();
}
?>
<p style="text-align:center"><input type=submit value="Anmelden! (NUR EINMAL KLICKEN)" style='height: 25px'></p>
</form>
<?
end_frame();

?>
</form>
<?
} else {
    begin_frame("Neuen Account erstellen", FALSE, "650px");
?>
<p>Hinweis: Du musst Cookies akzeptieren, um Dich Anmelden und Einloggen zu k&ouml;nnen!</p>
<p>Um die Anmeldung durchführen zu können, musst Du zuerst einen Prüfungscode eingeben.
Durch die Angabe dieses Prüfcodes wird das Verwenden automatischer Registrierungstools
verhindert. Nach der Eingabe des Codes kannst Du mit der Anmeldung fortfahren, sofern
Accounts frei sind.</p>
<p>Groß-/Kleinschreibung spielt keine Rolle. Wenn Du den Code nicht lesen kannst, lade die Seite erneut.</p>
<form method="post" action="signup.php">
<center>
<? begin_table(); ?>
<tr><td class="tablea"><img src="proofimg.php?<?=SID?>" width="300" height="80" alt="Der Browser muss Grafiken anzeigen können, um die Anmeldung durchzuführen!"></td></tr>
<tr><td class="tablea">Code eingeben: <input type="text" size="20" name="proofcode" /></td></tr>
<tr><td class="tableb" style="text-align:center"><input type="submit" value="Code prüfen" /></td></tr>
<? end_table(); ?>
</center>
</form>
<?
}
end_frame();
stdfoot();

?>
