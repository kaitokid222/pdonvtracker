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

ob_start("ob_gzhandler");

require "include/bittorrent.php";

stdhead("FAQ");
?>

<table cellpadding="4" cellspacing="1" border="0" style="width:750px" class="tableinborder">
 <tr class="tabletitle" width="100%">
  <td colspan="10" width="100%"><span class="normalfont"><center><b>Willkommen bei NetVision! </b></center></span></td> 
 </tr><tr><td width="100%" class="tablea">
<p>Dies ist ein privater Tracker, daher musst Du Dich registrieren, bevor Du vollen Zugriff hast.
Bevor Du irgendetwas anderes machst, solltest Du die <a  href="rules.php">Regeln</a> durchlesen!
Es sind nicht viele Regeln zu beachten, aber die Einhaltung dieser setzen wir zwingend voraus.</p>
<p>Ausserdem erklärst Du Dich mit einer Registrierung automatisch mit dem
<a href="useragreement.php">User Agreement</a> einverstanden.</p>
</td></tr></table>

<br>
<table cellpadding="4" cellspacing="1" border="0" style="width:750px;" class="tableinborder">
 <tr class="tabletitle" width="100%">
  <td colspan="10" width="100%"><span class="normalfont"><center><b>Inhalt der FAQ</b></center></span></td> 
 </tr><tr><td width="100%" class="tablea">
<ul>
<li><a href="#site"><b>Grundlegende Informationen</b></a>
  <ul>
    <li><a href="#site1" class="altlink">Was genau ist BitTorrent eigentlich und wie komme ich an die Files?</a></li>
    <li><a href="#site2" class="altlink">Wohin geht das gespendete Geld?</a></li>
    <li><a href="#site3" class="altlink">Woher bekomme ich eine Kopie der Tracker-Source?</a></li>
    <li><a href="#site4" class="altlink">Was ist ein Root- bzw. Webseeder?</a></li>
  </ul>
</li>
<br>
<li><a href="#user"><b>Benutzer Informationen</b></a>
  <ul>
      <li><a href="#user1" class="altlink">Ich habe mich registriert, jedoch keine Bestätigungsmail erhalten!</a></li>
      <li><a href="#user2" class="altlink">Ich habe meinen Benutzernamen/Passwort vergessen, könnt ihr mir diese senden?</a></li>
      <li><a href="#user3" class="altlink">Könnt ihr meinen Account umbenennen?</a></li>
      <li><a href="#user4" class="altlink">Könnt ihr meinen (aktivierten) Account löschen?</a></li>
      <li><a href="#userb" class="altlink">Was ist eigentlich meine Ratio?</a></li>
      <li><a href="#user5" class="altlink">Warum wird meine IP in meinem Profil angezeigt?</a></li>
      <li><a href="#user6" class="altlink">Hilfe! Ich kann mich nicht einloggen!</a></li>
      <li><a href="#user7" class="altlink">Meine IP ist dynamisch, wie bleibe ich eingeloggt?</a></li>
      <li><a href="#user8" class="altlink">Warum bin ich als "not connectable" aufgeführt?(Und warum sollte ich das ändern?)</a></li>
      <li><a href="#user9" class="altlink">Was bedeuten die verschiedenen Usergruppen?</a></li>
      <li><a href="#usera" class="altlink">Wie funktioniert das mit den Beförderungen?</a></li>
      <li><a href="#userb" class="altlink">Warum gibt es Power User, die weniger als 25GB geuppt haben?</a></li>
      <li><a href="#userc" class="altlink">Warum kann mein Freund kein Mitglied werden?</a></li>
      <li><a href="#userd" class="altlink">Wie füge ich meinem Profil einen Avatar hinzu?</a></li>
      <li><a href="#usere" class="altlink">Wie benutze ich den BitBucket, und was ist das eigentlich?</a></li>
      <li><a href="#userf" class="altlink">Wozu dient der PassKey?</a></li>
  </ul>
</li>
<br>
<li><a href="#stats"><b>Statistiken</b></a>
  <ul>
    <li><a href="#stats1" class="altlink">Hauptgründe für nicht aktualisierte Stats</a></li>
    <li><a href="#stats2" class="altlink">Tipps für Clients</a></li>
    <li><a href="#stats3" class="altlink">kann ich jeden BitTorrent-Clienten benutzen?</a></li>
    <li><a href="#stats4" class="altlink">Warum ist ein Torrent den ich leeche/seede mehrmals in meinem Profil aufgeführt?</a></li>
    <li><a href="#stats5" class="altlink">Ich habe den Torrent beendet/gestoppt, warum ist er trotzdem noch in meinem Profil?</a></li>
    <li><a href="#stats6" class="altlink">Warum sehe ich manchmal Torrents in meinem Profil, die ich gar nicht leeche/seede?</a></li>
    <li><a href="#stats7" class="altlink">Mehrfach-IP's (Kann ich mich von verschiedenen PC's einloggen?)</a></li>
    <li><a href="#stats8" class="altlink">Wie funktioniert das ganze mit NAT/ICS?</a></li>
  </ul>
</li>
<br>
<li><a href="#up"><b>Uploaden</b></a>
  <ul>
    <li><a href="#up1" class="altlink">Warum kann ich keine Torrents uploaden?</a> </li>
    <li><a href="#up2" class="altlink">Welche Kriterien muss ich erfüllen um Uploader zu werden?</a></li>
    <li><a href="#up3" class="altlink">Kann ich eure Torrents auf andere Tracker uploaden?</a></li>
    <li><a href="#up4" class="altlink">Ich möchte einen Torrent uploaden lassen, was muss ich beachten?</a></li>
  </ul>
</li>
<br>
<li><a href="#dl"><b>Downloaden</b></a>
  <ul>
    <li><a href="#dl1" class="altlink">Was mache ich mit den Dateien, die ich gedownloadet habe?</a></li>
    <li><a href="#dl2" class="altlink">Ich habe einen Film gedownloadet aber weiss nicht was CAM/TS/TC/SCR bedeutet?</a></li>
    <li><a href="#dl3" class="altlink">Warum ist ein aktiver Torrent plötzlich verschwunden?</a></li>
    <li><a href="#dl4" class="altlink">Wie kann ich einen abgebrochenen Download weitermachen oder etwas reseeden?</a></li>
    <li><a href="#dl5" class="altlink">Warum stoppen meine Downloads manchmal bei 99%?</a></li>
    <li><a href="#dl6" class="altlink">Was bedeutet die Fehlermeldung: "a piece has failed an hash check"?</a></li>
    <li><a href="#dlg" class="altlink">Warum wird in der Peerliste manchmal eine negative Geschwindigkeit angezeigt?</a></li>
    <li><a href="#dl7" class="altlink">Der Torrent ist 100MB gross, warum hab ich 120MB davon gezogen?</a></li>
    <li><a href="#dl8" class="altlink">Warum bekomme ich die Meldung: "Wartezeit (noch xxh) - Bitte lies das FAQ!"?</a></li>
    <li><a href="#dl9" class="altlink">Warum bekomme ich die Meldung: "Der TCP-Port xxxxx ist nicht erlaubt."?</a></li>
    <li><a href="#dla" class="altlink">Warum bekomme ich die Meldung: "Du benutzt einen gebannten Client. Bitte lies das FAQ!"?</a></li>
    <li><a href="#dlb" class="altlink">Warum bekomme ich die Meldung: "Ungueltiger PassKey. Lies das FAQ!"?</a></li>
    <li><a href="#dlc" class="altlink">Warum bekomme ich die Meldung: "Zu viele unterschiedliche IPs fuer diesen Benutzer (max <?=$MAX_PASSKEY_IPS?>)"?</a></li>
    <li><a href="#dld" class="altlink">Was bedeutet "IOError - [Errno13] Permission denied"?</a></li>
    <li><a href="#dle" class="altlink">Was bedeutet "TTL" in der Torrentssektion?</a></li>
    <li><a href="#dlf" class="altlink">Wozu dient die Wartezeitaufhebung und welche Kriterien muss ich daf&uuml;r erf&uuml;llen?</a></li>
  </ul>
</li>
<br>
<li><a href="#dlsp"><b>Wie kann ich meinen Downloadspeed erhöhen?</b></a></p>
  <ul>
    <li><a href="#dlsp1" class="altlink">Lade die aktuellsten Torrents nicht gleich bei Erscheinen</a></li>
    <li><a href="#dlsp2" class="altlink">Mach dich selbst "erreichbar"</a></li>
    <li><a href="#dlsp3" class="altlink">Setze deine Uploadgeschwindigkeit nicht auf "Unlimited"</a></li>
    <li><a href="#dlsp4" class="altlink">Limitiere die maximale Anzahl Verbindungen</a></li>
    <li><a href="#dlsp5" class="altlink">Limitiere die Anzahl gleichzeitiger Uploads</a></li>
    <li><a href="#dlsp6" class="altlink">Habe einfach etwas Geduld ;)</a></li>
    <li><a href="#dlsp7" class="altlink">Warum ist das Browsen so langsam wenn ich leeche?</a></li>
  </ul>
</li>
<br>
<li><a href="#prox"><b>Mein ISP benutzt einen transparenten Proxy, was soll ich tun?</b></a>
  <ul>
      <li><a href="#prox1" class="altlink">Was ist ein Proxy?</a></li>
      <li><a href="#prox2" class="altlink">Wie finde ich heraus, dass ich hinter einem transparenten/anonymen Proxy bin?</a></li>
      <li><a href="#prox3" class="altlink">Warum steht bei mir "not connectable" auch wenn ich nicht über eine Firewall oder per NAT im Internet bin?</a></li>
      <li><a href="#prox4" class="altlink">Kann ich meinen ISP Proxy umgehen?</a></li>
      <li><a href="#prox5" class="altlink">Wie konfiguriere ich meinen BT Clienten für einen Proxy?</a></li>
      <li><a href="#prox6" class="altlink">Warum kann ich mich über einen Proxy nicht anmelden?</a></li>
      <li><a href="#prox7" class="altlink">Gilt dies auch für andere Tracker?</a></li>
  </ul>
</li>
<br>
<li><a href="#conn"><b>Warum kann ich nicht connecten? Werde ich geblockt?</b></a></li>
  <ul>
    <li><a href="#conn2" class="altlink">Vielleicht ist meine Adresse geblockt?</a></li>
    <li><a href="#conn3" class="altlink">Dein ISP blockt die Site-Adresse</a></li>
    <li><a href="#conn4" class="altlink">Alternativport (81)</a>
  </ul>
</li>
<br>
    <li><a href="#other"><b>Was soll ich tun wenn ich die Antwort hier nicht finde?</b></a>
</td></tr></table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:750px" class="tableinborder">
 <tr class="tabletitle" width="100%">
  <td colspan="10" width="100%"><span class="normalfont"><center><b>Grundlegende Informationen<a name="site" id="site"></a></center></span></td> 
 </tr><tr><td width="100%" class="tablea">
<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Was genau ist BitTorrent eigentlich und wie komme ich an die Files?</b><a name="site1" id="site1"></a></td></tr>
<tr><td class="tablea">Hier gibt es ein umfangreiches FAQ --> <a class=altlink href="redir.php?http://www.btfaq.com/">Brian's BitTorrent FAQ and Guide</a>.</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Wohin geht das gespendete Geld?</b><a name="site2" id="site2"></a></td></tr>
<tr><td class="tablea">Dieser Tracker läuft auf einem gemieteten Server, die wir bezahlen müssen. Die Spenden fliessen in diesen Server.</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Woher bekomme ich eine Kopie der Tracker-Source?<a name="site3" id="site3"></a></b></td></tr>
<tr><td class="tablea">Alles rund um die pdonv-Source gibts <b><a class=altlink href="redir.php?url=http://www.netvision-technik.de/">hier</a></b>.</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Was ist ein Root- bzw. Webseeder?<a name="site4" id="site4"></a></b></td></tr>
<tr><td class="tablea"><p>Ein Webseeder ist jemand, der einen Server bei einem Provider
gemietet hat, der mit einer hohen Bandbreite an das Internet angeschlossen ist,
in der Regel 10 oder sogar 100 MBit. Dadurch kann dieser Benutzer mit sehr
hohen Geschwindigkeiten Daten hochladen, und einen großen Torrent schnell verteilen.</p>

<p>Da jeoch bei so hohen Geschwindigkeiten ein Torrent nur schlecht unter den Leechern
verteilt wird, da die mit den schnellsten Leitungen zuerst fertig werden, ist es nicht ratsam,
mit so hohen Geschwindigkeiten zu seeden. Da auf einem Anti-Leech-Tracker wie diesem
eine gute Ratio nötig ist, nehmen Webseeder häufig anderen Benutzern die Möglichkeit,
ihre Ratio halten zu können, da sie im Vergleich sehr langsam Daten hochladen. Aus diesem
Grund gibt es einige Beschränkungen für Webseeder, die eingehalten werden müssen.</p></td></tr>
</table>
</td></tr></table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:750px" class="tableinborder">
 <tr class="tabletitle" width="100%">
  <td colspan="10" width="100%"><span class="normalfont"><center><b>Benutzer Informationen<a name="user" id="user"></a></center></span></td> 
 </tr><tr><td width="100%" class="tablea">
<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Ich habe mich registriert, jedoch keine Bestätigungsmail erhalten!</b><a name="user1" id="user1"></a></td></tr>
<tr><td class="tablea">Du kannst <a class=altlink href="delacct.php">dieses Formular</a> benutzen, um Deinen Account zu löschen und neu zu registrieren.
Wenn Du keine Mail bekommen hast, solltest du es mit einer anderen E-Mail Adresse versuchen.</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Ich habe meinen Benutzernamen/Passwort vergessen, könnt ihr mir diese senden?</b><a name="user2" id="user2"></a></td></tr>
<tr><td class="tablea">Bitte nutze <a class=altlink href="recover.php">dieses Formular</a> um Dir deine Daten zusenden zu lassen. Kommt keine Mail, wende Dich an einen Admin.</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Könnt ihr meinen Account umbenennen?</b><a name="user3" id="user3"></a></td></tr>
<tr><td class="tablea">Wir benennen keine Accounts um. (Mit <a href=delacct.php class=altlink>diesem Formular</a> kannst Du
Deinen Account löschen und einen neuen registrieren)</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Könnt ihr meinen (aktivierten) Account löschen?</b><a name="user4" id="user4"></a></td></tr>
<tr><td class="tablea">Das kannst du selbst tun, indem Du <a href=delacct.php class=altlink>dieses Formular</a> benutzt.</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Was ist eigentlich meine Ratio?</b><a name="userb" id="userb"></a></td></tr>
<tr><td class="tablea">Klicke auf dein <a class=altlink href=my.php>Profil</a> und dann auf deinen Benutzernamen ganz oben.<br>
<br>
Es ist wichtig, zwischen deiner Gesamtratio und deiner Ratio pro Torrent zu unterscheiden. Die Gesamtratio
errechnet sich aus deinem Gesamtupload und -download. Die Ratio pro Torrent hingegen gilt nur für dein
aktives Torrent.<br>
<br>
Bei deiner Ratio können abgesehen von einer Zahl zwei andere Dinge stehen. Entweder "Inf." für eine unendliche Ratio
(ergibt sich, wenn du noch nichts gedownloadet hast aber schon etwas geseedet) oder "---" wenn du weder etwas gedownloadet
oder geseet hast.</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Warum wird meine IP in meinem Profil angezeigt?</b><a name="user5" id="user5"></a></td></tr>
<tr><td class="tablea">Nur Du und die Mods/Admins können Deine IP und E-Mail in Deinem Profil sehen,
die regulären User können dies nicht.</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Hilfe! Ich kann mich nicht einloggen!</b><a name="user6" id="user6"></a></td></tr>
<tr><td class="tablea">Dieses Problem taucht manchmal mit dem MS Internet Explorer auf. Schliesse alle
Browserfenster und lösche die Cookies in den Internetoptionen. Danach müsstest
Du Dich wieder einloggen können.</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Meine IP ist dynamisch, wie bleibe ich eingeloggt?</b><a name="user7" id="user7"></a></td></tr>
<tr><td class="tablea">Normalerweise sollte der Tracker bei einem IP-Wechsel automatisch die neue IP übernehmen, solange du
beim Torrentstart eingeloggt warst. Sollte dies nicht der Fall sein, musst du dich nur kurz einloggen
und dann kanns weitergehen.</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Warum bin ich als "not connectable" aufgeführt? (Und warum sollte ich das ändern?)</b><a name="user8" id="user8"></a></td></tr>
<tr><td class="tablea">Der Tracker sieht, dass Du per Firewall oder NAT eingeloggt bist
und Du somit keine einkommenden Verbindungen annehmen kannst.<br>
<br>
Das wiederum heisst, dass eine Verbindung untereinander unmöglich ist, wenn zwei User beim
selben Torrent diesen Status haben. Um dieses Problem zu beheben musst Du bei Deiner Firewall
die Ports zu deiner IP weiterleiten. Diese Ports müssen mit denen übereinstimmen,
die Du in deinem Clienten angegeben hast. Mehr dazu findest Du in der Dokumentation Deiner Firewall.
Ausserdem findest Du auch bei <a class=altlink
href="redir.php?url=http://portforward.com/">portforward.com</a> viele Informationen zu diesem Thema.</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Was bedeuten die verschiedenen Usergruppen?</b><a name="user9" id="user9"></a></td></tr>
<tr><td class="tablea"><table cellspacing=1 cellpadding=2>
<tr>
    <td class=inposttable width=100>&nbsp; <font class="ucuser"><b>User</b></font></td>
    <td width=5>&nbsp;</td>
    <td>Die Standardklasse eines neuen Mitglieds.</td>
</tr>
<tr>
    <td class=inposttable>&nbsp; <font class="ucpoweruser"><b>Power User</b></font></td>
    <td width=5>&nbsp;</td>
    <td>Kann NFO Files lesen.</td>
</tr>
<tr>
    <td class=inposttable>&nbsp; <b><img src="<?=$GLOBALS["PIC_BASE_URL"]?>star.gif" alt="Star"></td>
    <td width=5>&nbsp;</td>
    <td>Hat Geld gespendet . </td>
</tr>
<tr>
    <td class=inposttable valign=top>&nbsp; <font class="ucvip"><b>VIP</b></font></td>
    <td width=5>&nbsp;</td>
    <td valign=top>Gleiche Privilegien wie Power User, wird aber nicht automatisch vergeben.</td>
</tr>
<tr>
    <td class=inposttable>&nbsp; <b>Spezialtitel</b></td>
    <td width=5>&nbsp;</td>
    <td>Benutzerdefinierte Titel, Farbe entspricht Benutzer-Rang.</td>
</tr>
<tr>
    <td class=inposttable>&nbsp; <font class="ucuploader"><b>Uploader</b></font></td>
    <td width=5>&nbsp;</td>
    <td>Gleiche Privilegien wie PU, hat zusätzlich Uploadfähigkeit und wird nicht automatisch zurückgestuft.</td>
</tr>
<tr>
    <td class=inposttable>&nbsp; <font class="ucguteam"><b>GU-Betreuer</b></font></td>
    <td width=5>&nbsp;</td>
    <td>Gleiche Privilegien wie Uploader, kann aber Gastuploads vor der Freischaltung bearbeiten und diese (falls korrekt) freischalten.</td>
</tr>
<tr>
    <td class=inposttable valign=top>&nbsp; <font class="ucmoderator"><b>Moderator</b></font></td>
    <td width=5>&nbsp;</td>
    <td valign=top>Kann Torrents editieren und löschen, Kommentare editieren und Accounts deaktivieren.</td>
</tr>
<tr>
    <td class=inposttable>&nbsp; <font class="ucadministrator"><b>Administrator</b></font></td>
    <td width=5>&nbsp;</td>
    <td>Können sozusagen fast alles ;).</td>
</tr>
<tr>
    <td class=inposttable>&nbsp; <font class="ucsysop"><b>SysOp</b></font></td>
    <td width=5>&nbsp;</td>
    <td>Eigentümer, kann absolut <b>alles</b>.</td>
</tr>
</table>
</td></tr></table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Wie funktioniert das mit den Beförderungen?</b><a name="usera" id="usera"></a></td></tr>
<tr><td class="tablea">
<table cellspacing=1 cellpadding=2>
<tr>
    <td class=inposttable valign=top width=100>&nbsp; <font class="ucpoweruser"><b>Power User</b></font></td>
    <td width=5>&nbsp;</td>
    <td valign=top>Muss seit mindestens 4 Wochen dabei sein, mind. 25GB geuploadet haben und eine
    Ratio von 1.05 oder höher vorweisen können.<br>
    Trifft dies zu, wird der User automatisch zum Power User, sollte die Ratio jedoch unter 0.95 fallen<br>
    wird er automatisch wieder zurückgestuft.</td>
</tr>
<tr>
    <td class=inposttable>&nbsp; <b><img src="<?=$GLOBALS["PIC_BASE_URL"]?>star.gif" alt="Star"></td>
    <td width=5>&nbsp;</td>
    <td>User, die NetVision mit Spenden unterstützen (frag uns, wenn Du spenden m&ouml;chtest!)</td>
</tr>
<tr>
    <td class=inposttable valign=top>&nbsp; <font class="ucvip"><b>VIP</b></font></td>
    <td width=5>&nbsp;</td>
    <td valign=top>Wird von Mods vergeben, wenn sie von einem User denken, er habe sich den Titel verdient.<br>
    (Jeder, der um diesen Status bettelt, ist disqualifiziert <img src="<?=$GLOBALS["PIC_BASE_URL"]?>smilies/wink.gif" alt=";)">)</td>
</tr>
<tr>
    <td class=inposttable>&nbsp; <b>Spezialtitel</b></td>
    <td width=5>&nbsp;</td>
    <td>Wird von Mods auf Wunsch vergeben. Bringt keine speziellen Privilegien mit sich.</td>
</tr>
<tr>
    <td class=inposttable>&nbsp; <font class="ucuploader"><b>Uploader</b></font></td>
    <td width=5>&nbsp;</td>
    <td>Wird von Admins/SysOps vergeben, Kriterien zu finden in der "Upload" Sparte.</td>
</tr>
<tr>
    <td class=inposttable valign=top>&nbsp; <font class="ucguteam"><b>GU-Betreuer</b></font></td>
    <td width=5>&nbsp;</td>
    <td>Wird von Admins/SysOps bevorzugt an Uploader vergeben, muss sich mit den Upload-Regeln gut auskennen und sollte auch
    rhetorisch etwas begabt sein, da dieser Rang sehr viel Kommunikation mit Gastuploadern erfordert.</td>
</tr>
<tr>
    <td class=inposttable>&nbsp; <font class="ucmoderator"><b>Moderator</b></font></td>
    <td width=5>&nbsp;</td>
    <td>Du fragst nicht uns, wir fragen Dich. Moderatorentitel werden nur bei Bedarf
        an vom Team ausgesuchte Mitglieder vergeben.</td>
</tr>
</table>
</td></tr></table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Warum gibt es Power User, die weniger als 25GB geuppt haben?</b><a name="userb"></a></td></tr>
<tr><td class="tablea">Das Limit für PU war anfangs tiefer und wir haben niemanden heruntergestuft,
bei der Limitänderung.</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Warum kann mein Freund kein Mitglied werden?</b><a name="userc"></a></td></tr>
<tr><td class="tablea">Wir haben ein Userlimit von <?=$GLOBALS["MAX_USERS"]?>, danach ist die Anmeldung deaktiviert. Versucht es einfach ab und zu
wieder, da inaktive Accounts nach 42 Tagen automatisch gelöscht werden und Leecher ebenfalls gekickt werden.
Es gibt keine Warteschlange o.Ä., Du musst einfach zur richtigen Zeit am richtigen Ort sein.</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Wie füge ich meinem Profil einen Avatar hinzu?</b><a name="userd"></a></td></tr>
<tr><td class="tablea">Zuerst such dir ein Bild aus, das den 
<a class=altlink href=rules.php>Regeln</a> entspricht. Danach musst Du dieses Bild auf einen Webserver oder
in unseren <a class="altlink" href="bitbucket.php">BitBucket</a> hochladen. (Kostenloses Bilderhosting &uuml;bernehmen z.B 
<a class="altlink" href="http://uploadit.org/">Upload-It!</a> oder
<a class="altlink" href="http://www.imageshack.us/">ImageShack</a>).
Alles was Du jetzt noch tun musst, ist die URL zu deinem Avatar in dein
<a class="altlink" href="my.php">Profil</a> einzutragen.</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Wie benutze ich den BitBucket, und was ist das eigentlich?</b><a name="usere"></a></td></tr>
<tr><td class="tablea">Der <a class="altlink" href="bitbucket.php">BitBucket</a> ist eine Art Verzeichnis,
in dem Du Bilder ablegen kannst, die Du auf dem Tracker benutzen m&ouml;chtest. Dazu z&auml;hlen beispielsweise
Dein Avatar und Bilder zu Deinen Torrents (Screenshots, Covers, etc.). Du hast auf unserem Tracker f&uuml;r
Deine Bilder insgesamt 1 MB (=1.024 KB) Platz zur Verf&uuml;gung, und die maximale Gr&ouml;&szlig;e pro Bilddatei
betr&auml;gt 256 KB.<br>
<br>
Du kannst Die hochgeladenen Bilder jederzeit wieder l&ouml;schen, um f&uuml;r neue Platz zu schaffen.
In der BitBucket-&Uuml;bersicht werden die Original-Dateinamen der hochgeladenen Bilder angezeigt. Um
an den Link auf eines Deiner Bilder zu kommen, klicke einfach mit der rechten Maustaste auf den Bildnamen,
und w&auml;hle aus dem Popup-Men&uuml; den Befehl "Link-Adresse kopieren" aus. Diesen Link kannst Du
nun als Avatar-URL oder in Deiner Torrent-Beschreibung benutzen.<br>
<br>
Bitte beachte, dass eine externe Verlinkung der Bilder nicht m&ouml;glich ist. Die hochgeladenen Bilder
sind alleine zur Verwendung auf diesem Tracker bestimmt. Ebenso m&uuml;ssen s&auml;mtliche hochgeladenen
Bilder mit den <a class=altlink href=rules.php>Avatar-Regeln</a> konform sein!</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Wozu dient der PassKey?</b><a name="userf"></a></td></tr>
<tr><td class="tablea">Der PassKey ist wie ein Passwort f&uuml;r Deine Torrents. Jeder Torrent, den Du von diesem
Tracker runterl&auml;dst, wird mit Deinem pers&ouml;nlichen PassKey versehen. Der Tracker
ordnet mittels dieses Keys dann deinen Client Deinem Profil zu. Dieses Verfahren hat mehrere
Vorteile gegen&uuml;ber der alten Methode, Dich anhand Deiner IP-Adresse zuzuordnen.<br>
<br>
Du hast so selber die totale Kontrolle dar&uuml;ber, wer Deinen Account benutzt. Wenn Du
Deine Account-Zugangsdaten und die heruntergeladenen Torrents unter Verschluss beh&auml;ltst,
ist es unm&ouml;glich, dass jemand &uuml;ber Dein Profil leecht. Sollte einmal jemand eine
Torrent-Datei von Dir erhalten haben, und entgegen Deinem Willen &uuml;ber Dein Profil
leechen, kannst Du in Deinem Profil einfach einen neuen PassKey generieren. Dadurch werden
alle vorher von Dir runtergeladenen Torrents unbrauchbar, und Du musst die noch aktiven Torrents
erneut &uuml;ber die Download-Funktion herunterladen, oder die Announce-URL manuell &auml;ndern.<br>
<?php if ($CURUSER) { ?>Deine aktuelle Announce-URL findest Du in <a href="userdetails.php?id=<?=$CURUSER["id"]?>">Deinem
Profil</a>.<br><?php } ?>
<br>
Ein weiterer Vorteil ist es, dass es nun keine Probleme mehr gibt, wenn mehrere Benutzer auf
dem Tracker sich eine IP-Adresse teilen (z.B. in einer WG etc.). Da die Benutzer anhand des
PassKeys identifiziert werden, k&ouml;nnen die Peers dennoch korrekt zugeordnet werden.<br>
<br>
Da hierdurch aber auch die Mi&szlig;brauchsgefahr gesteigert wird, wenn z.B. ein Torrent
auf einer fremden Seite hochgeladen oder &uuml;ber IRC verbreitet wird, sind pro
PassKey/User maximal <?=$MAX_PASSKEY_IPS?> verschiedene IP-Adressen erlaubt. Dieses Limit gilt f&uuml;r alle
Benutzer, und kann nicht individuell angehoben werden, fragt also bitte nicht danach!</td></tr>
</table>
<br>
</td></tr></table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:750px" class="tableinborder">
 <tr class="tabletitle" width="100%">
  <td colspan="10" width="100%"><span class="normalfont"><center><b>Stats<a name="stats"></a></b></center></span></td> 
 </tr><tr><td width="100%" class="tablea">

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Hauptgründe für nicht aktualisierte Stats</b><a name="stats1"></a></td></tr>
<tr><td class="tablea">
<ul>
    <li>Der User cheatet --> resultiert in Ban</li>
    <li>Der Server ist überlastet und reagiert nicht --> Lasst einfach die Session offen und habt Geduld,
        dauerndes manuelles Updaten resultiert nur in einer längeren Downtime</li>
    <li>Du benutzt einen fehlerhaften Client. Experimentelle oder CVS Clients benutzt du auf eigenes Risiko.
        Ebenso behalten wir es uns vor, bestimmte Clients vom Tracker auszuschlie&szlig;en.</li>
</ul></td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Tipps für Clients</b><a name="stats2"></a></td></tr>
<tr><td class="tablea">
<ul>
    <li>Wenn einer Deiner aktuellen Torrents nicht in deinem Profil aufgeführt ist, 
        warte oder mache ein manuelles Update.</li>
    <li>Beende deinen Client richtig, damit der Tracker das completed Event erhält.</li>
    <li>Sollte der Tracker down sein, seede weiter, sonst werden die Statistiken für
        Dich nicht korrekt übertragen und Dir können Daten fehlen.</li>
</ul></td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Kann ich jeden BitTorrent-Clienten benutzen?</b><a name="stats3"></a></td></tr>
<tr><td class="tablea">
Prinzipiell ja, der Tracker sollte die Daten von jedem Clienten richtig verstehen.<br>
Auf Clients im Beta- oder CVS-Stadium sollte man allerdigns verzichten, da diese in einigen
F&auml;llen fehlerhafte Daten an den Tracker senden, und so Deine Statistiken nicht
korrekt aktualisiert werden.<br>
Beachte bitte auch, dass wir einige spezielle Clients ausschlie&szlig;en, die daf&uuml;r
bekannt sind, absichtlich falsche Daten an den Tracker zu senden. Wir werden keine Liste
dieser Clients ver&ouml;ffentlichen, und auch nicht, welche Methoden wir zur Identifizierung
verwenden.</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Warum ist ein Torrent, den ich leeche/seede, mehrmals in meinem
Profil aufgeführt?</b><a name="stats4"></a></td></tr>
<tr><td class="tablea">
Sollte aus irgendeinem Grund Dein PC oder Dein Client abgestürzt sein und Du verbindest Dich neu, hast Du eine andere
ID, was darin resultiert, das Dein Torrent neu angezeigt wird. Die alten sind noch zu sehen, bis ein TimeOut erfolgt.
Du kannst es ignorieren, bis es von selbst verschwindet. Deine Statistiken werden trotzdem korrekt aktualisiert.</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Ich habe den Torrent beendet/gestoppt, warum ist er trotzdem noch
in meinem Profil?</b><a name="stats5"></a></td></tr>
<tr><td class="tablea">
Einige Clienten liefern den "completed" Event nicht richtig, daher weiss der Tracker nicht, dass Du Deinen Download
beendet/abgebrochen hast. Ignoriere es einfach, bis der Torrent nach dem TimeOut automatisch verschwindet.</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Warum sehe ich manchmal Torrents in meinem Profil,
die ich gar nicht leeche/seede?</b><a name="stats6"></a></td></tr>
<tr><td class="tablea">
Durch das neue PassKey-System sollte dieses Problem behoben sein, weswegen dieser Fehler nicht mehr
vorkommen sollte. Ist das dennoch der Fall, solltest Du sowohl Dein Account-Passwort, als auch Deinen
PassKey zur&uuml;cksetzen, da entweder jemand Zugriff auf Deinen Account hat, oder eines Deiner
Torrent-Files in die Finger bekommen hat.</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Mehrfach-IP's (Kann ich mich von verschiedenen PC's 
einloggen?)</b><a name="stats7" id="stats7"></a></td></tr>
<tr><td class="tablea">
Durch das neue PassKey-System ist dies nun ohne Weiteres m&ouml;glich, es ist keine Anmeldung
am Tracker mehr n&ouml;tig, bevor Du den Torrent startest. Es gibt aber nun eine neue Beschr&auml;nkung,
dass Du nur von maximal <?=$MAX_PASSKEY_IPS?> verschiedenen IP-Adressen gleichzeitg auf den
Tracker zugreifen kannst (per BitTorrent-Client).</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Wie funktioniert das ganze mit NAT/ICS?<a name="stats8" id="stats8"></a></b></td></tr>
<tr><td class="tablea">
Versuche bei ICS den Client auf dem Gateway-PC laufen zu lassen. Clients, die auf den PCs hinter dem Gateway laufen, 
können sich wahrscheinlich nicht zum Tracker und den anderen Peers verbunden (die Ports werden dann als "---" angezeigt) 
es sei denn, du konfigurierst dein ICS aufwendig (eine gute Anleitung findest du <a class=altlink href="redir.php?url=http://www.microsoft.com/downloads/details.aspx?FamilyID=1dcff3ce-f50f-4a34-ae67-cac31ccd7bc9&amp;displaylang=en">hier</a>). Sind die Clients hinter einer 
NAT, dann solltest du den Clients verschiedene Ports geben. (Wie das geht ist von Router zu Router unterschiedlich.)<br>
<br>
Da das PassKey-System nun die Benutzer &uuml;ber die Torrent-Datei und den darin enthaltenen
PassKey identifiziert, ist es nun problemlos m&ouml;glich, dass verschiedene Nutzer von einer
einzelnen IP aus auf den Tracker zugreifen. Hierf&uuml;r muss nat&uuml;rlich jeder Benutzer seine
eigenen Torrent-Dateien verwenden!</td></tr>
</table>
<br>
</td></tr></table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:750px" class="tableinborder">
 <tr class="tabletitle" width="100%">
  <td colspan="10" width="100%"><span class="normalfont"><center><b>Uploaden</b><a name="up"></a></b></center></span></td> 
 </tr><tr><td width="100%" class="tablea">

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Warum kann ich keine Torrents uploaden?</b><a name="up1"></a></td></tr>
<tr><td class="tablea">
Prinzipiell hat jeder Benutzer die Möglichkeit, einen Torrent hochzuladen. Bdvor dieser auf
dem Tracker erscheint, muss er aber von einem Moderator freigeschaltet werden. Nur speziell
autorisierte User (<font color="#4040C0"><b>Uploader</b></font>) haben das Recht,
Torrents hochzuladen, die ohne Freischaltung direkt verfügbar sind.<br>
<br>
Wenn Du keinen Zugriff auf das Upload-Formular hast, wurde Dein Uploadrecht von
einem Moderator entfernt. Wenn Du nicht weißt warum das passiert ist, solltest
Du ein Teammitglied über einen der üblichen Wege kontaktieren.</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Welche Kriterien muss ich erfüllen um <font color="#4040C0">Uploader</font>
zu werden?</b><a name="up2"></a></td></tr>
<tr><td class="tablea">
Du musst:
<ul>
<li>mindestens 4 Torrents pro Monat hochladen,</li>
<li>eine NFO zum Torrent schreiben,</li>
<li>Szene-Releases im Originalzustand seeden,</li>
<li>darauf achten, dass dein Torrent lange genug geseedet wird,</li>
<li>eine Upload-Geschwindigkeit von mindestens 32 KB/sek, besser mehr</li>
<li>mindestens 5GB geuppt haben, l&auml;nger als 4 Wochen dabei sein, und eine Ratio von 1.1+ aufweisen.</li>
</ul>
Wenn du diese Kriterien erfüllst, <a class=altlink href=staff.php>melde dich</a> bei einem Administrator.<br>
<b>Merke!</b> Am Besten Dein Themengebiet angeben, in welchem Du etwas uppst und Deine Uploadgeschwindigkeit.<br>
<br>
Wenn Du nur unregelmäßig bzw. unter 4 Torrents im Monat anzubieten hast, benötigst Du diesen Rang
nicht. Benutze einfach den normalen Benutzer-Upload mit Freischaltung.</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Kann ich eure Torrents auf andere Tracker uploaden?</b><a name="up3"></a></td></tr>
<tr><td class="tablea">
Nein. Da sich User bei uns registrieren müssen und je nach Situation die Anmeldung geschlossen wird, rückt das
unseren Tracker nur in ein schlechtes Licht, wenn sich die User was ziehen wollen, aber sich nicht registrieren
können. Au&szlig;erdem ist der Torrent &uuml;ber den PassKey fest mit Deinem Account verkn&uuml;pft,
und s&auml;mtliche Daten, die dann gesaugt werden, werden Deinem Profil angerechnet!<br>
<br>
Mit Deinen heruntergeladenen Files kannst Du hingegen machen was du willst, z.B. einen neuen Torrent erstellen
und auf einem Tracker Deiner Wahl uploaden.<br>
</td></tr></table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Ich möchte einen Torrent uploaden, was muss ich beachten?</b><a name="up4"></a></td></tr>
<tr><td class="tablea">
<p>Auch wenn Du kein Uploader bist, kannst Du gerne einen Torrent auf dem Tracker anbieten
und selber hochladen. Jedoch gelten für Dich die selben Regeln wie für "echte" Uploader.</p>
<ul>
<li><p>Wenn Du Dir unsicher bist, ob Dein Torrent den Tracker-Regeln entspricht (Inhalt, Größe, Art
der Software, ...), dann frage bitte vorher unbedingt einen Moderator um Rat!</p></li>
<li><p>Erstelle den Torrent mit der korrekten Announce-URL. Folgende URLs sind erlaubt (Nur <b>EINE</b>
    davon benutzen):</p>
<ol>
<?php
sort($GLOBALS["ANNOUNCE_URLS"]);
for ($I=0; $I<count($GLOBALS["ANNOUNCE_URLS"]); $I++) echo "<li>", htmlspecialchars($GLOBALS["ANNOUNCE_URLS"][$I]), "</li>\n";
?>
</ol>
</li>
<li><p>Wenn das Release ein Original-Scene-Release ist, dann sende dem Uploader die beiliegende .NFO mit
dem Torrent zu, wenn keine .NFO dabei ist, erstelle selbst eine normale Textdatei mit Infos zu
Deinem Torrent. Je nach Typ variieren die nötigen Infos, grundsätzlich sollten folgende Dinge enthalten
sein:</p>
<ol>
<li>Name und evtl. Produzent/Hersteller</li>
<li>Inhaltszusammenfassung oder Beschreibung</li>
<li>Informationen zu Systemvoraussetzungen, Qualität, verwendete Codecs, Laufzeit, ...</li>
<li>Nötige Seriennummern, CD-Keys etc.</li>
<li>Kurze Installationsanleitung, falls relevant</li>
</ol>
</li>
<li><p>Falls vorhanden, max. 2 Cover-Bilder, Screenshots etc., die oberhalb der
Torrentbeschreibung angezeigt werden sollen.</p></li>
</ul>
<p>Wenn Du diese Dinge beachtest, steht der Freischaltung des Uploads nichts mehr im Weg.
Bei weiteren Fragen zu spezielleren Themen wende Dich bitte an einen Moderator.</p>
</td></tr></table>
<br>
</td></tr></table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:750px" class="tableinborder">
 <tr class="tabletitle" width="100%">
  <td colspan="10" width="100%"><span class="normalfont"><center><b>Downloaden</b><a name="dl"></a></b></center></span></td> 
 </tr><tr><td width="100%" class="tablea">

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Was mache ich mit den Dateien, die ich gedownloadet habe?</b><a name="dl1"></a></td></tr>
<tr><td class="tablea">
<a class="altlink" href="formats.php">Hier</a> gibts Infos dazu (auf Englisch).</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Ich habe einen Film gedownloadet aber weiss nicht
was CAM/TS/TC/SCR bedeutet?</b><a name="dl2"></a></td></tr>
<tr><td class="tablea">
<a class="altlink" href="videoformats.php">Hier</a> gibts Infos dazu (auf Englisch).</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Warum ist ein (aktiver) Torrent plötzlich
verschwunden?</b><a name="dl3" id="dl3"></a></td></tr>
<tr><td class="tablea">
Dafür können drei Gründe verantwortlich sein:<br>
<ol type="1">
<li>Der Torrent verstösst gegen die <a class=altlink href=rules.php>Regeln</a>.</li>
<li>Der Uploader/ein Mod oder höher hat den Torrent aus irgendwelchen Gründen gelöscht.</li>
<li>Torrents werden automatisch gelöscht, wenn sie l&auml;nger als <?=$GLOBALS["MAX_DEAD_TORRENT_TIME"]?>
 Tage inaktiv sind und das Upload-Datum mindestens <?=$GLOBALS["MAX_TORRENT_TTL"]?> Tage zur&uuml;ckliegt.</li>
</ol></td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Wie kann ich einen abgebrochenen Download weitermachen
oder etwas reseeden?</b><a name="dl4" id="dl4"></a></td></tr>
<tr><td class="tablea">
Öffne das .torrent File und gib als Speicherort Deine vorhanden Dateien an. Dein Client wird dann
die schon fertigen Daten &uuml;berpr&uuml;fen, und mit dem Download fortfahren bzw. beginnen
zu seeden.</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Warum stoppen meine Downloads manchmal bei 99%?</b><a name="dl5"></a></td></tr>
<tr><td class="tablea">
Umso mehr Teile einer Datei Du hast, um so schwerer wird es, User zu finden, die die Stücke haben, 
die Du brauchst. Warte einfach ab und trinke einen Pott Kaffee, irgendwann geht es weiter.
Normalerweise sollte es mit BitTorrent aber auch bei den letzten Daten noch schnell gehen.</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Was bedeutet die Fehlermeldung: "a piece has
failed a hash check"?</b><a name="dl6"></a></td></tr>
<tr><td class="tablea">
Dein Client überprüft jeden fertigen Teil deines Torrents auf Fehler, findet er einen fehlerhaften
Teil, lädt er diesen neu.<br>
<br>
Einige Clienten haben die Möglichkeit, User zu bannen, die Dir absichtlich fehlerhafte Teile schicken.
Dies macht insofern Sinn, dass Du weniger unn&uuml;tze Daten runterl&auml;dst, und sollte
demnach aktiviert werden.</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Warum wird in der Peerliste manchmal eine negative Geschwindigkeit angezeigt?</b><a name="dlg"></a></td></tr>
<tr><td class="tablea">
Dies ist ein kleiner Fehler im Tracker, der recht selten auftritt und keine Auswirkungen
auf die Ratio des betreffenden Benutzers hat. Der tatsächliche Upload bzw. Download
wird korrekt angerechnet, die Geschwindigkeit spielt hierbei keine Rolle, da diese nur
zu informativen Zwecken für die Tabelle berechnet wird.</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Der Torrent ist 100MB gross, warum hab
ich z.B. 120MB davon gezogen?</b><a name="dl7"></a></td></tr>
<tr><td class="tablea">
Aufgrund des oben beschrieben Fehlers kann es sein, dass Du manche Teile mehrmals laden musst.
Daraus resultiert auch eine höhere Downloadmenge, die Deinem Profil aber je nach Client nicht
unbedingt angerechnet wird.</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Warum bekomme ich die Meldung: "Wartezeit
(noch XXh) - Bitte lies das FAQ!"?</b><a name="dl8" id="dl8"></a></td></tr>
<tr><td class="tablea">
Vom Zeitpunkt des Uploads eines <b>neuen</b> Torrents, zählt eine Wartezeit für gewisse User.<br>
Diese Wartezeit betrifft nur User mit geringer Ratio und geringem Upload.<br>
<br>
<table cellspacing=1 cellpadding=2>
  <tr>
    <td width="70">Ratio unter</td>  
    <td width="10">&nbsp;</td>
    <td width="130">und/oder Upload unter</td>
    <td width="10">&nbsp;</td>
    <td width="130">und registriert seit weniger als </td>
    <td width="10">&nbsp;</td>
    <td width="80">Wartezeit von</td>
  </tr>
<?php
    $wrules = explode("|", $GLOBALS["WAIT_TIME_RULES"]);
    for ($I=0; $I<count($wrules); $I++) {
        $wrule = explode(":", $wrules[$I]);
        preg_match("/([0-9]+w)?([0-9]+d)*|([\\*0])?/", $wrule[2], $regrule);
        $regruledays = intval($regrule[1])*7 + intval($regrule[2]);        
        if ($regruledays)
            $regruledays .= " Tag(en)";
        else    
            $regruledays = "Inf.";
?>
  <tr>
    <td class="inposttable" width="40"><font color="#BB0000"><div align="center"><?=$wrule[0]?></div></font></td>
    <td width="10">&nbsp;</td>
    <td class="inposttable" width="40"><div align="center"><?=$wrule[1]?> GB</div></td>
    <td width="10">&nbsp;</td>
    <td class="inposttable" width="40"><div align="center"><?=$regruledays?></div></td>
    <td width="10">&nbsp;</td>
    <td class="inposttable" width="40"><div align="center"><?=$wrule[3]?>h</div></td>
  </tr>
<?php        
    }
?>
</table>

<p>"<b>und/oder</b>" meint eines oder beide. "<b>und</b>" meint alle vorhergehenden Regeln und diese Regel.
Die Wartezeit ist immer der <b>höchste</b> Wert aus allen passenden Regeln.</p>

<p><?php
if ($CURUSER)
{
  // ratio as a string
    function format_ratio($up,$down, $color = True)
    {
        if ($down > 0)
        {
            $r = number_format($up / $down, 2);
        if ($color)
                $r = "<font color=".get_ratio_color($r).">$r</font>";
        }
        else
            if ($up > 0)
              $r = "'Inf.'";
          else
              $r = "'---'";
        return $r;
    }

    $wait = get_cur_wait_time($CURUSER["id"]);

    print("In <a class=altlink href=userdetails.php?id=" . $CURUSER['id'] . ">Deinem</a> Fall ");

    if ($wait)
    {
        print("hast Du eine Wartezeit von $wait Stunden.");
    }
    else
      print("hast Du keine Wartezeit.");
}
?></p>

<p>Diese Wartezeit betrifft auch speziell neue User, weswegen sich ein Neuanmelden nicht auszahlt.</p>

<p>Aufgrund der Möglichkeit, dass man die Wartezeit umgehen kann, wenn diese nur fürs Leechen aktiviert ist,
zählt diese auch fürs Seeden. Somit m&uuml;sstest Du auch warten, wenn Du das File schon komplett hast und seeden
willst. F&uuml;r diesen Fall k&ouml;nnen aber mittels der <a href="faq.php#dlf">Wartezeitaufhebung</a> Ausnahmen gemacht werden.</p></td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Warum bekomme ich die Meldung: "Der TCP-Port xxxxx
ist nicht erlaubt"?</b><a name="dl9"></a></td></tr>
<tr><td class="tablea">
Dein Client will sich über den Standardport oder über einen der bekannten P2P Ports mit dem 
Tracker verbinden. Diese sind geblockt.<br>
<br>
Aufgrund der Tatsache, dass eigene ISP's diese Ports drosseln, also den Speed verlangsamen, musst Du
andere Ports wählen, um bei uns etwas herunterladen zu können. <br>
<br>
Eine Liste der geblockten Ports auf diesem Tracker:<br>
<br>
<table cellspacing=1 cellpadding=2>
  <tr>
    <td width="140">Direct Connect</td>
    <td class="inposttable" width="80"><div align="center">411 - 413</div></td>
  </tr>
  <tr>
    <td width="140">Kazaa</td>
    <td class="inposttable" width="80"><div align="center">1214</div></td>
  </tr>
  <tr>
    <td width="140">eDonkey</td>
    <td class="inposttable" width="80"><div align="center">4662</div></td>
  </tr>
  <tr>
    <td width="140">Gnutella</td>
    <td class="inposttable" width="80"><div align="center">6346 - 6347</div></td>
  </tr>
  <tr>
    <td width="140">BitTorrent</td>
    <td class="inposttable" width="80"><div align="center">6881 - 6889</div></td>
 </tr>
</table>
<br>
Du musst Deinen Client so einstellen, dass Ports benutzt werden, die nicht in der obigen Liste stehen.
<br>
Diese Ports werden für die Kommunikation zwischen den Peers benutzt, nicht für die Kommunikation 
mit dem Tracker. Mit den geänderten Ports kannst Du trotzdem zu Peers connecten, die die Standardports 
benutzen. Sollte Dein Client eine mauelle Einstellung der Ports nicht ermöglichen, solltest Du Dir einen 
anderen Client suchen.<br>
<br>
Bei Fragen bezüglich alternativer Clients besuche bitte unser Board.<br>
<br>
Zu guter Letzt musst Du auch noch die Ports in Deinem Router forwarden und in Deiner Firewall die 
Ports freigeben. Beachte das Thema "Wieso wird mein Port als "---"  angezeigt",
und lies die Informationen dort.</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Warum bekomme ich die Meldung: "Du benutzt einen
gebannten Client. Bitte lies das FAQ!"</b><a name="dla"></a></td></tr>
<tr><td class="tablea">
Da es in letzter Zeit immer &ouml;fter vorkommt, dass Benutzer einen gemoddeten Client benutzen,
der Fake-Upload produziert, sind wir dazu gezwungen, Ma&szlig;nahmen zu ergreifen.<br>
Diese Ma&szlig;nahmen schlie&szlig;en auch das Blockieren einiger Clients ein. Die
einzige M&ouml;glichkeit, diesen Fehler zu beseitigen ist es, einen ungemoddeten Client
zu benutzen. Beachte bitte, dass die entsprechenden Clients auch dann gebannt werden, 
wenn der Fake-Upload nicht aktiviert ist.<br>
<br>
Wir werden keine Liste der gebannten Clients oder der Methoden ver&ouml;ffentlichen, nach
denen wir die Sperrungen vornehmen!</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Warum bekomme ich die Meldung: "Ungueltiger PassKey.
 Lies das FAQ!"?</b><a name="dlb"></a></td></tr>
<tr><td class="tablea">
Du hast wahrscheinlich Deinen PassKey zur&uuml;ckgesetzt, nachdem Du diesen Torrent vom
Tracker heruntergeladen hast. Jeder Torrent, den Du auf dem Tracker runterl&auml;dst, wird
mit Deinem pers&ouml;nlichen PassKey versehen. Anhand dieses Keys wird Dein Client mit
Deinem Profil verbunden (wie fr&uuml;her anhand Deiner IP-Adresse).<br>
<br>
Um den Torrent weiter benutzen zu k&ouml;nnen, musst Du den Torrent entweder neu vom Tracker
runterladen (Dein Fortschritt geht dabei nicht verloren!) oder aber die Announce-URL mit
Deinem Client nachtr&auml;glich &auml;ndern.
<?php if ($CURUSER) { ?>Deine aktuelle Announce-URL findest Du in <a href="userdetails.php?id=<?=$CURUSER["id"]?>">Deinem
Profil</a>.<br><?php } ?></td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Warum bekomme ich die Meldung: "Zu viele unterschiedliche
IPs fuer diesen Benutzer (max <?=$MAX_PASSKEY_IPS?>)"?</b><a name="dlc"></a></td></tr>
<tr><td class="tablea">
Du darfst mit Deinem Account maximal von <?=$MAX_PASSKEY_IPS?> verschiedenen IP-Adressen aus auf
den Tracker zugreifen.<br>
<br>
Diese Beschr&auml;nkung gibt es deshalb, weil jeder Torrent Deinen PassKey enth&auml;lt
und somit direkt Deinem Profil zugeordnet wird. Theoretisch w&auml;re es deshalb denkbar,
dass ein User einen Torrent in irgendeinem Board etc. posted, und so dort jeder die
M&ouml;glichkeit h&auml;tte, &uuml;ber diesen Account die Daten runterzuladen.<br>
<br>
Wenn Du den Torrent aus Versehen irgendwo ver&ouml;ffentlicht hast, und nun nicht
mehr runterladen kannst, da zu viele andere Deinen Account missbrauchen, setze einfach
Deinen PassKey im <a href="my.php">Profil</a> zur&uuml;ck, und lade Deine aktuellen
Torrents neu runter, da sich die Announce-URL f&uuml;r Dich damit ebenfalls &auml;ndert.
Alle bisherigen Torrents, die Du runtergeladen hast, werden augenblicklich nicht mehr
funktionieren.</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Was bedeutet "IOError - [Errno13] Permission
denied"?</b><a name="dld"></a></td></tr>
<tr><td class="tablea">
Normalerweise sollte es helfen, den PC zu rebooten und/oder Deinen Router kurz vom Strom 
trennen. Hilft dies nicht, lies bitte weiter<br>
<br>
IOError bedeutet Eingabe-Ausgabe-Fehler. Dies ist ein Fehler vom Client. Meist sind mehrere 
gleichzeitig geöffnete Clients dafür verantwortlich: Du hast Deinen Client geschlossen, der 
Prozess geistert aber noch im System rum. Öffnest Du jetzt den Client erneut, versucht er auf 
etwas zuzugreifen, was der noch immer laufende Prozess gerade macht.<br>
<br>
Es kommt auch vor, dass Deine FAT-Partition einen Fehler hat. Nach einem Bluescreen
beispielsweise kann ein runtergeladenes Stück fehlerhaft sein. Scandisk oder ein 
ähnliches Programm sollte das Problem lösen. (NTFS-Partitionen sollten solche Probleme nicht haben.)</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Was bedeutet "TTL" in der
Torrentssektion?</b><a name="dle"></a></td></tr>
<tr><td class="tablea">
Die TimeToLive (Lebenszeit) des Torrents. Nach Ablauf dieser Frist (zurzeit <?=$GLOBALS["MAX_TORRENT_TTL"]?> Tage)
wird der Torrent automatisch gelöscht, sofern er zu diesem oder einem sp&auml;teren Zeitpunkt l&auml;nger als
<?=$GLOBALS["MAX_DEAD_TORRENT_TIME"]?> Tage inaktiv ist bzw. war.<br>
<br>
Merke, dass aus bestimmten Gründen der Torrent auch schon vorher gelöscht werden kann, z.B.
wenn er gegen die Regeln verst&ouml;&szlig;t, oder der Uploader festgestellt hat, dass einige
der Dateien fehlerhaft sind bzw. das Release nicht funktioniert.
</td></tr></table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Wozu dient die Wartezeitaufhebung und welche Kriterien muss ich daf&uuml;r erf&uuml;llen?<a name="dlf" id="dlf"></a></b></td></tr>
<tr><td class="tablea">
<p>Du kannst f&uuml;r einzelne Torrent die Aufhebung der Wartezeit beantragen, um sofort mit
dem Seeden zu beginnen. Ein Moderator wird Deine Angaben &uuml;berpr&uuml;fen und Dir
im Erfolgsfall die Wartezeit erlassen. Den Antrag kannst Du in der Detailseite
zum jeweiligen Torrent stellen, ganz oben unter dem Download-Link f&uuml;r die Torrent-Datei.
Ein solcher Antrag kann nur einmal pro Torrent gestellt werden. Bitte beachte, dass
eine Aufhebung mit gewissen Regeln und Vorraussetzungen verbunden ist, die beachtet
bzw. erf&uuml;llt werden m&uuml;ssen:</p>

<ul>
<li>Du darfst f&uuml;r diesen Torrent noch keine Aufhebung beantragt haben.</li>
<li>Du musst f&uuml;r diesen Torrent zum Zeitpunkt des Antrags noch mindestens 3 Stunden
Wartezeit &uuml;brig haben. Bei weniger als 3 Stunden steht die Option nicht mehr zur Verf&uuml;gung.</li>
<li>Dein Account muss schon l&auml;ngere Zeit existieren, und einigen Traffic aufweisen.
Eine Ausnahme sind Torrents, die ein Uploader f&uuml;r Dich auf den Tracker geuppt hat. Dazu
muss Dein Nick in der Torrentbeschreibung unter "Seeded by..." stehen!</li>
<li>Du musst den Torrent, f&uuml;r den Du die Aufhebung beantragst, bereits vollst&auml;ndig
auf Deinem Rechner haben. Fehlen ein paar Pieces (meist nur einige MB), z&auml;hlt das
auch als korrekter Antrag, da Du nicht bei 0% anf&auml;ngst.</li>
<li>Du bist Dir dar&uuml;ber im Klaren, dass Dein Account bei Missbrauch <b>umgehend</b>
von einem Moderator deaktiviert wird. Das bedeutet z.B., dass Du zwar schreibst "Ich will
seeden, habe das komplett", aber dann bei 0% (oder einer &auml;hnlich geringen Quote) beginnst
den Torrent zu leechen.</li>
<li>Dr&uuml;cke Deinen Antrag klar und deutlich aus. Ebenso werden wir grunds&auml;tzlich
keine "Bettel"-Antr&auml;ge wie "Ich will den heute Abend mit Freunden gucken, BITTE, ich muss
den haben!!!!" freischalten. Sollte ein Benutzer wiederholt derartige Antr&auml;ge stellen,
resultiert dies in einer Verwarnung, oder in schlimmen F&auml;llen in der Deaktivierung
des Accounts.</li>
<li>Das Team beh&auml;lt sich vor, bereits freigeschaltete Torrents wieder zu sperren, wenn z.B.
ein Irrtum vorliegt, und der Benutzer den Torrent noch nicht gestartet hat.</li>
</ul>

<p>Wenn Du Dich an diese paar Grundregeln h&auml;ltst, wirst Du wenig Probleme mit
einer erfolgreichen Freischaltung haben. Dieses Feature ist lediglich dazu gedacht, um
Benutzern mit einer schlechten Ratio es zu erm&ouml;glichen z.B. eigene Torrents, oder
bereits woanders heruntergeladene Torrents zwecks Ratioverbesserung zu seeden. Noch einmal:
Dieses Feature dient nicht dazu, vorzeitig mit dem Download beginnen zu k&ouml;nnen,
egal ob mit dem Nachsatz "Ich seede danach auch!" oder &auml;hnlichen Argumenten.</p>
</td></tr>
</table>
<br>
</td></tr></table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:750px" class="tableinborder">
 <tr class="tabletitle" width="100%">
  <td colspan="10" width="100%"><span class="normalfont"><center><b>Wie kann ich meinen
  Downloadspeed erhöhen?<a name="dlsp"></a></b></b></center></span></td> 
 </tr><tr><td width="100%" class="tablea">
 
<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tablea">Der Downloadspeed ist abhängig vom Seeder-Leecher-Ratio (SLR). Normalerweise haben die
neuen Torrents ein Speedproblem, da die SLR niedrig ist.<br>
<br>
Nebenbemerkung: Wenn Du Dich über den lahmen Speed ärgerst: <b>Seede!</b> Denn sonst werden
sich die nächsten auch ärgern, wenn der Speed lahm ist. Ebenso bewirkt der Einsatz
von gemoddeten Clients, die falsche Upload-Mengen zur&uuml;ckgeben, dass die Geschwindigkeit
der Torrents zur&uuml;ckgeht, da Benutzer solcher Mods nicht selten nur einen minimalen
Upload eingestellt haben (3 KB/sek oder so).<br>
<br>
Trotzdem kannst Du ein paar Dinge anpassen, um Deinen Speed zu verbessern:</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Lade die aktuellsten Torrents nicht gleich bei
Erscheinen</b><a name="dlsp1"></a></td></tr>
<tr><td class="tablea">
Da am Anfang die wenigsten Seeder da sind, ist auch der Speed am schlechtesten.
Normalerweise hat man die beste Verbindung, wenn man in der Mitte der Torrentaktivität
hinzukommt. Der Nachteil daran ist, dass man weniger Zeit hat zu Seeden.
Man muss also immer die Vor- und Nachteile abwägen.</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Mach dich selbst "erreichbar"</b> <a name="dlsp2"></a></td></tr>
<tr><td class="tablea">
Gehe zu <i><a href="#user8" class="altlink">Warum werde ich als "not
connectable" angezeigt</a></i></td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Setze Deine Uploadgeschwindigkeit nicht
auf "Unlimited"</b><a name="dlsp3"></a></td></tr>
<tr><td class="tablea">
Der Uploadspeed beeinflusst Deinen Downloadspeed auf zwei Arten:<br>
<ul>
    <li>Die Clients bevorzugen andere Clients, von denen Daten kommen. Beispielsweise laden A und B den gleichen 
    Torrent. Wenn A viel zu B hochlädt, versucht sich B zu revangieren und lädt zu A hoch. Das bedeutet, dass 
    Dein hoher Upload Dir meist mit einem hohen Download gedankt wird.</li>

    <li>Auf der anderen Seite steht das TCP Protokoll. Wenn B etwas erfolgreich empfangen hat, teilt er das A mit. 
    (Dies wird Acknowledgements - ACKs -, genannt). Wenn B dieses Signal aber aus welchen Gründen auch immer nicht 
    sendet, wartet A mit dem weiteren Senden von Daten. Wenn B mit voller Upload-Bandbreite hochlädt, kann es sein, 
    dass TCP warten muss, bis es dieses Signal senden kann. Das bedeutet, dass ein zu hoher Uploadspeed Deinen 
    Downloadspeed negativ beeinflussen kann.</li>
</ul>

Für die beste Geschwindigkeit brauchst Du eine Mischung aus beiden Weisen. Der Upload sollte so hoch wie möglich 
bleiben, trotzdem sollte TCP noch die ACKs senden können. <b>Als Fausformel kann man sagen, dass 80% des maximalen 
Uploads eine gute Wahl sind.</b> Du solltest jedoch die Einstellungen noch etwas tunen und korrigieren. (Beachte auch, 
dass ein hoher Upload Deine Stats positiv beeinflusst.)
 <br>
<br>
Hast Du mehrere Clients laufen, gilt diese Regel für den gesamten Traffic. Manche Clients (Azureus) begrenzen den 
gesamten Upload, andere Clients (Shad0w's) begrenzen den Speed pro Torrent. Das gleiche gilt, wenn Du nebenbei noch 
was anderes machst (surfen, FTP, ...), beachte immer Deinen Upload-Speed.</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Limitiere die maximale Anzahl
Verbindungen</b><a name="dlsp4"></a></td></tr>
<tr><td class="tablea">
Manche Betriebssysteme (Windows 9x) vertragen viele gleichzeitige Verbindungen nicht. Auch manche Router bekommen 
Probleme oder werden langsam, wenn sie zu viele Verbindungen haben. Du kannste dagegen nicht viel machen, außer 
etwas mit den maximalen gleichzeitigen Verbindungen zu experimentieren. Beachte, dass Dich Verbindungen vervielfachen, 
wenn Du mehrere Clients gleichzeitig laufen lässt.</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Limitiere die Anzahl gleichzeitiger Uploads</b><a name="dlsp5"></a></td></tr>
<tr><td class="tablea">
Je mehr Uploadslots Du aktiviert hast, desto weniger Bandbreite kriegt jeder Slot zugewiesen. Daher kriegst Du
selbst auch weniger Speed zurück.</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Habe einfach etwas Geduld ;)</b><a name="dlsp6"></a></td></tr>
<tr><td class="tablea">
Wie oben beschrieben, bevorzugen die Clients andere Clients, von denen sie Daten bekommen. Hast Du gerade erst mit 
einem Torrent angefangen, hast Du ja noch nicht viel, was Du ihnen senden kannst. Aus diesem Grund wird Dein 
Download-Speed mit der Zeit schneller. Sobald Du Teile hast, die andere
Clients nicht haben, wird Dein Download höher.</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Warum ist das Browsen so langsam wenn
ich leeche?</b><a name="dlsp7"></a></td></tr>
<tr><td class="tablea">
Deine gesamte maximale Downloadgeschwindigkeit ist begrenzt (vom ISP und technisch gesehen). 
Lädst Du von Leuten mit einem sehr hohen Uploadspeed (Standleitungen,...), ist Deine Bandbreite 
möglicherweise komplett ausgelastet. Es gibt einige Clients, die die maximale Downloadgeschwindigkeit
begrenzen k&ouml;nnten. Wenn Dein Client das nicht kann, brauchst Du eine externe Lösung, z.B. den
<a class=altlink href="redir.php?url=http://www.netlimiter.com/">NetLimiter</a>.<br>
<br>
Surfen gilt hierbei nur als Beispiel! Das gleiche gilt für andere Downloads, Zocken, ...</td></tr>
</table>
<br>
</td></tr></table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:750px" class="tableinborder">
 <tr class="tabletitle" width="100%">
  <td colspan="10" width="100%"><span class="normalfont"><center><b>Mein ISP benutzt
  einen transparenten Proxy, was soll ich tun?<a name="prox" id="prox"></a></b></b></center></span></td> 
 </tr><tr><td width="100%" class="tablea">


<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tablea"><i>Das ist ein sehr komplexes Thema. Alles können wir hier nicht erklären.</i><br>
<br>
Einfachste Lösung: Wechsel deinen ISP! Kannst oder willst Du das nicht, dann lies weiter</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Was ist ein Proxy?</b><a name="prox1"></a></td></tr>
<tr><td class="tablea">
Im Prinzip is ein Proxy ein Mittelsmann. Wenn Du über einen Proxy surfst, sendest Du die Anfragen nicht direkt an 
die Server, wo die Seiten liegen, sondern an diesen Proxy. Dieser lädt die Seiten vom eigentlichen Server und sendet 
Dir die Daten. Es gibt mehrere Arten von Proxys:<br>
<br>
<table cellspacing=1 cellpadding=2>
 <tr>
    <td class="inposttable" valign="top" width="100">&nbsp;Transparent</td>
    <td width="10">&nbsp;</td>
    <td valign="top">Bei einem transparenten Proxy musst Du bei Dir am PC nichts konfigurieren.
                                    Der gesamte Traffic über Port 80 (http) wird über den Proxy abgewickelt.</td>
 </tr>
 <tr>
    <td class="inposttable" valign="top">&nbsp;Nicht-Transparent</td>
    <td width="10">&nbsp;</td>
    <td valign="top">ein PC muss für den Proxy konfiguriert werden.</td>
 </tr>
 <tr>
    <td class="inposttable" valign="top">&nbsp;Anonym</td>
    <td width="10">&nbsp;</td>
    <td valign="top">Der Proxy sendet KEINE Informationen über Dich zum Server. 
                                    (HTTP_X_FORWARDED_FOR header wird nicht gesendet; der Server sieht Deine IP NICHT.)</td>
 </tr>
 <tr>
    <td class="inposttable" valign="top">&nbsp;sehr Anonym</td>
    <td width="10">&nbsp;</td>
    <td valign="top">Der Proxy sendet weder Deine noch die eigenen (vom Proxy) Informationen
                                    zum Server. (HTTP_X_FORWARDED_FOR, HTTP_VIA und HTTP_PROXY_CONNECTION
                                    Headers werden nicht gesendet; der Server sieht weder deine IP noch weiss
                                    er, dass Du einen Proxy benutzt.)</td>
 </tr>
 <tr>
    <td class="inposttable" valign="top">&nbsp;öffentlich</td>
    <td width="10">&nbsp;</td>
    <td valign="top">Selbsterklärend</td>
 </tr>
</table>
<br>
Ein transparenter Proxy kann anonym sein, muss er aber nicht. Es gibt auch verschiedene Stufen der
Anonymität.</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Wie finde ich heraus, dass ich hinter einem
transparenten/anonymen Proxy bin?</b><a name="prox2"></a></td></tr>
<tr><td class="tablea">
Gehe auf <a href=redir.php?url=http://proxyjudge.org class="altlink">ProxyJudge</a>.  Hier werden die Header
aufgelistet, die der Server von Dir empfängt. Relevant sind HTTP_CLIENT_IP,
HTTP_X_FORWARDED_FOR und REMOTE_ADDR.</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Warum steht bei mir "not connectable" auch wenn ich
nicht über eine Firewall oder per NAT im Internet bin?</b><a name="prox3"></a></td></tr>
<tr><td class="tablea">
Dieser Tracker findet meist immer Deine richtige IP, dennoch braucht er den Proxy, um den HTTP Header
HTTP_X_FORWARDED_FOR zu verarbeiten. Verhindert der Proxy diese Informationsweitergabe, nimmt der
Tracker an, dass die Proxy-IP die IP des Clients ist. Wenn du Dich im Tracker einloggst, prüft
dieser, ob Du hinter einer Firewall/NAT bist und versucht, eine Verbindung zu Deinem Client
herzustellen (jedoch auf dem Proxy). Normalerweise hat ein Proxy diesen Port nicht offen, darum
kann der Tracker sich nicht verbinden und zeigt Deinen Port nicht korrekt an.</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Kann ich meinen ISP Proxy umgehen?</b><a name="prox4"></a></td></tr>
<tr><td class="tablea">
Wenn dein ISP nur HTTP-Traffic über Port 80 zulässt oder die normalen Proxy Ports blockt,
kannst Du z.B. Sockets benutzen. Dieses Thema übersteigt jedoch den Umfang dieser FAQ.<br>
<br>
Du kannst aber folgendes probieren:<br>
<ul>
    <li>Nimm irgendeinen &ouml;ffentlichen, <b>nicht-anonymen</b> Proxy, der <b>nicht</b> Port 80 benutzt
    (z.B. aus <a href="redir.php?url=http://tools.rosinstrument.com/proxy" class="altlink">dieser</a>,
    <a href="redir.php?url=http://www.proxy4free.com/index.html"  class="altlink">dieser</a> oder
    <a href="redir.php?url=http://www.samair.ru/proxy"  class="altlink">dieser</a> Liste).</li>

    <li>Konfiguriere Deinen Computer für Proxy-Nutzung. Unter Windows XP, gehe &uumlber <i>Start</i>,
    <i>Einstellungen</i>, <i>Systemsteuerung</i,> <i>Internet Optionen</i>,
    <i>Verbindungen</i>, <i>LAN Einstellungen</i>, <i>Proxy Server für LAN verwenden</i>,
    <i>Erweitert</i> und gib die IP Deines gewählten Proxies ein. Oder vom Internet Explorer
    aus &uuml;ber <i>Tools</i>, <i>Internet Optionen</i>, ...<br></li>

    <li>(Fakultativ) Besuch <a href="redir.php?url=http://proxyjudge.org" class="altlink">ProxyJudge</a>.
    Wenn Du HTTP_X_FORWARDED_FOR in der Liste bei deiner IP siehst, ist alles ok, ansonsten wähle einen
    anderen Proxy und versuchs nochmal.<br></li>

    <li>Komm hierher. Wenn alles stimmt, sollte der Tracker Dich jetzt korrekt erkennen.
    (Sieh im Profil nach um sicherzugehn).</li>
</ul>
<br>
Merke Dir, dass Du jetzt die ganze Zeit über einen public Proxy surfst, welche meistens ziemlich langsam sind.
Verbindungen zwischen Peers gehen nicht über Port 80 und sind daher davon nicht eingeschränkt.
Ausserdem sollten sie einen besseren Speed garantieren, als wenn Du "unconnectable" bist.</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Wie konfiguriere ich meinen BT Clienten für einen Proxy?</b><a name="prox5"></a></td></tr>
<tr><td class="tablea">
Konfiguriere Windows XP wie oben beschrieben. Wenn Du Deinen Internet Explorer für einen Proxy konfigurierst,
machst Du das automatisch für den kompletten HTTP-Traffic. Auf der anderen Seite, wenn Du einen anderen
Browser benutzt (was ich stark hoffen will ;)), z.B. Mozilla, Opera, Firefox, konfigurierst Du den Proxy
nur für diesen Browser.</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Warum kann ich mich über einen Proxy nicht anmelden?</b><a name="prox6"></a></td></tr>
<tr><td class="tablea">
Es gehört zu unseren Regeln, keine Neuanmeldungen über Proxy zu erlauben.</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Gilt dies auch für andere Tracker?</b><a name="prox7"></a></td></tr>
<tr><td class="tablea">
Diese FAQ gilt im Speziellen nur für NetVision, auf anderen Trackern <b>kann</b> es so oder
ähnlich sein, <b>muss</b> aber nicht.</td></tr>
</table>
<br>
</td></tr></table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:750px" class="tableinborder">
 <tr class="tabletitle" width="100%">
  <td colspan="10" width="100%"><span class="normalfont"><center><b>Warum kann ich nicht
  connecten? Werde ich geblockt?<a name="conn" id="conn"></a></b></b></center></span></td> 
 </tr><tr><td width="100%" class="tablea">

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tablea">Dein Verbindungsproblem kann aus vielerlei Gründen auftauchen.</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Vielleicht ist meine Adresse geblockt?</b><a name="conn2"></a></td></tr>
<tr><td class="tablea">
Diese Seite blockiert IPs von der <a class=altlink href="redir.php?url=http://methlabs.org/">PeerGuardian</a>
Datenbank, ausserdem die IPs von gebannten Usern. Das Läuft über Apache/PHP Level, ein einfaches Script, dass die
<i>Logins</i> von solchen Adressen unterbindet. Es sollte Dich nicht hindern, diese Seite zu erreichen. Es
werden auch keine Lower-Level Protokolle geblockt, Du kannst uns anpingen/tracerouten, auch wenn
Deine Adresse blacklisted ist. Wenn nicht, liegt das Problem anderswo.<br>
<br>
Sofern Deine Adresse aus irgendeinem Grund in der PeerGuardian Datenbank ist, kontaktiere nicht uns sondern die Betreiber
der Datenbank.</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Dein ISP blockt die Site-Adresse</b><a name="conn3"></a></td></tr>
<tr><td class="tablea">
(Erst einmal... es ist unüblich, dass ein ISP sowas macht, normalerweise liegen die Probleme anderswo.)
<br>
In diesem Fall gibt es nichts was wir tun können. Du solltest Deinen ISP kontaktieren (oder zu einem anderen wechseln).
Du kannst es auch über einen Proxy versuchen, wie oben beschrieben.<br>
<br>
Merke Dir aber, dass Du dann immer als "unconnectable" gekennzeichnet sein wirst, da Du keine einkommenden Verbindungen
annehmen kannst.</td></tr>
</table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:100%" class="tableinborder">
<tr><td class="tableb"><b>Alternativport (81)</b><a name="conn4"></a></td></tr>
<tr><td class="tablea">
Einige unserer Torrents laufen nicht über den Standardport 80, was bei einigen Usern Probleme verursachen kann.

Du kannst das leicht verhindern, indem du den Torrent mit irgendeinem TorrentEditor änderst, z.B.
<a href="redir.php?url=http://sourceforge.net/projects/burst/" class="altlink">MakeTorrent</a>,
und da den Port 81 mit 80 tauschst oder gleich weglässt..<br>
<br>
Hinweis: Das Editieren eines Torrentfiles mit Notepad ist <b>nicht</b> empfohlen, da diese
Dateien auch nicht als Text darstellbare Zeichen beinhalten, und diese beim Speichern der
Datei m&ouml;glicherweise verloren gehen oder ver&auml;ndert werden.</td></tr>
</table>
<br>
</td></tr></table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:750px" class="tableinborder">
 <tr class="tabletitle" width="100%">
  <td colspan="10" width="100%"><span class="normalfont"><center><b>Was soll ich tun wenn
  ich die Antwort hier nicht finde?<a name="other" id="other"></a></b></b></center></span></td> 
 </tr><tr><td width="100%" class="tablea">

<p>Poste Deine Frage im <a class="altlink" href="http://pttsc.biz/NetVision">Forum</a>, oder
besuche unseren IRC-Channel <a href="irc://just-root.ath.cx/netvision">#netvision auf just-root.ath.cx</a>.
Du wirst dort sicher Hilfe finden, sofern Du einige grundlegende Regeln beachtest:</p>
<ul>
<li>Vergewissere Dich, dass Dein Problem nicht in der FAQ steht. Es bringt nichts, etwas zu posten, nur um dann
wieder hierher geschickt zu werden.
<li>Lies vor dem Posten die Topics, die festgesetzt wurden ("Sticky"). Viele Informationen, die noch nicht in die FAQ
übernommen wurden, kann man da finden.</li>
<li>Hilf uns, Dir zu helfen. Komm nicht einfach und schreib "Es funktioniert nicht", sondern gib uns genauere Infos, damit wir
keine Zeit mit solchen Fragen vergeuden. Wichtig sind folgende Fragen, abh&auml;ngig vom Problem:
<ul>
<li>Welchen Client und welche Version benutzt Du (Azureus, BitComet, BitTornado, ...)?</li>
<li>Was hast Du für ein Betriebssystem?</li>
<li>Wie sehen Deine Netzwerkeinstellungen aus (Proxy, Router, ...)?</li>
<li>Wie lautet die genaue Fehlermeldung?</li>
<li>Tritt das Problem nur bei einem bestimmten Torrent auf?</li>
</ul>
Je mehr Infos Du uns geben kannst, desto einfacher ist es für uns, Dir zu helfen</li>
<li>Eigentlich logisch: Sei freundlich. Versuchen Hilfe zu erzwingen wird selten klappen, darum zu bitten bringt dir normalerweise mehr.</li>
</ul>
</td>
</tr></table>

<br>

<table cellpadding="4" cellspacing="1" border="0" style="width:750px" class="tableinborder">
 <tr class="tabletitle" width="100%">
  <td colspan="10" width="100%"><span class="smallfont"><center>
  FAQ zuletzt editiert am 08.02.2005 von Gartenzwerg (0:05 CET)</center></span></td> 
 </tr></table>

<?php
stdfoot();
?>