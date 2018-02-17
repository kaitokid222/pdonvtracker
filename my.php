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

userlogin();
loggedinorreturn();

if($_SERVER["REQUEST_METHOD"] == "POST"){
	if(isset($_POST["rulessubmit"])){
		if(isset($_POST["acceptrules"]) && $_POST["acceptrules"] == "yes"){
			$sql = "UPDATE `users` SET `accept_rules`='yes' WHERE `id`= :id";
			$qry = $GLOBALS['DB']->prepare($sql);
			$qry->bindParam(':id', $CURUSER["id"], PDO::PARAM_INT);
			$qry->execute();
			user::refreshSession($CURUSER["id"]);
			stderr("Regeln akzeptiert!", "<p>Du hast die Regeln akzeptiert, und kannst nun den Tracker weiter verwenden.</p><p>Bitte halte Dich auch an die Regeln!</p>");
		}else
			bark("Du musst die geänderten Regeln akzeptieren, bevor Du diesen Tracker weiterhin verwenden darfst!");
	}

	if(!mkglobal("email:chpassword:passagain"))
		bark("missing form data");

	$updateset = array();
	$changedemail = 0;

	if($chpassword != ""){
		if(strlen($chpassword) > 40)
			bark("Sorry, Dein Passwort ist zu lang (Maximal 40 Zeichen)");
		if($chpassword != $passagain)
			bark("Die Passwörter stimmen nicht überein! Du musst Dich vertippt haben. bitte versuche es erneut!");
		$sec = mksecret();
		$passhash = md5($sec . $chpassword . $sec);
		$updateset[] = "secret = '" . $sec . "'";
		$updateset[] = "passhash = '" . $passhash . "'";
		logincookie($CURUSER["id"], $passhash);
	}

	if($email != $CURUSER["email"]){
		if(!validemail($email))
			bark("Das scheint keine gültige E-Mail Adresse zu sein.");
		$c = $database->row_count("users","email=" . $email);
		if($c > 0)
			bark("Die E-Mail Adresse " . $email . " wird bereits verwendet.");
		$changedemail = 1;
	}

	$_POST["acceptpms"] = ((isset($_POST["acceptpms"])) ? $_POST["acceptpms"] : "");
	$acceptpms = $_POST["acceptpms"];
	$_POST["deletepms"] = ((isset($_POST["deletepms"])) ? $_POST["deletepms"] : "");
	$deletepms = (($_POST["deletepms"] != "") ? "yes" : "no");
	$_POST["savepms"] = ((isset($_POST["savepms"])) ? $_POST["savepms"] : "");
	$savepms = (($_POST["savepms"] != "") ? "yes" : "no");
	$_POST["useruploads"] = ((isset($_POST["useruploads"])) ? $_POST["useruploads"] : "");
	$hideuseruploads = (($_POST["useruploads"] == "yes") ? "yes" : "no");
	$_POST["wgeturl"] = ((isset($_POST["wgeturl"])) ? $_POST["wgeturl"] : "");
	$wgeturl = (($_POST["wgeturl"] == "yes") ? "yes" : "no");
	$_POST["acceptemails"] = ((isset($_POST["acceptemails"])) ? $_POST["acceptemails"] : "");
	$acceptemails = $_POST["acceptemails"];
	$_POST["pmnotif"] = ((isset($_POST["pmnotif"])) ? $_POST["pmnotif"] : "");
	$pmnotif = $_POST["pmnotif"];
	$_POST["emailnotif"] = ((isset($_POST["emailnotif"])) ? $_POST["emailnotif"] : "");
	$emailnotif = $_POST["emailnotif"];

	$notifs = (($pmnotif == "yes") ? "[pm]" : "");
	$notifs .= (($emailnotif == "yes") ? "[email]" : "");

	$sql = "SELECT id FROM categories";
	$qry = $GLOBALS['DB']->prepare($sql);
	$qry->execute();
	$arr = $qry->FetchAll(PDO::FETCH_ASSOC);
	foreach($arr as $a){
		$_POST["cat" . $a["id"]] = ((isset($_POST["cat" . $a["id"]])) ? $_POST["cat" . $a["id"]] : "");
		if($_POST["cat" . $a["id"]] == "yes")
			$notifs .= "[cat" . $a["id"] . "]";
	}
	$_POST["avatar"] = ((isset($_POST["avatar"])) ? $_POST["avatar"] : "");
	$avatar = $_POST["avatar"];
	$_POST["statbox"] = ((isset($_POST["statbox"])) ? $_POST["statbox"] : "");
	$statbox = $_POST["statbox"];
	$_POST["log_ratio"] = ((isset($_POST["log_ratio"])) ? $_POST["log_ratio"] : "");
	$rstats = (($_POST["log_ratio"] != "") ? "yes" : "no");
	$_POST["avatars"] = ((isset($_POST["avatars"])) ? $_POST["avatars"] : "");
	$avatars = (($_POST["avatars"] != "") ? "yes" : "no");
	$_POST["chpasskey"] = ((isset($_POST["chpasskey"])) ? $_POST["chpasskey"] : "");
	$chpasskey = (($_POST["chpasskey"] == "1") ? "yes" : "no");
	$_POST["info"] = ((isset($_POST["info"])) ? $_POST["info"] : "");
	$info = $_POST["info"];
	$_POST["stylesheet"] = ((isset($_POST["stylesheet"])) ? $_POST["stylesheet"] : "");
	$stylesheet = $_POST["stylesheet"];
	$_POST["country"] = ((isset($_POST["country"])) ? $_POST["country"] : "");
	$country = $_POST["country"];

	$updateset[] = "oldtorrentlist = '" . (($_POST["torrentlist"] == "old") ? "yes" : "no") . "'";
	$updateset[] = "torrentsperpage = '" . min(100, 0 + $_POST["torrentsperpage"]) . "'";
	$updateset[] = "topicsperpage = '" . min(100, 0 + $_POST["topicsperpage"]) . "'";
	$updateset[] = "postsperpage = '" . min(100, 0 + $_POST["postsperpage"]) . "'";

	if(is_valid_id($stylesheet))
		$updateset[] = "stylesheet = '" . $stylesheet . "'";
	if(is_valid_id($country))
		$updateset[] = "country = '" . $country . "'";

	$updateset[] = "info = '" . $info . "'";
	$updateset[] = "acceptpms = '" . $acceptpms . "'";
	$updateset[] = "deletepms = '" . $deletepms . "'";
	$updateset[] = "savepms = '" . $savepms . "'";
	$updateset[] = "accept_email = '" . $acceptemails . "'";
	$updateset[] = "notifs = '" . $notifs . "'";
	$updateset[] = "avatar = '" . $avatar . "'";
	$updateset[] = "log_ratio = '" . $rstats . "'";
	$updateset[] = "statbox = '" . $statbox . "'";
	$updateset[] = "avatars = '" . $avatars . "'";
	$updateset[] = "hideuseruploads = '" . $hideuseruploads . "'";
	$updateset[] = "wgeturl = '" . $wgeturl . "'";

	if($chpasskey == "yes"){
		$updateset[] = "passkey = '" . mksecret(8) . "'";
		write_log("passkeyreset", "PassKey wurde von Benutzer '<a href=\"userdetails.php?id=".$CURUSER["id"]."\">".$CURUSER["username"]."</a>' zurückgesetzt.");
	}

	$urladd = "";
	if($changedemail){
		$sec = mksecret();
		$hash = md5($sec . $email . $sec);
		$obemail = urlencode($email);
		$updateset[] = "editsecret = '" . $sec . "'";
		$thishost = $_SERVER["_HOST"];
		$thisdomain = preg_replace('/^www\./is', "", $thishost);
		$body = "Du hast in Auftrag gegeben, dass Dein Profil (Benutzername " . $CURUSER["username"] . ")auf " . $thisdomain . " mit dieser E-Mail Adresse (" . $email . ") als Kontaktadresse aktualisiert werden soll.".
			"\n\nWenn Du dies nicht beauftragt hast, ignoriere bitte diese Mail. Die Person, die Deine E-Mail Adresse eingegeben hat, hatte die IP-Adresse " . $_SERVER["REMOTE_ADDR"] . ".".
			"\n\nBitte antworte nicht auf diese automatisch generierte Nachricht.".
			"Um die Aktualisierung Deines Profils abzuschließen, klicke auf folgenden Link:".
			"\n\nhttp://" . $thishost . "/confirmemail.php?id=" . $CURUSER["id"] . "&secret=" . $hash . "&email=" . $obemail .
			"\n\nDie neue E-Mail Adresse wird dann in Deinem Profil erscheinen. Wenn Du diesen Link nicht anklickst, wird Dein Profil unverändert bleiben.";
		mail($email, $thisdomain . " Profiländerungsbestätigung", $body, "From: " . $GLOBALS["SITEEMAIL"]);
		$urladd .= "&mailsent=1";
	}

	$sql = "UPDATE users SET " . implode(", ", $updateset) . " WHERE id= :uid";
	$qry = $GLOBALS['DB']->prepare($sql);
	$qry->bindParam(':uid', $CURUSER["id"], PDO::PARAM_INT);
	$qry->execute();
	// Session aktualisieren
	user::refreshSession($CURUSER["id"]);
	header("Location: " . $BASEURL . "/my.php?edited=1".$urladd);
}

$messages = $database->row_count("messages","receiver=" . $CURUSER["id"] . " AND folder_in<>0");
$unread = $database->row_count("messages","receiver=" . $CURUSER["id"] . " AND folder_in<>0 AND unread='yes'");
$outmessages = $database->row_count("messages","sender=" . $CURUSER["id"] . " AND folder_out<>0");

$t_out = "";
if(isset($_GET["edited"])){
	$t_out .= "Dein Profil wurde aktualisiert!";
	if(isset($_GET["mailsent"]))
		$t_out .= "<br>Best&auml;tigungsmail wurde versandt!";
}elseif(isset($_GET["emailch"]))
	$t_out .= "eMail-Addresse ge&auml;ndert!";
else
	$t_out .= "Willkommen, <a href=\"userdetails.php?id=" . $CURUSER["id"] . "\">" . $CURUSER["username"] . "</a>!";

$sql = "SELECT * from stylesheets";
$qry = $GLOBALS['DB']->prepare($sql);
$qry->execute();
$data = $qry->FetchAll(PDO::FETCH_ASSOC);
$ss_sa = array();
foreach($data as $ss_a){
	$ss_id = $ss_a["id"];
	$ss_name = $ss_a["name"];
	$ss_sa[$ss_name] = $ss_id;
}
ksort($ss_sa);
reset($ss_sa);
$stylesheets = "";
//while(list($ss_name, $ss_id) = each($ss_sa)){
foreach($ss_sa as $ss_name => $ss_id){
	if($ss_id == $CURUSER["stylesheet"])
		$ss = "\" selected=\"selected";
	else
		$ss = "";
	$stylesheets .= "<option value=\"" . $ss_id . $ss . "\">" . $ss_name . "</option>";
}

$countries = "<option value=\"0\">---- Keines ausgew&auml;hlt ----</option>\n";
$sql = "SELECT id, name FROM countries ORDER BY name";
$qry = $GLOBALS['DB']->prepare($sql);
$qry->execute();
$data = $qry->FetchAll(PDO::FETCH_ASSOC);
foreach($data as $ct_a)
	$countries .= "<option value=\"" . $ct_a["id"] . "\"" . ($CURUSER["country"] == $ct_a['id'] ? " selected=\"selected\"" : "") . ">" . $ct_a["name"] . "</option>";

$categories = "";
$sql = "SELECT id, name FROM categories ORDER BY name";
$qry = $GLOBALS['DB']->prepare($sql);
$qry->execute();
if($qry->rowCount()){
	$data = $qry->FetchAll(PDO::FETCH_ASSOC);
	$categories .= "<table><tr>";
	$i = 0;
	foreach($data as $a){
		$categories .=  (($i && $i % 2 == 0) ? "</tr><tr>" : "");
		$categories .= "<td class=\"bottom\" style=\"padding-right: 5px\"><input name=\"cat" . $a["id"] . "\" type=\"checkbox\" " . ((strpos($CURUSER['notifs'], "[cat" . $a["id"] . "]") !== false) ? " checked=\"checked\"" : "") . " value=\"yes\">&nbsp;" . htmlspecialchars($a["name"]) . "</td>";
		++$i;
	}
	$categories .= "</tr></table>";
}

$statboxcontent = array("top"=>"&Uuml;ber dem Men&uuml;", "bottom"=>"Unter dem Men&uuml;", "hide"=>"Gar nicht anzeigen");
$statbox = "";
foreach($statboxcontent as $dbval => $dispval){
	$statbox .= "<input id=\"statbox" . $dbval . "\" type=\"radio\" name=\"statbox\" value=\"" . $dbval . "\"";
	if($CURUSER["statbox"] == $dbval)
		$statbox .= " checked=\"checked\"";
	$statbox .= "><label for=\"statbox" . $dbval . "\"> " . $dispval . "</label>";
}

stdhead($CURUSER["username"] . "s Profil", false);
echo "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" style=\"width:600px\" class=\"tableinborder\">\n".
	"    <tr class=\"tabletitle\">\n".
	"        <td width=\"100%\"><span class=\"normalfont\"><center><b>" . $t_out . "</b></center></span></td>\n".
	"    </tr>\n".
	"    <tr>\n".
	"        <td width=\"100%\" class=\"tablea\">\n".
	"            <table class=\"tableinborder\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\" align=\"center\" width=\"100%\">\n".
	"                <tr>\n".
	"                    <td class=\"tablea\" style=\"text-align:center\" width=\"25%\"><a href=\"logout.php\"><b>Logout</b></a></td>\n".
	"                    <td class=\"tableb\" style=\"text-align:center\" width=\"25%\"><a href=\"mytorrents.php\"><b>Meine Torrents</b></a></td>\n".
	"                    <td class=\"tablea\" style=\"text-align:center\" width=\"25%\"><a href=\"friends.php\"><b>Meine Buddyliste</b></a></td>\n".
	"                    <td class=\"tableb\" style=\"text-align:center\" width=\"25%\"><a href=\"bitbucket.php\"><b>Mein BitBucket</b></a></td>\n".
	"                </tr>\n";
if($messages){
	echo "                <tr>\n".
		"                    <td class=\"tablea\" colspan=\"4\" align=\"center\">Du hast " . $messages . " Nachricht" . (($messages != 1) ? "en" : "") . " (" . $unread . " ungelesene) in Deinem <a href=\"messages.php?folder=" . PM_FOLDERID_INBOX . "\"><b>Posteingang</b></a>, ";
	if($outmessages){
		echo "<br>und " . $outmessages . " Nachricht" . (($outmessages != 1) ? "en" : "") . " in Deinem <a href=\"messages.php?folder=" . PM_FOLDERID_OUTBOX . "\"><b>Postausgang</b></a>.</td>\n".
			"                </tr>\n";
	}else{
		echo "<br>und Dein <a href=\"messages.php?folder=" . PM_FOLDERID_OUTBOX . "\"><b>Postausgang</b></a> ist leer.</td>\n".
			"                </tr>\n";
	}
}else{
	echo "                <tr>\n".
		"                    <td class=\"tablea\" colspan=\"4\" align=\"center\">Dein <a href=\"messages.php?folder=" . PM_FOLDERID_INBOX . "\"><b>Posteingang</b></a> ist leer, ";
	if($outmessages){
		echo "<br>und Du hast " . $outmessages . " Nachricht" . (($outmessages != 1) ? "en" : "") . " in Deinem <a href=\"messages.php?folder=" . PM_FOLDERID_OUTBOX . "\"><b>Postausgang</b></a>.</td>\n".
			"                </tr>\n";
	}else{
		echo "<br>und Dein <a href=\"messages.php?folder=" . PM_FOLDERID_OUTBOX . "\"><b>Postausgang</b></a> auch.</td>\n".
			"                </tr>\n";
	}
}

echo "            </table>\n".
	"        <br>\n".
	"        <form method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "\">\n".
	"        <table class=\"tableinborder\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\" align=\"center\" width=\"100%\">\n";
tr("PNs akzeptieren", "<input type=\"radio\" name=\"acceptpms\"" . (($CURUSER["acceptpms"]) == "yes" ? " checked=\"checked\"" : "") . " value=\"yes\">Alle (au&szlig;er blockierte) <input type=\"radio\" name=\"acceptpms\"" .  (($CURUSER["acceptpms"] == "friends") ? " checked=\"checked\"" : "") . " value=\"friends\"><b>nur</b> Freunde (Buddyliste) <input type=\"radio\" name=\"acceptpms\"" .  ($CURUSER["acceptpms"] == "no" ? " checked=\"checked\"" : "") . " value=\"no\"><b>nur</b> Team",1);
tr("PNs l&ouml;schen", "<input type=\"checkbox\" name=\"deletepms\"" . (($CURUSER["deletepms"] == "yes") ? " checked=\"checked\"" : "") . "> (Bei Antwort PN l&ouml;schen)",1);
tr("PNs speichern", "<input type=\"checkbox\" name=\"savepms\"" . (($CURUSER["savepms"] == "yes") ? " checked=\"checked\"" : "") . "> (Bei Antwort PN speichern)",1);
tr("E-Mails akzeptieren","<input type=\"radio\" name=\"acceptemails\"" . (($CURUSER["accept_email"] == "yes") ? " checked=\"checked\"" : "") . " value=\"yes\">Alle (au&szlig;er blockierte) <input type=\"radio\" name=\"acceptemails\"" .  (($CURUSER["accept_email"] == "friends") ? " checked=\"checked\"" : "") . " value=\"friends\"><b>nur</b> Freunde (Buddyliste) <input type=\"radio\" name=\"acceptemails\"" .  (($CURUSER["accept_email"] == "no") ? " checked=\"checked\"" : "") . " value=\"no\"><b>nur</b> Team",1);
tr("eMail Benachrichtigung", "<input type=\"checkbox\" name=\"pmnotif\"" . ((strpos($CURUSER['notifs'], "[pm]") !== false) ? " checked=\"checked\"" : "") . "\" value=\"yes\"> Wenn ich eine PN erhalten habe.<br><input type=\"checkbox\" name=\"emailnotif\"" . ((strpos($CURUSER['notifs'], "[email]") !== false) ? " checked" : "") . "\" value=\"yes\"> Wenn ein Torrent in den unten markierten Kategorien hochgeladen wurde.", 1);
tr("Diese Kategorien beim<br>browsen anzeigen",$categories,1);
tr("Stylesheet", "<select name=\"stylesheet\">" . $stylesheets . "</select>",1);
tr("Land", "<select name=\"country\">" . $countries . "</select>",1);
tr("Avatar URL", "<input name=\"avatar\" size=\"60\" value=\"" . htmlspecialchars($CURUSER["avatar"]) . "\"><br>Die Breite sollte 150 Pixel betragen (wird ge&auml;ndert, wenn n&ouml;tig!).<br>Wenn Du keinen Server f&uuml;r Deine Bilder zur Verf&uuml;gung hast,<br>probiere doch unseren <a href=\"bitbucket.php\">BitBucket</a>!" . (($CURUSER["avatar"] != "") ? "<br><br>Dein Avatar:<br><img src=\"" . $CURUSER["avatar"] . "\" border=\"0\">" : ""),1);
if($GLOBALS["CLIENT_AUTH"] == CLIENT_AUTH_PASSKEY)
	tr("PassKey","<input type=\"checkbox\" name=\"chpasskey\" value=\"1\"> PassKey neu generieren (Bitte vorher das <a href=\"faq.php#userf\"><b>FAQ LESEN</b></a>!)",1);
tr("Torrentliste","<input type=\"radio\" id=\"torrentlistnew\" name=\"torrentlist\" value=\"new\"" . (($CURUSER["oldtorrentlist"] == "no") ? " checked=\"checked\"" : "") . "><label for=\"torrentlistnew\"> Platzsparendes Layout mit PopUp f&uuml;r zus&auml;tzliche Informationen</label><br><input type=\"radio\" id=\"torrentlistold\" name=\"torrentlist\" value=\"old\"" . (($CURUSER["oldtorrentlist"]=="yes") ? " checked=\"checked\"" : "") . "><label for=\"torrentlistold\"> Tabellarisches Layout, sehr breite Darstellung</label>",1);
tr("Useruploads","<input type=\"radio\" id=\"useruploadsno\" name=\"useruploads\" value=\"no\"" . (($CURUSER["hideuseruploads"]=="no") ? " checked=\"checked\"" : "") . "><label for=\"useruploadsno\"> Alle Uploads anzeigen</label><br><input type=\"radio\" id=\"useruploadsyes\" name=\"useruploads\" value=\"yes\"" . (($CURUSER["hideuseruploads"]=="yes") ? " checked=\"checked\"" : "") . "><label for=\"useruploadsyes\"> Nur Uploads von Uploadern und Staffmitgliedern anzeigen</label>",1);
tr("Torrents pro Seite", "<input type=\"text\" size=\"10\" name=\"torrentsperpage\" value=\"" . $CURUSER["torrentsperpage"] . "\"> (0=Standardwert)",1);
tr("Topics pro Seite", "<input type=\"text\" size=\"10\" name=\"topicsperpage\" value=\"" . $CURUSER["topicsperpage"] . "\"> (0=Standardwert)",1);
tr("Posts pro Seite", "<input type=\"text\" size=\"10\" name=\"postsperpage\" value=\"" . $CURUSER["postsperpage"] . "\"> (0=Standardwert)",1);
tr("Avatare anzeigen", "<input type=\"checkbox\" name=\"avatars\"" . (($CURUSER["avatars"] == "yes") ? " checked=\"checked\"" : "") . "> (User mit niedriger Bandbreite, sollten diese Option deaktivieren)",1);
tr("Ratio- und Torrentstatistik", $statbox, 1);
tr("Ratio-Histogramm", "<input type=\"checkbox\" name=\"log_ratio\"" . (($CURUSER["log_ratio"] == "yes") ? " checked=\"checked\"" : "") . " value=\"yes\"> Aktivieren<br><br>Das Histogramm erscheint auf der Seite \"Mein Profil\", sobald eine Schwankung der Ratio auftritt, fr&uuml;hestens jedoch nach zwei Stunden. Ein Deaktivieren der Option l&ouml;scht alle bisher gespeicherten Daten!",1);
tr("wget-Kommando", "<input type=\"checkbox\" name=\"wgeturl\"" . (($CURUSER["wgeturl"] == "yes") ? " checked=\"checked\"" : "") . " value=\"yes\"> Anzeigen<br><br>Dieses Kommando kann dazu benutzt werden, um den gew&uuml;nschten Torrent schnell &uuml;ber die Kommandozeile herunterzuladen. Wird in der Torrent-Detailansicht angezeigt.",1);
tr("Info", "<textarea name=\"info\" cols=\"60\" rows=\"4\">" . $CURUSER["info"] . "</textarea><br>Wird in Deinem Profil angezeigt. <a href=\"tags.php\" target=\"_new\">BBCodes</a> d&uuml;rfen verwendet werden.", 1);
tr("eMail-Addresse", "<input type=\"text\" name=\"email\" size=\"50\" value=\"" . htmlspecialchars($CURUSER["email"]) . "\" />", 1);
echo "    <tr>\n".
	"        <td class=\"tablecat\" colspan=\"2\" align=\"left\"><center><b>Hinweis:</b> Du bekommst eine eMail zur Best&auml;tigung zugeschickt!</center></td>\n".
	"    </tr>\n";
tr("Passwort &auml;ndern", "<input type=\"password\" name=\"chpassword\" size=\"50\" />", 1);
tr("Passwort wiederholen", "<input type=\"password\" name=\"passagain\" size=\"50\" />", 1);
echo "                <tr>\n".
	"                    <td class=\"tablea\" colspan=\"2\" style=\"text-align:center\"><input type=\"submit\" value=\"Ok!\" style=\"height: 25px\"> <input type=\"reset\" value=\"Zur&uuml;cksetzen!\" style=\"height: 25px\"></td>\n".
	"                </tr>\n".
	"                <tr>\n".
	"                    <td class=\"tableb\" colspan=\"2\" style=\"text-align:center\"><a href=\"delacct.php\">Mitgliedschaft beenden (Account l&ouml;schen)</a></td>\n".
	"                </tr>\n".
	"            </table>\n".
	"            </form>\n".
	"        </td>\n".
	"    </tr>\n".
	"</table>\n";
stdfoot();

function bark($msg){
	genbark($msg, "Aktualisierung Deiner Daten fehlgeschlagen!");
}
?>