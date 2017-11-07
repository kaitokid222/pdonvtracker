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

dbconn();

hit_count();

stdhead("Login");

unset($returnto);
if (!empty($_GET["returnto"])) {
	$returnto = $_GET["returnto"];
	if (!$_GET["nowarn"]) {
?>
<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
 <tr class="tabletitle" width="100%">
  <td colspan="10" width="100%"><span class="normalfont"><center><b> Nicht angemeldet! </b></center></span></td> 
 </tr><tr><td width="100%" class="tablea"><img src="<?=$GLOBALS["PIC_BASE_URL"]?>warned16.gif"> Die gew&uuml;nschte Seite ist nur angemeldeten Benutzern
 zug&auml;nglich.</td></tr></table><br>
<?
	}
}

?>
<form method="post" action="takelogin.php">
<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
 <tr class="tabletitle" width="100%">
  <td colspan="10" width="100%"><span class="normalfont"><center><b> Tracker Login </b></center></span></td> 
 </tr><tr><td width="100%" class="tablea"><center>
<p>Hinweis: Du musst Deinen Browser so eingestellt haben, dass er Cookies akzeptiert, damit Du
Dich einloggen kannst.</p>
<table border="0" cellspacing="1" cellpadding="4" class="tableinborder">
<tr><td class=tableb align=left>Benutzername:</td><td class=tablea align=left><input type="text" size=40 name="username" /></td></tr>
<tr><td class=tableb align=left>Passwort:</td><td class=tablea align=left><input type="password" size=40 name="password" /></td></tr>
<!--<tr><td class=rowhead>Duration:</td><td align=left><input type=checkbox name=logout value='yes' checked>Log me out after 15 minutes inactivity</td></tr>-->
<tr><td class=tablea colspan="2" align="center"><input type="submit" value="Log in!" class=btn></td></tr>
</table>
<?

if (isset($returnto))
	print("<input type=\"hidden\" name=\"returnto\" value=\"" . htmlspecialchars($returnto) . "\" />\n");

?>
</form>
<p>Du hast noch keinen Account? <a href="signup.php">Registriere Dich</a> hier!</p>
</center>
</td></tr></table>
<?

stdfoot();

hit_end();

?>
