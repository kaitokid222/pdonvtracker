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

hit_start();

dbconn();

$res = mysql_query("SELECT COUNT(*) FROM users") or sqlerr(__FILE__, __LINE__);
$arr = mysql_fetch_row($res);
if ($arr[0] >= $GLOBALS["MAX_USERS"])
	stderr("Fehler", "Sorry, das Benutzerlimit wurde erreicht. Bitte versuche es später erneut.");

if (!mkglobal("wantusername:wantpassword:passagain:email"))
	die();

function bark($msg) {
  stdhead();
	stdmsg("Registrierung fehlgeschlagen!", $msg);
  stdfoot();
  exit;
}

function validusername($username)
{
	if ($username == "")
	  return false;

	// The following characters are allowed in user names
	$allowedchars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

	for ($i = 0; $i < strlen($username); ++$i)
	  if (strpos($allowedchars, $username[$i]) === false)
	    return false;

	return true;
}

function isportopen($port)
{
	global $_SERVER;
	$sd = @fsockopen($_SERVER["REMOTE_ADDR"], $port, $errno, $errstr, 1);
	if ($sd)
	{
		fclose($sd);
		return true;
	}
	else
		return false;
}

function isproxy()
{
	$ports = array(80, 88, 1075, 1080, 1180, 1182, 2282, 3128, 3332, 5490, 6588, 7033, 7441, 8000, 8080, 8085, 8090, 8095, 8100, 8105, 8110, 8888, 22788);
	for ($i = 0; $i < count($ports); ++$i)
		if (isportopen($ports[$i])) return true;
	return false;
}

session_start();

//if ($_SESSION["proofcode"] == "" || $_POST["proofcode"] == "" || strtolower($_POST["proofcode"]) != strtolower($_SESSION["proofcode"]))
//        bark("Der Anmeldungscode ist ungültig.");

if (empty($wantusername) || empty($wantpassword) || empty($email))
	bark("Du musst alle Felder ausfüllen.");

if (strlen($wantusername) > 12)
	bark("Sorry, Dein Benutzername ist zu lang (Maximum sind 12 Zeichen)");

if ($wantpassword != $passagain)
	bark("Die Passwörter stimmen nicht überein! Du musst Dich vertippt haben. bitte versuche es erneut!");

if (strlen($wantpassword) < 6)
	bark("Sorry, Dein Passwort ist zu kurz (Mindestens 6 Zeichen)");

if (strlen($wantpassword) > 40)
	bark("Sorry, Dein Passwort ist zu lang (Maximal 40 Zeichen)");

if ($wantpassword == $wantusername)
	bark("Sorry, Dein Passwort darf nicht mit Deinem Benutzernamen identisch sein.");

if (!validemail($email))
	bark("Die E-Mail Adresse sieht nicht so aus, als ob sie gültig wäre.");

if (!validusername($wantusername))
	bark("Ungültiger Benutzername.");

// make sure user agrees to everything...
if ($_POST["rulesverify"] != "yes" || $_POST["faqverify"] != "yes" || $_POST["ageverify"] != "yes")
	stderr("Anmeldung fehlgeschlagen", "Sorry, aber Du bist nicht dafür qualifiziert, ein Mitglied dieser Seite zu werden.");

// check if email addy is already in use
$a = (@mysql_fetch_row(@mysql_query("select count(*) from users where email='$email'"))) or die(mysql_error());
if ($a[0] != 0)
  bark("Die E-Mail Adresse $email wird schon verwendet.");

// Trash-/Freemail Anbieter sind nicht gewünscht.
foreach ($GLOBALS["EMAIL_BADWORDS"] as $badword) {
    if (preg_match("/".preg_quote($badword)."/i", $email))
	stderr("Anmeldung fehlgeschlagen", "Diese E-Mail Adresse kann nicht für eine Anmeldung an diesem Tracker verwendet werden. Wir akzeptieren keine Wegwerf-Mailadressen!");
}

/*
// do simple proxy check
if (isproxy())
	bark("You appear to be connecting through a proxy server. Your organization or ISP may use a transparent caching HTTP proxy. Please try and access the site on <a href=http://torrentbits.org:81/signup.php>port 81</a> (this should bypass the proxy server). <p><b>Note:</b> if you run an Internet-accessible web server on the local machine you need to shut it down until the sign-up is complete.");
*/
hit_count();

$secret = mksecret();
$wantpasshash = md5($secret . $wantpassword . $secret);
$editsecret = mksecret();
$passkey = mksecret(8);

$arr = mysql_fetch_assoc(mysql_query("SELECT `id` FROM `stylesheets` WHERE `default`='yes'"));
$stylesheet = $arr["id"];

$ret = mysql_query("INSERT INTO users (username, passhash, passkey, secret, editsecret, email, status, stylesheet, added) VALUES (" .
		implode(",", array_map("sqlesc", array($wantusername, $wantpasshash, $passkey, $secret, $editsecret, $email, 'pending', $stylesheet))) .
		",'" . get_date_time() . "')");

if (!$ret) {
	if (mysql_errno() == 1062)
		bark("Der Benutzername existiert bereits!");
	bark("borked");
}

$id = mysql_insert_id();

//write_log("User account $id ($wantusername) was created");

$psecret = md5($editsecret);

$body = <<<EOD
Du oder jemand anderes hat auf {$GLOBALS["SITENAME"]} einen neuen Account erstellt und
diese E-Mail Adresse ($email) dafür verwendet.

Wenn Du den Account nicht erstellt hast, ignoriere diese Mail. In diesem
Falle wirst Du von uns keine weiteren Nachrichten mehr erhalten. Die
Person, die Deine E-Mail Adresse benutzt hat, hatte die IP-Adresse
{$_SERVER["REMOTE_ADDR"]}. Bitte antworte nicht auf diese automatisch
erstellte Nachricht

Um die Anmeldung zu bestätigen, folge bitte dem folgenden Link:

$DEFAULTBASEURL/confirm.php?id=$id&secret=$psecret

Wenn du dies getan hast, wirst Du in der Lage sein, Deinen neuen Account zu
verwenden. Wenn die Aktivierung fehlschlägt, oder Du diese nicht vornimmst,
wird Dein Account innerhalb der nächsten Tage wieder gelöscht.
Wir empfehlen Dir dringlichst, die Regeln und die FAQ zu lesen, bevor Du
unseren Tracker verwendest.
EOD;
mail($email, $GLOBALS["SITENAME"]." Anmeldebestätigung", $body, "From: ".$GLOBALS["SITEEMAIL"]);

header("Refresh: 0; url=ok.php?type=signup&email=" . urlencode($email));

hit_end();

?>
