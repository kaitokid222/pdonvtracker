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

userlogin();
stdhead("Video Formats");

echo "<table class=\"main\" width=\"750\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n".
	"    <tr>\n".
	"        <td class=\"embedded\">\n".
	"            <h2>Downloaded a movie and don't know what CAM/TS/TC/SCR means?</h2>\n".
	"            <table width=\"100%\" border=\"1\" cellspacing=\"0\">\n".
	"                <tr>\n".
	"                    <td class=\"text\">\n";

echo "<b>CAM -</b><br>\n".
	"<br>\n".
	"A cam is a theater rip usually done with a digital video camera. A mini tripod is\n".
	"sometimes used, but a lot of the time this wont be possible, so the camera make shake.\n".
	"Also seating placement isn't always idle, and it might be filmed from an angle.\n".
	"If cropped properly, this is hard to tell unless there's text on the screen, but a lot\n".
	"of times these are left with triangular borders on the top and bottom of the screen.\n".
	"Sound is taken from the onboard microphone of the camera, and especially in comedies,\n".
	"laughter can often be heard during the film. Due to these factors picture and sound\n".
	"quality are usually quite poor, but sometimes we're lucky, and the theater will be'\n".
	"fairly empty and a fairly clear signal will be heard.<br>\n".
	"<br>\n".
	"<br>\n";

echo "<b>TELESYNC (TS) -</b><br>\n".
	"<br>\n".
	"A telesync is the same spec as a CAM except it uses an external audio source (most\n".
	"likely an audio jack in the chair for hard of hearing people). A direct audio source\n".
	"does not ensure a good quality audio source, as a lot of background noise can interfere.\n".
	"A lot of the times a telesync is filmed in an empty cinema or from the projection both\n".
	"with a professional camera, giving a better picture quality. Quality ranges drastically,\n".
	"check the sample before downloading the full release. A high percentage of Telesyncs\n".
	"are CAMs that have been mislabeled.<br>\n".
	"<br>\n".
	"<br>\n";

echo "<b>TELECINE (TC) -</b><br>\n".
	"<br>\n".
	"A telecine machine copies the film digitally from the reels. Sound and picture should\n".
	"be very good, but due to the equipment involved and cost telecines are fairly uncommon.\n".
	"Generally the film will be in correct aspect ratio, although 4:3 telecines have existed.\n".
	"A great example is the JURASSIC PARK 3 TC done last year. TC should not be confused with\n".
	"TimeCode , which is a visible counter on screen throughout the film.<br>\n".
	"<br>\n".
	"<br>\n";

echo "<b>SCREENER (SCR) -</b><br>\n".
	"<br>\n".
	"A pre VHS tape, sent to rental stores, and various other places for promotional use.\n".
	"A screener is supplied on a VHS tape, and is usually in a 4:3 (full screen) a/r, although\n".
	"letterboxed screeners are sometimes found. The main draw back is a \"ticker\" (a message\n".
	"that scrolls past at the bottom of the screen, with the copyright and anti-copy\n".
	"telephone number). Also, if the tape contains any serial numbers, or any other markings\n".
	"that could lead to the source of the tape, these will have to be blocked, usually with a\n".
	"black mark over the section. This is sometimes only for a few seconds, but unfortunately\n".
	"on some copies this will last for the entire film, and some can be quite big. Depending\n".
	"on the equipment used, screener quality can range from excellent if done from a MASTER\n".
	"copy, to very poor if done on an old VHS recorder thru poor capture equipment on a copied\n".
	"tape. Most screeners are transferred to VCD, but a few attempts at SVCD have occurred,\n".
	"some looking better than others.<br>\n".
	"<br>\n".
	"<br>\n";

echo "<b>DVD-SCREENER (DVDscr) -</b><br>\n".
	"<br>\n".
	"Same premise as a screener, but transferred off a DVD. Usually letterbox , but without\n".
	"the extras that a DVD retail would contain. The ticker is not usually in the black bars,\n".
	"and will disrupt the viewing. If the ripper has any skill, a DVDscr should be very good.\n".
	"Usually transferred to SVCD or DivX/XviD.<br>\n".
	"<br>\n".
	"<br>\n";

echo "<b>DVDRip -</b><br>\n".
	"<br>\n".
	"A copy of the final released DVD. If possible this is released PRE retail (for example,\n".
	"Star Wars episode 2) again, should be excellent quality. DVDrips are released in SVCD\n".
	"and DivX/XviD.<br>\n".
	"<br>\n".
	"<br>\n";

echo "<b>VHSRip -</b><br>\n".
	"<br>\n".
	"Transferred off a retail VHS, mainly skating/sports videos and XXX releases.<br>\n".
	"<br>\n".
	"<br>\n";

echo "<b>TVRip -</b><br>\n".
	"<br>\n".
	"TV episode that is either from Network (capped using digital cable/satellite boxes are\n".
	"preferable) or PRE-AIR from satellite feeds sending the program around to networks a few\n".
	"days earlier (do not contain \"dogs\" but sometimes have flickers etc) Some programs such\n".
	"as WWF Raw Is War contain extra parts, and the \"dark matches\" and camera/commentary\n".
	"tests are included on the rips. PDTV is capped from a digital TV PCI card, generally\n".
	"giving the best results, and groups tend to release in SVCD for these. VCD/SVCD/DivX/XviD\n".
	"rips are all supported by the TV scene.<br>\n".
	"<br>\n".
	"<br>\n";

echo "<b>WORKPRINT (WP) -</b><br>\n".
	"<br>\n".
	"A workprint is a copy of the film that has not been finished. It can be missing scenes,\n".
	"music, and quality can range from excellent to very poor. Some WPs are very different\n".
	"from the final print (Men In Black is missing all the aliens, and has actors in their\n".
	"places) and others can contain extra scenes (Jay and Silent Bob) . WPs can be nice\n".
	"additions to the collection once a good quality final has been obtained.<br>\n".
	"<br>\n".
	"<br>\n";

echo "<b>DivX Re-Enc -</b><br>\n".
	"<br>\n".
	"A DivX re-enc is a film that has been taken from its original VCD source, and re-encoded\n".
	"into a small DivX file. Most commonly found on file sharers, these are usually labeled\n".
	"something like Film.Name.Group(1of2) etc. Common groups are SMR and TND. These aren't\n".
	"really worth downloading, unless you're that unsure about a film u only want a 200mb copy\n".
	"of it. Generally avoid.<br>\n".
	"<br>\n".
	"<br>\n";

echo "<b>Watermarks -</b><br>\n".
	"<br>\n".
	"A lot of films come from Asian Silvers/PDVD (see below) and these are tagged by the\n".
	"people responsible. Usually with a letter/initials or a little logo, generally in one\n".
	"of the corners. Most famous are the \"Z\" \"A\" and \"Globe\" watermarks.<br>\n".
	"<br>\n".
	"<br>\n";

echo "<b>Asian Silvers / PDVD -</b><br>\n".
	"<br>\n".
	"These are films put out by eastern bootleggers, and these are usually bought by some\n".
	"groups to put out as their own. Silvers are very cheap and easily available in a lot of\n".
	"countries, and its easy to put out a release, which is why there are so many in the scene\n".
	"at the moment, mainly from smaller groups who don't last more than a few releases. PDVDs\n".
	"are the same thing pressed onto a DVD. They have removable subtitles, and the quality is\n".
	"usually better than the silvers. These are ripped like a normal DVD, but usually released\n".
	"as VCD.<br>\n".
	"<br>\n".
	"<br>\n";

echo "<b>Scene Tags...</b><br>\n".
	"<br>\n";

echo "<b>PROPER -</b><br>\n".
	"<br>\n".
	"Due to scene rules, whoever releases the first Telesync has won that race (for example).\n".
	"But if the quality of that release is fairly poor, if another group has another telesync\n".
	"(or the same source in higher quality) then the tag PROPER is added to the folder to\n".
	"avoid being duped. PROPER is the most subjective tag in the scene, and a lot of people\n".
	"will generally argue whether the PROPER is better than the original release. A lot of\n".
	"groups release PROPERS just out of desperation due to losing the race. A reason for the\n".
	"PROPER should always be included in the NFO.<br>\n".
	"<br>\n".
	"<br>\n";

echo "<b>LIMITED -</b><br>\n".
	"<br>\n".
	"A limited movie means it has had a limited theater run, generally opening in less than\n".
	"250 theaters, generally smaller films (such as art house films) are released as limited.<br>\n".
	"<br>\n".
	"<br>\n";

echo "<b>INTERNAL -</b><br>\n".
	"<br>\n".
	"An internal release is done for several reasons. Classic DVD groups do a lot of INTERNAL\n".
	"releases, as they wont be dupe'd on it. Also lower quality theater rips are done INTERNAL\n".
	"so not to lower the reputation of the group, or due to the amount of rips done already.\n".
	"An INTERNAL release is available as normal on the groups affiliate sites, but they can't\n".
	"be traded to other sites without request from the site ops. Some INTERNAL releases still\n".
	"trickle down to IRC/Newsgroups, it usually depends on the title and the popularity.\n".
	"Earlier in the year people referred to Centropy going \"internal\". This meant the group\n".
	"were only releasing the movies to their members and site ops. This is in a different\n".
	"context to the usual definition.<br>\n".
	"<br>\n".
	"<br>\n";

echo "<b>STV -</b><br>\n".
	"<br>\n".
	"Straight To Video. Was never released in theaters, and therefore a lot of sites do not\n".
	"allow these.<br>\n".
	"<br>\n".
	"<br>\n";

echo "<b>ASPECT RATIO TAGS -</b><br>\n".
	"<br>\n".
	"These are *WS* for widescreen (letterbox) and *FS* for Fullscreen.<br>\n".
	"<br>\n".
	"<br>\n";

echo "<b>REPACK -</b><br>\n".
	"<br>\n".
	"If a group releases a bad rip, they will release a Repack which will fix the problems.<br>\n".
	"<br>\n".
	"<br>\n";

echo "<b>NUKED -</b><br>\n".
	"<br>\n".
	"A film can be nuked for various reasons. Individual sites will nuke for breaking their\n".
	"rules (such as \"No Telesyncs\") but if the film has something extremely wrong with it\n".
	"(no soundtrack for 20mins, CD2 is incorrect film/game etc) then a global nuke will occur,\n".
	"and people trading it across sites will lose their credits. Nuked films can still reach\n".
	"other sources such as p2p/usenet, but its a good idea to check why it was nuked first in\n".
	"case. If a group realise there is something wrong, they can request a nuke.<br>\n".
	"<br>\n".
	"<br>\n";

echo "<b>NUKE REASONS...</b><br>\n".
	"<br>\n".
	"this is a list of common reasons a film can be nuked for (generally DVDRip)<br>\n".
	"<br>\n".
	"<b>BAD A/R</b> = bad aspect ratio, ie people appear too fat/thin<br>\n".
	"<b>BAD IVTC</b> = bad inverse telecine. process of converting framerates was incorrect.<br>\n".
	"<b>INTERLACED</b> = black lines on movement as the field order is incorrect.<br>\n".
	"<br>\n".
	"<br>\n";

echo "<b>DUPE -</b><br>\n".
	"<br>\n".
	"Dupe is quite simply, if something exists already, then theres no reason for it to exist\n".
	"again without proper reason.<br>\n".
	"<br>\n".
	"<br>\n";

echo "                    </td>\n".
	"                </tr>\n".
	"            </table>\n".
	"        </td>\n".
	"    </tr>\n".
	"</table>\n";
stdfoot();
?>