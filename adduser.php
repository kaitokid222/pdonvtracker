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

require "include/bittorrent.php";
dbconn();
loggedinorreturn();
if (get_user_class() < UC_ADMINISTRATOR)
	stderr("Error", "Access denied.");
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    if ($_POST["username"] == "" || $_POST["password"] == "" || $_POST["email"] == "")
        stderr("Fehler", "Formulardaten unvollständig.");
    if ($_POST["password"] != $_POST["password2"])
        stderr("Fehler", "Passwörter sind nicht identisch.");
    $username = sqlesc($_POST["username"]);
    $password = $_POST["password"];
    $email = sqlesc($_POST["email"]);
    $secret = mksecret();
    $passkey = sqlesc(mksecret(8));
    $passhash = sqlesc(md5($secret . $password . $secret));
    $secret = sqlesc($secret);
    
    mysql_query("INSERT INTO users (added, last_access, secret, username, passhash, passkey, status, email) VALUES(NOW(), NOW(), $secret, $username, $passhash, $passkey, 'confirmed', $email)") or sqlerr(__FILE__, __LINE__);
    $res = mysql_query("SELECT id FROM users WHERE username=$username");
    $arr = mysql_fetch_row($res);
    if (!$arr)
        stderr("Fehler", "Der Account konnte nicht erstellt werden. Möglicherweise ist der Benuzername bereits vergeben.");
    header("Location: $BASEURL/userdetails.php?id=".$arr[0]."&".SID);
    die;
}
stdhead("Add user");

begin_frame("Benutzeraccount anlegen", FALSE, "400px");
?>

<form method="post" action="adduser.php">
<? begin_table(TRUE); ?>
<tr><td class="tableb">Benutzername:</td><td class="tablea"><input type="text" name="username" size="40"></td></tr>
<tr><td class="tableb">Passwort:</td><td class="tablea"><input type="password" name="password" size="40"></td></tr>
<tr><td class="tableb">Passwort wdh.:</td><td class="tablea"><input type="password" name="password2" size="40"></td></tr>
<tr><td class="tableb">E-Mail:</td><td class="tablea"><input type="text" name="email" size="40"></td></tr>
<tr><td class="tablea" colspan="2" style="text-align:center"><input type="submit" value="Okay" class="btn"></td></tr>
<? end_table(); ?>
</form>
<?
end_frame();
stdfoot();
?>