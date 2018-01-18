<?php

/*
// +--------------------------------------------------------------------------+
// | Project:    pdonvtracker - NetVision BitTorrent Tracker 2017             |
// +--------------------------------------------------------------------------+
// | This file is part of pdonvtracker. NVTracker is based on BTSource,       |
// | originally by RedBeard of TorrentBits, extensively modified by           |
// | Gartenzwerg.                                                             |
// +--------------------------------------------------------------------------+
// | Obige Zeilen dürfen nicht entfernt werden!    Do not remove above lines! |
// +--------------------------------------------------------------------------+
 */

function stdhead($title = "", $msgalert = true){
	global $CURUSER, $_SERVER, $PHP_SELF, $BASEURL;

	if(!$GLOBALS["SITE_ONLINE"])
		die("Die Seite ist momentan aufgrund von Wartungsarbeiten nicht verfügbar.<br>");

	header("Content-Type: text/html; charset=iso-8859-1");
	header("Pragma: No-cache");
	header("Expires: 300");
	header("Cache-Control: private");

	if($title == "")
		$title = $GLOBALS["SITENAME"];
	else
		$title = $GLOBALS["SITENAME"] . " :: " . htmlspecialchars($title);

	if($CURUSER){
		$qry = $GLOBALS['DB']->prepare('SELECT `uri` FROM `stylesheets` WHERE `id`= :id');
		$qry->bindParam(':id', $CURUSER["stylesheet"], PDO::PARAM_INT);
		$qry->execute();
		$ss_a = $qry->fetchObject();
		if ($ss_a)
			$GLOBALS["ss_uri"] = $ss_a->uri;
	}else{
		if(!isset($GLOBALS["ss_uri"])){
			$qry = $GLOBALS['DB']->prepare("SELECT `uri` FROM `stylesheets` WHERE `default`='yes'");
			$qry->execute();
			if($qry->rowCount() > 0){
				$row = $qry->fetchObject();
			}
			$GLOBALS["ss_uri"] = $row->uri;
		}
	}

	if($msgalert && $CURUSER){
		$unread = $GLOBALS['database']->row_count("messages","receiver=" . $CURUSER["id"] . " AND folder_in<>0 AND unread='yes'");
		if($unread < 1)
			unset($unread);
		if($CURUSER["class"] >= UC_MODERATOR){
			$unread_mod = $GLOBALS['database']->row_count("messages","sender=0 AND receiver=0 && mod_flag= 'open'");
			if($unread_mod < 1)
				unset($unread_mod);
		}
	}

	$fn = substr($PHP_SELF, strrpos($PHP_SELF, "/") + 1);
	$logo_pic = $GLOBALS["PIC_BASE_URL"] . $GLOBALS["ss_uri"] . "/";
	if(file_exists($logo_pic . "logo.gif"))
		$logo_pic .= "logo.gif";
	if(file_exists($logo_pic . "logo_top.gif"))
		$logo_pic .= "logo_top.gif";
	if(file_exists($logo_pic . "logo.jpg"))
		$logo_pic .= "logo.jpg";
	if(file_exists($logo_pic . "logo_top.jpg"))
		$logo_pic .= "logo_top.jpg";
	if(file_exists("header.jpg"))
		$logo_pic .= "header.jpg";
	if(file_exists($logo_pic . "header.gif"))
		$logo_pic .= "header.gif";


	echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\"\n".
		"        \"http://www.w3.org/TR/html4/loose.dtd\">\n".
		"<html>\n".
		"<head>\n".
		"    <title>" . $title . "</title>\n".
		"    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\n".
		"    <meta http-equiv=\"Content-Script-Type\" content=\"text/javascript\">\n".
		"    <meta http-equiv=\"pragma\" content=\"no-cache\">\n".
		"    <meta http-equiv=\"expires\" content=\"300\">\n".
		"    <meta http-equiv=\"cache-control\" content=\"private\">\n".
		"    <meta name=\"robots\" content=\"noindex, nofollow, noarchive\">\n".
		"    <meta name=\"MSSmartTagsPreventParsing\" content=\"true\">\n".
		"    <link rel=\"stylesheet\" href=\"" . $GLOBALS["PIC_BASE_URL"] . $GLOBALS["ss_uri"] . "/" . $GLOBALS["ss_uri"] . ".css\" type=\"text/css\">\n";

	if(date('m') == 12 || date('m') == 1){
		echo "    <script type=\"text/javascript\" src=\"/js/jquery-3.2.1.min.js\"></script>\n";
		echo "    <script type=\"text/javascript\" src=\"/js/jsnow.js\"></script>\n";
	}

	if($GLOBALS["DYNAMIC_RSS"]){
		echo "    <link rel=\"alternate\" title=\"NetVision RSS\" href=\"" . $BASEURL . "/rss.php\" type=\"application/rss+xml\">\n".
			"    <link rel=\"alternate\" title=\"NetVision RSS (Direktdownload)\" href=\"" . $BASEURL . "/rss.php?type=directdl\" type=\"application/rss+xml\">\n".
			"    <link rel=\"alternate\" title=\"NetVision RSS (Benutzerkategorien)\" href=\"" . $BASEURL . "/rss.php?categories=profile\" type=\"application/rss+xml\">\n".
			"    <link rel=\"alternate\" title=\"NetVision RSS (Benutzerkategorien, Direktdownload)\" href=\"" . $BASEURL . "/rss.php?categories=profile&type=directdl\" type=\"application/rss+xml\">\n";
	} 

	echo "</head>\n".
		"<body>\n";

	if(date('m') == 12 || date('m') == 1){
		echo "    <script type=\"text/javascript\">\n".
			"    $(function() {\n".
			"        $(document).snow({ SnowImage: \"" . $BASEURL . "/" . $GLOBALS["PIC_BASE_URL"] . "weather-snowflake.png\" });\n".
			"    });\n".
			"    </script>\n";
	}

	echo "    <table style=\"width:100%\" cellpadding=\"0\" cellspacing=\"1\" align=\"center\" border=\"0\" class=\"tableoutborder\">\n".
		"        <tr>\n".
		"            <td class=\"mainpage\" align=\"center\">\n".
		"                <table style=\"width:100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n".
		"                    <tr>\n".
		"                        <td class=\"logobackground\" align=\"left\"><a href=\"index.php\"><img src=\"" . $logo_pic . "\" border=\"0\" alt=\"NetVision\" title=\"NetVision\" /></a></td>\n".
		"                    </tr>\n".
		"                    <tr>\n".
		"                        <td align=\"right\" class=\"topbuttons\" nowrap=\"nowrap\">\n".
		"                            <span class=\"smallfont\">\n";

	if($GLOBALS["PORTAL_LINK"] != "")
		echo "                                <a href=\"" . $GLOBALS["PORTAL_LINK"] . "\"><img src=\"" . $GLOBALS["PIC_BASE_URL"] . $GLOBALS["ss_uri"] . "/top_portal.gif\" border=\"0\" alt=\"\" title=\"Zum Portal\" /></a>\n";
	echo "                            </span>\n".
		"                        </td>\n".
		"                    </tr>\n".
		"                </table>\n".
		"                <table style=\"width:100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n".
		"                    <tr>\n".
		"                        <td valign=\"top\" align=\"left\" style=\"padding: 5px;width:150px\">\n".
		"                            <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"width:150px\">\n".
		"                                <tr>\n".
		"                                    <td align=\"left\"><img src=\"" . $GLOBALS["PIC_BASE_URL"] . $GLOBALS["ss_uri"] . "/obenlinks.gif\" alt=\"\" title=\"\" /></td>\n".
		"                                    <td style=\"width:100%\" class=\"obenmitte\"></td>\n".
		"                                    <td align=\"right\"><img src=\"" . $GLOBALS["PIC_BASE_URL"] . $GLOBALS["ss_uri"] . "/obenrechts.gif\" alt=\"\" title=\"\" /></td>\n".
		"                                </tr>\n".
		"                            </table>\n".
		"                            <table cellpadding=\"0\" cellspacing=\"1\" border=\"0\" style=\"width:100%\" class=\"tableinborder\">\n";
	if($CURUSER && $CURUSER["statbox"] == "top")
		ratiostatbox();       
	echo "                                <tr>\n".
		"                                    <td class=\"tabletitle\" style=\"padding: 4px;\"><b>NetVision :.</b></td>\n".
		"                                </tr>\n".
		"                                <tr>\n".
		"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"index.php\" title=\"Neuigkeiten vom Team sowie allgemeine Tracker-Stats und Umfragen\">Tracker-News</a></td>\n".
		"                                </tr>\n";
	if($GLOBALS["PORTAL_LINK"] != ""){
		echo "                                <tr>\n".
			"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"" . $GLOBALS["PORTAL_LINK"] . "\" title=\"Unser Portal und Forum f&uuml;r alles M&ouml;gliche\">Portal</a></td>\n".
			"                                </tr>\n";
	}
	echo "                                <tr>\n".
		"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"faq.php\" title=\"Oft gestellte Fragen zu diversen trackerspezifischen Themen\">FAQ</a></td>\n".
		"                                </tr>\n".
		"                                <tr>\n".
		"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"rules.php\" title=\"Alle Verhaltensregeln f&uuml;r den Tracker - LESEPFLICHT!\">Regeln</a></td>\n".
		"                                </tr>\n";
	if($CURUSER){
		if($GLOBALS["IRCAVAILABLE"]){
			echo "                                <tr>\n".
				"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"chat.php\" title=\"IRC-Serverdaten und ein einfach zu benutzendes Java-Applet\">IRC Chat</a></td>\n".
				"                                </tr>\n";
		}
		echo "                                <tr>\n".
			"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"users.php\" title=\"Liste aller Mitglieder, inkl. Suchfunktion\">Mitglieder</a></td>\n".
			"                                </tr>\n".
			"                                <tr>\n".
			"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"topten.php\" title=\"Diverse Top-Listen\">Top 10</a></td>\n".
			"                                </tr>\n".
			"                                <tr>\n".
			"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"staff.php\" title=\"Schnelle &Uuml;bersicht &uuml;ber das  Trackerteam\">Team</a></td>\n".
			"                                </tr>\n".
			"                                <tr>\n".
			"                                    <td class=\"tabletitle\" style=\"padding: 4px;\"><b>Torrents :.</b></td>\n".
			"                                </tr>\n".
			"                                <tr>\n".
			"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"browse.php\" title=\"Verf&uuml;gbare Torrents anzeigen oder suchen\">Durchsuchen</a></td>\n".
			"                                </tr>\n".
			"                                <tr>\n".
			"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"upload.php\" title=\"Lade einen eigenen Torrent auf den Tracker hoch\">Hochladen</a></td>\n".
			"                                </tr>\n".
			"                                <tr>\n".
			"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"mytorrents.php\" title=\"Hier werden alle von Dir hochgeladenen Torrents angezeigt\">Meine Torrents</a></td>\n".
			"                                </tr>\n";
		if(get_user_class() >= UC_GUTEAM){
			echo "                                <tr>\n".
				"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"guestuploads.php\" title=\"Zeigt alle noch nicht freigeschalteten Gastuploads\">Neue Gastuploads</a></td>\n".
				"                                </tr>\n";
		}
		echo "                                <tr>\n".
			"                                    <td class=\"tabletitle\" style=\"padding: 4px;\"><b>Mein Account :.</b></td>\n".
			"                                </tr>\n".
			"                                <tr>\n".
			"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"userdetails.php?id=" . $CURUSER["id"] . "\" title=\"Deine Statistik-Seite, die auch andere Benutzer sehen\">Mein Profil</a></td>\n".
			"                                </tr>\n".
			"                                <tr>\n".
			"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"my.php\" title=\"Hier kannst Du Deine Einstellungen &auml;ndern\">Profil bearbeiten</a></td>\n".
			"                                </tr>\n".
			"                                <tr>\n".
			"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"friends.php\" title=\"Eine Liste Deiner &quot;Freunde&quot; auf dem Tracker\">Buddyliste</a></td>\n".
			"                                </tr>\n".
			"                                <tr>\n".
			"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"messages.php\" title=\"Pers&ouml;nliche Nachrichten lesen und beantworten\">Nachrichten";
		if(isset($unread) || isset($unread_mod))
			echo "&nbsp;&nbsp;";
		if(isset($unread)){
			echo "<img src=\"" . $GLOBALS["PIC_BASE_URL"] . "multipage.gif\" border=\"0\"> <b>" . $unread . "</b>";
			if(isset($unread_mod))
				echo "&nbsp;&nbsp;";
		}
        if(isset($unread_mod)){
            echo "<img src=\"" . $GLOBALS["PIC_BASE_URL"] . "multipagemod.gif\" border=\"0\"> <b>" . $unread_mod . "</b>";
        } 
		echo "</a></td>\n";
		echo "                                </tr>\n".
			"                                <tr>\n".
			"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"bitbucket.php\" title=\"Hier kannst Du Avatare und andere Bilder ablegen\">BitBucket</a></td>\n".
			"                                </tr>\n".
			"                                <tr>\n".
			"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"logout.php\" title=\"Beendet Deine Sitzung und l&ouml;scht die Autologin-Cookies\">Ausloggen</a></td>\n".
			"                                </tr>\n";
		if(get_user_class() >= UC_MODERATOR){
			echo "                                <tr>\n".
				"                                    <td class=\"tabletitle\" style=\"padding: 4px;\"><b>Administration :.</b></td>\n".
				"                                </tr>\n".
				"                                <tr>\n".
				"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"log.php\" title=\"Tracker-Logbuch anzeigen\">Site Log</a></td>\n".
				"                                </tr>\n".
				"                                <tr>\n".
				"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"usersearch.php\" title=\"Suche nach Benutzern &uuml;ber diverse Angaben\">Benutzersuche</a></td>\n".
				"                                </tr>\n".
				"                                <tr>\n".
				"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"polls.php\" title=\"Umfrageverwaltung\">Umfragen</a></td>\n".
				"                                </tr>\n".
				"                                <tr>\n".
				"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"staff.php?act=last\" title=\"Liste aller Benutzer, nach Anmeldedatum sortiert\">Neueste Benutzer</a></td>\n".
				"                                </tr>\n".
				"                                <tr>\n".
				"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"staff.php?act=ban\" title=\"Hier kannst Du IP-Bereiche vom Tracker aussperren\">IPs sperren</a></td>\n".
				"                                </tr>\n".
				"                                <tr>\n".
				"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"staff.php?act=upstats\" title=\"Schnelle &Uuml;bersicht &uuml;ber die Uploadaktivit&auml;ten\">Uploader-Stats</a></td>\n".
				"                                </tr>\n".
				"                                <tr>\n".
				"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"bitbucket-gallery.php\" title=\"Zeigt s&auml;mtliche BitBucket-Bilder an, nach Benutzern sortiert\">BitBucket Gallerie</a></td>\n".
				"                                </tr>\n".
				"                                <tr>\n".
				"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"startstoplog.php\" title=\"Diverse Werzeuge, die mit dem Eventlog verkn&uuml;pft sind\">Start-/Stop-Log</a></td>\n".
				"                                </tr>\n";
			if(get_user_class() >= UC_ADMINISTRATOR){
				echo "                                <tr>\n".
					"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"adduser.php\" title=\"Hier kannst Du einen neuen Account anlegen, der sofort aktiv ist\">Account erstellen</a></td>\n".
					"                                </tr>\n".
					"                                <tr>\n".
					"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"staff.php?act=cleanaccs\" title=\"Benutzer nach Ratio- und Aktivit&auml;tskriterien suchen und deaktivieren\">Accountbereinigung</a></td>\n".
					"                                </tr>\n";
			} 
		} 
    }else{
		echo "                                <tr>\n".
			"                                    <td class=\"tabletitle\"><b>Account :.</b></td>\n".
			"                                </tr>\n".
			"                                <tr>\n".
			"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"signup.php\">Registrieren</a></td>\n".
			"                                </tr>\n".
			"                                <tr>\n".
			"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"login.php\">Einloggen</a></td>\n".
			"                                </tr>\n".
			"                                <tr>\n".
			"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"recover.php\">PW vergessen?</a></td>\n".
			"                                </tr>\n";
	} 
	if ($CURUSER && $CURUSER["statbox"] == "bottom")
		ratiostatbox();
	echo "                            </table>\n".
		"                            <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"width:150px\">\n".
		"                                <tr>\n".
		"                                    <td align=\"left\"><img src=\"" . $GLOBALS["PIC_BASE_URL"] . $GLOBALS["ss_uri"] . "/untenlinks.gif\" alt=\"\" title=\"\" /></td>\n".
		"                                    <td style=\"width:100%\" class=\"untenmitte\" align=\"center\"></td>\n".
		"                                    <td align=\"right\"><img src=\"" . $GLOBALS["PIC_BASE_URL"] . $GLOBALS["ss_uri"] . "/untenrechts.gif\" alt=\"\" title=\"\" /></td>\n".
		"                                </tr>\n".
		"                            </table>\n".
		"                        </td>\n".
		"                        <td valign=\"top\" align=\"center\" style=\"padding: 5px;width:100%\">\n\n".
		"<!-- MAIN CONTENT STARTET HIER! -->\n\n";
		
} // stdhead

function ratiostatbox(){
	global $CURUSER;

	if($CURUSER){
		$ratio = ($CURUSER["downloaded"] > 0 ? number_format($CURUSER["uploaded"] / $CURUSER["downloaded"], 3, ",", ".") : "Inf.");
		$seeds = $GLOBALS['database']->row_count('peers','`userid`=' . $CURUSER["id"] . ' AND `seeder`= yes') ?: 0;
		$leeches = $GLOBALS['database']->row_count('peers','`userid`=' . $CURUSER["id"] . ' AND `seeder`= no') ?: 0;
		$tlimits = get_torrent_limits($CURUSER);

		if($ratio < 0.5)
			$ratiowarn = " style=\"background-color:red;color:white;\"";
		elseif ($ratio < 0.75)
			$ratiowarn = " style=\"background-color:#FFFF00;color:black;\"";

		if($tlimits["seeds"] >= 0){
			if($tlimits["seeds"] - $seeds < 1)
				$seedwarn = " style=\"background-color:red;color:white;\"";
			else
				$seedwarn = "";
			$tlimits["seeds"] = " / " . $tlimits["seeds"];
		}else
			$tlimits["seeds"] = "";

		if($tlimits["leeches"] >= 0){
			if($tlimits["leeches"] - $leeches < 1)
				$leechwarn = " style=\"background-color:red;color:white;\"";
			else
				$leechwarn = "";
			$tlimits["leeches"] = " / " . $tlimits["leeches"];
		}else
			$tlimits["leeches"] = "";

		if($tlimits["total"] >= 0){
			if($tlimits["total"] - $leeches + $seeds < 1)
				$totalwarn = " style=\"background-color:red;color:white;\"";
			else
				$totalwarn = "";
			$tlimits["total"] = " / " . $tlimits["total"];
		}else
			$tlimits["total"] = "";

		echo "                                <tr>\n".
			"                                    <td class=\"tabletitle\" style=\"padding: 4px;\"><b>" . htmlspecialchars($CURUSER["username"]) . " :.</b></td>\n".
			"                                </tr>\n".
			"                                <tr>\n".
			"                                    <td class=\"tablea\" style=\"padding-left: 4px;\">\n".
			"                                        <table cellspacing=\"0\" cellpadding=\"2\" border=\"0\" style=\"width:140px;\">\n".
			"                                            <tr>\n".
			"                                               <td><b>Download:</b></td>\n".
			"                                               <td style=\"text-align:right\">" . mksize($CURUSER["downloaded"]) . "</td>\n".
			"                                            </tr>\n".
			"                                            <tr>\n".
			"                                                <td><b>Upload:</b></td>\n".
			"                                                <td style=\"text-align:right\">" . mksize($CURUSER["uploaded"]) . "</td>\n".
			"                                            </tr>\n".
			"                                            <tr>\n".
			"                                                <td><b>Ratio:</b></td>\n".
			"                                                <td style=\"text-align:right\" style=\"color:" . get_ratio_color($ratio) . "\">" . $ratio . "</td>\n".
			"                                            </tr>\n".
			"                                            <tr>\n".
			"                                                <td colspan=\"2\">&nbsp;</td>\n".
			"                                            </tr>\n".
			"                                            <tr" . $seedwarn . ">\n".
			"                                                <td><b>Seeds:</b></td>\n".
			"                                                <td style=\"text-align:right\">" . $seeds . $tlimits["seeds"] . "</td>\n".
			"                                            </tr>\n".
			"                                            <tr" . $leechwarn . ">\n".
			"                                                <td><b>Leeches:</b></td>\n".
			"                                                <td style=\"text-align:right\">" . $leeches . $tlimits["leeches"] . "</td>\n".
			"                                            </tr>\n".
			"                                            <tr" . $totalwarn . ">\n".
			"                                                <td><b>Gesamt:</b></td>\n".
			"                                                <td style=\"text-align:right\">" . ($seeds + $leeches) . $tlimits["total"] . "</td>\n".
			"                                            </tr>\n".
			"                                        </table>\n".
			"                                    </td>\n".
			"                                </tr>\n";
	} 
}

function stdfoot(){
	echo "                        </td>\n".
		"                    </tr>\n".
		"                </table>\n".
		"            </td>\n".
		"        </tr>\n".
		"    </table>\n".
		"</body>\n".
		"</html>\n";
}

function stderr($heading, $text){
    stdhead();
    stdmsg($heading, $text);
    stdfoot();
    die;
}

function stdmsg($heading, $text){
	echo "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" style=\"width:100%\" class=\"tableinborder\">\n".
		"    <tr>\n".
		"        <td class=\"tabletitle\" width=\"100%\"><b>" . $heading . "</b></td>\n". 
		"    </tr>\n".
		"    <tr>\n".
		"        <td width=\"100%\" class=\"tablea\">" . $text . "</td>\n".
		"    </tr>\n".
		"</table>\n".
		"<br>\n";
}

function begin_main_frame(){
	echo "<table class=\"main\" width=\"750\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n".
		"    <tr>\n".
		"        <td class=\"embedded\">\n";
}

function end_main_frame(){
	echo "        </td>\n".
		"    </tr>\n".
		"</table>\n";
}

function begin_frame($caption = "", $center = false, $width = "100%"){
	if($center)
		$tdextra = " style=\"text-align: center\"";
	else
		$tdextra = " ";

	echo "<table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" style=\"width:" . $width . "\" class=\"tableinborder\">\n".
		"    <tr>\n".
		"        <td class=\"tabletitle\" width=\"100%\" style=\"text-align: center\"><b>" . $caption . "</b></td>\n".
		"    </tr>\n".
		"    <tr>\n".
		"        <td width=\"100%\" class=\"tablea\"" . $tdextra . ">\n";
}

function attach_frame(){
	echo "        </td>\n".
		"    </tr>\n".
		"    <tr>\n".
		"        <td class=\"tablea\" style=\"border-top: 0px\">\n";
}

function end_frame(){
	echo "        </td>\n".
		"    </tr>\n".
		"</table>\n".
		"<br>\n";
}

function begin_table($fullwidth = false, $padding = 4){
	if ($fullwidth)
		$width = " width=\"100%\"";
	else
		$width = "";
	echo "<table class=\"tableinborder\"" . $width . " border=\"0\" cellspacing=\"1\" cellpadding=\"" . $padding . "\">\n";
}

function end_table(){
	echo "</table>\n".
		"<br>\n";
}

function tr($x, $y, $noesc = 0){
    if ($noesc != 0)
        $a = $y;
    else {
        $a = htmlspecialchars($y);
        $a = str_replace("\n", "<br />\n", $a);
    } 
    echo "    <tr>\n".
		"        <td class=\"tableb\" valign=\"top\" align=\"left\">" . $x . "</td>\n".
		"        <td class=\"tablea\" valign=\"top\" align=\"left\">" . $a . "</td>\n".
		"    </tr>\n";
}

function tr_msg($msg){
    echo "<tr>\n".
		"    <td class=\"tablea\" style=\"text-align:left;\">" . $msg . "</td>\n";
}

function tr_status($status){
    echo "<td class=\"tableb\" style=\"text-align:center;\"><img src=\"".$GLOBALS["PIC_BASE_URL"];
    if ($status == "ok")
        echo "button_online2.gif";
    else
        echo "button_offline2.gif";
    echo "\"></td>\n".
		"</tr>\n";
    //flush();
} 


function genbark($x, $y){
    stdhead($y);
	echo "                            <table cellpadding=\"4\" cellspacing=\"1\" border=\"0\" style=\"width:100%\" class=\"tableinborder\">\n".
		"                                <tr>\n".
		"                                    <td class=\"tabletitle\" colspan=\"10\" width=\"100%\">\n".
		"                                        <span class=\"normalfont\"><center><b> " . htmlspecialchars($y) . " </b></center></span>\n".
		"                                    </td>\n". 
		"                                </tr>\n".
		"                                <tr>\n".
		"                                    <td width=\"100%\" class=\"tablea\">" . htmlspecialchars($x) . "</td>\n".
		"                                </tr>\n".
		"                            </table>\n".
		"                            <br>\n";
    stdfoot();
    exit();
} 

$smilies = array(":-)" => "smile1.gif",
    ":smile:" => "smile2.gif",
    ":-D" => "grin.gif",
    ":lol:" => "laugh.gif",
    ":w00t:" => "w00t.gif",
    ":-P" => "tongue.gif",
    ";-)" => "wink.gif",
    ":-|" => "noexpression.gif",
    ":-/" => "confused.gif",
    ":-(" => "sad.gif",
    ":'-(" => "cry.gif",
    ":weep:" => "weep.gif",
    ":-O" => "ohmy.gif",
    ":o)" => "clown.gif",
    "8-)" => "cool1.gif",
    "|-)" => "sleeping.gif",
    ":innocent:" => "innocent.gif",
    ":whistle:" => "whistle.gif",
    ":unsure:" => "unsure.gif",
    ":closedeyes:" => "closedeyes.gif",
    ":cool:" => "cool2.gif",
    ":fun:" => "fun.gif",
    ":thumbsup:" => "thumbsup.gif",
    ":thumbsdown:" => "thumbsdown.gif",
    ":blush:" => "blush.gif",
    ":unsure:" => "unsure.gif",
    ":yes:" => "yes.gif",
    ":no:" => "no.gif",
    ":love:" => "love.gif",
    ":?:" => "question.gif",
    ":!:" => "excl.gif",
    ":idea:" => "idea.gif",
    ":arrow:" => "arrow.gif",
    ":arrow2:" => "arrow2.gif",
    ":hmm:" => "hmm.gif",
    ":hmmm:" => "hmmm.gif",
    ":huh:" => "huh.gif",
    ":geek:" => "geek.gif",
    ":look:" => "look.gif",
    ":rolleyes:" => "rolleyes.gif",
    ":kiss:" => "kiss.gif",
    ":shifty:" => "shifty.gif",
    ":blink:" => "blink.gif",
    ":smartass:" => "smartass.gif",
    ":sick:" => "sick.gif",
    ":crazy:" => "crazy.gif",
    ":wacko:" => "wacko.gif",
    ":alien:" => "alien.gif",
    ":wizard:" => "wizard.gif",
    ":wave:" => "wave.gif",
    ":wavecry:" => "wavecry.gif",
    ":baby:" => "baby.gif",
    ":angry:" => "angry.gif",
    ":ras:" => "ras.gif",
    ":sly:" => "sly.gif",
    ":devil:" => "devil.gif",
    ":evil:" => "evil.gif",
    ":evilmad:" => "evilmad.gif",
    ":sneaky:" => "sneaky.gif",
    ":axe:" => "axe.gif",
    ":slap:" => "slap.gif",
    ":wall:" => "wall.gif",
    ":rant:" => "rant.gif",
    ":jump:" => "jump.gif",
    ":yucky:" => "yucky.gif",
    ":nugget:" => "nugget.gif",
    ":smart:" => "smart.gif",
    ":shutup:" => "shutup.gif",
    ":shutup2:" => "shutup2.gif",
    ":crockett:" => "crockett.gif",
    ":zorro:" => "zorro.gif",
    ":snap:" => "snap.gif",
    ":beer:" => "beer.gif",
    ":beer2:" => "beer2.gif",
    ":drunk:" => "drunk.gif",
    ":strongbench:" => "strongbench.gif",
    ":weakbench:" => "weakbench.gif",
    ":dumbells:" => "dumbells.gif",
    ":music:" => "music.gif",
    ":stupid:" => "stupid.gif",
    ":dots:" => "dots.gif",
    ":offtopic:" => "offtopic.gif",
    ":spam:" => "spam.gif",
    ":oops:" => "oops.gif",
    ":lttd:" => "lttd.gif",
    ":please:" => "please.gif",
    ":sorry:" => "sorry.gif",
    ":hi:" => "hi.gif",
    ":yay:" => "yay.gif",
    ":cake:" => "cake.gif",
    ":hbd:" => "hbd.gif",
    ":band:" => "band.gif",
    ":punk:" => "punk.gif",
    ":rofl:" => "rofl.gif",
    ":bounce:" => "bounce.gif",
    ":mbounce:" => "mbounce.gif",
    ":thankyou:" => "thankyou.gif",
    ":gathering:" => "gathering.gif",
    ":hang:" => "hang.gif",
    ":chop:" => "chop.gif",
    ":rip:" => "rip.gif",
    ":whip:" => "whip.gif",
    ":judge:" => "judge.gif",
    ":chair:" => "chair.gif",
    ":tease:" => "tease.gif",
    ":box:" => "box.gif",
    ":boxing:" => "boxing.gif",
    ":guns:" => "guns.gif",
    ":shoot:" => "shoot.gif",
    ":shoot2:" => "shoot2.gif",
    ":flowers:" => "flowers.gif",
    ":wub:" => "wub.gif",
    ":lovers:" => "lovers.gif",
    ":kissing:" => "kissing.gif",
    ":kissing2:" => "kissing2.gif",
    ":console:" => "console.gif",
    ":group:" => "group.gif",
    ":hump:" => "hump.gif",
    ":hooray:" => "hooray.gif",
    ":happy2:" => "happy2.gif",
    ":clap:" => "clap.gif",
    ":clap2:" => "clap2.gif",
    ":weirdo:" => "weirdo.gif",
    ":yawn:" => "yawn.gif",
    ":bow:" => "bow.gif",
    ":dawgie:" => "dawgie.gif",
    ":cylon:" => "cylon.gif",
    ":book:" => "book.gif",
    ":fish:" => "fish.gif",
    ":mama:" => "mama.gif",
    ":pepsi:" => "pepsi.gif",
    ":medieval:" => "medieval.gif",
    ":rambo:" => "rambo.gif",
    ":ninja:" => "ninja.gif",
    ":hannibal:" => "hannibal.gif",
    ":party:" => "party.gif",
    ":snorkle:" => "snorkle.gif",
    ":evo:" => "evo.gif",
    ":king:" => "king.gif",
    ":chef:" => "chef.gif",
    ":mario:" => "mario.gif",
    ":pope:" => "pope.gif",
    ":fez:" => "fez.gif",
    ":cap:" => "cap.gif",
    ":cowboy:" => "cowboy.gif",
    ":pirate:" => "pirate.gif",
    ":pirate2:" => "pirate2.gif",
    ":rock:" => "rock.gif",
    ":cigar:" => "cigar.gif",
    ":icecream:" => "icecream.gif",
    ":oldtimer:" => "oldtimer.gif",
    ":trampoline:" => "trampoline.gif",
    ":banana:" => "bananadance.gif",
    ":smurf:" => "smurf.gif",
    ":yikes:" => "yikes.gif",
    ":osama:" => "osama.gif",
    ":saddam:" => "saddam.gif",
    ":santa:" => "santa.gif",
    ":indian:" => "indian.gif",
    ":pimp:" => "pimp.gif",
    ":nuke:" => "nuke.gif",
    ":jacko:" => "jacko.gif",
    ":ike:" => "ike.gif",
    ":greedy:" => "greedy.gif",
    ":super:" => "super.gif",
    ":wolverine:" => "wolverine.gif",
    ":spidey:" => "spidey.gif",
    ":spider:" => "spider.gif",
    ":bandana:" => "bandana.gif",
    ":construction:" => "construction.gif",
    ":sheep:" => "sheep.gif",
    ":police:" => "police.gif",
    ":detective:" => "detective.gif",
    ":bike:" => "bike.gif",
    ":fishing:" => "fishing.gif",
    ":clover:" => "clover.gif",
    ":horse:" => "horse.gif",
    ":shit:" => "shit.gif",
    ":soldiers:" => "soldiers.gif",
    );

$privatesmilies = array(":)" => "smile1.gif", 
    // ";)" => "wink.gif",
    ":wink:" => "wink.gif",
    ":D" => "grin.gif",
    ":P" => "tongue.gif",
    ":(" => "sad.gif",
    ":'(" => "cry.gif",
    ":|" => "noexpression.gif", 
    // "8-)" => "cool1.gif",
    ":Boozer:" => "alcoholic.gif",
    ":deadhorse:" => "deadhorse.gif",
    ":spank:" => "spank.gif",
    ":yoji:" => "yoji.gif",
    ":locked:" => "locked.gif",
    ":grrr:" => "angry.gif", // legacy
    "O:-" => "innocent.gif", // legacy
    ":sleeping:" => "sleeping.gif", // legacy
    "-_-" => "unsure.gif", // legacy
    ":clown:" => "clown.gif",
    ":mml:" => "mml.gif",
    ":rtf:" => "rtf.gif",
    ":morepics:" => "morepics.gif",
    ":rb:" => "rb.gif",
    ":rblocked:" => "rblocked.gif",
    ":maxlocked:" => "maxlocked.gif",
    ":hslocked:" => "hslocked.gif",
    );

function insert_smilies_frame(){
	global $smilies, $BASEURL;
	begin_frame("Smilies", true);
	echo "<center>\n";
	begin_table(false, 5);
	echo "    <tr>\n";
	for($I = 0; $I < 3; $I++){
		if($I > 0)
			echo "        <td class=\"tablecat\">&nbsp;</td>\n";
		echo "        <td class=\"tablecat\">Eingeben...</td>\n".
			"        <td class=\"tablecat\">...f&uuml;r Smilie</td>\n";
	}
	$I = 0;
	echo "    </tr>\n".
		"    <tr>\n";
	while(list($code, $url) = each($smilies)){
		if($I && $I % 3 == 0)
			echo "    </tr>\n".
				"    <tr>\n";
		if($I % 3)
			echo "        <td class=\"inposttable\">&nbsp;</td>\n";
		echo "        <td class=\"tablea\">" . $code . "</td>\n".
			"        <td class=\"tableb\"><img src=\"" . $BASEURL . "/pic/smilies/" . $url . "\"></td>\n";
		$I++;
	}
	if($I % 3)
		echo "        <td class=\"inposttable\" colspan=" . ((3 - $I % 3) * 3) . ">&nbsp;</td>\n";
	echo "    </tr>\n";
	end_table();
	echo "</center>\n";
	end_frame();
}
?>