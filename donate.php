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
stdhead();

begin_main_frame();
begin_frame("Spenden via PayPal"); 
begin_table(TRUE);
?>
<tr><td class=tableb valign=top>Per Kreditkarte</td>
<td class=tablea valign=top>
Klicke einfach auf den Button, um eine Spende per Kreditkarte zu t&auml;tigen!
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_xclick">
<input type="hidden" name="business" value="netvisiondlam@web.de">
<input type="hidden" name="item_name" value="NetVision">
<input type="hidden" name="no_note" value="1">
<input type="hidden" name="currency_code" value="EUR">
<input type="hidden" name="tax" value="0">
<input type="image" src="https://www.paypal.com/de_DE/i/logo/logo-xclickBox.gif" border="0" name="submit"
alt="Sicher spenden &uuml;ber PayPal" style='margin-top: 5px'>
</form>
</td></tr>
<tr><td class=tableb valign=top>Per &Uuml;berweisung<br><br><img src="<?=$GLOBALS["PIC_BASE_URL"]?>flag/germany.gif" style='margin-right: 10px'></td>
<td class=tablea valign=top>
Kontoinhaber: PayPal International Ltd.<br>
Kontonummer: 6161604670<br>
Bank: JP Morgan<br>
BLZ: 501 108 00
<p><b>Wichtig!</b> Als Verwendungszweck bitte folgenden Text angeben: 21EU8W8H6HQUCPN</p>
</td>
</tr>
<?php end_table(); ?>
<p><b>Nachdem Du gespendet hast, sende einem Admin oder SysOp die <font color=red>PayPal Transaction ID</font> damit wir die Spende Deinem Account zuordnen k&ouml;nnen!</b></p>

<?php 
end_frame(); 
end_main_frame(); 
?>

<?php
stdfoot();
?>