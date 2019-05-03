<?php

/************************************************************
* Tracker configuration
* 
* Please read the comments before changing anything!
* Database configuration: see secrets.inc.php
**************************************************************/

define ('CLIENT_AUTH_IP', 0);
define ('CLIENT_AUTH_PASSKEY', 1);

define ('PASSKEY_USE_PARAM', 1);
define ('PASSKEY_USE_SUBDOMAIN', 0);

define ('DOWNLOAD_REWRITE', 0);
define ('DOWNLOAD_ATTACHMENT', 1);


/************************************************************
* Global settings
**************************************************************/

// Set this to FALSE, if you have to do some time consuming maintenance
$GLOBALS["SITE_ONLINE"] = TRUE;

// Maximum size for uploaded .torrent files, default is 1 MB
$GLOBALS["MAX_TORRENT_SIZE"] = 1024 * 1024;

// Reannounce interval suggested to clients.
// Do not set this lower than about 20 minutes, since some clients
// do not honour this value, and rather do their reannounces at 30 minute
// intervals. This would result in concurrent timeouts for these peers.
$GLOBALS["ANNOUNCE_INTERVAL"] = 60 * 20;

// Minimum votes necessary to rate a torrent
$GLOBALS["MINVOTES"] = 1;

// Max users on site
$GLOBALS["MAX_USERS"] = 2150;

// Use IP or PassKey method for user authentication
// Valid values are CLIENT_AUTH_IP and CLIENT_AUTH_PASSKEY
$GLOBALS["CLIENT_AUTH"] = CLIENT_AUTH_PASSKEY;

// PassKey source, either by parameter "passkey=...", or by
// subdomain "http://passkey.tracker.net/announce.php"
// Use subdomain, if you have access to wildcard subdomains,
// but not mod_rewrite
$GLOBALS["PASSKEY_SOURCE"] = PASSKEY_USE_PARAM;

// Download method being used by the tracker to publish .torrent files.
// If you use themod_rewrite method, set this to DOWNLOAD_REWRITE, since
// this solution is most compatible. Otherwise, set it to DOWNLOAD_ATTACHMENT.
$GLOBALS["DOWNLOAD_METHOD"] = DOWNLOAD_ATTACHMENT;

// Set this to FALSE to deactivate dynamic RSS feed via rss.php
$GLOBALS["DYNAMIC_RSS"] = FALSE;


/************************************************************
* Limits
*************************************************************/

// Maximum size of files uploaded into the BitBucket in bytes
$GLOBALS["MAX_UPLOAD_FILESIZE"] = 256 * 1024;

// Maximum size of the BitBucket per user in bytes
$GLOBALS["MAX_BITBUCKET_SIZE_USER"] = 1024 * 1024;

// Maximum size of the BitBucket for uploaders in bytes
$GLOBALS["MAX_BITBUCKET_SIZE_UPLOADER"] = 5 * 1024 * 1024;

// Number of categories displayed per row in torrent browser
$GLOBALS["BROWSE_CATS_PER_ROW"] = 5;

// Wait time only for leechers
// Be careful, as it is possible to cheat with the wait time if set to TRUE
$GLOBALS["ONLY_LEECHERS_WAIT"] = FALSE;

// Disallow leeching even if the wait time for a torrent is disabled
// Be careful, as it is still possible to cheat if set to TRUE
$GLOBALS["NOWAITTIME_ONLYSEEDS"] = FALSE;

// Rules for wait time
// Format is Max.Ratio:Max.UpGigs:Regtime:Waittime|...
// Regtime format is [#w][#d] or 0 and * for infinite, e.g.: 2w, 2w3d, 5d
// If no rule does match, the wait time is 0, otherwise the highest wait time
// of any rule that matches counts.
$GLOBALS["WAIT_TIME_RULES"] = "0.2:2:0:48|0.4:4:0:24|0.6:6:0:12|0.9:8:0:6";

// Rules for torrent limitation
// Format is Ratio:UpGigs:SeedsMax:LeechesMax:AllMax|...
// Ratio and UpGigs are "minimum" requirements.
$GLOBALS["TORRENT_RULES"] = "0:0:10:2:12|1.01:5:10:3:13|2.01:20:10:4:14";

// Maximum number of different IP addresses for each passkey/user
$GLOBALS["MAX_PASSKEY_IPS"] = 5;

// Threshold for ratio faker tool detection. If you allow root seeding,
// consider setting this so a high value, e.g. 4 MB/sec
$GLOBALS["RATIOFAKER_THRESH"] = 2 * 1024 * 1024;

// Alwas perfom a "deep" cleanup. On small trackers, you can set
// this to TRUE, but on large trackers with lots of torrents, you
// should set this to FALSE and do deep cleanups by running
//      docleanup.php?deep=1
// as a moderator or higher ranked user.
$GLOBALS["ALWAYS_DEEP_CLEAN"] = TRUE;


/************************************************************
* Timeout settings and intervals
*************************************************************/

// Max dead torrent timeout (days)
// Time after which torrents with 0 peers are marked as dead (invisible)
$GLOBALS["MAX_DEAD_TORRENT_TIME"] = 2;

// Max torrent TTL (days, 0 to disable)
// Time after which torrents are automatically deleted
$GLOBALS["MAX_TORRENT_TTL"] = 28;

// Max signup timeout (hours)
// Time given to an user to activate his/her account
$GLOBALS["SIGNUP_TIMEOUT"] = 48;

// Max inactive timeout (days)
// Period of inactivity/no logins after an user is deleted
$GLOBALS["INACTIVE_TIMEOUT"] = 42;

// Max disabled timeout (days)
// Period of time after a disabled user is deleted
$GLOBALS["DISABLED_TIMEOUT"] = 3;

// Lock timeout for forum threads (days, 0=disabled)
// Forum threads where last post was made x days ago will be locked
$GLOBALS["THREAD_LOCK_TIMEOUT"] = 7;

// Autoclean interval (seconds)
// Time between torrent and user cleanups. If the tracker suffers
// from high load, consider setting this to a higher value.
$GLOBALS["AUTOCLEAN_INTERVAL"] = 30 * 60;

// Maximum personal message prune time (days, 0=disabled)
// Personal messages are getting deleted after this number of days,
// regardless of any user setting for PM folders.
$GLOBALS["PM_PRUNE_DAYS"] = 0;


/************************************************************
* Path and URL settings
* 
* Paths are relative to index.php or absolute if beginning
* with / (or X:\ in Windows)
* All paths must not end with a trailing slash if not
* otherwise stated!
*************************************************************/

// Path where .torrent files are stored
// This path MUST NOT be publicly available. The download
// script takes care of delivering the files to users.
// Webserver MUST have write permission on this directory!
// No trailing slash.
$GLOBALS["TORRENT_DIR"] = "torrents";

// Path where all Bit-Bucket files are stored. These are:
// User's files, torrent and NFO images
// This path MUST be a subdir of the tracker root, and MUST be
// publicly available, optionally with referrer check
// Webserver MUST have write permission on this directory!
// No trailing slash.
$GLOBALS["BITBUCKET_DIR"] = "bitbucket";

// Relative or absolute URL where all images for the interface are stored.
// MUST include a trailing slash!
$GLOBALS["PIC_BASE_URL"] = "pic/";

// Relative or absolute URL to the portal, if it exists. Leave blank
// to hide the portal links.
$GLOBALS["PORTAL_LINK"] = "forums.php";

// Socket-base
$GLOBALS["SOCKET_PORT"] = "81";
$GLOBALS["SOCKET_URL"] = "http://localhost:" . $GLOBALS["SOCKET_PORT"];
//$GLOBALS["SOCKET_IP"] = "127.0.0.1:81";
$GLOBALS["SOCKET_IP"] = "[::1]:" . $GLOBALS["SOCKET_PORT"];

// Valid tracker announce URLs
// The first entry will be displayed on the upload page
$GLOBALS["ANNOUNCE_URLS"] = array();
$GLOBALS["ANNOUNCE_URLS"][] = "http://localhost:81/announce";

// Announce URL with passkey placeholder
$GLOBALS["PASSKEY_ANNOUNCE_URL"] = "http://localhost:81/announce?passkey={KEY}";

if ($_SERVER["HTTP_HOST"] == "")
    $_SERVER["HTTP_HOST"] = $_SERVER["SERVER_NAME"];
$GLOBALS["BASEURL"] = "http://" . $_SERVER["HTTP_HOST"];
//if ($_SERVER["SERVER_PORT"] != 80)
    //$GLOBALS["BASEURL"] .= ":".$_SERVER["SERVER_PORT"];

// Set this to your site URL, if automatic detection won't work
$GLOBALS["DEFAULTBASEURL"] = "http://localhost";

// Array containing all domains which are used to reach the tracker
// This array is used in the redirector script to determine the type of redirect
// Do not add "http://" in front of the domain, and no trailing slash
$GLOBALS["TRACKERDOMAINS"] = array();
$GLOBALS["TRACKERDOMAINS"][] = "localhost";
$GLOBALS["TRACKERDOMAINS"][] = "127.0.0.1";

// Set this to true to make this a tracker that only registered users may use
// Setting this to FALSE is currently not supported, sorry!
$GLOBALS["MEMBERSONLY"] = TRUE;

// Email for sender/return path.
$GLOBALS["SITEEMAIL"] = "noreply@localhost";

$GLOBALS["SITENAME"] = "pdonvtracker :: NV Reloaded";


/************************************************************
* Shoutcast server settings
*************************************************************/

// Set to FALSE to disable Shoutcast plugin
$GLOBALS["ENABLESHOUTCAST"] = FALSE;

// Radio title
$GLOBALS["RADIOTITLE"] = "Tracker Radio";

// Hostname and port of your Shoutcast server
$GLOBALS["SC_HOSTNAME"] = "my.shoutcast.host";
$GLOBALS["SC_PORT"] = 8000;

// Username and password for your SC admin account
$GLOBALS["SC_USERNAME"] = "admin";
$GLOBALS["SC_PASSWORD"] = "passwd";

// Connection timeout
// If the Shoutcast server does not run, the HTTP request
// will freeze script execution for this amount of time.
// Better disable the plugin if your server is down, or start
// the SC server immediately. This means the sc_serv process
// running or not, NOT the stream online/offline status!
$GLOBALS["SC_HTTPTIMEOUT"] = 2;


/************************************************************
 * IRC network settings
 *************************************************************/

// Set this to FALSE if you don't have an IRC channel
$GLOBALS["IRCAVAILABLE"] = FALSE;

// Title of your IRC network, e.g. "Quakenet", leave blank if there is no web page
$GLOBALS["IRCNETWORKTITLE"] ="My IRC Net";

// Web page of your IRC network, leave blank if there is no web page
$GLOBALS["IRCNETWORKWEB"] = "http://my-irc.example.com";

// Hostname of the IRC server or hub
$GLOBALS["IRCHOST"] = "my-irc.example.com";

// Port of the IRC server or hub
$GLOBALS["IRCPORT"] = "6667";

// Channel to join, leave blank to display channel list to users
$GLOBALS["IRCCHANNEL"] = "#MyChannel";

// Alternate nickname prefix (if nick is already in use)
$GLOBALS["IRCALTNICK"] = "MyNick";


/************************************************************
 * Ban settings
 *************************************************************/

// Badwords in email addresses, matches everywhere
$GLOBALS["EMAIL_BADWORDS"] = array(
    "sofort-mail", 
    "lycos", 
    "yahoo", 
    "dfgh", 
    "hotmail", 
    "spamgourmet", 
    "trash-mail",
    "gmail",
    "freenet.de",
    "rocketmail",
    "mailinator",
    "msn.com",
    "msn.de",
    "spammotel",
    "fakemail",
    "germanmail",
    "discardmail",
    "spacemail",
    "mail.ru",
    "mytrashmail",
    "jetable",
    "spam.la",
    // DynDNS.org Hosts anfang
    "homeip",
    "ath.cx",
    "blogdns",
    "dnsalias",
    "dvrdns",
    "dynalias",
    "dyndns",
    "game-host.org",
    "game-server.cc",
    "getmyip.com",
    "gotdns",
    "ham-radio-op.net",
    "homedns",
    "homeftp",
    "homelinux",
    "homeunix",
    "is-a-geek",
    "isa-geek",
    "kicks-ass",
    "merseine.nu",
    "mine.nu",
    "myphotos.cc",
    "podzone",
    "serveftp",
    "servegame",
    "shacknet.nu",
    // DynDNS.org Hosts ende
    "dyn.pl",
    "bbth3c",
);

// Array of banned peer ids. Match is done only at the beginning of the string.
$GLOBALS["BAN_PEERIDS"] = array(
    "A\x02\x06\x09-",
    "-ÄZ{Ü"
);

// Array of banned user agents, only exact matches
$GLOBALS["BAN_USERAGENTS"] = array(
    "Azureus 2.1.0.0",
    "Azureus 2.2.0.3_B1",
    "Azureus 2.2.0.3_B4",
    "Azureus 2.2.0.3_B29",
    "BitComet",
    "Python-urllib/2.0a1"
);

// aus der global.php

// User levels
define('UC_USER', 0);
define('UC_POWER_USER', 1);
define('UC_VIP', 5);
define('UC_UPLOADER', 10);
define('UC_GUTEAM', 20);
define('UC_MODERATOR', 25);
define('UC_ADMINISTRATOR', 50);
define('UC_SYSOP', 100);

// PM special folder IDs
define('PM_FOLDERID_INBOX', -1);
define('PM_FOLDERID_OUTBOX', -2);
define('PM_FOLDERID_SYSTEM', -3);
define('PM_FOLDERID_MOD', -4);


$client_uas = array("/^Azureus ([0-9]+\\.[0-9]+\\.[0-9]+\\.[0-9]+)(;.+?)?$/i",
	"/^Azureus ([0-9]+\\.[0-9]+\\.[0-9]+\\.[0-9]+)_B([0-9]+)(;.+?)?$/i",
	"/^BitTorrent\\/S-([0-9]+\\.[0-9]+(\\.[0-9]+)*)(.*)$/i",
	"/^BitTorrent\\/U-([0-9]+\\.[0-9]+\\.[0-9]+)$/i",
	"/^BitTor(rent|nado)\\/T-(.+)$/i",
	"/^BitTorrent\\/([0-9]+\\.[0-9]+(\\.[0-9]+)*)$/i",
	"/^Python-urllib\\/[0-9]+\\.[a-z0-9]+$/i",
	"/^Python-urllib\\/.+?, BitTorrent\\/([0-9]+\\.[0-9]+(\\.[0-9]+)*)$/i",
	"/^Python-urllib\\/.+?, BitTorrent\\/TurboBT ([0-9]+\\.[0-9]+(\\.[0-9]+)*)$/i",
	"/^BitTorrent\\/BitSpirit$/i",
	"/^BitTorrent\\/brst(.+)$/i",
	"/^RAZA (.+)$/i",
	"/^BitTorrent\\/ABC-([0-9]+\\.[0-9]+\\.[0-9]+)$/i",
	"/^BitComet\\/([0-9]+\\.[0-9]+)$/i"
);

$clean_uas = array("Azureus/\\1",
	"Azureus/\\1 (Beta \\2)",
	"Shadow's/\\1",
	"UPnP/\\1",
	"BitTornado/\\2",
	"BitTorrent/\\1",
	"G3 Torrent",
	"BitTorrent/\\1",
	"TurboBT/\\1",
	"BitSpirit",
	"Burst/\\1",
	"Shareaza/\\1",
	"ABC/\\1",
	"BitComet/\\1"
);

?>