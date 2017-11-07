<?php

if (isset($_SERVER["HTTP_HOST"]))
    die ("Dieses Script kann nicht aus dem Browser aufgerufen werden, sondern ist für die Ausführung via Cronjob bestimmt!");

chdir(dirname($_SERVER["SCRIPT_FILENAME"]));

require "include/bittorrent.php";

dbconn(false);

// Ratio-Histogramm zeichnen
function paint_stats_histogram($uid)
{
    $res = mysql_query("SELECT `username` FROM `users` WHERE `id`=$uid");
    $arr = mysql_fetch_assoc($res);
    $uname = $arr["username"];

    // Min / Max Ratio für Skala
    $res = mysql_query("SELECT MIN(ROUND(`uploaded`/`downloaded`,8)) AS `min`, MAX(ROUND(`uploaded`/`downloaded`,8)) AS `max` FROM `ratiostats` WHERE `userid`=$uid");
    $minmax = mysql_fetch_Assoc($res);
    $minratio = doubleval($minmax["min"]);
    $maxratio = doubleval($minmax["max"]);
    
    // Datensätze
    $hourlyres = mysql_query("SELECT `uploaded`,`downloaded` FROM `ratiostats` WHERE `type`='hourly' AND `userid`=$uid ORDER BY `timecode` DESC LIMIT 240");
    $dailyres = mysql_query("SELECT `uploaded`,`downloaded` FROM `ratiostats` WHERE `type`='daily' AND `userid`=$uid ORDER BY `timecode` DESC LIMIT 240");
    
    if (mysql_num_rows($hourlyres) <= 1 || $minratio == $maxratio)
	return;
    
    $img = imagecreate(285, 150);
    
    $white  = imagecolorallocate($img, 255, 255, 255);
    $black  = imagecolorallocate($img,   0,   0,   0);
    $red    = imagecolorallocate($img, 255,   0,   0);
    $blue   = imagecolorallocate($img,   0,   0, 255);
    $green  = imagecolorallocate($img,   0, 128,   0);
    $silver = imagecolorallocate($img, 192, 192, 192);
    $lgray  = imagecolorallocate($img, 224, 224, 224);
    $gray   = imagecolorallocate($img, 128, 128, 128);
    
    $fw1 = imagefontwidth(1);
    $fw3 = imagefontwidth(3);
    
    // Skala berechnen
    $scale_start  = $minratio - ($maxratio-$minratio)/10;
    $scale_end    = $maxratio + ($maxratio-$minratio)/10;
    $scale_factor = 100 / ($scale_end - $scale_start);

    // Diagramm-Struktur zeichnen
    imagefill($img, 0, 0, $white);
    
    for ($I=0; $I<10; $I++) {
	imageline($img, 39+$I*24, 20, 39+$I*24, 118, $silver);
	for ($J=1; $J<4; $J++) {
	    imageline($img, 39+$I*24+$J*6, 20, 39+$I*24+$J*6, 118, $lgray);
	}
    }
    
    // Skalenlinien
    $scalemaxline = 119 - $scale_factor * ($maxratio - $scale_start);
    $maxratiotext = number_format($maxratio,3,",",".");
    imagestring($img, 1, 38-strlen($maxratiotext)*$fw1, $scalemaxline-imagefontheight(1)/2, $maxratiotext, $black);
    imageline($img, 39, $scalemaxline, 279, $scalemaxline, $silver);
    $scaleminline = 119 - $scale_factor * ($minratio - $scale_start);
    $minratiotext = number_format($minratio,3,",",".");
    imagestring($img, 1, 38-strlen($minratiotext)*$fw1, $scaleminline-imagefontheight(1)/2, $minratiotext, $black);
    imageline($img, 39, $scaleminline, 279, $scaleminline, $silver);
    
    // Ratio=1 in Skala?
    if ($scale_start <= 1 && $scale_end >= 1) {
	$oneline = 119 - $scale_factor * (1 - $scale_start);
	imagestring($img, 1, 38-$fw1, $oneline-imagefontheight(1)/2, "1", $green);
	imageline($img, 39, $oneline, 279, $oneline, $green);
    }
    
    imagerectangle($img, 39, 19, 279, 119, $gray);
    
    imageline($img, 39, 136, 44, 136, $red);
    imagestring($img, 2, 51, 130, "Stundenlinie", $silver);
    imagestring($img, 2, 50, 129, "Stundenlinie", $red);
    
    imageline($img, 169, 136, 174, 136, $blue);
    imagestring($img, 2, 181, 130, "Tageslinie", $silver);
    imagestring($img, 2, 180, 129, "Tageslinie", $blue);

    $title = "Ratio-History für $uname";
    imagestring($img, 3, (320-$fw3*strlen($title))/2+1, 3, $title, $silver);
    imagestring($img, 3, (320-$fw3*strlen($title))/2, 2, $title, $black);

    $cnt = 0;
    $lastval = 0;

    // Tageslinie
    while ($rdata = mysql_fetch_assoc($dailyres)) {	
	$curval = 119 - $scale_factor * ($rdata["uploaded"]/$rdata["downloaded"] - $scale_start);
	
	if ($cnt>0)
	    imageline($img, 280-$cnt, $lastval, 279-$cnt, $curval, $blue);
	else
	    imagesetpixel($img, 279-$cnt, $curval, $green);
	    
	$lastval = $curval;
	$cnt++;
    }
    
    $cnt = 0;
    $lastval = 0;

    // Stundenlinie
    while ($rdata = mysql_fetch_assoc($hourlyres)) {	
	$curval = 119 - $scale_factor * ($rdata["uploaded"]/$rdata["downloaded"] - $scale_start);
	if ($cnt>0)
	    imageline($img, 280-$cnt, $lastval, 279-$cnt, $curval, $red);
	else
	    imagesetpixel($img, 279-$cnt, $curval, $red);

	$lastval = $curval;
	$cnt++;
    }

    $filename = "./".$GLOBALS["BITBUCKET_DIR"]."/rstat-".$uid.".png";
    imagepng($img, $filename);
    imagedestroy($img);
    echo "Wrote image $filename.\n";
}

// Alle Diagramme löschen
shell_exec("find ./".$GLOBALS["BITBUCKET_DIR"]."/ -name 'rstat-*.png' | xargs rm");

// Alle User aus DB holen, die Stats wünschen
$res = mysql_query("SELECT `id`,`uploaded`,`downloaded` FROM `users` WHERE `log_ratio`='yes' AND `enabled`='yes'");

$timestamp = date("Y-m-d H:00:00");
$usermask = "";
while ($userstats = mysql_fetch_assoc($res)) {
    // Aktuellen Eintrag hinzufügen
    mysql_query("INSERT INTO `ratiostats` (`userid`,`timecode`,`downloaded`,`uploaded`) VALUES(".$userstats["id"].",'$timestamp',".$userstats["downloaded"].",".$userstats["uploaded"].")");    
    echo "Inserted UID ".$userstats["id"]." into database.\n";

    $daycntarr = mysql_fetch_assoc(mysql_query("SELECT COUNT(*) AS `cnt` FROM `ratiostats` WHERE `type`='daily' AND `userid`=".$userstats["id"]));
    $daycnt = $daycntarr["cnt"];
    if ($daycnt == 0) {
	// Erster Tageseintrag
	mysql_query("INSERT INTO `ratiostats` (`userid`,`type`,`timecode`,`downloaded`,`uploaded`) VALUES(".$userstats["id"].",'daily','$timestamp',".$userstats["downloaded"].",".$userstats["uploaded"].")");
    } else {
        $lastdayres = mysql_query("SELECT UNIX_TIMESTAMP(`timecode`) AS `time` FROM `ratiostats` WHERE `type`='daily' AND `userid`=".$userstats["id"]." ORDER BY `timecode` DESC LIMIT 1");
	if ($lastday = mysql_fetch_assoc($lastdayres)) {
	    // Letzter Eintrag 24h her?
	    if ($lastday["time"] <= time()-24*3600)
		mysql_query("INSERT INTO `ratiostats` (`userid`,`type`,`timecode`,`downloaded`,`uploaded`) VALUES(".$userstats["id"].",'daily','$timestamp',".$userstats["downloaded"].",".$userstats["uploaded"].")");	    
	}
    }
    
    $usermask .= ($usermask!=""?",":"").$userstats["id"];
    
    // Stats-Diagramm aktualisieren
    paint_stats_histogram($userstats["id"]);
}

// Alle Einträge von Benutzern löschen, die kein Diagramm wünschen
mysql_query("DELETE FROM `ratiostats` WHERE `userid` NOT IN ($usermask)");

// Ältere Einträge löschen (Stunden/Tage)
mysql_query("DELETE FROM `ratiostats` WHERE `type`='hourly' AND `timecode`<'".date("Y-m-d H:00:00", time()-264*3600)."'");
mysql_query("DELETE FROM `ratiostats` WHERE `type`='daily' AND `timecode`<'".date("Y-m-d H:00:00", time()-264*86400)."'");

?>