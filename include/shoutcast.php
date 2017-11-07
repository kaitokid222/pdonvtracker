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

/*
 * XML2Array Parser Klasse
 * 
 * Code aus dem PHP Handbuch, Kommentare von
 * 
 * randlem@gmail.com
 * tgrabietz@bupnet.de
 * bbellwfu@gmail.com
 * 
 * http://www.php.net/manual/de/function.xml-parse.php
 * 
 **/
class xml2Array {
    var $arrOutput = array();
    var $resParser;
    var $strXmlData;

    function parse($strInputXML)
    {
        $this->resParser = xml_parser_create ();
        xml_set_object($this->resParser, $this);
        xml_set_element_handler($this->resParser, "tagOpen", "tagClosed");

        xml_set_character_data_handler($this->resParser, "tagData");

        $this->strXmlData = xml_parse($this->resParser, $strInputXML);
        if (!$this->strXmlData) {
            die(sprintf("XML error: %s at line %d",
                    xml_error_string(xml_get_error_code($this->resParser)),
                    xml_get_current_line_number($this->resParser)));
        } 

        xml_parser_free($this->resParser);

        return $this->arrOutput;
    } 
    function tagOpen($parser, $name, $attrs)
    {
        $tag = array("name" => $name, "attrs" => $attrs);
        array_push($this->arrOutput, $tag);
    } 

    function tagData($parser, $tagData)
    {
        if (trim($tagData)) {
            if (isset($this->arrOutput[count($this->arrOutput)-1]['tagData'])) {
                $this->arrOutput[count($this->arrOutput)-1]['tagData'] .= $tagData;
            } else {
                $this->arrOutput[count($this->arrOutput)-1]['tagData'] = $tagData;
            } 
        } 
    } 

    function tagClosed($parser, $name)
    {
        $this->arrOutput[count($this->arrOutput)-2]['children'][] = $this->arrOutput[count($this->arrOutput)-1];
        array_pop($this->arrOutput);
    } 
} 

// Simple HTTP-Anfragefunktion, um die Stats zu erhalten
// Funktioniert auch mit aktivem safe_mode
function get_http_data($URL, $referrer = "")
{ 
    // Gibt die Daten der HTTP-Antwort zurück
    // URL zerlegen
    if (!preg_match("/http\:\/\/(([\w\.\-]+)(\:(.+?))?@)?([\w\.\-]+)\:?(\d*)(\/?\S*)/i", $URL, $match)) return false; 
    // Verbinden
    $fhandle = @fsockopen($match[5], ($match[6] > 0?$match[6]:80), $errno, $errstr, $GLOBALS["SC_HTTPTIMEOUT"]);

    if (!$fhandle) {
        return false;
    } else {
        $request = "GET " . ($match[7] <> ""?$match[7]:"/") . " HTTP/1.0\r\n";
        $request .= "Host: " . $match[5] . "\r\n";
        if ($referrer != "") {
            $request .= "Referer: " . $referrer . "\r\n";
        } 
        if ($match[2] != "") {
            $authstring = base64_encode($match[2] . ":" . $match[4]);
            $request .= "Authorization: Basic " . $authstring . "\r\n";
        }
        $request .= "Connection: close\r\nUser-Agent: Mozilla/5.0 (compatible;)\r\n\r\n"; 
        // Request senden. HTTP 1.0 verwenden, um Chunked Encoding zu verhindern
        fputs ($fhandle, $request);

        $retr = "";
        while (!feof($fhandle)) {
            $retr .= fgets($fhandle, 128);
        } 
        // Verbindung beenden
        fclose($fhandle); 
        // Daten und Header trennen
        $pos = strpos($retr, "\r\n\r\n");
        $header = substr($retr, 0, $pos - 1);
        $data = substr($retr, $pos + 4); 
        // Daten und Header zurückgeben
        return array("header" => $header, "data" => $data);
    } 
}

// Tag suchen und inkl. Kinder zurückgeben
function xmlarr_find_tag(&$array, $tagname)
{
    foreach ($array as $search) {
        if ($search["name"] == $tagname) {
            return $search;
        } 
    } 
    return false;
} 

// Daten des aktuellen Tags zurückgeben
function xmlarr_get_tagdata(&$array, $tagname)
{
    foreach ($array as $search) {
        if ($search["name"] == $tagname) {
            return $search["tagData"];
        } 
    } 
    return false;
} 

// Alle Shoutcast-Statistiken in ein einfach zu verwendendes Array kopieren
function get_sc_stats()
{
    $URL = "http://" . $GLOBALS["SC_USERNAME"] . ":" . $GLOBALS["SC_PASSWORD"] . "@";
    $URL .= $GLOBALS["SC_HOSTNAME"] . ":" . $GLOBALS["SC_PORT"] . "/admin.cgi?mode=viewxml";
    $retr = get_http_data($URL);

    if ($retr == FALSE)
        return FALSE;
    
    $XMLParser = new xml2Array();
    $array = $XMLParser->parse($retr["data"]);
    $array = xmlarr_find_tag($array, "SHOUTCASTSERVER");
    $array = $array["children"];
    
    $statsarr = array();
    
    // Global stats
    $statsarr["currentlisteners"]  = xmlarr_get_tagdata($array, "CURRENTLISTENERS");
    $statsarr["peaklisteners"]     = xmlarr_get_tagdata($array, "PEAKLISTENERS");
    $statsarr["maxlisteners"]      = xmlarr_get_tagdata($array, "MAXLISTENERS");
    $statsarr["reportedlisteners"] = xmlarr_get_tagdata($array, "REPORTEDLISTENERS");
    $statsarr["averagetime"]       = xmlarr_get_tagdata($array, "AVERAGETIME");
    $statsarr["servergenre"]       = xmlarr_get_tagdata($array, "SERVERGENRE");
    $statsarr["serverurl"]         = xmlarr_get_tagdata($array, "SERVERURL");
    $statsarr["servertitle"]       = xmlarr_get_tagdata($array, "SERVERTITLE");
    $statsarr["songtitle"]         = xmlarr_get_tagdata($array, "SONGTITLE");
    $statsarr["songurl"]           = xmlarr_get_tagdata($array, "SONGURL");
    $statsarr["irc"]               = xmlarr_get_tagdata($array, "IRC");
    $statsarr["icq"]               = xmlarr_get_tagdata($array, "ICQ");
    $statsarr["aim"]               = xmlarr_get_tagdata($array, "AIM");
    $statsarr["webhits"]           = xmlarr_get_tagdata($array, "WEBHITS");
    $statsarr["streamhits"]        = xmlarr_get_tagdata($array, "STREAMHITS");
    $statsarr["streamstatus"]      = xmlarr_get_tagdata($array, "STREAMSTATUS");
    $statsarr["bitrate"]           = xmlarr_get_tagdata($array, "BITRATE");
    $statsarr["content"]           = xmlarr_get_tagdata($array, "CONTENT");
    $statsarr["version"]           = xmlarr_get_tagdata($array, "VERSION");

    // Web stats
    $webarray = xmlarr_find_tag($array, "WEBDATA");
    $webarray = $webarray["children"];
    $statsarr["webdata"]              = array();
    $statsarr["webdata"]["index"]     = xmlarr_get_tagdata($webarray, "INDEX");
    $statsarr["webdata"]["listen"]    = xmlarr_get_tagdata($webarray, "LISTEN");
    $statsarr["webdata"]["palm7"]     = xmlarr_get_tagdata($webarray, "PALM7");
    $statsarr["webdata"]["login"]     = xmlarr_get_tagdata($webarray, "LOGIN");
    $statsarr["webdata"]["loginfail"] = xmlarr_get_tagdata($webarray, "LOGINFAIL");
    $statsarr["webdata"]["played"]    = xmlarr_get_tagdata($webarray, "PLAYED");
    $statsarr["webdata"]["cookie"]    = xmlarr_get_tagdata($webarray, "COOKIE");
    $statsarr["webdata"]["admin"]     = xmlarr_get_tagdata($webarray, "ADMIN");
    $statsarr["webdata"]["updinfo"]   = xmlarr_get_tagdata($webarray, "UPDINFO");
    $statsarr["webdata"]["kicksrc"]   = xmlarr_get_tagdata($webarray, "KICKSRC");
    $statsarr["webdata"]["kickdst"]   = xmlarr_get_tagdata($webarray, "KICKDST");
    $statsarr["webdata"]["unbandst"]  = xmlarr_get_tagdata($webarray, "UNBANDST");
    $statsarr["webdata"]["bandst"]    = xmlarr_get_tagdata($webarray, "BANDST");
    $statsarr["webdata"]["viewban"]   = xmlarr_get_tagdata($webarray, "VIEWBAN");
    $statsarr["webdata"]["unripdst"]  = xmlarr_get_tagdata($webarray, "UNRIPDST");
    $statsarr["webdata"]["ripdst"]    = xmlarr_get_tagdata($webarray, "RIPDST");
    $statsarr["webdata"]["viewrip"]   = xmlarr_get_tagdata($webarray, "VIEWRIP");
    $statsarr["webdata"]["viewxml"]   = xmlarr_get_tagdata($webarray, "VIEWXML");
    $statsarr["webdata"]["viewlog"]   = xmlarr_get_tagdata($webarray, "VIEWLOG");
    $statsarr["webdata"]["invalid"]   = xmlarr_get_tagdata($webarray, "INVALID");
    
    // Listener stats
    $statsarr["listeners"] = array();
    $lstarray = xmlarr_find_tag($array, "LISTENERS");
    if (is_array($lstarray)) {
        $lstarray = $lstarray["children"]; 
        for ($I = 0; $I < count($lstarray); $I++) {
            $listener = $lstarray[$I]["children"];
            $listener_info = array();
            $listener_info["hostname"]    = xmlarr_get_tagdata($listener, "HOSTNAME");
            $listener_info["useragent"]   = xmlarr_get_tagdata($listener, "USERAGENT");
            $listener_info["underruns"]   = xmlarr_get_tagdata($listener, "UNDERRUNS");
            $listener_info["connecttime"] = xmlarr_get_tagdata($listener, "CONNECTTIME");
            $listener_info["pointer"]     = xmlarr_get_tagdata($listener, "POINTER");
            $listener_info["uid"]         = xmlarr_get_tagdata($listener, "UID");
            array_push($statsarr["listeners"], $listener_info);
        }
    }
    
    // Song history
    $statsarr["songhistory"] = array();
    $songarray = xmlarr_find_tag($array, "SONGHISTORY");
    if (is_array($songarray)) {
        $songarray = $songarray["children"]; 
        for ($I = 0; $I < count($songarray); $I++) {
            $song = $songarray[$I]["children"];
            $song_info = array();
            $song_info["playedat"] = xmlarr_get_tagdata($song, "PLAYEDAT");
            $song_info["title"]    = xmlarr_get_tagdata($song, "TITLE");
            array_push($statsarr["songhistory"], $song_info);
        }
    }
    
    return $statsarr;
} 


// Radio-Infobox für den Tracker
function sc_infobox()
{
    $stats = get_sc_stats();
    if ($stats == FALSE)
        return;

    begin_table(TRUE);

    ?>
  <colgroup>
    <col width="50%" />
    <col width="50%" />
  </colgroup>
  <tr class="tabletitle">
    <td colspan="2" width="100%" class="tabletitle"><a name="radiopls"></a><span class="normalfont"><center><img src="<?=$GLOBALS["PIC_BASE_URL"]?>radio.png" width="16" height="16" alt="" style="vertical-align: middle;"> <b><?=htmlspecialchars($GLOBALS["RADIOTITLE"])?></b></center></span></td> 
  </tr>
  <tr>
    <td class="tableb" align="center"><span class="normalfont"><center><img src="<?=$GLOBALS["PIC_BASE_URL"]?>radiolisten.png" width="16" height="16" alt="" style="vertical-align: middle;"> <a href="http://<?=$GLOBALS["SC_HOSTNAME"] . ":" . $GLOBALS["SC_PORT"]?>/listen.pls">Stream anh&ouml;ren</a></center></span></td>
    <td class="tableb" align="center"><span class="normalfont"><center><img src="<?=$GLOBALS["PIC_BASE_URL"]?>radioplaylist.png" width="16" height="16" alt="" style="vertical-align: middle;"> <a href="#radiopls" onclick="var myDiv=document.getElementById('playlist');myDiv.style.display='block';">Letzte 10 Songs anzeigen</a></center></span></td>
  </tr>
  <tr>
    <td class="tablea" style="vertical-align:top;text-align:left;" colspan="2">
      <?php
    if ($stats["streamstatus"] == 1) {
        echo "<center><table><tr><td valign=\"middle\"><img src=\"" . $GLOBALS["PIC_BASE_URL"] . "button_online2.gif\" width=\"15\" height=\"15\" alt=\"Online\" style=\"vertical-align: middle;\"></td>";
        echo "<td valign=\"middle\" style=\"font-size:200%;font-weight:bold;\">On Air</td>";
        echo "<td valign=\"middle\"><img src=\"" . $GLOBALS["PIC_BASE_URL"] . "button_online2.gif\" width=\"15\" height=\"15\" alt=\"Online\" style=\"vertical-align: middle;\"></td></tr></table></center>";

        echo "<br><table><tr><td valign=\"top\"><img src=\"" . $GLOBALS["PIC_BASE_URL"] . "viewmag16.png\" width=\"16\" height=\"16\" alt=\"\" style=\"vertical-align: middle;\"></td>";
        echo "<td valign=\"top\"><b>" . htmlspecialchars($stats["servertitle"]) . "</b><br><br>";
        printf("Aktuell <b>%d</b> Zuhörer von maximal <b>%d</b> möglichen, der Rekord liegt bei <b>%d</b>", $stats["currentlisteners"], $stats["maxlisteners"], $stats["peaklisteners"]);

        ?>
                <br><br><b>Momentan l&auml;uft:</b><br>
                <table>
                  <tr>
                    <td valign="middle"><img src="<?=$GLOBALS["PIC_BASE_URL"]?>song.png" width="16" height="16" alt=""></td>
                    <td style="vertical-align:middle;text-align:left;"><?=htmlspecialchars($stats["songtitle"])?> @<?=$stats["bitrate"]?> kbps</td>
                  </tr>
                </table>
                
                <br><b>Unsere Zuh&ouml;rer vom Tracker:</b><br>
                <?php
        // Zuhörer
        $ipaddrs = array();
        for ($I=0; $I < count($stats["listeners"]); $I++) {
            $ip = gethostbyname($stats["listeners"][$I]["hostname"]);
            $ipaddrs[] = sqlesc($ip);
        }
        $res = mysql_query("SELECT DISTINCT(`ip`),`id`,`username`,`class`,`donor`,`warned`,`added`,`enabled` FROM `users` WHERE `ip` IN (".implode(",", $ipaddrs).") GROUP BY `ip` ORDER BY `class` DESC,`username` ASC");
        $activeusers = "";
        while ($arr = mysql_fetch_assoc($res))
        {
            if ($activeusers) $activeusers .= ",\n";
            $arr["username"] = "<font class=".get_class_color($arr["class"]).">" . $arr["username"] . "</font>";
            $activeusers .= "<a href=userdetails.php?id=" . $arr["id"] . "><b>" . $arr["username"] . "</b></a>";          
            $activeusers .= "&nbsp;".get_user_icons($arr);
        }
        
        if ($activeusers != "") {
            echo $activeusers;
        } else {
            echo "Aktuell sind keine Tracker-Mitglieder unter den Zuhörern.";
        }
        ?>
        </td></tr></table> 
        <?php
    } else {
        echo "<center><table><tr><td valign=\"middle\"><img src=\"" . $GLOBALS["PIC_BASE_URL"] . "button_offline2.gif\" width=\"15\" height=\"15\" alt=\"\"></td><td valign=\"middle\" style=\"font-size:200%;font-weight:bold;\">Offline</td><td valign=\"middle\"><img src=\"" . $GLOBALS["PIC_BASE_URL"] . "button_offline2.gif\" width=\"15\" height=\"15\" alt=\"\"></td></tr></table></center>";
    }    
    ?>
      <div id="playlist" style="display:none;width:100%;"><br>
      <?php
    begin_table(TRUE);
    echo "<tr><td class=\"tablecat\">&nbsp;</td><td class=\"tablecat\"><b>Zeit</b></td><td class=\"tablecat\"><b>Song</b></td></tr>";
    for ($I = 0; $I < count($stats["songhistory"]); $I++) {
        echo "<tr><td class=\"tablea\" style=\"vertical-aling:middle;text-align:center;\"><img src=\"" . $GLOBALS["PIC_BASE_URL"] . "song.png\" width=\"16\" height=\"16\" alt=\"\"></td>";
        echo "<td class=\"tableb\" nowrap=\"nowrap\">" . date("d.n.Y, G:i:s", $stats["songhistory"][$I]["playedat"]) . "</td>";
        echo "<td class=\"tablea\" width=\"100%\">" . htmlspecialchars($stats["songhistory"][$I]["title"]) . "</td></tr>\n";
    }
    end_table();

    ?>      
      </div>
    </td>
  </tr>
<?php
    end_table();
} 

?>