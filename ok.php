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

hit_count();

if (!mkglobal("type"))
	die();

if ($type == "signup" && mkglobal("email")) {
	stdhead("Benutzeranmeldung");
        stdmsg("Anmeldung erfolgreich!",
	    "Du bekommst in Kürze eine Bestätigungsmail mit dem Aktivierungslink. Folge bitte den Anweisungen in der Mail!");
	stdfoot();
}
elseif ($type == "confirmed") {
	stdhead("Account bereits aktiviert");
	print("<h1>Account bereits aktiviert</h1>\n");
	print("<p>Dieser Account wurde bereits aktiviert. Du kannst Dich nun auf der Seite mit Deinen Benutzerdaten <a href=\"login.php\">einloggen</a>.</p>\n");
	stdfoot();
}
elseif ($type == "confirm") {
	if (isset($CURUSER)) {
		stdhead("Account aktivieren");
		print("<h1>Account erfolgreich aktiviert!</h1>\n");
		print("<p>Dein Account wurde aktiviert! Du wurdest automatisch eingeloggt. Du kannst nun zur <a href=\"/\"><b>Startseite</b></a> gehen, und Deinen Account benutzen.</p><p>Bitte denke daran, Dich immer an die Regeln zu halten!</p>\n");
		print("<p>Bevor Du den NetVision-Tracker verwendest, emfpehlen wir Dir dringend, Dir die <a href=\"rules.php\"><b>REGELN</b></a> und das <a href=\"faq.php\"><b>FAQ</b></a> durchzulesen.</p>\n");
		stdfoot();
	}
	else {
		stdhead("Account aktivieren");
		print("<h1>Account erfolgreich aktiviert!</h1>\n");
		print("<p>Dein Account wurde aktiviert! Jedoch sieht es so aus, dass Du nicht automatisch eingeloggt werden konntest. Ein möglicher Grund dafür ist, dass Du Deinem Browser das Annehmen von Cookies verboten hast. Du musst Deinen Browser zu konfigurieren, dass er Cookies von dieser Seite akzeptiert. Bitte prüfe dies nach, <a href=\"login.php\">logge Dich ein</a> und versuche es erneut.</p><p>Bitte denke auch daran, Dich immer an die Regeln zu halten!</p>\n");
		stdfoot();
	}
}
else
	die();

hit_end();

?>
