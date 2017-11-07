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

$id = intval($_GET["id"]);
if (!$id)
    stderr("Fehler", "Ungültige oder Fehlende ID.");

$res = mysql_query("SELECT `username`, `class`, `email`, `accept_email` FROM `users` WHERE `id`=$id");
$arr = mysql_fetch_assoc($res) or stderr("Fehler", "Der Benutzer existiert nicht.");

if ($id == $CURUSER["id"])
    stderr("Fehler", "Du kannst Dir nicht selber eine E-Mail senden.");

// Moderatoren und höhere User können immer Mails senden und empfangen!
if ($arr["class"] < UC_MODERATOR && $CURUSER["class"] < UC_MODERATOR) {
    if ($arr["accept_email"] ==  "no")
        stderr("Fehler", "Dieser Benutzer ist kein Team-Mitglied, und akzeptiert keine E-Mails.");
    
    if ($arr["accept_email"] ==  "friends") {
        $r = mysql_query("SELECT `id` FROM `friends` WHERE `userid`=".$id." AND `friendid`=".$CURUSER["id"]) or sqlerr(__FILE__,__LINE__);
        if (mysql_num_rows($r) == 0)
            stderr("Fehler", "Dieser Benutzer ist kein Team-Mitglied, und akzeptiert E-Mails nur von Freunden.");
    }
    
    $r = mysql_query("SELECT `id` FROM `blocks` WHERE `userid`=".$id." AND `blockid`=".$CURUSER["id"]) or sqlerr(__FILE__,__LINE__);
    if (mysql_num_rows($r) > 0)
        stderr("Fehler", "Dieser Benutzer ist kein Team-Mitglied, und akzeptiert von Dir keine E-Mails.");
}

$username = htmlspecialchars($arr["username"]);

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
	$to = $arr["email"];

	$from = substr(trim($_POST["from"]), 0, 80);
	if ($from == "") $from = "Anonym";

	$from_email = substr(trim($_POST["from_email"]), 0, 80);
	if ($from_email == "") $from_email = "noreply@example.com";
	if (!strpos($from_email, "@")) stderr("Fehler", "Die angegebene E-Mail-Adresse scheint ungültig zu sein.");

	$from = "$from <$from_email>";

	$subject = substr(trim($_POST["subject"]), 0, 80);
	if ($subject == "") $subject = "(Kein Betreff)";
	$subject = "FW: $subject";

	$message = trim($_POST["message"]);
	if ($message == "") stderr("Fehler", "Du musst eine Nachricht eingeben!");

	$message = "Nachricht gesendet von ".$_SERVER["REMOTE_ADDR"]." am " . date("Y-m-d H:i:s") . ".\n" .
		"Hinweis: Wenn Du auf diese Nachricht antwortest, gibst Du Deine E-Mail-Adresse preis.\n" .
		"---------------------------------------------------------------------\n\n" .
		$message . "\n\n" .
		"---------------------------------------------------------------------\n".$GLOBALS["SITENAME"]." E-Mail Gateway\n";

	$success = mail($to, $subject, $message, "From: $from");

	if ($success)
		stderr("Erfolg", "Die E-Mail wurde erfolgreich gesendet.");
	else
		stderr("Fehler", "Die Mail konnte nicht versendet werden. Entweder ist das Mail-System des Servers nicht verfügbar oder nicht korrekt konfiguriert.");
}

stdhead("E-Mail Gateway");
begin_frame("<img src=\"".$GLOBALS["PIC_BASE_URL"]."email.png\" width=\"22\" height=\"22\" alt=\"E-Mail\" style=\"vertical-align: middle;\"> Eine E-Mail an $username senden", FALSE, "600px");

?>
<form method="post" action="email-gateway.php?id=<?=$id?>">
<?php

begin_table(TRUE);

?>
<tr><td class="tableb">Dein Name</td><td class="tablea"><input type="text" name="from" size="40"></td></tr>
<tr><td class="tableb">Deine E-Mail</td><td class="tablea"><input type="text" name="from_email" size="40"></td></tr>
<tr><td class="tableb">Betreff</td><td class="tablea"><input type="text" name="subject" size="60"></td></tr>
<tr><td class="tableb">Nachricht</td><td class="tablea"><textarea name="message" cols="60" rows="10"></textarea></td></tr>
<tr><td class="tablea" colspan="2" style="text-align:center"><input type="submit" value="E-Mail senden" class="btn"></td></tr>
<?php
end_table();
?>
</form>
<p><b>Hinweis:</b> Deine IP-Adresse wird gespeichert, und ist für den Empfänger sichtbar, um Mißbrauch zu vermeiden.<br>
Stelle sicher, dass Du eine gültige E-Mail-Adresse angibst, falls Du eine Antwort erwartest.</p>
<?php

end_frame();
stdfoot();
?>