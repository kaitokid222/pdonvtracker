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

if ($GLOBALS["IRCCHANNEL"] != "")
    $joincommand = "/join ".$GLOBALS["IRCCHANNEL"];
else
    $joincommand = "/list";

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
        "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title><?=$GLOBALS["SITENAME"]?> IRC - <?=$GLOBALS["IRCHOST"]?> - <?=$GLOBALS["IRCCHANNEL"]?></title>
<style>
html, body {
    margin: 0px;
    padding: 0px;
}
</style>
</head>
<body>
<applet code=IRCApplet.class archive="pjirc/irc.jar,pjirc/pixx.jar" style="width:<?=intval($_GET["width"])?>px;height:<?=intval($_GET["height"])?>px">
<param name="CABINETS" value="pjirc/irc.cab,pjirc/securedirc.cab,pjirc/pixx.cab">

<param name="nick" value="<?=$CURUSER["username"]?>">
<param name="alternatenick" value="<?=$GLOBALS["IRCALTNICK"].$CURUSER["id"]?>">
<param name="name" value="Java Applet User">
<param name="host" value="<?=$GLOBALS["IRCHOST"]?>">
<param name="gui" value="pixx">
<param name="language" value="pjirc/german">
<param name="autoconnection" value="true">
<param name="quitmessage" value="<?=$GLOBALS["SITENAME"]?> rulez!">
<param name="command1" value="<?=$joincommand?>">

<param name="style:bitmapsmileys" value="true">
<param name="style:smiley1" value=":) pjirc/img/sourire.gif">
<param name="style:smiley2" value=":-) pjirc/img/sourire.gif">
<param name="style:smiley3" value=":-D pjirc/img/content.gif">
<param name="style:smiley4" value=":d pjirc/img/content.gif">
<param name="style:smiley5" value=":-O pjirc/img/OH-2.gif">
<param name="style:smiley6" value=":o pjirc/img/OH-1.gif">
<param name="style:smiley7" value=":-P pjirc/img/langue.gif">
<param name="style:smiley8" value=":p pjirc/img/langue.gif">
<param name="style:smiley9" value=";-) pjirc/img/clin-oeuil.gif">
<param name="style:smiley10" value=";) pjirc/img/clin-oeuil.gif">
<param name="style:smiley11" value=":-( pjirc/img/triste.gif">
<param name="style:smiley12" value=":( pjirc/img/triste.gif">
<param name="style:smiley13" value=":-| pjirc/img/OH-3.gif">
<param name="style:smiley14" value=":| pjirc/img/OH-3.gif">
<param name="style:smiley15" value=":'( pjirc/img/pleure.gif">
<param name="style:smiley16" value=":$ pjirc/img/rouge.gif">
<param name="style:smiley17" value=":-$ pjirc/img/rouge.gif">
<param name="style:smiley18" value="(H) pjirc/img/cool.gif">
<param name="style:smiley19" value="(h) pjirc/img/cool.gif">
<param name="style:smiley20" value=":-@ pjirc/img/enerve1.gif">
<param name="style:smiley21" value=":@ pjirc/img/enerve2.gif">
<param name="style:smiley22" value=":-S pjirc/img/roll-eyes.gif">
<param name="style:smiley23" value=":s pjirc/img/roll-eyes.gif">
<param name="style:smiley24" value=":D pjirc/img/content.gif">
<param name="style:floatingasl" value="true">

<param name="pixx:language" value="pjirc/pixx-german">
<param name="pixx:styleselector" value="true">
<param name="pixx:highlight" value="true">
<param name="pixx:highlightnick" value="true">

</applet>
</body>