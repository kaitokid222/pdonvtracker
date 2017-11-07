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
loggedinorreturn();

if (!$GLOBALS["IRCAVAILABLE"]) {
    stderr("Fehler", "Sorry, dieser Tracker hat keinen eigenen IRC-Channel. Wende dich bitte per PN an das Team!");
}

stdhead();
begin_main_frame();
begin_frame("IRC");

?>
<script type="text/javascript">

function openApplet()
{
    var resSel = document.getElementById('resolution');
    var posX;
    var posY;
    var resX;
    var resY;
    
    switch (resSel.options[resSel.selectedIndex].value) {
        case '1':
                resX = 640;
                resY = 480;
                break;
        case '2':
        default:
                resX = 800;
                resY = 600;
        break;
        case '3':
                resX = 1024;
                resY = 768;
        break;
    }
    
    posX = (screen.availWidth - resX) / 2;
    posY = (screen.availHeight - resY) / 2;
    
    var dummy = window.open('ircapplet.php?width='+resX+'&height='+resY,'nvirc','left='+posX+',top='+posY+',innerWidth='+resX+',innerHeight='+resY);
    dummy.focus();
}

</script>
<p>Unser offizieller IRC-Channel ist <a href="irc://<?=$GLOBALS["IRCHOST"].":".$GLOBALS["IRCPORT"]."/".str_replace("#", "", $GLOBALS["IRCCHANNEL"])?>"><?=$GLOBALS["IRCCHANNEL"]?></a>
<?php if ($GLOBALS["IRCNETWORKTITLE"]!="" && $GLOBALS["IRCNETWORKWEB"]!="") { ?>im <a href="<?=$GLOBALS["IRCNETWORKWEB"] ?>"><?=$GLOBALS["IRCNETWORKTITLE"]?></a> Netzwerk<? } ?>. Bitte
beachtet die <a href="rules.php">Regeln in unserem IRC-Channel</a>!</p>
<p>Alternativ zu Deinem eigenen Client kannst Du einfach unseren Java-Client benutzen.
Der Chat startet in einem eigenen Fenster, so dass Du hier in Ruhe weitersurfen kannst.</p>
<p align="center">Größe: <select id="resolution" size="1">
        <option value="1">640 x 480</option>
        <option value="2" selected="selected">800 x 600</option>
        <option value="3">1024 x 768</option>
</select> <input type="button" onclick="openApplet();" value="IRC &uuml;ber Java-Applet starten"></p>
<p align="center">Powered by <a href="http://www.pjirc.com/">P J I R C - a free Java IRC client</a></p>
<?php

end_frame();
end_main_frame();
stdfoot();

?>