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
userlogin();
if ($_SERVER["REQUEST_METHOD"] == "POST"){
	if (!isset($_POST["username"]) OR !isset($_POST["password"]))
		stderr("Fehler", "Bitte fülle das Formular vollständig aus.");
	else{
		$username = $_POST["username"];
		$password = $_POST["password"];
	}

	$qry = $GLOBALS['DB']->prepare("SELECT id FROM users WHERE username=:username AND status='pending' AND passhash=md5(concat(secret,concat(:password,secret)))");
	$qry->bindParam(':username', $username, PDO::PARAM_STR);
	$qry->bindParam(':password', $password, PDO::PARAM_STR);
	$qry->execute();
	if(!$qry->rowCount())
		stderr("Fehler", "Ungültiger Benutzername oder Passwort, oder der Account ist bereits bestätigt. Bitte stelle sicher, dass die eingegebenen Informationen korrekt sind!");
	else
		$arr = $qry->FetchAll();

	$del = delete_acct($arr['id']);

	if ($del !== TRUE)
		stderr("Fehler", "Der Account konnte nicht gelöscht werden.");
	stderr("ERfolg", "Der Account <b>" . $username . "</b> wurde erfolgreich gelöscht.");
}
$un = "";
if($CURUSER)
	$un .= " value=\"".$CURUSER["username"]."\"";

stdhead("Account löschen");
begin_frame("Account löschen", FALSE, "500px");

echo "<p>Bitte gebe Deinen Benutzernamen und Dein Passwort zur Best&auml;tigung an, um Deinen noch nicht bestätigten Account zu entfernen.</p>\n".
	"<p>Wenn Dein Account bereits bestätigt wurde, kannst du diesen nicht löschen. Sende in diesem Fall eine PN an ein Teammitglied. Dieses wird Deinen Account dann deaktivieren.</p>\n";
begin_table(TRUE);
echo "<form method=\"post\" action=\"" . $_SERVER["PHP_SELF"] . "\">\n".
	"    <tr>\n".
	"        <td class=\"tableb\">Benutzername</td>\n".
	"        <td class=\"tablea\"><input size=\"40\" name=\"username\"" . $un . "></td>\n".
	"    </tr>\n".
	"    <tr>\n".
	"        <td class=\"tableb\">Passwort</td>\n".
	"        <td class=\"tablea\"><input type=\"password\" size=\"40\" name=\"password\"></td>\n".
	"    </tr>\n".
	"    <tr>\n".
	"        <td class=\"tablea\" colspan=\"2\" align=\"center\"><input type=\"submit\" class=\"btn\" value=\"L&ouml;schen\"></td>\n".
	"    </tr>\n".
	"</form>\n";
end_table();
end_frame();
stdfoot();
?>