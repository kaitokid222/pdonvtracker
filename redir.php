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

require("include/config.php");

define("REDIR_EXTERNAL", 1);
define("REDIR_INTERNAL", 2);

function check_domain($domain)
{
    if (in_array(strtolower($domain), $GLOBALS["TRACKERDOMAINS"]))
        return REDIR_INTERNAL;
        
    return REDIR_EXTERNAL;
}

$url = '';

while (list($var,$val) = each($HTTP_GET_VARS))
    $url .= "&$var=$val";

$i = strpos($url, "&url=");

if ($i !== false)
    $url = substr($url, $i + 5);

if (substr($url, 0, 4) == "www.")
    $url = "http://" . $url;

// Determine redirection target (inside/outside)    
$port = strpos($url, ":", 7);
$slash = strpos($url, "/", 7);

// We make no differentiation between FALSE and 0, because there should
// be no / or : directly after the "http://".
if ($port == FALSE && $slash == FALSE)
    $type = REDIR_EXTERNAL;

if ($port == FALSE && $slash > 0)
    $type = check_domain(substr($url, 7, $slash-7));

if ($port > 0 && $slash == FALSE)
    $type = check_domain(substr($url, 7, $port-7));

if ($type == REDIR_EXTERNAL) {
    // META redirect via anonym.to
    print("<html><head><meta http-equiv=refresh content='0;url=http://anonym.to/?$url'></head><body>\n");
    print("<table border=0 width=100% height=100%><tr><td><h2 align=center>Du wirst jetzt umgeleitet nach:<br>\n");
    print("$url</h2></td></tr></table></body></html>\n");
} else {
    // HTTP redirect
    header("Location: $url");
}
?>