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
dbconn();

session_start();

stdhead("Anmeldung");
begin_frame("Fragebogen zu FAQ und Regeln", FALSE, "650px");

// Sieben Fragen per Zufall auswählen
$questions = mysql_query("SELECT * FROM `test` ORDER BY RAND() LIMIT 7");

?>
<p>Um die Registrierung abschließen zu können, musst Du noch einige Fragen zu
den <a href="rules.php">Regeln</a> und dem <a href="faq.php">FAQ</a> beantworten.
Da Du auf diesem Tracker wegen Missachtung der Regeln gebannt werden kannst,
solltest Du Dir die Regeln in eigenem Interesse durchlesen. Das FAQ enthält auch
nützliche Informationen zu den meistgestellten Fragen.</p>
<form action="faqtest.php" method="post">
<?php

while ($qdata = mysql_fetch_assoc($questions)) {
    begin_table(true);
    $antworten = unserialize($qdata["answers"]);
    // Auch Antworten in zufälliger Reihenfolge zeigen
    shuffle($antworten);
    echo "<tr><td class=\"tablecat\"><b>" . htmlspecialchars($qdata["question"]) . "</b></td></tr>\n";
    echo "<tr><td class=\"tablea\">\n<table cellspacing=\"2\" cellpadding=\"0\" border=\"0\">\n";
    for ($I=0; $I<count($antworten); $I++) {
	echo "<tr>\n<td>" . ($I+1) . ".&nbsp;&nbsp;\n";
	if ($qdata["type"] == "radio")
	    echo "<td><input type=\"" . $qdata["type"] . "\" id=\"ch" . $qdata["id"] . "x" . ($I+1) . "\" name=\"choice[" .
		$qdata["id"] . "]\" value=\"" . $antworten[$I]["id"] . "\"></td>\n";
	else
	    echo "<td><input type=\"" . $qdata["type"] . "\" id=\"ch" . $qdata["id"] . "x" . ($I+1) . "\" name=\"choice[" .
		$qdata["id"] . "][" . $antworten[$I]["id"] . "]\" value=\"1\"></td>\n";
	echo "<td><label for=\"ch" . $qdata["id"] . "x" . ($I+1) . "\">" . htmlspecialchars($antworten[$I]["answer"]) .
	    "</label></td>\n</tr>\n";
    }
    echo "</table>\n</td></tr>\n";
    end_table();
}
?>
<p style="text-align:center"><input type="submit" name="dosubmit" value="Weiter"></p>
</form>
<?php
end_frame();
stdfoot();

?>
