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
if ($HTTP_SERVER_VARS["REQUEST_METHOD"] == "POST")
{
  $username = trim($_POST["username"]);
  $password = trim($_POST["password"]);
  if (!$username || !$password)
    stderr("Fehler", "Bitte fülle das Formular vollständig aus.");
  $res = mysql_query(

  "SELECT * FROM users WHERE username=" . sqlesc($username) .
  " AND status='pending' AND passhash=md5(concat(secret,concat(" . sqlesc($password) . ",secret)))") or sqlerr();
  if (mysql_num_rows($res) != 1)
    stderr("Fehler", "Ungültiger Benutzername oder Passwort, oder der Account ist bereits bestätigt. Bitte stelle sicher, dass die eingegebenen Informationen korrekt sind!");
  $arr = mysql_fetch_assoc($res);

  delete_acct($arr['id']);

  if (mysql_affected_rows() != 1)
    stderr("Fehler", "Der Account konnte nicht gelöscht werden.");
  stderr("ERfolg", "Der Account <b>$username</b> wurde erfolgreich gelöscht.");
}
stdhead("Account löschen");
begin_frame("Account löschen", FALSE, "500px");
?>
<p>Bitte gebe Deinen Benutzernamen und Dein Passwort zur Best&auml;tigung an, um Deinen
noch nicht bestätigten Account zu entfernen.</p>
<p>Wenn Dein Account bereits bestätigt wurde, kannst du diesen nicht löschen. Sende
in diesem Fall eine PN an ein Teammitglied. Dieses wird Deinen Account dann
deaktivieren.</p>
<form method=post action=delacct.php>
<?php
begin_table(TRUE);
?>
<tr><td class=tableb>Benutzername</td><td class=tablea><input size="40" name="username"<? if ($CURUSER) echo " value=\"".$CURUSER["username"]."\""; ?>></td></tr>
<tr><td class=tableb>Passwort</td><td class=tablea><input type=password size=40 name=password></td></tr>
<tr><td class=tablea colspan=2 align="center"><input type=submit class=btn value='L&ouml;schen'></td></tr>
<?php end_table(); ?>
</form>
<?php
end_frame();

stdfoot();
?>