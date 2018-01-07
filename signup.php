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

function bark($msg){
	stderr("Registrierung fehlgeschlagen!", $msg);
}

function validusername($username){
	if($username == "")
		return false;
	// The following characters are allowed in user names
	$allowedchars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	for($i = 0; $i < strlen($username); ++$i)
		if(strpos($allowedchars, $username[$i]) === false)
			return false;
	return true;
}

$registered = number_format($database->row_count('users'));
if($registered >= $GLOBALS["MAX_USERS"])
	stderr("Sorry", "Das aktuelle Benutzerlimit (" . number_format($GLOBALS["MAX_USERS"]) . ") wurde erreicht. Inactive Accounts werden regelmäßig gelöscht, versuche es also einfach später nochmal...");

if($_SERVER["REQUEST_METHOD"] == "POST"){
	foreach(explode(":","wantusername:wantpassword:passagain:email") as $k){
		if(!isset($_POST[$k]))
			bark("Eingaben fehlen!");
		else
			$$k = $_POST[$k];
	}

	if(empty($wantusername) || empty($wantpassword) || empty($email))
		bark("Du musst alle Felder ausfüllen.");

	if(strlen($wantusername) > 12)
		bark("Sorry, Dein Benutzername ist zu lang (Maximum sind 12 Zeichen)");

	if($wantpassword != $passagain)
		bark("Die Passwörter stimmen nicht überein! Du musst Dich vertippt haben. bitte versuche es erneut!");

	if(strlen($wantpassword) < 6)
		bark("Sorry, Dein Passwort ist zu kurz (Mindestens 6 Zeichen)");

	if(strlen($wantpassword) > 40)
		bark("Sorry, Dein Passwort ist zu lang (Maximal 40 Zeichen)");

	if($wantpassword == $wantusername)
		bark("Sorry, Dein Passwort darf nicht mit Deinem Benutzernamen identisch sein.");

	if(!validemail($email))
		bark("Die E-Mail Adresse sieht nicht so aus, als ob sie gültig wäre.");

	if(!validusername($wantusername))
		bark("Ungültiger Benutzername.");

	// make sure user agrees to everything...
	if($_POST["rulesverify"] != "yes" || $_POST["faqverify"] != "yes" || $_POST["ageverify"] != "yes")
		bark("Sorry, aber Du bist nicht dafür qualifiziert, ein Mitglied dieser Seite zu werden.");

	$con = "email='" . $email . "' OR username='" . $wantusername . "'";
	$c = number_format($database->row_count("users", $con));
	if($c != 0)
		bark("Der Username oder die E-Mail Adresse werden schon verwendet.");

	// Trash-/Freemail Anbieter sind nicht gewünscht.
	foreach($GLOBALS["EMAIL_BADWORDS"] as $badword){
		if(preg_match("/".preg_quote($badword)."/i", $email))
			bark("Diese E-Mail Adresse kann nicht für eine Anmeldung an diesem Tracker verwendet werden. Wir akzeptieren keine Wegwerf-Mailadressen!");
	}

	$secret = mksecret();
	$wantpasshash = md5($secret . $wantpassword . $secret);
	$editsecret = mksecret();
	$passkey = mksecret(8);
	$qry = $GLOBALS['DB']->prepare("SELECT `id` FROM `stylesheets` WHERE `default`='yes'");
	$qry->execute();
	if($qry->rowCount() > 0){
		$row = $qry->fetchObject();
		$stylesheet = $row->id;
	}else
		$stylesheet = 1;
	$dt = get_date_time();
	$status = "pending";
	$res = user::addUser($wantusername,$wantpasshash,$passkey,$secret,$editsecret,$email,$status,$stylesheet,$dt);
	if($res === false)
		bark("db-error");
	$psecret = md5($editsecret);
	$body = "Du oder jemand anderes hat auf " . $GLOBALS["SITENAME"] . " einen neuen Account erstellt und diese E-Mail Adresse (" . $email . ") dafür verwendet.\n\n ".
		"Wenn Du den Account nicht erstellt hast, ignoriere diese Mail. In diesem Falle wirst Du von uns keine weiteren Nachrichten mehr erhalten. Die Person,  ".
		"die Deine E-Mail Adresse benutzt hat, hatte die IP-Adresse " . $_SERVER["REMOTE_ADDR"] . ". Bitte antworte nicht auf diese automatisch erstellte Nachricht.\n\n ".
		"Um die Anmeldung zu bestätigen, folge bitte dem folgenden Link: " . $DEFAULTBASEURL . "/confirm.php?id=" . $res . "&secret=" . $psecret . "\n\n".
		"Wenn du dies getan hast, wirst Du in der Lage sein, Deinen neuen Account zu verwenden. Wenn die Aktivierung fehlschlägt, oder Du diese nicht vornimmst, wird ".
		"Dein Account innerhalb der nächsten Tage wieder gelöscht. Wir empfehlen Dir dringlichst, die Regeln und die FAQ zu lesen, bevor Du unseren Tracker verwendest.";
	mail($email, $GLOBALS["SITENAME"]." Anmeldebestätigung", $body, "From: ".$GLOBALS["SITEEMAIL"]);
	header("Refresh: 0; url=ok.php?type=signup&email=" . urlencode($email));
}

stdhead("Anmeldung");
begin_frame("Neuen Account erstellen", FALSE, "680px");
echo "<form method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "\">\n".
	"<p>Hinweis: Du musst Cookies akzeptieren, um Dich Anmelden und Einloggen zu k&ouml;nnen!</p>\n".
	"<table border=\"0\" cellspacing=\"1\" cellpadding=\"4\" class=\"tableinborder\" width=\"100%\">\n".
	"    <tr>\n".
	"        <td align=\"right\" class=\"tableb\">Gew&uuml;nschter Benutzername:</td>\n".
	"        <td class=\"tablea\" align=\"left\"><input type=\"text\" size=\"20\" name=\"wantusername\" /></td>\n".
	"    </tr>\n".
	"    <tr>\n".
	"        <td align=\"right\" class=\"tableb\">W&auml;hle ein Passwort:</td>\n".
	"        <td class=\"tablea\" align=\"left\"><input type=\"password\" size=\"20\" name=\"wantpassword\" /></td>\n".
	"    </tr>\n".
	"    <tr>\n".
	"        <td align=\"right\" class=\"tableb\">Gebe Dein Passwort erneut ein:</td>\n".
	"        <td class=\"tablea\" align=\"left\"><input type=\"password\" size=\"20\" name=\"passagain\" /></td>\n".
	"    </tr>\n".
	"    <tr valign=\"top\">\n".
	"        <td align=\"right\" class=\"tableb\">E-Mail Adresse:</td>\n".
	"        <td class=\"tablea\" align=\"left\"><input type=\"text\" size=\"30\" name=\"email\" />\n".
	"            <table width=\"350\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n".
	"                <tr>\n".
	"                    <td><font class=\"small\">Die angegebene E-Mail Adresse muss g&uuml;ltig sein. Du wirst eine Best&auml;tigungsmail von uns erhalten, auf die Du antworten musst. Deine E-Mail Adresse wird nirdgendwo auf dem Tracker &ouml;ffentlich angezeigt.</font></td>\n".
	"                </tr>\n".
	"            </table>\n".
	"        </td>\n".
	"    </tr>\n".
	"    <tr>\n".
	"        <td class=\"tableb\"></td>\n".
	"        <td class=\"tablea\" align=\"left\">".
	"<input type=\"checkbox\" name=\"rulesverify\" value=\"yes\"> Ich habe die Regeln gelesen.<br>".
	"<input type=\"checkbox\" name=\"faqverify\" value=\"yes\"> Ich werde die FAQ lesen, bevor ich Fragen an einen Moderator oder Admin stelle.<br>".
	"<input type=\"checkbox\" name=\"ageverify\" value=\"yes\"> Ich bin mindestens 18 Jahre alt.".
	"</td>\n".
	"    </tr>\n".
	"    <tr>\n".
	"        <td align=\"center\" colspan=\"2\" class=\"tableb\"><p style=\"text-align:center\"><input type=\"submit\" value=\"Anmelden! (NUR EINMAL KLICKEN)\" style=\"height: 25px\"></p></td>\n".
	"    </tr>\n".
	"</table>\n".
	"</form>\n";
end_frame();
stdfoot();
?>