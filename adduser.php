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
loggedinorreturn();

if (get_user_class() < UC_ADMINISTRATOR)
	stderr("Error", "Access denied.");

if ($_SERVER["REQUEST_METHOD"] == "POST"){
    if ($_POST["username"] == "" || $_POST["password"] == "" || $_POST["email"] == "")
        stderr("Fehler", "Formulardaten unvollständig.");
    if ($_POST["password"] != $_POST["password2"])
        stderr("Fehler", "Passwörter sind nicht identisch.");
    $username = $_POST["username"];
    $password = $_POST["password"];
    $email = $_POST["email"];
    $secret = mksecret();
    $passkey = mksecret(8);
    $passhash = md5($secret . $password . $secret);
	$now = date("Y-m-d H:i:s");
	$state = 'confirmed';
    $qry = $GLOBALS['DB']->prepare('INSERT INTO users (added, last_access, secret, username, passhash, passkey, status, email) VALUES(:now, :now, :secret, :un, :passhash, :passkey, :c, :mail)');
	$qry->bindParam(':now', $now, PDO::PARAM_STR);
	$qry->bindParam(':secret', $secret, PDO::PARAM_STR);
	$qry->bindParam(':un', $username, PDO::PARAM_STR);
	$qry->bindParam(':passhash', $passhash, PDO::PARAM_STR);
	$qry->bindParam(':passkey', $passkey, PDO::PARAM_STR);
	$qry->bindParam(':c', $state, PDO::PARAM_STR);
	$qry->bindParam(':mail', $email, PDO::PARAM_STR);
	$qry->execute();
	if(!$qry->rowCount())
        stderr("Fehler", "Der Account konnte nicht erstellt werden. Möglicherweise ist der Benuzername bereits vergeben.");
    header("Location: " . $BASEURL . "/userdetails.php?id=" . $GLOBALS['DB']->lastInsertId());
    die;
}

stdhead("Benutzer hinzufügen");
begin_frame("Benutzeraccount anlegen", FALSE, "400px");
begin_table(TRUE);
?>
<form method="post" action="<?=$_SERVER['PHP_SELF'] ?>">
<tr><td class="tableb">Benutzername:</td><td class="tablea"><input type="text" name="username" size="40"></td></tr>
<tr><td class="tableb">Passwort:</td><td class="tablea"><input type="password" name="password" size="40"></td></tr>
<tr><td class="tableb">Passwort wdh.:</td><td class="tablea"><input type="password" name="password2" size="40"></td></tr>
<tr><td class="tableb">E-Mail:</td><td class="tablea"><input type="text" name="email" size="40"></td></tr>
<tr><td class="tablea" colspan="2" style="text-align:center"><input type="submit" value="Okay" class="btn"></td></tr>
</form>
<?php
end_table();
end_frame();
stdfoot();
?>