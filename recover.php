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

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
  $email = trim($_POST["email"]);
  if (!$email)
    stderr("FEhler", "Du musst eine E-Mail Adresse eingeben");
  $res = mysql_query("SELECT * FROM users WHERE email=" . sqlesc($email) . " LIMIT 1") or sqlerr();
  $arr = mysql_fetch_assoc($res) or stderr("Fehler", "Die E-Mail Adresse <b>$email</b> wurde nicht in der Datenbank gefunden.\n");

	$sec = mksecret();

  mysql_query("UPDATE users SET editsecret=" . sqlesc($sec) . " WHERE id=" . $arr["id"]) or sqlerr();
  if (!mysql_affected_rows())
	  stderr("Fehler", "Datenbankfehler. Bitte informiere den Administrator darüber.");

  $hash = md5($sec . $email . $arr["passhash"] . $sec);

  $body = <<<EOD
Jemand, hoffentlich Du, hat ein neues Passwort für den Account
beantragt, mit dem diese E-Mail Adresse ($email) verknüpft ist.
Die Anfrage wurde von der IP {$_SERVER["REMOTE_ADDR"]} gestellt.

Wenn Du dies nicht getan hast, ignoriere diese Mail einfach, und
antwtorte nicht darauf.


Wenn Du die Anfrage bestätigen möchtest, klicke auf folgenden Link:

$DEFAULTBASEURL/recover.php?id={$arr["id"]}&secret=$hash


Nachdem Du dies getan hast, wird Dein Passwort zurückgesetzt und Dir
per Mail zugestellt.
--
{$GLOBALS["SITENAME"]}
EOD;

  @mail($arr["email"], $GLOBALS["SITENAME"]." Passwort zurücksetzen", $body, "From: ".$GLOBALS["SITEEMAIL"])
    or stderr("Fehler", "Die E-Mail konnte nicht gesendet werden. Bitte informiere den Administrator darüber.");
  stderr("Erfolg", "Eine Bestätigungsmail wurde an <b>$email</b> versandt.\n" .
    "Bitte habe einen Moment Geduld, bis die Mail angekommen ist.");
}
elseif($_GET)
{
//	if (!preg_match(':^/(\d{1,10})/([\w]{32})/(.+)$:', $_SERVER["PATH_INFO"], $matches))
//	  httperr();

//	$id = 0 + $matches[1];
//	$md5 = $matches[2];

	$id = 0 + $_GET["id"];
  $md5 = $_GET["secret"];

	if (!$id)
	  httperr();

	$res = mysql_query("SELECT username, email, passhash, editsecret FROM users WHERE id = $id");
	$arr = mysql_fetch_array($res) or httperr();

  $email = $arr["email"];

	$sec = hash_pad($arr["editsecret"]);
	if (preg_match('/^ *$/s', $sec))
	  httperr();
	if ($md5 != md5($sec . $email . $arr["passhash"] . $sec))
	  httperr();

	// generate new password;
	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

  $newpassword = "";
  for ($i = 0; $i < 10; $i++)
    $newpassword .= $chars[mt_rand(0, strlen($chars) - 1)];

 	$sec = mksecret();

  $newpasshash = md5($sec . $newpassword . $sec);

	mysql_query("UPDATE users SET secret=" . sqlesc($sec) . ", editsecret='', passhash=" . sqlesc($newpasshash) . " WHERE id=$id AND editsecret=" . sqlesc($arr["editsecret"]));

	if (!mysql_affected_rows())
		stderr("Fehler", "Das Benutzerprofil konnte nicht aktualisiert werden. Bitte melde diesen Fehler an den Administrator.");

  $body = <<<EOD
Auf Deine Anforderung hin haben wir Dir ein neues Passwort erstellt.

Dies sind die Informationen, unter denen wir Deinen Account führen:

    Benutezrname: {$arr["username"]}
    Passwort:  $newpassword

Du kannst Dich nun unter $DEFAULTBASEURL/login.php einloggen.

--
{$GLOBALS["SITENAME"]}
EOD;
  @mail($email, $GLOBALS["SITENAME"]." Account-Details", $body, "From: ".$GLOBALS["SITEEMAIL"])
    or stderr("Fehler", "Die E-Mail konnte nicht gesendet werden. Bitte informiere den Administrator darüber.");
  stderr("Erfolg", "Eine Bestätigungsmail wurde an <b>$email</b> versandt.\n" .
    "Bitte habe einen Moment Geduld, bis die Mail angekommen ist.");
}
else
{
 	stdhead("Passwort vergessen");
    begin_frame("Verlorenes Passwort und/oder Benutzernamen wiederfinden", FALSE, "650px");    
	?>
	<p>Benutze das folgende Formular, und Deine Account-Daten werden Dir umgehend per Mail zugesandt.<br>
  (Du musst zuerst eine Best&auml;tigungsmail beantworten)</p>
	<form method=post action=recover.php>
    <center>
    <?php begin_table(); ?>
	<tr><td class="tableb">Verwendete E-Mail Adresse</td><td class="tablea"><input type=text size=40 name=email></td></tr>
	<tr><td class="tablea" colspan="2" style="text-align:center"><input type="submit" value="Und ab!" class="btn"></td></tr>
	<?php
    end_table();
    ?></center></form><?php
    end_frame();
	stdfoot();
}

?>