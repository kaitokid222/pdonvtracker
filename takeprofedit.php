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

hit_start();

function bark($msg) {
	genbark($msg, "Aktualisierung Deiner Daten fehlgeschlagen!");
}

dbconn();

hit_count();

loggedinorreturn();

if (isset($_POST["rulessubmit"])) {
    if ($_POST["acceptrules"] == "yes") {
	mysql_query("UPDATE `users` SET `accept_rules`='yes' WHERE `id`=".$CURUSER["id"]);
	stderr("Regeln akzeptiert!", "<p>Du hast die Regeln akzeptiert, und kannst nun den Tracker weiter verwenden.</p><p>Bitte halte Dich auch an die Regeln!</p>");
    } else
	bark("Du musst die geänderten Regeln akzeptieren, bevor Du diesen Tracker weiterhin verwenden darfst!");
}

if (!mkglobal("email:chpassword:passagain"))
	bark("missing form data");

// $set = array();

$updateset = array();
$changedemail = 0;

if ($chpassword != "") {
	if (strlen($chpassword) > 40)
		bark("Sorry, Dein Passwort ist zu lang (Maximal 40 Zeichen)");
	if ($chpassword != $passagain)
		bark("Die Passwörter stimmen nicht überein! Du musst Dich vertippt haben. bitte versuche es erneut!");

	$sec = mksecret();

        $passhash = md5($sec . $chpassword . $sec);

	$updateset[] = "secret = " . sqlesc($sec);
	$updateset[] = "passhash = " . sqlesc($passhash);
	logincookie($CURUSER["id"], $passhash);
}

if ($email != $CURUSER["email"]) {
	if (!validemail($email))
		bark("Das scheint keine gültige E-Mail Adresse zu sein.");
  $r = mysql_query("SELECT id FROM users WHERE email=" . sqlesc($email)) or sqlerr();
	if (mysql_num_rows($r) > 0)
		bark("Die E-Mail Adresse $email wird bereits verwendet.");
	$changedemail = 1;
}

$acceptpms = $_POST["acceptpms"];
$deletepms = ($_POST["deletepms"] != "" ? "yes" : "no");
$savepms = ($_POST["savepms"] != "" ? "yes" : "no");
$hideuseruploads = ($_POST["useruploads"] == "yes" ? "yes" : "no");
$wgeturl = ($_POST["wgeturl"] == "yes" ? "yes" : "no");
$acceptemails = $_POST["acceptemails"];
$pmnotif = $_POST["pmnotif"];
$emailnotif = $_POST["emailnotif"];
$notifs = ($pmnotif == 'yes' ? "[pm]" : "");
$notifs .= ($emailnotif == 'yes' ? "[email]" : "");
$r = mysql_query("SELECT id FROM categories") or sqlerr();
$rows = mysql_num_rows($r);
for ($i = 0; $i < $rows; ++$i)
{
	$a = mysql_fetch_assoc($r);
	if ($_POST["cat$a[id]"] == 'yes')
	  $notifs .= "[cat$a[id]]";
}
$avatar = $_POST["avatar"];
$statbox = $_POST["statbox"];
$rstats = ($_POST["log_ratio"] != "" ? "yes" : "no");
$avatars = ($_POST["avatars"] != "" ? "yes" : "no");
$chpasskey = ($_POST["chpasskey"] == "1" ? "yes" : "no");
// $ircnick = $_POST["ircnick"];
// $ircpass = $_POST["ircpass"];
$info = $_POST["info"];
$stylesheet = $_POST["stylesheet"];
$country = $_POST["country"];
//$timezone = 0 + $_POST["timezone"];
//$dst = ($_POST["dst"] != "" ? "yes" : "no");

/*
if ($privacy != "normal" && $privacy != "low" && $privacy != "strong")
	bark("whoops");

$updateset[] = "privacy = '$privacy'";
*/

$updateset[] = "oldtorrentlist = " . ($_POST["torrentlist"]=="old"?"'yes'":"'no'");
$updateset[] = "torrentsperpage = " . min(100, 0 + $_POST["torrentsperpage"]);
$updateset[] = "topicsperpage = " . min(100, 0 + $_POST["topicsperpage"]);
$updateset[] = "postsperpage = " . min(100, 0 + $_POST["postsperpage"]);

if (is_valid_id($stylesheet))
  $updateset[] = "stylesheet = '$stylesheet'";
if (is_valid_id($country))
  $updateset[] = "country = $country";

//$updateset[] = "timezone = $timezone";
//$updateset[] = "dst = '$dst'";
$updateset[] = "info = " . sqlesc($info);
$updateset[] = "acceptpms = " . sqlesc($acceptpms);
$updateset[] = "deletepms = '$deletepms'";
$updateset[] = "savepms = '$savepms'";
$updateset[] = "accept_email = " . sqlesc($acceptemails);
$updateset[] = "notifs = '$notifs'";
$updateset[] = "avatar = " . sqlesc($avatar);
$updateset[] = "log_ratio = '$rstats'";
$updateset[] = "statbox = " . sqlesc($statbox);;
$updateset[] = "avatars = '$avatars'";
$updateset[] = "hideuseruploads = '$hideuseruploads'";
$updateset[] = "wgeturl = '$wgeturl'";

if ($chpasskey == "yes") {
    $updateset[] = "passkey = " . sqlesc(mksecret(8));
    write_log("passkeyreset", "PassKey wurde von Benutzer '<a href=\"userdetails.php?id=".$CURUSER["id"]."\">".$CURUSER["username"]."</a>' zurückgesetzt.");
}

/* ****** */

$urladd = "";

if ($changedemail) {
	$sec = mksecret();
	$hash = md5($sec . $email . $sec);
	$obemail = urlencode($email);
	$updateset[] = "editsecret = " . sqlesc($sec);
	$thishost = $_SERVER["_HOST"];
	$thisdomain = preg_replace('/^www\./is', "", $thishost);
	$body = <<<EOD
Du hast in Auftrag gegeben, dass Dein Profil (Benutzername {$CURUSER["username"]})
auf $thisdomain mit dieser E-Mail Adresse ($email) als Kontaktadresse aktualisiert
werden soll.

Wenn Du dies nicht beauftragt hast, ignoriere bitte diese Mail. Die Person, die
Deine E-Mail Adresse eingegeben hat, hatte die IP-Adresse {$_SERVER["REMOTE_ADDR"]}.
Bitte antworte nicht auf diese automatisch generierte Nachricht.

Um die Aktualisierung Deines Profils abzuschließen, klicke auf folgenden Link:

http://$thishost/confirmemail.php?id={$CURUSER["id"]}&secret=$hash&email=$obemail

Die neue E-Mail Adresse wird dann in Deinem Profil erscheinen. Wenn Du
diesen Link nicht anklickst, wird Dein Profil unverändert bleiben.
EOD;

	mail($email, "$thisdomain Profiländerungsbestätigung", $body, "From: ".$GLOBALS["SITEEMAIL"]);

	$urladd .= "&mailsent=1";
}

mysql_query("UPDATE users SET " . implode(",", $updateset) . " WHERE id = " . $CURUSER["id"]) or sqlerr(__FILE__,__LINE__);

// Session aktualisieren
session_unset();
$_SESSION["userdata"] = mysql_fetch_assoc(mysql_query("SELECT * FROM `users` WHERE `id` = " . $CURUSER["id"]));

header("Location: $BASEURL/my.php?edited=1&".SID.$urladd);

hit_end();

?>