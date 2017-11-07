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

function insert_tag($name, $description, $syntax, $example, $remarks)
{
	$result = format_comment($example);
	print("<p class=sub><b>$name</b></p>\n");
	begin_table(TRUE);
	print("<tr valign=top><td width=15% class=tablea>Beschreibung:</td><td class=tableb>$description\n");
	print("<tr valign=top><td class=tablea>Syntax:</td><td class=tableb><tt>$syntax</tt>\n");
	print("<tr valign=top><td class=tablea>Beispiel:</td><td class=tableb><tt>$example</tt>\n");
	print("<tr valign=top><td class=tablea>Ergebnis:</td><td class=tableb>$result\n");
	if ($remarks != "")
		print("<tr><td class=tablea>Bemerkungen:</td><td class=tableb>$remarks\n");
	end_table();
}

stdhead("Tags");
begin_frame("Tags");
$test = $_POST["test"];
?>
<p>Dieser Tracker unterstützt eine Reihe <i>BBCodes</i>, die Du benutzen kannst, um deinen Beiträgen etc.
etwas mehr "Pfiff" zu verleihen. Diese Codes kannst Du in Kommentaren, Torrent-Beschreibungen,
Signaturen, PMs und in Forum-Beiträgen benutzen.</p>

<form method=post action=?>
<p align="center">
<textarea name=test cols=60 rows=3><? print($test ? htmlspecialchars($test) : "")?></textarea><br><br>
<input type=submit value="Teste diesen BBCode!" style='height: 23px; margin-left: 5px'>
</p>
</form>
<?php

if ($test != "")
  print("<p><b>Dein BBCode wird so aussehen:</b><br><hr>" . format_comment($test) . "<hr></p>\n");

insert_tag(
	"Fett",
	"Macht den Text innerhalb der Klammern fett.",
	"[b]<i>Text</i>[/b]",
	"[b]Dies ist fettgedruckter Text.[/b]",
	""
);

insert_tag(
	"Kursiv",
	"Macht den eingeschlossenen Text kursiv.",
	"[i]<i>Text</i>[/i]",
	"[i]Das ist kursiver Text.[/i]",
	""
);

insert_tag(
	"Unterstreichen",
	"Unterstreicht den Text innerhalb der Klammern.",
	"[u]<i>Text</i>[/u]",
	"[u]Dieser Text ist unterstrichen.[/u]",
	""
);

insert_tag(
	"Farbe (Alt. 1)",
	"Ändert die Farbe des eingeschlossenen Textes.",
	"[color=<i>Farbe</i>]<i>Text</i>[/color]",
	"[color=red]Dieser Text ist rot.[/color]",
	"Welche Farben gültig sind, hängt vom benutzten Browser ab. Eine Übersicht über gültige Farbnamen findest Du beim <a href=\"http://www.w3.org/TR/html4/types.html#type-color\">WorldWideWeb Consortium (W3C)</a>."
);

insert_tag(
	"Farbe (Alt. 2)",
	"Ändert die Farbe des eingeschlossenen Textes.",
	"[color=#<i>RGB</i>]<i>Text</i>[/color]",
	"[color=#ff0000]Dieser Text ist rot.[/color]",
	"<i>RGB</i> muss eine sechsstellige Hexadezimalzahl sein. Jeweils zwei Stellen definieren die Helligkeit der Grundfarben Rot, Grün und Blau (in dieser Reihenfolge). FF0000 ist Rot, 00FF00 volles Grün und 0000FF Blau."
);

insert_tag(
	"Größe",
	"Bestimmt die Größe des angezeigten Textes.",
	"[size=<i>n</i>]<i>text</i>[/size]",
	"[size=4]Dies ist Größe 4.[/size]",
	"<i>n</i> muss eine Zahl von 1 (am kleinsten) bis 7 (am größten) sein. Die Standardgröße ist 2."
);

insert_tag(
	"Schriftart",
	"Setzt die Schriftart des eingeschlossenen Textes.",
	"[font=<i>Schriftart</i>]<i>Text</i>[/font]",
	"[font=Impact]Hallo Welt![/font]",
	"Du kannst mehrere Schriftarten durch Komma getrennt angeben. Denke bitte daran, dass nicht jeder Benutzer auch die Fonts installiert hat, die Du verwendest."
);

insert_tag(
	"Zentriert",
	"Zeigt den Text zentriert an.",
	"[center]<i>Text</i>[/center]",
	"[center]Zentrierter Text[/center]",
	""
);

insert_tag(
	"Hyperlink (Alt. 1)",
	"Setzt einen Link auf eine andere Website.",
	"[url]<i>URL</i>[/url]",
	"[url]http://www.example.com/[/url]",
	"Dieser Tag ist überflüssig. Alle URLs werden automatisch als Link dargestellt."
);

insert_tag(
	"Hyperlink (Alt. 2)",
	"Setzt einen Link auf eine andere Website.",
	"[url=<i>URL</i>]<i>Link Text</i>[/url]",
	"[url=http://www.example.com/]Beispiellink[/url]",
	"Diesen Tag brauchst Du nur benutzen, wenn der angezeigte Text sich von der Linkadresse unterscheiden soll."
);

insert_tag(
	"Bild (Alt. 1)",
	"Fügt eine Grafik ein.",
	"[img=<i>URL</i>]",
	"[img=http://tracker-netvision.ath.cx/".$GLOBALS["PIC_BASE_URL"]."cat_movies2.gif]",
	"Die URL muss mit <b>.gif</b>, <b>.jpg</b> oder <b>.png</b> enden."
);

insert_tag(
	"Bild (alt. 2)",
	"Fügt eine Grafik ein.",
	"[img]<i>URL</i>[/img]",
	"[img]http://tracker-netvision.ath.cx/".$GLOBALS["PIC_BASE_URL"]."cat_movies2.gif[/img]",
	"Die URL muss mit <b>.gif</b>, <b>.jpg</b> oder <b>.png</b> enden."
);

insert_tag(
	"Zitat (Alt. 1)",
	"Fügt ein Zitat ein.",
	"[quote]<i>Zitierter Text</i>[/quote]",
	"[quote]The quick brown fox jumps over the lazy dog.[/quote]",
	""
);

insert_tag(
	"Zitat (Alt. 2)",
	"Fügt ein Zitat ein.",
	"[quote=<i>Autor</i>]<i>Zitierter text</i>[/quote]",
	"[quote=John Doe]The quick brown fox jumps over the lazy dog.[/quote]",
	""
);

insert_tag(
	"Aufzählungsliste",
	"Zeigt eine Liste mit Aufzählungssymbolen an.",
	"[list]<i>Listenelemente</i>[/list]<br>[list=disc|circle|square]<i>Listenelemente</i>[/list]",
	"[list=circle][*]Aufzählung mit Kreis-Symbolen[/list]",
	"Wird kein Symboltyp angegeben, wird das vom Browser voreingestellte Symbol für unsortierte Listen verwendet."
);

insert_tag(
	"Nummerierte Liste",
	"Zeigt eine nummerierte Liste an.",
	"[list=1|a|A|i|I]<i>Listenelemente</i>[/list]",
	"[list=I][*]Punkt 1[*]Punkt 2[*]Punkt 3[*]Punkt 4[/list]",
	"\"1\" zeigt eine dezimal nummerierte Liste an, \"a\" und \"A\" verwenden dazu Buchstaben nach dem Prinzip \"a., b., ..., aa., ab., ...\" und \"i\" bzw. \"I\" verwendet lateinische Zahlen."
);

insert_tag(
	"Listeneintrag",
	"Fügt einen Listeneintrag hinzu.",
	"[*]<i>Text</i>",
	"[list][*]Punkt 1[*]Punkt 2[/list]",
	"Bitte den [list]-Tag nicht vergessen, da sonst die Einträge nicht korrekt angezeigt werden."
);

insert_tag(
	"Formatierter Text",
	"Formatierter Text (Monospace). Bricht nicht automatisch um.",
	"[pre]<i>Text</i>[/pre]",
	"[pre]Dies ist formatierter Text mit fester Breite.[/pre]",
	"Bei der verwendeten Schriftart haben alle Zeichen (z.B. W und I) die selbe Breite."
);

end_frame();
stdfoot();
?>