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

userlogin();
stdhead("Links");
echo "<table width=\"850px\" class=\"main\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n".
	"    <tr>\n".
	"        <td class=\"embedded\">\n".
	"            <h2>Other pages on this site</h2>\n".
	"            <table width=\"100%\" border=\"1\" cellspacing=\"0\" cellpadding=\"10\">\n".
	"                <tr>\n".
	"                    <td class=\"text\">".
	"<ul>".
	"<li><a class=\"altlink\" href=\"rss.xml\">RSS feed</a> - For use with RSS-enabled software. An alternative to torrent email notifications.</li>".
	"<li><a class=\"altlink\" href=\"rssdd.xml\">RSS feed (direct download)</a> - Links directly to the torrent file.</li>".
	"<li><a class=\"altlink\" href=\"bitbucket-upload.php\">Bitbucket</a> - If you need a place to host your avatar or other pictures.</li>".
	"</ul>".
	"</td>\n".
	"                </tr>\n".
	"            </table>\n".
	"            <h2>BitTorrent Information</h2>\n".
	"            <table width=\"100%\" border=\"1\" cellspacing=\"0\" cellpadding=\"10\">\n".
	"                <tr>\n".
	"                    <td class=\"text\">".
	"<ul>".
	"<li><a class=\"altlink\" href=\"http://dessent.net/btfaq/\">Brian's BitTorrent FAQ and Guide</a> - Everything you need to know about BitTorrent. Required reading for all n00bs.</font></li>".
	"<li><a class=\"altlink\" href=\"http://10mbit.com/faq/bt/\">The Ultimate BitTorrent FAQ</a> - Another nice BitTorrent FAQ, by Evil Timmy.</li>".
	"</ul>".
	"</td>\n".
	"                </tr>\n".
	"            </table>\n".
	"            <h2>BitTorrent Software</h2>\n".
	"            <table width=\"100%\" border=\"1\" cellspacing=\"0\" cellpadding=\"10\">\n".
	"                <tr>\n".
	"                    <td class=\"text\">".
	"<ul>".
	"<li><a class=\"altlink\" href=\"http://pingpong-abc.sourceforge.net/\">ABC</a> - \"ABC is an improved client for the Bittorrent peer-to-peer file distribution solution.\"</li>".
	"<li><a class=\"altlink\" href=\"http://azureus.sourceforge.net/\">Azureus</a> - \"Azureus is a java bittorrent client. It provides a quite full bittorrent protocol implementation using java language.\"</li>".
	"<li><a class=\"altlink\" href=\"http://bnbt.go-dedicated.com/\">BNBT</a> - Nice BitTorrent tracker written in C++.</li>".
	"<li><a class=\"altlink\" href=\"http://bittornado.com/\">BitTornado</a> - a.k.a \"TheSHAD0W's Experimental BitTorrent Client\".</li>".
	"<li><a class=\"altlink\" href=\"http://www.bitconjurer.org/BitTorrent\">BitTorrent</a> - Bram Cohen's official BitTorrent client.</li>".
	"<li><a class=\"altlink\" href=\"http://ei.kefro.st/projects/btclient/\">BitTorrent EXPERIMENTAL</a> - \"This is an unsupported, unofficial, and, most importantly, experimental build of the BitTorrent GUI for Windows.\"</li>".
	"<li><a class=\"altlink\" href=\"http://krypt.dyndns.org:81/torrent/\">Burst!</a> - Alternative Win32 BitTorrent client.</li>".
	"<li><a class=\"altlink\" href=\"http://g3torrent.sourceforge.net/\">G3 Torrent</a> - \"A feature rich and graphically empowered bittorrent client written in python.\"</li>".
	"<li><a class=\"altlink\" href=\"http://krypt.dyndns.org:81/torrent/maketorrent/\">MakeTorrent</a> - A tool for creating torrents.</li>".
	"<li><a class=\"altlink\" href=\"http://ptc.sourceforge.net/\">Personal Torrent Collector</a> - BitTorrent client.</li>".
	"<li><a class=\"altlink\" href=\"http://www.shareaza.com/\">Shareaza</a> - Gnutella, eDonkey and BitTorrent client.</li>".
	"</ul>".
	"</td>\n".
	"                </tr>\n".
	"            </table>\n".
	"        </td>\n".
	"    </tr>\n".
	"</table>\n";
stdfoot();
?>