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
	
	header("Content-Type: text/html; charset=utf-8");
	header("Pragma: No-cache");
	header("Expires: 300");
	header("Cache-Control: private");

	if($title == "")
		$title = $GLOBALS["SITENAME"];
	else
		$title = $GLOBALS["SITENAME"] . ".de :: pdo-nv ::" . htmlspecialchars($title);

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
		"    <link rel=\"stylesheet\" href=\"" . $GLOBALS["PIC_BASE_URL"] . $GLOBALS["ss_uri"] . "/" . $GLOBALS["ss_uri"] . ".css\" type=\"text/css\">\n".
		"    <script type=\"text/javascript\" src=\"/js/jquery-3.2.1.min.js\"></script>\n";
	if(date('m') == 12 || date('m') == 1)
		echo "    <script type=\"text/javascript\" src=\"/js/jsnow.js\"></script>\n";
	if($_SERVER["SCRIPT_NAME"] == "/details.php" || $_SERVER["SCRIPT_NAME"] == "/bitbucket.php" || $_SERVER["SCRIPT_NAME"] == "/bitbucket-gallery.php")
		echo "    <link href=\"css/lightbox.min.css\" rel=\"stylesheet\">\n";


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
		"                                    <td class=\"tabletitle\" style=\"padding: 4px;\"><b>" . $GLOBALS["SITENAME"] . " :.</b></td>\n".
		"                                </tr>\n".
		"                                <tr>\n".
		"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"index.php\" title=\"Neuigkeiten vom Team sowie allgemeine Tracker-Stats und Umfragen\">Tracker-News</a></td>\n".
		"                                </tr>\n".
		"                                <tr>\n".
		"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"test.php\" title=\"titel\">Testseite</a></td>\n".
		"                                </tr>\n".
		"                                <tr>\n".
		"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"https://github.com/kaitokid222/pdonvtracker/issues\" title=\"github issues\" target=\"_blank\">Bugs melden!</a></td>\n".
		"                                </tr>\n";
	if($GLOBALS["PORTAL_LINK"] != ""){
		//echo "                                <tr>\n".
		//	"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"" . $GLOBALS["PORTAL_LINK"] . "\" title=\"Unser Portal und Forum f&uuml;r alles M&ouml;gliche\">Portal</a></td>\n".
		//	"                                </tr>\n";
	}
	echo "                                <tr>\n".
		"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"faq.php\" title=\"Oft gestellte Fragen zu diversen trackerspezifischen Themen\">FAQ</a></td>\n".
		"                                </tr>\n".
		"                                <tr>\n".
		"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"rules.php\" title=\"Alle Verhaltensregeln f&uuml;r den Tracker - LESEPFLICHT!\">Regeln</a></td>\n".
		"                                </tr>\n";
	if($CURUSER){
		if($GLOBALS["IRCAVAILABLE"]){
			//echo "                                <tr>\n".
			//	"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"chat.php\" title=\"IRC-Serverdaten und ein einfach zu benutzendes Java-Applet\">IRC Chat</a></td>\n".
			//	"                                </tr>\n";
		}
		echo "                                <tr>\n".
			"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"users.php\" title=\"Liste aller Mitglieder, inkl. Suchfunktion\">Mitglieder</a></td>\n".
			"                                </tr>\n".
			//"                                <tr>\n".
			//"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"staff.php\" title=\"Schnelle &Uuml;bersicht &uuml;ber das  Trackerteam\">Team</a></td>\n".
			//"                                </tr>\n".
			"                                <tr>\n".
			"                                    <td class=\"tabletitle\" style=\"padding: 4px;\"><b>Torrents :.</b></td>\n".
			"                                </tr>\n".
			"                                <tr>\n".
			"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"browse.php\" title=\"Verf&uuml;gbare Torrents anzeigen oder suchen\">Durchsuchen</a></td>\n".
			"                                </tr>\n".
			"                                <tr>\n".
			"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"upload.php\" title=\"Lade einen eigenen Torrent auf den Tracker hoch\">Hochladen</a></td>\n".
			"                                </tr>\n".
			//"                                <tr>\n".
			//"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"mytorrents.php\" title=\"Hier werden alle von Dir hochgeladenen Torrents angezeigt\">Meine Torrents</a></td>\n".
			//"                                </tr>\n".
			"";
		if(get_user_class() >= UC_GUTEAM){
			//echo "                                <tr>\n".
			//	"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"guestuploads.php\" title=\"Zeigt alle noch nicht freigeschalteten Gastuploads\">Neue Gastuploads</a></td>\n".
			//	"                                </tr>\n";
		}
		echo "                                <tr>\n".
			"                                    <td class=\"tabletitle\" style=\"padding: 4px;\"><b>Mein Account :.</b></td>\n".
			"                                </tr>\n".
			"                                <tr>\n".
			"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"userdetails.php?id=" . $CURUSER["id"] . "\" title=\"Deine Statistik-Seite, die auch andere Benutzer sehen\">Mein Profil</a></td>\n".
			"                                </tr>\n".
			"                                <tr>\n".
			"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"my.php\" title=\"Hier kannst Du Deine Einstellungen &auml;ndern\">Profil bearbeiten</a></td>\n".
			"                                </tr>\n"; // /!\
			//"                                <tr>\n".
			//"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"friends.php\" title=\"Eine Liste Deiner &quot;Freunde&quot; auf dem Tracker\">Buddyliste</a></td>\n".
			//"                                </tr>\n".
			/*"                                <tr>\n".
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
		echo "</a></td>\n";*/
		echo "".
		//	"                                </tr>\n".
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
				//"                                <tr>\n".
				//"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"log.php\" title=\"Tracker-Logbuch anzeigen\">Site Log</a></td>\n".
				//"                                </tr>\n".
				"                                <tr>\n".
				"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"polls.php\" title=\"Umfrageverwaltung\">Umfragen</a></td>\n".
				"                                </tr>\n".
				/*"                                <tr>\n".
				"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"staff.php?act=last\" title=\"Liste aller Benutzer, nach Anmeldedatum sortiert\">Neueste Benutzer</a></td>\n".
				"                                </tr>\n".
				"                                <tr>\n".
				"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"staff.php?act=ban\" title=\"Hier kannst Du IP-Bereiche vom Tracker aussperren\">IPs sperren</a></td>\n".
				"                                </tr>\n".
				"                                <tr>\n".
				"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"staff.php?act=upstats\" title=\"Schnelle &Uuml;bersicht &uuml;ber die Uploadaktivit&auml;ten\">Uploader-Stats</a></td>\n".
				"                                </tr>\n".*/
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
					"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"saconf.php\" title=\"Hier kannst Du die Announce starten und stoppen\">Socket-Announce</a></td>\n".
					"                                </tr>\n".
					"                                <tr>\n".
					"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"vouchers.php\" title=\"Hier kannst Du Gutscheine verwalten\">Gutschein-CP</a></td>\n".
					"                                </tr>\n"; // /!\
					//"                                <tr>\n".
					//"                                    <td class=\"tablea\"><a style=\"display:block;padding:4px;\" href=\"staff.php?act=cleanaccs\" title=\"Benutzer nach Ratio- und Aktivit&auml;tskriterien suchen und deaktivieren\">Accountbereinigung</a></td>\n".
					//"                                </tr>\n";
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
	$diff = unique_ts()-$GLOBALS["SCRIPT_START_TIME"];
	if($_SERVER["SCRIPT_NAME"] == "/details.php" || $_SERVER["SCRIPT_NAME"] == "/bitbucket.php" || $_SERVER["SCRIPT_NAME"] == "/bitbucket-gallery.php")
		echo "                        <script type=\"text/javascript\" src=\"/js/lightbox.min.js\"></script>\n";
	echo "                        </td>\n".
		"                    </tr>\n".
		"                </table>\n".
		"            </td>\n".
		"        </tr>\n".
		"    </table>\n".
		"    <p align=\"center\">PHP-Version: " . phpversion() . " || <a href=\"https://github.com/kaitokid222/pdonvtracker/issues\" title=\"github issues\" target=\"_blank\">Bugs melden!</a> || Diese Seite wurde in " . round(($diff/1000), 4) . " Sekunden erstellt.</p>\n".
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
	//while(list($code, $url) = each($smilies)){
	foreach($smilies as $code => $url){
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

function torrenttable_row_oldschool($torrent_info){
	$torrent_info["is_new"] = "no";
	$torrent_info["has_wait"] = "no";
	global $CURUSER;

	if(strlen($torrent_info["name"]) > 45)
		$displayname = substr($torrent_info["name"], 0, 45) . "...";
	else
		$displayname = $torrent_info["name"];

	$returnto = "&amp;returnto=" . urlencode($_SERVER["REQUEST_URI"]);
	$baselink = "details.php?id=" . $torrent_info["id"];
	if($torrent_info["variant"] == "index"){
		$baselink .= "&amp;hit=1";
		$filelistlink = $baselink . "&amp;filelist=1";
		$commlink = $baselink . "&amp;tocomm=1";
		$seederlink = $baselink . "&amp;toseeders=1";
		$leecherlink = $baselink . "&amp;todlers=1";
		$snatcherlink = $baselink . "&amp;tosnatchers=1";
	}else{
		$baselink .= $returnto;
		$filelistlink = $baselink . "&amp;filelist=1#filelist";
		$commlink = $baselink . "&amp;page=0#startcomments";
		$seederlink = $baselink . "&amp;dllist=1#seeders";
		$leecherlink = $baselink . "&amp;dllist=1#leechers";
		$snatcherlink = $baselink . "&amp;snatcher=1#snatcher";
	} 

	if($torrent_info["leechers"])
		$ratio = $torrent_info["seeders"] / $torrent_info["leechers"];
	elseif ($torrent_info["seeders"])
		$ratio = 1;
	else
		$ratio = 0;
	$seedercolor = get_slr_color($ratio);

	
	if (!isset($torrent_info["cat_pic"])){
		if ($torrent_info["cat_name"] != "")
			$tci = "<a href=\"browse.php?cat=" . $torrent_info["category"] . "\">" . $torrent_info["cat_name"] . "</a>";
		else
			$tci = "-";
	}else{
		$tci = "<a href=\"browse.php?cat=" . $torrent_info["category"] . "\"><img src=\"" . $GLOBALS["PIC_BASE_URL"] . $torrent_info["cat_pic"] . "\" alt=\"" . $torrent_info["cat_name"] . "\" title=\"" . $torrent_info["cat_name"] . "\" border=\"0\"></a>";
	}

	if(isset($torrent_info["uploaderclass"]) && $torrent_info["uploaderclass"] < UC_UPLOADER)
		$gu = "<font color=\"red\">[GU]</font> ";
	else
		$gu = "";
		
	if($torrent_info["variant"] != "guestuploads" && $torrent_info["is_new"] != "no")
		$isnew = " <font style=\"color:red\">(NEU)</font>";
	else
		$isnew = "";

	if($torrent_info["gu_agent"] > 0)
		$gua = "Ja";
	else
		$gua = "<font color=\"red\">Nein</font>";
		
	if($torrent_info["variant"] == "mytorrents"){
		$trex = "        <td class=\"tablea\" style=\"text-align:center;vertical-align:middle;\" nowrap=\"nowrap\"><a href=\"edit.php?id=" . $torrent_info["id"] . $returnto . "\">Bearbeiten</a></td>\n".
				"        <td class=\"tablea\" style=\"text-align:center;vertical-align:middle;\" nowrap=\"nowrap\">" . ($torrent_info["visible"] == "yes"?"Ja":"Nein") . "</td>\n";
	}elseif($torrent_info["variant"] == "guestuploads")
		$trex = "        <td class=\"tablea\" style=\"text-align:center;vertical-align:middle;\" nowrap=\"nowrap\">" . $gua . "</td>\n";
	elseif($torrent_info["has_wait"] != "no")
		$trex = "        <td class=\"tablea\" style=\"text-align:center;vertical-align:middle;\" nowrap=\"nowrap\"><font color=\"" . $torrent_info["wait_color"] . "\">" . $torrent_info["wait_left"] . "<br>Std.</font></td>\n";
	else
		$trex = "        <td class=\"tablea\" style=\"text-align:center;vertical-align:middle;\" nowrap=\"nowrap\"></td>\n";

	if($torrent_info["variant"] == "index")
		$inde = "<td class=\"tablea\" style=\"text-align:left;vertical-align:middle;\" nowrap=\"nowrap\">" . $torrent_info["uploaderlink"] . "</td>\n";
	else
		$inde = "";

	echo "    <tr>\n".
		"        <td class=\"tableb\" valign=\"top\" style=\"width:1px;padding:0px;\">" . $tci . "</td>\n".
		"        <td class=\"tablea\" style=\"text-align:left;vertical-align:middle;\" nowrap=\"nowrap\">" . $gu . "<a href=\"" . $baselink . "\" title=\"" . htmlspecialchars($torrent_info["name"]) . "\"><b>" . htmlspecialchars($displayname) . "</b></a>" . $isnew . "</td>\n".
		$trex.
		"        <td class=\"tablea\" style=\"text-align:right;vertical-align:middle;\" nowrap=\"nowrap\"><a href=\"" . $filelistlink . "\">" . $torrent_info["numfiles"] . "</a></td>\n".
		"        <td class=\"tablea\" style=\"text-align:right;vertical-align:middle;\" nowrap=\"nowrap\"><a href=\"" . $commlink . "\">" . $torrent_info["comments"] . "</a></td>\n".
		"        <td class=\"tablea\" style=\"text-align:center;vertical-align:middle;\" nowrap=\"nowrap\">" . str_replace("&nbsp;", "<br>", $torrent_info["added"]) . "</td>\n".
		"        <td class=\"tablea\" style=\"text-align:center;vertical-align:middle;\" nowrap=\"nowrap\">" . str_replace(" ", "<br>", $torrent_info["ttl"]) . "</td>\n".
		"        <td class=\"tablea\" style=\"text-align:center;vertical-align:middle;\" nowrap=\"nowrap\">" . str_replace(" ", "<br>", mksize($torrent_info["size"])) . "</td>\n".
		"        <td class=\"tablea\" style=\"text-align:center;vertical-align:middle;\" nowrap=\"nowrap\"><div style=\"border:1px solid black;padding:0px;width:60px;height:10px;\"><div style=\"border:none;width:" . (60 * $torrent_info["dist"] / 100) . "px;height:10px;background-image:url(" . $GLOBALS["PIC_BASE_URL"] . "ryg-verlauf-small.png);background-repeat:no-repeat;\"></div></div></td>\n".
		"        <td class=\"tablea\" style=\"text-align:center;vertical-align:middle;\" nowrap=\"nowrap\"><a href=\"" . $snatcherlink . "\">" . $torrent_info["times_completed"] . "</a></td>\n".
		"        <td class=\"tablea\" style=\"text-align:center;vertical-align:middle;\" nowrap=\"nowrap\"><a href=\"" . $seederlink . "\"><font color=\"" . $seedercolor . "\">" . intval($torrent_info["seeders"]) . "</font></a></td>\n".
		"        <td class=\"tablea\" style=\"text-align:center;vertical-align:middle;\" nowrap=\"nowrap\"><a href=\"" . $leecherlink . "\"><font color=\"" . linkcolor($torrent_info["seeders"]) . "\">" . intval($torrent_info["leechers"]) . "</font></a></td>\n".
		"        <td class=\"tablea\" style=\"text-align:left;vertical-align:middle;\" nowrap=\"nowrap\">D:&nbsp;" . $torrent_info["dlspeed"] . "&nbsp;KB/s<br>U:&nbsp;" . $torrent_info["ulspeed"] . "&nbsp;KB/s</td>\n".
		$inde.
		"    </tr>\n";
}

function torrenttable_row($torrent_info){
	global $CURUSER;

	$returnto = "&amp;returnto=" . urlencode($_SERVER["REQUEST_URI"]);
	$baselink = "details.php?id=" . $torrent_info["id"];
	if($torrent_info["variant"] == "index"){
		$baselink .= "&amp;hit=1";
		$filelistlink = $baselink . "&amp;filelist=1";
		$commlink = $baselink . "&amp;tocomm=1";
		$seederlink = $baselink . "&amp;toseeders=1";
		$leecherlink = $baselink . "&amp;todlers=1";
		$snatcherlink = $baselink . "&amp;tosnatchers=1";
	}else{
		$baselink .= $returnto;
		$filelistlink = $baselink . "&amp;filelist=1#filelist";
		$commlink = $baselink . "&amp;page=0#startcomments";
		$seederlink = $baselink . "&amp;dllist=1#seeders";
		$leecherlink = $baselink . "&amp;dllist=1#leechers";
		$snatcherlink = $baselink . "&amp;snatcher=1#snatcher";
	} 

	if ($torrent_info["leechers"])
		$ratio = $torrent_info["seeders"] / $torrent_info["leechers"];
	elseif ($torrent_info["seeders"])
		$ratio = 1;
	else
		$ratio = 0;

	$seedercolor = get_slr_color($ratio);
	
	$qry = $GLOBALS["DB"]->prepare("SELECT DISTINCT(user_id) as id, username, class, peers.id as peerid FROM completed,users LEFT JOIN peers ON peers.userid=users.id AND peers.torrent= :ptid AND peers.seeder='yes' WHERE completed.user_id=users.id AND completed.torrent_id= :ctid ORDER BY complete_time DESC LIMIT 10");
	$qry->bindParam(':ptid', $torrent_info["id"], PDO::PARAM_INT);
	$qry->bindParam(':ctid', $torrent_info["id"], PDO::PARAM_INT);
	$qry->execute();
	$data = $qry->FetchAll(PDO::FETCH_ASSOC);

	$last10users = "";
	foreach($data as $arr){
		if($last10users)
			$last10users .= ", ";
		$arr["username"] = "<font class=\"" . get_class_color($arr["class"]) . "\">" . $arr["username"] . "</font>";
		if($arr["peerid"] > 0){
			$arr["username"] = "<b>" . $arr["username"] . "</b>";
		}
		$last10users .= "<a href=\"userdetails.php?id=" . $arr["id"] . "\">" . $arr["username"] . "</a>";
	}

	if($last10users == "")
		$last10users = "Diesen Torrent hat noch niemand fertiggestellt.";
	else
		$last10users .= "<br/><br/>(Fettgedruckte User seeden noch)";

	if ($GLOBALS["DOWNLOAD_METHOD"] == DOWNLOAD_REWRITE)
		$download_url = "download/" . $torrent_info["id"] . "/" . rawurlencode($torrent_info["filename"]);
	else
		$download_url = "download.php?torrent=" . $torrent_info["id"];

	if (!isset($torrent_info["cat_pic"])){
		if ($torrent_info["cat_name"] != "")
			$tci = "<a href=\"browse.php?cat=" . $torrent_info["category"] . "\">" . $torrent_info["cat_name"] . "</a>";
		else
			$tci = "-";
	}else{
		$tci = "<a href=\"browse.php?cat=" . $torrent_info["category"] . "\"><img src=\"" . $GLOBALS["PIC_BASE_URL"] . $torrent_info["cat_pic"] . "\" alt=\"" . $torrent_info["cat_name"] . "\" title=\"" . $torrent_info["cat_name"] . "\" border=\"0\"></a>";
	}
	
	if($torrent_info["variant"] != "index")
		$edit_l = "[ <a href=\"edit.php?id=" . $torrent_info["id"] . $returnto ."\">Bearbeiten</a> ]";
	else
		$edit_l = "";
		
	if(isset($torrent_info["uploaderclass"]) && $torrent_info["uploaderclass"] < UC_UPLOADER)
		$gu = "<font color=\"red\">[GU]</font> ";
	else
		$gu = "";

	$ecl = "<a href=\"javascript:expandCollapse('" . $torrent_info["id"] . "');\"><img id=\"plusminus" . $torrent_info["id"] . "\" src=\"" . $GLOBALS["PIC_BASE_URL"] . $GLOBALS["ss_uri"] . "/plus.gif\" alt=\"Auf-/Zuklappen\" border=\"0\"></a>";

	$nlink = "<a href=\"" . $baselink . "\"><b>" . $torrent_info["name"] . "</b></a>";
	
	if($torrent_info["variant"] != "guestuploads" && $torrent_info["is_new"])
		$isnew = " <font style=\"color:red\">(NEU)</font>";
	else
		$isnew = " ";

	if ($torrent_info["variant"] == "guestuploads" && $torrent_info["gu_agent"] > 0)
		$guagent = " <font style=\"color:red\">(Bereits in Bearbeitung)</font>";
	else
		$guagent = "";
		
	if ($torrent_info["seeders"] == 0 && $torrent_info["variant"] == "index"){
		$deadtext = "<div style=\"padding:4px;\"><b><font color=\"red\">HINWEIS:</font></b> Es sind keine Seeder für diesen Torrent aktiv. ".
					"Dies bedeutet, dass Du diesen Torrent wahrscheinlich nicht fertigstellen kannst, solange nicht wieder ein Seeder aktiv wird. ".
					"Sollte der Torrent längere Zeit inaktiv gewesen und als \"Tot\" markiert worden sein, solltest Du im Forum um einen Reseed bitten, ".
					"Falls Du noch Interesse daran hast.</div>";
	}else
		$deadtext = "";

	if($torrent_info["variant"] == "mytorrents"){
		$visibiblity = "                <tr>\n".
						"                    <td nowrap valign=\"top\"><b>Sichtbar:</b></td>\n".
						"                    <td>" . ($torrent_info["visible"] == "yes"?"Ja":"Nein, dieser Torrent ist inaktiv und als \"Tot\" markiert") . "</td>\n".
						"                </tr>\n";
	}else
		$visibiblity = "";
		
	if(isset($torrent_info["has_wait"])){
		$wtt = "                <tr>\n".
				"                    <td nowrap valign=\"top\"><b>Wartezeit:</b></td>\n".
				"                    <td><font color=\"" . $torrent_info["wait_color"] . "\">". $torrent_info["wait_left"] . " Stunde(n)</font></td>\n".
				"                </tr>\n";
	}else
		$wtt = "";
		
	$distributionbar = "<div style=\"border:1px solid black;padding:0px;width:300px;height:15px;\"><div style=\"border:none;width:" . (300 * $torrent_info["dist"] / 100) . "px;height:15px;background-image:url(" . $GLOBALS["PIC_BASE_URL"] . "ryg-verlauf.png);background-repeat:no-repeat;\"></div></div>";

	if($torrent_info["variant"] == "guestuploads"){
		if ($torrent_info["gu_agent"] == 0)
			$xdlpic = "<img src=\"" . $GLOBALS["PIC_BASE_URL"] . "nodl.png\" width=\"22\" height=\"22\" alt=\"Nicht in Bearbeitung\" title=\"Nicht in Bearbeitung\">";
		else
			$xdlpic = "<img src=\"" . $GLOBALS["PIC_BASE_URL"] . "taken.png\" width=\"22\" height=\"22\" alt=\"Bereits in Bearbeitung\" title=\"Bereits in Bearbeitung\">";
	}else{
		if ($torrent_info["activated"] == "yes") {
			if(!isset($torrent_info["wait_left"]) || $torrent_info["wait_left"] == 0){
				$xdlpic = "<a href=\"" . $download_url . "\"><img src=\"" . $GLOBALS["PIC_BASE_URL"] . "download.png\" width=\"22\" height=\"22\" alt=\"Torrent herunterladen\" title=\"Torrent herunterladen\" border=\"0\"></a>";
			}else{
				$xdlpic = "<img src=\"" . $GLOBALS["PIC_BASE_URL"] . "nodl.png\" width=\"22\" height=\"22\" alt=\"Wartezeit nicht abgelaufen\" title=\"Wartezeit nicht abgelaufen\" border=\"0\" />";
			} 
		}else{
			$xdlpic = "<img src=\"" . $GLOBALS["PIC_BASE_URL"] . "nodl.png\" width=\"22\" height=\"22\" alt=\"Torrent nicht freigeschaltet\" title=\"Torrent nicht freigeschaltet\" border=\"0\" />";
		}
	}
	
	echo "    <tr>\n".
		"        <td class=\"tableb\" valign=\"top\" style=\"color:#FFFFFF;width:1px;\">" . $tci . "</td>\n".
		"        <td class=\"tablea\" valign=\"top\" align=\"left\">\n".
		"            <table cellpadding=\"2\" cellspacing=\"2\" border=\"0\" style=\"width:100%\">\n".
		"                <colgroup>\n".
		"                    <col width=\"20%\">\n".
		"                    <col width=\"20%\">\n".
		"                    <col width=\"20%\">\n".
		"                    <col width=\"20%\">\n".
		"                    <col width=\"20%\">\n".
		"                </colgroup>\n".
		"                <tr>\n".
		"                    <td colspan=\"4\" nowrap=\"nowrap\">". $ecl . $edit_l . $gu . $nlink . $isnew . $guagent . "</td>\n".
        "                    <td nowrap=\"nowrap\">".($torrent_info["variant"] == "mytorrents" ? "Hochgeladen am:" : "Von: " . $torrent_info["uploaderlink"]). "</td>\n".
		"                </tr>\n".
		"                <tr>\n".
		"                    <td style=\"font-size:90%\"><b>" . mksize($torrent_info["size"]) . "</b> in <b>" . $torrent_info["numfiles"] . "</b> <a href=\"" . $filelistlink . "\">Datei(en)</a></td>\n".
		"                    <td style=\"font-size:90%\"><b><font color=\"" . $seedercolor . "\">" . intval($torrent_info["seeders"]) . "</font></b> <a href=\"" . $seederlink . "\">Seeder</a> &amp; <b><font color=\"" . linkcolor($torrent_info["seeders"]) . "\">" . intval($torrent_info["leechers"]) . "</font></b> <a href=\"" . $leecherlink . "\">Leecher</a></td>\n".
		"                    <td style=\"font-size:90%\"><b>" . $torrent_info["times_completed"] . "</b>x <a href=\"" . $snatcherlink . "\">heruntergeladen</a></td>\n".
		"                    <td style=\"font-size:90%\"><b>" . $torrent_info["comments"] . "</b> <a href=\"" . $commlink . "\">Kommentare</a></td>\n".
		"                    <td style=\"font-size:90%\">" . $torrent_info["added"] . "</td>\n".
		"                </tr>\n".
		"            </table>\n".
		"            <div id=\"details" . $torrent_info["id"] . "\" style=\"display:none;\">\n".
		$deadtext.
		"            <table cellspacing=\"2\" cellpadding=\"2\" border=\"0\" class=\"inposttable\" style=\"width:100%;\">\n".
		"                <colgroup>\n".
		"                    <col width=\"20%\">\n".
		"                    <col width=\"80%\">\n".
		"                </colgroup>\n".
		$visibiblity.
		$wtt.
		"                <tr>\n".
		"                    <td nowrap=\"nowrap\" valign=\"top\"><b>Letzte 10 Downloader:</b></td>\n".
		"                    <td>" . $last10users . "</td>\n".
		"                </tr>\n".
		"                <tr>\n".
		"                    <td nowrap=\"nowrap\" valign=\"top\"><b>&Oslash; Downloadgeschw.:</b></td>\n".
		"                    <td>" . $torrent_info["dlspeed"] . " KB/s (" . ($torrent_info["dlspeed"] * ($torrent_info["leechers"] + $torrent_info["seeders"])) . " KB/s gesamt)</td>\n".
		"                </tr>\n".
		"                <tr>\n".
		"                    <td nowrap=\"nowrap\" valign=\"top\"><b>&Oslash; Uploadgeschw.:</b></td>\n".
		"                    <td>" . $torrent_info["ulspeed"] . " KB/s (" . ($torrent_info["ulspeed"] * ($torrent_info["leechers"] + $torrent_info["seeders"])) . " KB/s gesamt)</td>\n".
		"                </tr>\n".
		"                <tr>\n".
		"                    <td nowrap=\"nowrap\" valign=\"top\"><b>Letzte Aktivit&auml;t:</b></td>\n".
		"                    <td>" . $torrent_info["last_action"] . "</td>\n".
		"                </tr>\n".
		"                <tr>\n".
		"                    <td nowrap=\"nowrap\" valign=\"top\"><b>Verbleibende Zeit:</b></td>\n".
		"                    <td>" . $torrent_info["ttl"] . " (falls inaktiv, sonst l&auml;nger)</td>\n".
		"                </tr>\n".
		"                <tr>\n".
		"                    <td nowrap=\"nowrap\" valign=\"top\"><b>&Oslash; Verteilung:</b></td>\n".
		"                    <td>" . $distributionbar . "</td>\n".
		"                </tr>\n".
		"            </table>\n".
		"            </div>\n".
		"        </td>\n".
		"        <td class=\"tableb\" valign=\"top\" align=\"center\" style=\"width:22px;padding:4px;padding-top:10px;\">" . $xdlpic . "</td>\n".
		"    </tr>\n";
}

function torrenttable($data, $variant = "index", $addparam = ""){
	global $CURUSER; 
	$addparam_nosort = preg_replace(array("/orderby=(.*?)&amp;/i", "/sort=(.*?)&amp;/i"), array("", ""), $addparam); 
	$has_wait = get_wait_time($CURUSER["id"], 0, true);

	if($variant == "mytorrents"){
		$vrtt = "        <td class=\"tablecat\" align=\"center\">Bearbeiten</td>\n".
				"        <td class=\"tablecat\" align=\"center\">Sichtbar</td>\n";
	}elseif($variant == "guestuploads")
		$vrtt = "        <td class=\"tablecat\" align=\"center\">In&nbsp;Bearbeitung</td>\n";
	elseif($has_wait !== false)
		$vrtt = "        <td class=\"tablecat\" align=\"center\">Wartez.</td>\n";
	else
		$vrtt = "        <td class=\"tablecat\" align=\"center\"></td>\n";
		
	if($variant == "index")
		$vrtti = "        <td class=\"tablecat\" align=\"center\">Uploader</td>\n";
	else
		$vrtti = "";
		
	echo "<script language=\"JavaScript\" src=\"js/expandCollapseTR.js\" type=\"text/javascript\"></script>\n".
		"<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"width:100%\">\n".
		"    <tr>\n".
		"        <td align=\"left\"><img src=\"" . $GLOBALS["PIC_BASE_URL"] . $GLOBALS["ss_uri"] . "/obenlinks.gif\" alt=\"\" title=\"\" /></td>\n".
		"        <td style=\"width:100%\" class=\"obenmitte\"></td>\n".
		"        <td align=\"right\"><img src=\"" . $GLOBALS["PIC_BASE_URL"] . $GLOBALS["ss_uri"] . "/obenrechts.gif\" alt=\"\" title=\"\" /></td>\n".
		"    </tr>\n".
		"</table>\n";
	if($CURUSER["oldtorrentlist"] == "yes"){
		echo "<table border=\"0\" cellspacing=\"1\" cellpadding=\"4\" class=\"tableinborder\" style=\"width:100%\">\n".
			"    <tr>\n".
			"        <td class=\"tablecat\" align=\"center\">Typ</td>\n".
			"        <td class=\"tablecat\" align=\"left\">Name</td>\n".
			$vrtt.
			"        <td class=\"tablecat\" align=\"right\">Dateien</td>\n".
			"        <td class=\"tablecat\" align=\"right\">Komm.</td>\n".
			"        <td class=\"tablecat\" align=\"center\">Hinzugef.</td>\n".
			"        <td class=\"tablecat\" align=\"center\">TTL</td>\n".
			"        <td class=\"tablecat\" align=\"center\">Gr&ouml;&szlig;e</td>\n".
			"        <td class=\"tablecat\" align=\"center\">Verteilung</td>\n".
			"        <td class=\"tablecat\" align=\"center\">Fertig</td>\n".
			"        <td class=\"tablecat\" align=\"right\">Seeder</td>\n".
			"        <td class=\"tablecat\" align=\"right\">Leecher</td>\n".
			"        <td class=\"tablecat\" align=\"left\">&Oslash;&nbsp;Geschw.</td>\n".
			$vrtti.
			"    </tr>\n";
	}else{
		echo "<table border=\"0\" cellspacing=\"1\" cellpadding=\"0\" class=\"tableinborder\" style=\"width:100%\">\n".
			"    <colgroup>\n".
			"        <col width=\"32\">\n".
			"        <col width=\"100%\">\n".
			"        <col width=\"22\">\n".
			"    </colgroup>\n";
	}

	foreach($data as $row){
		$id = $row["id"];
		$torrent_info = array();
		$torrent_info["id"] = $row["id"];
		$torrent_info["name"] = htmlspecialchars($row["name"]);
		$torrent_info["activated"] = (isset($row["activated"])?$row["activated"]:"yes");
		$torrent_info["gu_agent"] = (isset($row["gu_agent"])?$row["gu_agent"]:0);
		$torrent_info["filename"] = $row["filename"];
		$torrent_info["variant"] = $variant;
		$torrent_info["category"] = $row["category"];
		$torrent_info["cat_name"] = $row["cat_name"];
		$torrent_info["type"] = $row["type"];
		$torrent_info["numfiles"] = ($row["type"] == "single" ? 1 : $row["numfiles"]);
		$torrent_info["size"] = $row["size"];
		$torrent_info["times_completed"] = intval($row["times_completed"]);
		$torrent_info["seeders"] = $row["seeders"];
		$torrent_info["leechers"] = $row["leechers"];
		$torrent_info["uploaderlink"] = (isset($row["username"]) ? ("<a href=\"userdetails.php?id=" . $row["owner"] . "\"><b>" . htmlspecialchars($row["username"]) . "</b></a>") : "<i>(Gelöscht)</i>");
		$torrent_info["added"] = str_replace(" ", "&nbsp;", date("d.m.Y H:i:s", sql_timestamp_to_unix_timestamp($row["added"])));
		$torrent_info["comments"] = $row["comments"];
		$torrent_info["visible"] = $row["visible"];
		$torrent_info["last_action"] = str_replace(" ", "&nbsp;", date("d.m.Y H:i:s", sql_timestamp_to_unix_timestamp($row["last_action"])));

		if(isset($row["cat_pic"]) && $row["cat_pic"] != "")
			$torrent_info["cat_pic"] = $row["cat_pic"];

		if(isset($row["uploaderclass"]))
			$torrent_info["uploaderclass"] = $row["uploaderclass"];

		if($has_wait !== false){
			$torrent_info["has_wait"] = $has_wait;
			$torrent_info["wait_left"] = get_wait_time($CURUSER["id"], $id);
			$torrent_info["wait_color"] = dechex(floor(127 * ($torrent_info["wait_left"]) / 48 + 128) * 65536);
		} 

		$sql = "SELECT ROUND(AVG((downloaded - downloadoffset) / (UNIX_TIMESTAMP() - UNIX_TIMESTAMP(`started`)))/1024, 2) AS dlspeed, ROUND(AVG((uploaded - uploadoffset) / (UNIX_TIMESTAMP() - UNIX_TIMESTAMP(`started`)))/1024, 2) AS ulspeed FROM peers WHERE torrent= :id";
		$qry = $GLOBALS['DB']->prepare($sql);
		$qry->bindParam(':id', $id, PDO::PARAM_INT);
		$qry->execute();
		$speed = $qry->Fetch(PDO::FETCH_ASSOC);

		if($speed["dlspeed"] == 0)
			$speed["dlspeed"] = "0";
		if($speed["ulspeed"] == 0)
			$speed["ulspeed"] = "0";
		$torrent_info["dlspeed"] = $speed["dlspeed"];
		$torrent_info["ulspeed"] = $speed["ulspeed"];

		$sql = "SELECT ROUND(AVG((" . $row["size"] . " - `to_go`) / " . $row["size"] . " * 100),2) AS `dist` FROM `peers` WHERE torrent= :id";
		$qry = $GLOBALS['DB']->prepare($sql);
		$qry->bindParam(':id', $id, PDO::PARAM_INT);
		$qry->execute();
		$dist = $qry->Fetch(PDO::FETCH_ASSOC);

		$torrent_info["dist"] = $dist["dist"];

		$ttl = (28 * 24) - floor((time() - sql_timestamp_to_unix_timestamp($row["added"])) / 3600);
		if($ttl == 1)
			$ttl .= " Stunde";
		else
			$ttl .= " Stunden";
		$torrent_info["ttl"] = $ttl;

		$newadd = "";

		$torrentunix = sql_timestamp_to_unix_timestamp($row["added"]);
		$accessunix = sql_timestamp_to_unix_timestamp($CURUSER["last_access"]);

		if($torrentunix >= $accessunix)
			$torrent_info["is_new"] = true;
		else
			$torrent_info["is_new"] = false;

		if($CURUSER["oldtorrentlist"] == "yes") {
			torrenttable_row_oldschool($torrent_info);
		}else{
			torrenttable_row($torrent_info);
		} 
	} 

	echo "</table>\n".
		"<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"width:100%\">\n".
		"    <tr>\n".
		"        <td align=\"left\"><img src=\"" . $GLOBALS["PIC_BASE_URL"] . $GLOBALS["ss_uri"] . "/untenlinks.gif\" alt=\"\" title=\"\" /></td>\n".
		"        <td style=\"width:100%\" class=\"untenmitte\" align=\"center\"></td>\n".
		"        <td align=\"right\"><img src=\"" . $GLOBALS["PIC_BASE_URL"] . $GLOBALS["ss_uri"] . "/untenrechts.gif\" alt=\"\" title=\"\" /></td>\n".
		"    </tr>\n".
		"</table>\n";
}

function dltable($name, $arr, $torrent){
	global $CURUSER;

	$s = "<b>" . count($arr) . " " . $name . "</b>\n";
	if (!count($arr))
		return $s;

	$s .= "\n";
	$s .= "<table width=\"100%\" class=\"tableinborder\" border=\"0\" cellspacing=\"1\" cellpadding=\"4\">\n";
	$s .= "    <tr>\n";
	$s .= "        <td class=\"tablecat\">Benutzer/IP</td>\n";
	$s .= "        <td class=\"tablecat\" style=\"text-align:center\">Erreichbar</td>\n";
	$s .= "        <td class=\"tablecat\" style=\"text-align:center\">Hochgeladen</td>\n";
	$s .= "        <td class=\"tablecat\" style=\"text-align:center\">Rate</td>\n";
	$s .= "        <td class=\"tablecat\" style=\"text-align:center\">Runtergeladen</td>\n";
	$s .= "        <td class=\"tablecat\" style=\"text-align:center\">Rate</td>\n";
	$s .= "        <td class=\"tablecat\" style=\"text-align:center\">Ratio</td>\n";
	$s .= "        <td class=\"tablecat\" style=\"text-align:center\">Fertig</td>\n";
	$s .= "        <td class=\"tablecat\" style=\"text-align:center\">Verbunden</td>\n";
	$s .= "        <td class=\"tablecat\" style=\"text-align:center\">Unt&auml;tig</td>\n";
	$s .= "        <td class=\"tablecat\" style=\"text-align:center\">Client</td>";
	$s .= "    </tr>\n";

	$mod = get_user_class() >= UC_MODERATOR;
	$now = time();

	foreach($arr as $e){
		$qry = $GLOBALS['DB']->prepare("SELECT username, privacy, class, donor, enabled, warned, added FROM users WHERE id= :id ORDER BY last_access DESC LIMIT 1");
		$qry->bindParam(':id', $e["userid"], PDO::PARAM_INT);
		$qry->execute();
		$una = $qry->Fetch(PDO::FETCH_ASSOC);
		$tdclass = $CURUSER && $e["userid"] == $CURUSER["id"] ? " class=\"inposttable\"": " class=\"tableb\"";

		if($una["privacy"] == "strong")
			continue;

		$s .= "    <tr>\n";

		if ($una["username"])
			$s .= "        <td" . $tdclass . " nowrap=\"nowrap\"><a href=\"userdetails.php?id=" . $e["userid"] . "\"><font class=\"" . get_class_color($una["class"]) . "\"><b>" . $una["username"] . "</b></font></a>&nbsp;" . get_user_icons($una) . "</td>\n";
		else
			$s .= "        <td" . $tdclass . ">" . ($mod ? $e["ip"] : preg_replace('/\.\d+$/', ".xxx", $e["ip"])) . "</td>\n";

		$s .= "        <td" . $tdclass . " style=\"text-align:center\">" . ($e["connectable"] == "yes" ? "Ja" : "<font color=\"red\">Nein</font>") . "</td>\n";
		$s .= "        <td" . $tdclass . " style=\"text-align:right\">" . mksize($e["uploaded"]) . "</td>\n";
		$s .= "        <td" . $tdclass . " style=\"text-align:right\" nowrap=\"nowrap\">" . mksize($e["uploaded"] / max(1, $e["uploadtime"])) . "/s</td>\n";
		$s .= "        <td" . $tdclass . " style=\"text-align:right\">" . mksize($e["downloaded"]) . "</td>\n";
		$s .= "        <td" . $tdclass . " style=\"text-align:right\" nowrap=\"nowrap\">" . mksize($e["downloaded"] / max(1, $e["downloadtime"])) . "/s</td>\n";

		if($e["downloaded"]){
			$ratio = floor(($e["uploaded"] / $e["downloaded"]) * 1000) / 1000;
			$s .= "        <td" . $tdclass . " style=\"text-align:right\"><font color=\"" . get_ratio_color($ratio) . "\">" . number_format($ratio, 3) . "</font></td>\n";
		}else{
			if($e["uploaded"])
			$s .= "        <td" . $tdclass . " style=\"text-align:right\">Inf.</td>\n";
			else
			$s .= "        <td" . $tdclass . " style=\"text-align:right\">---</td>\n";
		}

		$s .= "        <td" . $tdclass . " style=\"text-align:right\"><div title=\"" . sprintf("%.2f%%", 100 * (1 - ($e["to_go"] / $torrent["size"]))) . "\"style=\"border:1px solid black;padding:0px;width:40px;height:10px;\"><div style=\"border:none;width:" . sprintf("%.2f", 40 * (1 - ($e["to_go"] / $torrent["size"]))) . "px;height:10px;background-image:url(" . $GLOBALS["PIC_BASE_URL"] . "ryg-verlauf-small.png);background-repeat:no-repeat;\"></div></div></td>\n";
		$s .= "        <td" . $tdclass . " nowrap style=\"text-align:right\">" . mkprettytime($now - $e["st"]) . "</td>\n";
		$s .= "        <td" . $tdclass . " style=\"text-align:right\">" . mkprettytime($now - $e["la"]) . "</td>\n";
		$s .= "        <td" . $tdclass . " style=\"text-align:left\">" . htmlspecialchars(getagent($e["agent"], $e["peer_id"])) . "</td>\n";
		$s .= "    </tr>\n";
	}
	$s .= "</table>\n";
	return $s;
}

function leech_sort($a, $b){
	if(isset($_GET["usort"]))
		return seed_sort($a, $b);
	$x = $a["to_go"];
	$y = $b["to_go"];
	if($x == $y)
		return 0;
	if($x < $y)
		return -1;
	return 1;
}

function seed_sort($a, $b){
	$x = $a["uploaded"];
	$y = $b["uploaded"];
	if($x == $y)
		return 0;
	if($x < $y)
		return 1;
	return -1;
}

function get_user_icons($arr, $big = false){
	if($big){
		$donorpic = "starbig.png";
		$warnedpic = "warnedbig.gif";
		$disabledpic = "disabledbig.png";
		$newbiepic = "pacifierbig.png";
		$style = "style=\"margin-left:4pt;vertical-align:middle;\"";
	}else{
		$donorpic = "star.png";
		$warnedpic = "warned.gif";
		$disabledpic = "disabled.png";
		$newbiepic = "pacifier.png";
		$style = "style=\"margin-left:2pt;vertical-align:middle;\"";
	} 

	$pics = $arr["donor"] == "yes" ? "<img src=\"" . $GLOBALS["PIC_BASE_URL"] . $donorpic . "\" alt=\"Spender\" title=\"Dieser Benutzer hat mit einer Spende zum Erhalt des Trackers beigetragen\" border=\"0\" " . $style . ">" : "";

	if(isset($arr["warned"]) && $arr["warned"] == "yes")
		$pics .= "<img src=\"" . $GLOBALS["PIC_BASE_URL"] . $warnedpic . "\" alt=\"Verwarnt\" title=\"Dieser Benutzer wurde verwarnt\" border=\"0\" " . $style . ">";

	if(isset($arr["enabled"]) && $arr["enabled"] == "no")
		$pics .= "<img src=\"" . $GLOBALS["PIC_BASE_URL"] . $disabledpic . "\" alt=\"Deaktiviert\" title=\"Dieser Benutzer ist deaktiviert\" border=\"0\" " . $style . ">";

	$timeadded = intval(sql_timestamp_to_unix_timestamp($arr["added"]));
	if($timeadded > 0 && time() - $timeadded < 604800)
		$pics .= "<img src=\"" . $GLOBALS["PIC_BASE_URL"] . $newbiepic . "\" alt=\"Newbie\" title=\"Ist noch neu hier\" border=\"0\" " . $style . ">";
	return $pics;
}

function commenttable($rows){
	global $CURUSER, $_SERVER;
	foreach ($rows as $row){
		begin_table(true);
		echo "    <colgroup>\n".
			"        <col width=\"150\"><col width=\"600\">\n".
			"    </colgroup>\n".
			"    <tr>\n".
			"        <td colspan=\"2\" class=\"tablecat\">\n".
			"            #" . $row["id"] . " von ";
		if(isset($row["username"])){
			$title = $row["title"];
			if($title == "")
				$title = get_user_class_name($row["class"]);
			else
				$title = htmlspecialchars($title);
			echo "<a name=\"comm" . $row["id"] . "\" href=\"userdetails.php?id=" . $row["user"] . "\"><b>" . htmlspecialchars($row["username"]) . "</b></a>" . get_user_icons(array("donor" => $row["donor"], "enabled" => $row["enabled"], "warned" => $row["warned"], "added" => $row["uadded"])) . " (" . $title . ")";
		}else
			echo "<a name=\"comm" . $row["id"] . "\"><i>(Gelöscht)</i></a>";
		echo " am " . $row["added"] . ($row["user"] == $CURUSER["id"] || get_user_class() >= UC_MODERATOR ? " - [<a href=\"comment.php?action=edit&amp;cid=" . $row["id"] . "\">Bearbeiten</a>]" : "") .
			(get_user_class() >= UC_MODERATOR ? " - [<a href=\"comment.php?action=delete&amp;cid=" . $row["id"] . "\">Löschen</a>]" : "") .
			($row["editedby"] && get_user_class() >= UC_MODERATOR ? " - [<a href=\"comment.php?action=vieworiginal&amp;cid=" . $row["id"] . "\">Original anzeigen</a>]" : "") . "\n" . 
			"        </td>\n".
			"    </tr>\n";
		$avatar = ($CURUSER["avatars"] == "yes" ? htmlspecialchars($row["avatar"]) : "");
		if(!$avatar)
			$avatar = $GLOBALS["PIC_BASE_URL"] . "default_avatar.gif";
		$text = stripslashes(format_comment($row["text"]));
		if($row["editedby"] > 0){
			$sql = "SELECT username FROM users WHERE id = :id";
			$qry = $GLOBALS['DB']->prepare($sql);
			$qry->bindParam(':id', $row["editedby"], PDO::PARAM_INT);
			$qry->execute();
			$d = $qry->Fetch(PDO::FETCH_ASSOC);
			$un = $d["username"];
			$text .= "<p><font size=\"1\" class=\"small\">Zuletzt von <a href=\"userdetails.php?id=" . $row["editedby"] . "\"><b>" . $un . "</b></a> am " . $row["editedat"] . " bearbeitet</font></p>";
		}
		echo "    <tr valign=\"top\">\n".
			"        <td class=\"tableb\" align=\"center\" style=\"padding: 0px;width: 150px\"><img width=\"150\" src=\"" . $avatar . "\" alt=\"Avatar von " . $row["username"] . "\"></td>\n".
			"        <td class=\"tablea\">" . $text . "</td>\n".
			"    </tr>\n";
		end_table();
	}
}

?>