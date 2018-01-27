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

require_once "include/bittorrent.php";
userlogin();
stdhead("Regeln");

$breite = 800;
$ansprechpartner = array("id" => 111, "username" => "kaitokid222");
$changed = array("by" => "][Dante][", "at" => "18.10.2005");

if(isset($_GET["accept_rules"])){
	echo "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" style=\"width:" . $breite . "px\" class=\"tableinborder\">\n".
		"    <tr>\n".
		"        <td width=\"100%\" class=\"tablea\" style=\"font-weight:bold;color:red;\">WICHTIG! Regeländerung - Du musst die neuen Regeln akzeptieren, bevor Du den Tracker weiter nutzen kannst!</td>\n".
		"    </tr>\n".
		"</table>\n".
		"<br>\n";
}

echo "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" style=\"width:" . $breite . "px\" class=\"tableinborder\">\n".
	"    <tr class=\"tabletitle\" width=\"100%\">\n".
	"        <td width=\"100%\"><span class=\"smallfont\"><center><b>Grundlegende Regeln</b> - Missachtung kann zu sofortigem Ban führen!</center></span></td>\n".
	"    </tr>\n".
	"    <tr>\n".
	"        <td width=\"100%\" class=\"tablea\">\n".
	"            <ul>\n".
	"                <li><p>Den Anordnungen des Teams ist grundsätzlich Folge zu leisten!</p></li>\n".
	"                <li><p>Bevor man eine Frage stellt in der FAQ lesen, ob diese schon beantwortet wurde.</p></li>\n".
	"                <li><p>Poste unsere Torrents nicht auf anderen Trackern.</p></li>\n".
	"                <li><p><a name=\"warning\"></a>Unsachgemässes Verhalten und Missachtung der Regeln resultiert in einer Verwarnung (<img src=\"" . $GLOBALS["PIC_BASE_URL"] . "warned.gif\" border=\"0\"> ).<br>Ändert sich nach dieser Verwarnung nichts, wirst du \"höflich\" gebeten zu gehen ;)</p></li>\n".
	"            </ul>\n".
	"        </td>\n".
	"    </tr>\n".
	"</table>\n".
	"<br>\n";

echo "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" style=\"width:" . $breite . "px\" class=\"tableinborder\">\n".
	"    <tr class=\"tabletitle\" width=\"100%\">\n".
	"        <td width=\"100%\"><span class=\"smallfont\"><center><b>IRC Regeln</b> - Missachtung kann zu Kick/Ban aus dem Channel oder aus dem IRC-Netzwerk führen!</center></span></td>\n".
	"    </tr>\n".
	"    <tr>\n".
	"        <td width=\"100%\" class=\"tablea\">\n".
	"            <ul>\n".
	"                <li><p>Keine Announcer-Scripts (also solche, die irgendetwas ankündigen wie MP3s, BT-Infos, Sysinfos, ...).</p></li>\n".
	"                <li><p>Keine \"Danke\"-Scripts (solche, die z.B. automatisch schreiben \"THX für Voice\" usw.).</p></li>\n".
	"                <li><p>Kein Betteln um Voice, HOP, OP usw. - wer bettelt, fliegt.</p></li>\n".
	"                <li><p>Seid nett zueinander. Kindergartenverhalten, Beleidigungen usw. werden nicht toleriert und resultieren in Kick und/oder Ban.</p></li>\n".
	"                <li><p>Kein Spam jeglichen Typs (Wiederholte Texte, mehrzeiliges ASCII-Art, häufige Nickchanges, Join/Part-Spam...).</p></li>\n".
	"                <li><p>Keine unnötigen CTCP-Requests an andere User.</p></li>\n".
	"                <li><p>Keine übermäßige Nutzung von Farben. Wenn Dein Script so etwas automatisch tut, schalte es bitte ab!</p></li>\n".
	"                <li><p>Keine Warscripts. Dieser Punkt gilt besonders für HOPs und OPs.</p></li>\n".
	"                <li><p>Das Verbreiten von Viren und schädlichen Scripten ist untersagt. Wenn wir mitbekommen, dass ein User solcherlei Dinge verbreitet, resultiert dies in einer KLine für diesen User (aka Ban aus dem IceVelvet-IRC-Netzwerk)!</p></li>\n".
	"                <li><p>Den Anweisungen der HOPs und OPs ist Folge zu leisten.</p></li>\n".
	"            </ul>\n".
	"        </td>\n".
	"    </tr>\n".
	"</table>\n".
	"<br>\n";

echo "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" style=\"width:" . $breite . "px\" class=\"tableinborder\">\n".
	"    <tr class=\"tabletitle\" width=\"100%\">\n".
	"        <td width=\"100%\"><span class=\"smallfont\"><center><b>Download Regeln</b> - Bei Missachtung wird die Möglichkeit etwas zu downloaden entzogen!</center></span></td>\n".
	"    </tr>\n".
	"    <tr>\n".
	"        <td width=\"100%\" class=\"tablea\">\n".
	"            <ul>\n".
	"                <li><p>Der Zugriff auf die neusten Torrents ist abhängig von deiner Ratio (Info hierzu: <a href=\"faq.php#dl8\" class=\"altlink\"><b>FAQ</b></a>.)</p></li>\n".
	"                <li><p>Eine tiefe Ratio resultiert in mehreren Konsequenzen. Im Extremfall Ban.</p></li>\n".
	"                <li><p>Doppelaccounts gibts nur auf Anfrage und mit Begründung (bei <a href=\"messages.php?action=send&amp;receiver=" . $ansprechpartner["id"] . "\">" . $ansprechpartner["username"] . "</a> melden). Werden unangemeldete Doppelaccs gefunden, werden sie umgehend gelöscht.</p></li>\n".
	"                <li><p>Wenn Du einen Torrent fertiggestellt hast, lasse diese noch einige Zeit im Seed. BitTorrent lebt davon, dass Torrents weiter geseedet werden. Ausreden wie \"Plattencrash\" sind INAKZEPTABEL (jaja, pro Woche schmieren hier 20 Usern die Platten exakt dann ab, wenn die gerade einen Torrent fertig hatten... sicherlich).</p></li>\n".
	"                <li><p>Für die Beantragung der Wartezeitaufhebung sind <a href=\"faq.php#dlf\">die im FAQ genannten Bedingungen</a> bindend.</p></li>\n".
	"            </ul>\n".
	"        </td>\n".
	"    </tr>\n".
	"</table>\n".
	"<br>\n";

echo "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" style=\"width:" . $breite . "px\" class=\"tableinborder\">\n".
	"    <tr class=\"tabletitle\" width=\"100%\">\n".
	"        <td width=\"100%\"><span class=\"smallfont\"><center><b>Regeln für <a href=\"faq.php#site4\" title=\"Was sind Webseeder?\"><u>Webseeder</u></a></b> - Bei Missachtung wird die IP des Rootservers dauerhaft gesperrt!</center></span></td>\n".
	"    </tr>\n".
	"    <tr>\n".
	"        <td width=\"100%\" class=\"tablea\">\n".
	"            <ul>\n".
	"                <li><p>Sei Dir im Klaren darüber, dass jedes zuviel hochgeladene GB auf Kosten der Ratio eines Benutzers geht, der nicht so einen schnellen Upload hat.</p></li>\n".
	"                <li><p>Es genügt oft, wenn Du etwa 400% der Torrent-Datenmenge seedest, um eine Grundverteilung zu erreichen. Wenn Du der Masterseeder bist, benutze bitte den \"Superseeder\"-Modus (Parameter \"--super_seeder 1\" in BitTornado). Haben nach den 400% noch nicht genügend Benutzer ihren Download durch, oder ist der Torrent sehr stark Nachgefragt, kann auch ein wenig weitergeseedet werden, bis genügend Seeder vorhanden sind. Die nötige Upload-Menge schwankt stark von Torrent zu Torrent, und soll hier auch nicht festgelegt werden. Wahre die Verhältnismäßigkeit!</p></li>\n".
	"                <li><p>Die Uploadgeschwindigkeit soll maximal 512 KB/sek betragen. Bei einer Seeder/Leecher-Ratio von 1:4 oder schlechter, kann die Geschwindigkeit auch auf 768 KB/sek erhöht werden, aber auf keinen Fall mehr. Die Upload-Geschwindigkeit kann man in BitTornado mit dem Parameter --max_upload_rate begrenzen. Mehr dazu im <a href=\"faq.php\" title=\"Hilfe zu BitTornado\"><b>FAQ</b></a>.</p></li>\n".
	"                <li><p>Seeden, nur um in die Top10 zu gelangen, ist untersagt. Es gibt keine Krone dafür.</p></li>\n".
	"                <li><p>Webseeder können nachträglich in einen Torrent einsteigen, um den Seed ein wenig zu unterstützen. Wie für Masterseeder, gilt auch hier die Einhaltung der Verhältnismäßigkeit.</p></li>\n".
	"                <li><p>Übermäßiges Seeden wird mit Abzug von Upload, der Sperrung der Server-IP, und in schweren oder wiederholten Fällen mit Verwarnungen oder Accountlöschung geahndet. Ausreden wie \"Ich hab vergessen den Seed zu Stoppen\" werden nicht akzeptiert - achtet selber darauf, was ihr tut!</p></li>\n".
	"                <li><p>Im Übrigen gilt wie immer: Anweisungen des Staffs ist umgehend Folge zu leisten.</p></li>\n".
	"            </ul>\n".
	"        </td>\n".
	"    </tr>\n".
	"</table>\n".
	"<br>\n";

echo "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" style=\"width:" . $breite . "px\" class=\"tableinborder\">\n".
	"    <tr class=\"tabletitle\" width=\"100%\">\n".
	"        <td width=\"100%\"><span class=\"smallfont\"><center><b>Richtlinien für Avatare und BitBucket-Inhalte</b> - Bitte beachten</center></span></td>\n".
	"    </tr>\n".
	"    <tr>\n".
	"        <td width=\"100%\" class=\"tablea\">\n".
	"            <ul>\n".
	"                <li><p>Die erlaubten Formate sind .gif, .jpg und .png.</p></li>\n".
	"                <li><p>Die maximal erlaubten Dimensionen eines Avatars betragen 150x150 und 150kByte. Avatare in anderen Grössen werden automatisch angepasst, deswegen kann es vorkommen, dass kleinere Avatare verzogen aussehen. Grössere hingegen verbrauchen nur unnötig Bandbreite, da auch sie automatisch angepasst werden.</p></li>\n".
	"                <li><p>Benutze kein potentiell angreifendes Material wie pornografische, Anti-Religiöse , Gewaltverherrlichende oder rassistische Bilder. Die Moderatoren wissen genau, was wie einzustufen ist. Bei Zweifeln einfach einen Mod per PN fragen.</p></li>\n".
	"                <li><p>Der BitBucket dient nicht nur dazu, einen Avatar abzulegen. Dort kannst Du auch Screenshots für Torrents, Banner für Signaturen u.ä. ablegen. Der Speicherplatz ist auf 1 MB begrenzt. Dein BitBucket-Inhalt kann von allen Moderatoren eingesehen werden, und diese können auch Bilder daraus löschen, falls sie gegen die Richtlinien verstoßen.</p></li>\n".
	"            </ul>\n".
	"        </td>\n".
	"    </tr>\n".
	"</table>\n".
	"<br>\n";

echo "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" style=\"width:" . $breite . "px\" class=\"tableinborder\">\n".
	"    <tr class=\"tabletitle\" width=\"100%\">\n".
	"        <td width=\"100%\"><span class=\"smallfont\"><center><b>Upload-Regeln</b> - Torrents, welche gegen diese Bestimmungen verstossen, werden kommentarlos gelöscht!</center></span></td>\n".
	"    </tr>\n".
	"    <tr>\n".
	"        <td width=\"100%\" class=\"tablea\">\n".
	"            <ul>\n".
	"                <li><p>Ein Gastuploader darf maximal 1 Eigenseed gleichzeitig haben</p></li>\n".
	"                <li><p>Der Torrent muss mindestens 30 MB groß sein. Falls Du etwas hast, was unbedingt auf den Tracker soll, aber kleiner als 30 MB ist, wende Dich an das Team.</p></li>\n".
	"                <li><p>Alle Uploads müssen eine NFO beinhalten.</p></li>\n".
	"                <li><p>Archive (ZIP, RAR) in Torrents dürfen kein Passwort enthalten.</p></li>\n".
	"                <li><p>Szenereleases müssen im Originalzustand geseedet werden.</p></li>\n".
	"                <li><p>Non-Final-Releases müssen mit *ALPHA*, *BETA* oder *PRERELEASE* Tag gekennzeichnet werden.</p></li>\n".
	"                <li><p>In der Torrentbeschreibung brauchen CD-Keys/Serials nicht entfernt werden, da jeder User NFOs lesen darf.</p></li>\n".
	"                <li><p>Folgende Torrents sind nicht erlaubt:</p>\n".
	"                    <ul>\n".
	"                        <li>Sämtliche Produkte von Microsoft, egal ob MS als Hersteller oder Publisher fungiert.</li>\n".
	"                        <li>Jegliche XBox-Titel (Da Microsoft-Konsole)</li>\n".
	"                        <li>Spiele von Valve</li>\n".
	"                        <li>Alle Torrents mit den weiter unten genannten Inhalten</li>\n".
	"                    </ul>\n".
	"                </li>\n".
	"                <li><p><b>Gastupload-Verhaltensregeln</b></p></li>\n".
	"                <li><p>Der Torrent-Name muss sich an der üblichen Szene-Schreibweise orientieren, und alle wichtigen Informationen in knapper Form enthalten. Beispiele:</p>\n".
	"                    <ul>\n".
	"                        <li><b>DVD:</b> Koenigreich.der.Himmel.German.AC3.Dubbed.DVDR-GTR</li>\n".
	"                        <li><b>Screener:</b> Die.weisse.Massai.TS.Line.Dubbed.German.SVCD-MRM</li>\n".
	"                        <li><b>MP3-Album:</b> Knorkator-Hasenchartbreaker.MP3.192kBit</li>\n".
	"                        <li><b>Spiel:</b> Fifa 2006-PAL-PS2DVD-DAGGER</li>\n".
	"                    </ul>\n".
	"                    <p>Eine gewisse Freiheit bei der Benennung ist jedem gestattet, dennoch sollten die Torrent-Titel einheitlich benannt werden. Folgendes Format sollte man generell als Ausgangspunkt nehmen, sowie Leerzeichen grundsätzlich durch Punkte ersetzen (Gruppe ist die Release-Group. Kann weggelassen werden, sofern unbekannt):</p>\n".
	"                </li>\n".
	"                <br>\n".
	"                <p><b>Beschreibungsinhalt</b></p><br>\n".
	"                <li>Anime: Plot/Inhaltsangabe</li>\n".
	"                <li>Audio: Tracklist</li>\n".
	"                <li>Doku/Magazin: Plot/Inhaltsangabe</li>\n".
	"                <li>Games/*: Spielbeschreibung<br></li>\n".
	"                <li>Hörspiel/Hörbuch: Plot/Inhaltsangabe</li>\n".
	"                <li>Movies/*: Plot/Inhaltsangabe</li>\n".
	"                <li>Serien: Plot/Inhaltsangabe</li>\n".
	"                <li>Software: Programmbeschreibung<br></li>\n".
	"                <li>Sonstiges: Beschreibung<br></li>\n".
	"                <br>\n".
	"                <p>Alle weiteren Informationen können der NFO entnommen werden, doppelte Infos sind nicht nötig.</p><br>\n".
	"                <li><p>Stelle sicher, dass dein Torrent lange genug geseedet wird.</p></li>\n".
	"                <li><p>Das Releasedatum sowie der Nick des Seeders gehören nicht in den Torrentnamen.</p></li>\n".
	"                <li>\n".
	"                    <p>Folgende Inhalte sind auf diesem Tracker untersagt, und werden umgehend gelöscht:</p>\n".
	"                    <ol>\n".
	"                        <li>Pornographie</li>\n".
	"                        <li>Gesetzlich verbotene Werke (Kinderpornos, Nazi-Musik, Spiele wie \"Manhunt\", ...)</li>\n".
	"                    </ol>\n".
	"                    <p>Bei indizierten Medien oder Unsicherheiten beispielsweise bei Splattern wendet ihr euch an <a href=\"userdetails.php?id=" . $ansprechpartner["id"] . "\">" . $ansprechpartner["username"] . "</a></p>\n".
	"                    <p>Bei schweren Verstößen und in Wiederholungsfällen kann der Uploader mit Verwarnung, Degradierung oder Kick bestraft werden!</p>\n".
	"                </li>\n".
	"                <li><p>Direkte Links auf Seiten wie offizielle Filmeseiten (z.B. XYZ-der-film.de) und auch das direkte Einbinden von Bildern auf diesen Seiten ist strengstens untersagt. Für Screenshots und Filmplakate/DVD-Covers gibt es den BitBucket, sowie die Möglichkeit beim Upload zwei Bilder mit hochzuladen. IMDB darf jedoch verlinkt werden.</p></li>\n".
	"                <li><p>Inaktive Uploader verlieren ihren Status. Als inaktiv wird ein Uploader gewertet, der in den letzten 28 Tagen weniger als 4 Torrents hochgeladen, und keine besondere Begründung dafür angegeben hat.</p></li>\n".
	"                <li><p>Torrents, die von Benutzern hochgeladen werden, die einen niedrigeren Rang als \"Uploader\" besitzen, müssen erst vom Team freigeschaltet werden. Das Team wird bei einem erfolgreichen Upload automatisch benachrichtigt, und schaltet den Torrent frei, sofern er den Regeln entspricht. Benutzer, die wiederholt gegen die Upload-Regeln verstoßen, verlieren diese Berechtigung.</p></li>\n".
	"            </ul>\n".
	"        </td>\n".
	"    </tr>\n".
	"</table>\n".
	"<br>\n";

if(get_user_class() >= UC_MODERATOR){
	echo "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" style=\"width:" . $breite . "px\" class=\"tableinborder\">\n".
		"    <tr class=\"tabletitle\" width=\"100%\">\n".
		"        <td width=\"100%\"><span class=\"smallfont\"><center><b>Moderator Regeln</b> - Wer wird befördert und warum</center></span></td>\n".
		"    </tr>\n".
		"    <tr>\n".
		"        <td width=\"100%\" class=\"tablea\">\n".
		"            <table border=\"0\" cellspacing=\"1\" cellpadding=\"2\">\n".
		"                <tr>\n".
		"                    <td class=\"inposttable\" valign=\"top\" width=\"140\">&nbsp;<b>Power User</b></td>\n".
		"                    <td width=\"5\">&nbsp;</td>\n".
		"                    <td>Wird automatisch vergeben, wenn ein User mindestens 4 Wochen dabei ist, mindestens 25GB geuploadet hat und eine Ratio von über 1.05 vorweisen kann. Von Mods gemachte Änderungen werden bei jeder Ausführung dieses Scriptes überschrieben.</td>\n".
		"                </tr>\n".
		"                <tr>\n".
		"                    <td class=\"inposttable\" valign=\"top\" width=\"140\">&nbsp;<b><img src=\"" . $GLOBALS["PIC_BASE_URL"] . "star.gif\" border=\"0\"></b></td>\n".
		"                    <td width=\"5\">&nbsp;</td>\n".
		"                    <td>Dieser Status wird <b>nur</b> von Administratoren oder SysOps vergeben.</td>\n".
		"                </tr>\n".
		"                <tr>\n".
		"                    <td class=\"inposttable\" valign=\"top\" width=\"140\">&nbsp;<b>VIP</b></td>\n".
		"                    <td width=\"5\">&nbsp;</td>\n".
		"                    <td>Für User, die deiner Meinung etwas besonderes in Bezug auf den Tracker geleistet haben. (Wer sich einen höheren Status erbetteln will, ist automatisch ausgeschlossen)</td>\n".
		"                </tr>\n".
		"                <tr>\n".
		"                    <td class=\"inposttable\" valign=\"top\" width=\"140\">&nbsp;<b>Sonstige</b></td>\n".
		"                    <td width=\"5\">&nbsp;</td>\n".
		"                    <td>Benutzerdefinierte Titel für spezielle User. (Nicht verfügbar für User und Power User).</td>\n".
		"                </tr>\n".
		"                <tr>\n".
		"                    <td class=\"inposttable\" valign=\"top\" width=\"140\">&nbsp;<b><font color=\"#4040c0\">Uploader</font></b></td>\n".
		"                    <td width=\"5\">&nbsp;</td>\n".
		"                    <td>Wird von Administratoren/SysOps vergeben. Sende eine PM an <a class=\"altlink\" href=\"messages.php?action=send&amp;receiver=" . $ansprechpartner["id"] . "\">" . $ansprechpartner["username"] . "</a> oder einen anderen Admin/SysOp, wenn du Dich als Uploader bewerben willst.</td>\n".
		"                </tr>\n".
		"                <tr>\n".
		"                    <td class=\"inposttable\" valign=\"top\" width=\"140\">&nbsp;<b><font color=\"#348534\">Moderator</font></b></td>\n".
		"                    <td width=\"5\">&nbsp;</td>\n".
		"                    <td>Wird von Administratoren/SysOps vergeben, sofern welche gesucht sind. Trifft dies zu sende eine Bewerbung an <a class=\"altlink\" href=\"messages.php?action=send&amp;receiver=" . $ansprechpartner["id"] . "\">" . $ansprechpartner["username"] . "</a>.</td>\n".
		"                </tr>\n".
		"            </table>\n".
		"        </td>\n".
		"    </tr>\n".
		"</table>\n".
		"<br>\n";

	echo "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" style=\"width:" . $breite . "px\" class=\"tableinborder\">\n".
		"    <tr class=\"tabletitle\" width=\"100%\">\n".
		"        <td width=\"100%\"><span class=\"smallfont\"><center><b>Moderator Regeln</b> - Grundlegendes</center></span></td>\n".
		"    </tr>\n".
		"    <tr>\n".
		"        <td width=\"100%\" class=\"tablea\">\n".
		"            <ul>\n".
		"                <li><p>Zier dich nicht, <b>NEIN!</b> zu sagen.</p></li>\n".
		"                <li><p>Keine öffentlichen Streitigkeiten mit anderen Mods. Löst das per PM/IM.</p></li>\n".
		"                <li><p>Sei tolerant. Gib dem User eine Chance, sich zu bessern, außer es ist offensichtlich, dass er daran absolut kein Interesse zeigt.</p></li>\n".
		"                <li><p>Handle nicht voreilig. Lass einen User einen Fehler machen und korrigiere ihn danach.</p></li>\n".
		"                <li><p>Bevor du einen Account deaktivierst, sende dem User eine Message. Wenn er sich meldet, gib ihm eine Woche Zeit, sich zu bessern.</p></li>\n".
		"                <li><p>Schreibe <b>immer</b> eine Begründung, warum der betreffende User deaktiviert/verwarnt wurde.</p></li>\n".
		"            </ul>\n".
		"        </td>\n".
		"    </tr>\n".
		"</table>\n".
		"<br>\n";

	echo "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" style=\"width:" . $breite . "px\" class=\"tableinborder\">\n".
		"    <tr class=\"tabletitle\" width=\"100%\">\n".
		"        <td width=\"100%\"><span class=\"smallfont\"><center><b>Moderator Regeln</b> - Freischalteregeln</center></span></td>\n".
		"    </tr>\n".
		"    <tr>\n".
		"        <td width=\"100%\" class=\"tablea\">\n".
		"            <ul>\n".
		"                <li>Exakt gleicher Torrent nicht bereits mit aktiven Usern vorhanden.</li>\n".
		"                <li>Torrentname stimmt mit dem Muster aus den Regeln überein.</li>\n".
		"                <li>Muster- oder gleichewetige NFO korrekt ausgefüllt oder Szene-NFO vorhanden</li>\n".
		"                <li>Beschreibung korrekt.</li>\n".
		"                <li>Typ korrekt.</li>\n".
		"                <li>Movies: Screenshot vorhanden</li>\n".
		"                <li>Gastuploader hat nicht bereits einen Eigenseed aktiv</li>\n".
		"            </ul>\n".
		"        </td>\n".
		"    </tr>\n".
		"</table>\n".
		"<br>\n";


	echo "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" style=\"width:" . $breite . "px\" class=\"tableinborder\">\n".
		"    <tr class=\"tabletitle\" width=\"100%\">\n".
		"        <td width=\"100%\"><span class=\"smallfont\"><center><b>Moderator Privilegien</b> - Was kann ich alles tun?</center></span></td>\n".
		"    </tr>\n".
		"    <tr>\n".
		"        <td width=\"100%\" class=\"tablea\">\n".
		"            <ul>\n".
		"                <li>Du kannst Forumposts löschen und editieren (wenns denn mal kommt *fg*).</li>\n".
		"                <li>Du kannst Torrents löschen und editieren.</li>\n".
		"                <li>Du kannst Useravatare ändern und löschen.</li>\n".
		"                <li>Du kannst Accounts deaktivieren.</li>\n".
		"                <li>Du kannst den Titel von VIP's bearbeiten.</li>\n".
		"                <li>Du siehst die kompletten Infos von Usern.</li>\n".
		"                <li>Du kannst einem User Comments hinzufügen, die andere Mods/Admins lesen können.</li>\n".
		"            </ul>\n".
		"        </td>\n".
		"    </tr>\n".
		"</table>\n".
		"<br>\n";
}

if(isset($_GET["accept_rules"])){
	echo "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" style=\"width:" . $breite . "px\" class=\"tableinborder\">\n".
		"    <tr class=\"tabletitle\" width=\"100%\">\n".
		"        <td width=\"100%\"><span class=\"smallfont\"><center><b>Regeländerung bestätigen</b></center></span></td>\n".
		"    </tr>\n".
		"    <tr>\n".
		"        <td width=\"100%\" class=\"tablea\">\n".
		"            <p>Durch eine kürzlich vorgenommene Regeländerung musst Du erneut bestätigen, diese gelesen zu haben. Bitte denke daran, Dich auch daran zu halten!</p>\n".
		"            <p>Wenn Du die Regeln nicht akzepierst, kannst Deinen Account <a href=\"delacct.php\">hier umgehend löschen</a>.</p>\n".
		"            <form action=\"my.php\" method=\"post\">\n".
		"            <center><table>\n".
		"                <tr>\n".
		"                    <td><input type=\"checkbox\" id=\"accept\" name=\"acceptrules\" value=\"yes\"></td>\n".
		"                    <td><label for=\"accept\">Ich habe die Regeln gelesen, und bin damit einverstanden.</label></td>\n".
		"                </tr>\n".
		"                <tr>\n".
		"                    <td colspan=\"2\" style=\"text-align:center\"><input type=\"submit\" name=\"rulessubmit\" value=\"Bestätigen\"></td>\n".
		"                </tr>\n".
		"            </table></center>\n".
		"            </form>\n".
		"        </td>\n".
		"    </tr>\n".
		"</table>\n".
		"<br>\n";
}

echo "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" style=\"width:" . $breite . "px\" class=\"tableinborder\">\n".
	"    <tr class=\"tabletitle\" width=\"100%\">\n".
	"        <td width=\"100%\"><span class=\"smallfont\"><center><b>Regeln zuletzt editiert am " . $changed["at"] . " von " . $changed["by"] . "</b></center></span></td>\n".
	"    </tr>\n".
	"</table>\n";
stdfoot();
?>