-- MySQL dump 9.11
--
-- Host: localhost    Database: tracker
-- ------------------------------------------------------
-- Server version	4.0.25

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `userid` int(10) NOT NULL default '0',
  `chash` varchar(32) NOT NULL default '',
  `lastaccess` datetime NOT NULL default '0000-00-00 00:00:00',
  `username` varchar(64) NOT NULL default '',
  `email` varchar(128) NOT NULL default '',
  `baduser` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`userid`,`chash`)
) TYPE=MyISAM;

--
-- Table structure for table `announcedelay`
--

CREATE TABLE `announcedelay` (
  `peer_id` varchar(20) binary NOT NULL default '',
  `first` int(10) unsigned NOT NULL default '0',
  `second` int(10) unsigned NOT NULL default '0',
  `quantity` bigint(20) unsigned NOT NULL default '0'
) TYPE=MyISAM;

--
-- Table structure for table `avps`
--

CREATE TABLE `avps` (
  `arg` varchar(20) NOT NULL default '',
  `value_s` text NOT NULL,
  `value_i` int(11) NOT NULL default '0',
  `value_u` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`arg`)
) TYPE=MyISAM;

--
-- Dumping data for table `avps`
--

INSERT INTO `avps` VALUES ('lastcleantime','',0,0);
INSERT INTO `avps` VALUES ('seeders','',0,0);
INSERT INTO `avps` VALUES ('leechers','',0,0);

--
-- Table structure for table `bans`
--

CREATE TABLE `bans` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `added` datetime NOT NULL default '0000-00-00 00:00:00',
  `addedby` int(10) unsigned NOT NULL default '0',
  `comment` varchar(255) NOT NULL default '',
  `first` int(11) default NULL,
  `last` int(11) default NULL,
  PRIMARY KEY  (id),
  KEY first_last (`first`,`last`)
) TYPE=MyISAM;

--
-- Table structure for table `bitbucket`
--

CREATE TABLE `bitbucket` (
  `id` int(11) NOT NULL auto_increment,
  `user` int(11) NOT NULL default '0',
  `filename` varchar(40) NOT NULL default '',
  `size` int(11) NOT NULL default '0',
  `originalname` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

--
-- Table structure for table `blocks`
--

CREATE TABLE blocks (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` int(10) unsigned NOT NULL default '0',
  `blockid` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `userfriend` (`userid`,`blockid`)
) TYPE=MyISAM;

--
-- Table structure for table `casino_bank`
--

CREATE TABLE `casino_bank` (
  `userid` int(10) unsigned NOT NULL default '0',
  `passphrase` varchar(32) NOT NULL default '',
  `balance` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`userid`)
) TYPE=MyISAM;

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(30) NOT NULL default '',
  `image` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` VALUES (1,'Software/PC ISO','cat_apps.gif');
INSERT INTO `categories` VALUES (4,'Games/PC ISO','cat_games.gif');
INSERT INTO `categories` VALUES (5,'Movies/SVCD','cat_movies3.gif');
INSERT INTO `categories` VALUES (6,'Audio','cat_music.gif');
INSERT INTO `categories` VALUES (7,'Serien','cat_episodes.gif');
INSERT INTO `categories` VALUES (9,'Sonstiges','cat_misc.gif');
INSERT INTO `categories` VALUES (12,'Games/Konsolen','cat_games3.gif');
INSERT INTO `categories` VALUES (23,'Anime','cat_anime.gif');
INSERT INTO `categories` VALUES (19,'Movies/XviD','cat_movies2.gif');
INSERT INTO `categories` VALUES (20,'Movies/DVD-R','cat_movies4.gif');
INSERT INTO `categories` VALUES (21,'Games/PC Rips','cat_games2.gif');
INSERT INTO `categories` VALUES (22,'Software/Sonstiges','cat_apps2.gif');
INSERT INTO `categories` VALUES (24,'Hörspiel/Hörbuch','cat_music2.gif');
INSERT INTO `categories` VALUES (25,'Doku / Magazin','cat_documentation.gif');
INSERT INTO `categories` VALUES (26,'Movies/(M)VCD','cat_movies5.gif');

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user` int(10) unsigned NOT NULL default '0',
  `torrent` int(10) unsigned NOT NULL default '0',
  `added` datetime NOT NULL default '0000-00-00 00:00:00',
  `text` text NOT NULL,
  `ori_text` text NOT NULL,
  `editedby` int(10) unsigned NOT NULL default '0',
  `editedat` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (id),
  KEY `user` (`user`),
  KEY `torrent` (`torrent`)
) TYPE=MyISAM;

--
-- Table structure for table `completed`
--

CREATE TABLE `completed` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `torrent_id` int(11) NOT NULL default '0',
  `torrent_name` varchar(255) NOT NULL default '',
  `torrent_category` int(10) NOT NULL default '0',
  `complete_time` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(50) default NULL,
  `flagpic` varchar(50) default NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` VALUES (1,'Sweden','sweden.gif');
INSERT INTO `countries` VALUES (2,'United States of America','usa.gif');
INSERT INTO `countries` VALUES (3,'Russia','russia.gif');
INSERT INTO `countries` VALUES (4,'Finland','finland.gif');
INSERT INTO `countries` VALUES (5,'Canada','canada.gif');
INSERT INTO `countries` VALUES (6,'France','france.gif');
INSERT INTO `countries` VALUES (7,'Germany','germany.gif');
INSERT INTO `countries` VALUES (8,'China','china.gif');
INSERT INTO `countries` VALUES (9,'Italy','italy.gif');
INSERT INTO `countries` VALUES (10,'Denmark','denmark.gif');
INSERT INTO `countries` VALUES (11,'Norway','norway.gif');
INSERT INTO `countries` VALUES (12,'United Kingdom','uk.gif');
INSERT INTO `countries` VALUES (13,'Ireland','ireland.gif');
INSERT INTO `countries` VALUES (14,'Poland','poland.gif');
INSERT INTO `countries` VALUES (15,'Netherlands','netherlands.gif');
INSERT INTO `countries` VALUES (16,'Belgium','belgium.gif');
INSERT INTO `countries` VALUES (17,'Japan','japan.gif');
INSERT INTO `countries` VALUES (18,'Brazil','brazil.gif');
INSERT INTO `countries` VALUES (19,'Argentina','argentina.gif');
INSERT INTO `countries` VALUES (20,'Australia','australia.gif');
INSERT INTO `countries` VALUES (21,'New Zealand','newzealand.gif');
INSERT INTO `countries` VALUES (23,'Spain','spain.gif');
INSERT INTO `countries` VALUES (24,'Portugal','portugal.gif');
INSERT INTO `countries` VALUES (25,'Mexico','mexico.gif');
INSERT INTO `countries` VALUES (26,'Singapore','singapore.gif');
INSERT INTO `countries` VALUES (70,'India','india.gif');
INSERT INTO `countries` VALUES (65,'Albania','albania.gif');
INSERT INTO `countries` VALUES (29,'South Africa','southafrica.gif');
INSERT INTO `countries` VALUES (30,'South Korea','southkorea.gif');
INSERT INTO `countries` VALUES (31,'Jamaica','jamaica.gif');
INSERT INTO `countries` VALUES (32,'Luxembourg','luxembourg.gif');
INSERT INTO `countries` VALUES (33,'Hong Kong','hongkong.gif');
INSERT INTO `countries` VALUES (34,'Belize','belize.gif');
INSERT INTO `countries` VALUES (35,'Algeria','algeria.gif');
INSERT INTO `countries` VALUES (36,'Angola','angola.gif');
INSERT INTO `countries` VALUES (37,'Austria','austria.gif');
INSERT INTO `countries` VALUES (38,'Yugoslavia','yugoslavia.gif');
INSERT INTO `countries` VALUES (39,'Western Samoa','westernsamoa.gif');
INSERT INTO `countries` VALUES (40,'Malaysia','malaysia.gif');
INSERT INTO `countries` VALUES (41,'Dominican Republic','dominicanrep.gif');
INSERT INTO `countries` VALUES (42,'Greece','greece.gif');
INSERT INTO `countries` VALUES (43,'Guatemala','guatemala.gif');
INSERT INTO `countries` VALUES (44,'Israel','israel.gif');
INSERT INTO `countries` VALUES (45,'Pakistan','pakistan.gif');
INSERT INTO `countries` VALUES (46,'Czech Republic','czechrep.gif');
INSERT INTO `countries` VALUES (47,'Serbia','serbia.gif');
INSERT INTO `countries` VALUES (48,'Seychelles','seychelles.gif');
INSERT INTO `countries` VALUES (49,'Taiwan','taiwan.gif');
INSERT INTO `countries` VALUES (50,'Puerto Rico','puertorico.gif');
INSERT INTO `countries` VALUES (51,'Chile','chile.gif');
INSERT INTO `countries` VALUES (52,'Cuba','cuba.gif');
INSERT INTO `countries` VALUES (53,'Congo','congo.gif');
INSERT INTO `countries` VALUES (54,'Afghanistan','afghanistan.gif');
INSERT INTO `countries` VALUES (55,'Turkey','turkey.gif');
INSERT INTO `countries` VALUES (56,'Uzbekistan','uzbekistan.gif');
INSERT INTO `countries` VALUES (57,'Switzerland','switzerland.gif');
INSERT INTO `countries` VALUES (58,'Kiribati','kiribati.gif');
INSERT INTO `countries` VALUES (59,'Philippines','philippines.gif');
INSERT INTO `countries` VALUES (60,'Burkina Faso','burkinafaso.gif');
INSERT INTO `countries` VALUES (61,'Nigeria','nigeria.gif');
INSERT INTO `countries` VALUES (62,'Iceland','iceland.gif');
INSERT INTO `countries` VALUES (63,'Nauru','nauru.gif');
INSERT INTO `countries` VALUES (64,'Slovenia','slovenia.gif');
INSERT INTO `countries` VALUES (66,'Turkmenistan','turkmenistan.gif');
INSERT INTO `countries` VALUES (67,'Bosnia Herzegovina','bosniaherzegovina.gif');
INSERT INTO `countries` VALUES (68,'Andorra','andorra.gif');
INSERT INTO `countries` VALUES (69,'Lithuania','lithuania.gif');
INSERT INTO `countries` VALUES (71,'Netherlands Antilles','nethantilles.gif');
INSERT INTO `countries` VALUES (72,'Ukraine','ukraine.gif');
INSERT INTO `countries` VALUES (73,'Venezuela','venezuela.gif');
INSERT INTO `countries` VALUES (74,'Hungary','hungary.gif');
INSERT INTO `countries` VALUES (75,'Romania','romania.gif');
INSERT INTO `countries` VALUES (76,'Vanuatu','vanuatu.gif');
INSERT INTO `countries` VALUES (77,'Vietnam','vietnam.gif');
INSERT INTO `countries` VALUES (78,'Trinidad & Tobago','trinidadandtobago.gif');
INSERT INTO `countries` VALUES (79,'Honduras','honduras.gif');
INSERT INTO `countries` VALUES (80,'Kyrgyzstan','kyrgyzstan.gif');
INSERT INTO `countries` VALUES (81,'Ecuador','ecuador.gif');
INSERT INTO `countries` VALUES (82,'Bahamas','bahamas.gif');
INSERT INTO `countries` VALUES (83,'Peru','peru.gif');
INSERT INTO `countries` VALUES (84,'Cambodia','cambodia.gif');
INSERT INTO `countries` VALUES (85,'Barbados','barbados.gif');
INSERT INTO `countries` VALUES (86,'Bangladesh','bangladesh.gif');
INSERT INTO `countries` VALUES (87,'Laos','laos.gif');
INSERT INTO `countries` VALUES (88,'Uruguay','uruguay.gif');
INSERT INTO `countries` VALUES (89,'Antigua Barbuda','antiguabarbuda.gif');
INSERT INTO `countries` VALUES (90,'Paraguay','paraguay.gif');
INSERT INTO `countries` VALUES (93,'Thailand','thailand.gif');
INSERT INTO `countries` VALUES (92,'Union of Soviet Socialist Republics','ussr.gif');
INSERT INTO `countries` VALUES (94,'Senegal','senegal.gif');
INSERT INTO `countries` VALUES (95,'Togo','togo.gif');
INSERT INTO `countries` VALUES (96,'North Korea','northkorea.gif');
INSERT INTO `countries` VALUES (97,'Croatia','croatia.gif');
INSERT INTO `countries` VALUES (98,'Estonia','estonia.gif');
INSERT INTO `countries` VALUES (99,'Colombia','colombia.gif');
INSERT INTO `countries` VALUES (100,'Lebanon','lebanon.gif');
INSERT INTO `countries` VALUES (101,'Latvia','latvia.gif');
INSERT INTO `countries` VALUES (102,'Costa Rica','costarica.gif');
INSERT INTO `countries` VALUES (103,'Egypt','egypt.gif');
INSERT INTO `countries` VALUES (104,'Bulgaria','bulgaria.gif');
INSERT INTO `countries` VALUES (105,'Isla de Muerte','jollyroger.gif');

--
-- Table structure for table `files`
--

CREATE TABLE `files` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `torrent` int(10) unsigned NOT NULL default '0',
  `filename` varchar(255) NOT NULL default '',
  `size` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `torrent` (`torrent`)
) TYPE=MyISAM;

--
-- Table structure for table `forums`
--

CREATE TABLE `forums` (
  `sort` tinyint(3) unsigned NOT NULL default '0',
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(60) NOT NULL default '',
  `description` varchar(200) default NULL,
  `minclassread` tinyint(3) unsigned NOT NULL default '0',
  `minclasswrite` tinyint(3) unsigned NOT NULL default '0',
  `postcount` int(10) unsigned NOT NULL default '0',
  `topiccount` int(10) unsigned NOT NULL default '0',
  `minclasscreate` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

--
-- Dumping data for table `forums`
--

INSERT INTO `forums` VALUES (3,1,'Support','Hier bekommt ihr Hilfe für alle Trackerprobleme, die NICHT in der FAQ beschrieben sind.',0,0,118,19,0);
INSERT INTO `forums` VALUES (4,2,'Support for English Users','If you are an english speaking user and have a question or a problem, you can post it here.',0,0,3,2,0);
INSERT INTO `forums` VALUES (0,3,'SysOp-Talk','SysOps willkommen, alle anderen müssen draussen bleiben ;)',6,6,0,0,6);
INSERT INTO `forums` VALUES (1,4,'Mod-Talk','',4,4,0,0,4);
INSERT INTO `forums` VALUES (2,5,'Uploader-Talk','Falls die Uploader untereinander etwas besprechen müssen',3,3,34,8,3);
INSERT INTO `forums` VALUES (0,6,'Off Topic','',0,0,82,21,0);
INSERT INTO `forums` VALUES (6,7,'Ich könnte anbieten','Der Name sagt wohl alles ;)',0,0,84,31,0);
INSERT INTO `forums` VALUES (7,8,'Umfragen','Hier könnt ihr über die aktuelle Umfrage diskutieren',0,0,29,4,0);

--
-- Table structure for table `friends`
--

CREATE TABLE `friends` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` int(10) unsigned NOT NULL default '0',
  `friendid` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `userfriend` (`userid`,`friendid`)
) TYPE=MyISAM;

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `sender` int(10) unsigned NOT NULL default '0',
  `receiver` int(10) unsigned NOT NULL default '0',
  `folder_in` int(10) NOT NULL default '0',
  `folder_out` int(10) NOT NULL default '0',
  `added` datetime default NULL,
  `subject` varchar(255) NOT NULL default '(Kein Betreff)',
  `msg` text,
  `unread` enum('yes','no') NOT NULL default 'yes',
  `poster` bigint(20) unsigned NOT NULL default '0',
  `mod_flag` enum('','open','closed') NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `receiver` (`receiver`)
) TYPE=MyISAM;

--
-- Table structure for table `modcomments`
--

CREATE TABLE `modcomments` (
  `added` datetime NOT NULL default '0000-00-00 00:00:00',
  `userid` int(10) unsigned NOT NULL default '0',
  `moduid` int(10) unsigned NOT NULL default '0',
  `txt` text NOT NULL
) TYPE=MyISAM;

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` int(11) NOT NULL default '0',
  `added` datetime NOT NULL default '0000-00-00 00:00:00',
  `title` varchar(255) NOT NULL default '',
  `body` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `added` (`added`)
) TYPE=MyISAM;

--
-- Table structure for table `nowait`
--

CREATE TABLE `nowait` (
  `user_id` int(10) unsigned NOT NULL default '0',
  `torrent_id` int(10) unsigned NOT NULL default '0',
  `status` enum('pending','granted','rejected') NOT NULL default 'pending',
  `grantor` int(10) NOT NULL default '0',
  `msg` text NOT NULL
) TYPE=MyISAM;

--
-- Table structure for table `peers`
--

CREATE TABLE `peers` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `torrent` int(10) unsigned NOT NULL default '0',
  `peer_id` varchar(20) binary NOT NULL default '',
  `ip` varchar(64) NOT NULL default '',
  `port` smallint(5) unsigned NOT NULL default '0',
  `uploaded` bigint(20) unsigned NOT NULL default '0',
  `downloaded` bigint(20) unsigned NOT NULL default '0',
  `to_go` bigint(20) unsigned NOT NULL default '0',
  `seeder` enum('yes','no') NOT NULL default 'no',
  `started` datetime NOT NULL default '0000-00-00 00:00:00',
  `last_action` datetime NOT NULL default '0000-00-00 00:00:00',
  `connectable` enum('yes','no') NOT NULL default 'yes',
  `userid` int(10) unsigned NOT NULL default '0',
  `agent` varchar(60) NOT NULL default '',
  `finishedat` int(10) unsigned NOT NULL default '0',
  `downloadoffset` bigint(20) unsigned NOT NULL default '0',
  `uploadoffset` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `torrent_peer_id` (`torrent`,`peer_id`),
  KEY `torrent` (`torrent`),
  KEY `torrent_seeder` (`torrent`,`seeder`),
  KEY `last_action` (`last_action`),
  KEY `connectable` (`connectable`),
  KEY `userid` (`userid`)
) TYPE=MyISAM;

--
-- Table structure for table `pmfolders`
--

CREATE TABLE `pmfolders` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `parent` int(10) unsigned NOT NULL default '0',
  `owner` int(10) unsigned NOT NULL default '0',
  `name` varchar(128) NOT NULL default '',
  `sortfield` varchar(64) NOT NULL default 'added',
  `sortorder` varchar(4) NOT NULL default 'DESC',
  `prunedays` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

--
-- Table structure for table `pollanswers`
--

CREATE TABLE `pollanswers` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pollid` int(10) unsigned NOT NULL default '0',
  `userid` int(10) unsigned NOT NULL default '0',
  `selection` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `pollid` (`pollid`),
  KEY `selection` (`selection`),
  KEY `userid` (`userid`)
) TYPE=MyISAM;

--
-- Table structure for table `polls`
--

CREATE TABLE `polls` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `added` datetime NOT NULL default '0000-00-00 00:00:00',
  `question` varchar(255) NOT NULL default '',
  `option0` varchar(150) NOT NULL default '',
  `option1` varchar(150) NOT NULL default '',
  `option2` varchar(150) NOT NULL default '',
  `option3` varchar(150) NOT NULL default '',
  `option4` varchar(150) NOT NULL default '',
  `option5` varchar(150) NOT NULL default '',
  `option6` varchar(150) NOT NULL default '',
  `option7` varchar(150) NOT NULL default '',
  `option8` varchar(150) NOT NULL default '',
  `option9` varchar(150) NOT NULL default '',
  `option10` varchar(150) NOT NULL default '',
  `option11` varchar(150) NOT NULL default '',
  `option12` varchar(150) NOT NULL default '',
  `option13` varchar(150) NOT NULL default '',
  `option14` varchar(150) NOT NULL default '',
  `option15` varchar(150) NOT NULL default '',
  `option16` varchar(150) NOT NULL default '',
  `option17` varchar(150) NOT NULL default '',
  `option18` varchar(150) NOT NULL default '',
  `option19` varchar(150) NOT NULL default '',
  `sort` enum('yes','no') NOT NULL default 'yes',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `topicid` int(10) unsigned NOT NULL default '0',
  `userid` int(10) unsigned NOT NULL default '0',
  `added` datetime default NULL,
  `body` text,
  `editedby` int(10) unsigned NOT NULL default '0',
  `editedat` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  KEY `topicid` (`topicid`),
  KEY `userid` (`userid`),
  FULLTEXT KEY `body` (`body`)
) TYPE=MyISAM;

--
-- Table structure for table `ratiostats`
--

CREATE TABLE `ratiostats` (
  `userid` int(10) unsigned NOT NULL default '0',
  `timecode` datetime NOT NULL default '0000-00-00 00:00:00',
  `type` enum('daily','hourly') NOT NULL default 'hourly',
  `downloaded` bigint(20) unsigned NOT NULL default '0',
  `uploaded` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`userid`,`timecode`,`type`)
) TYPE=MyISAM;

--
-- Table structure for table `readposts`
--

CREATE TABLE `readposts` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` int(10) unsigned NOT NULL default '0',
  `topicid` int(10) unsigned NOT NULL default '0',
  `lastpostread` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `userid` (`id`),
  KEY `topicid` (`topicid`)
) TYPE=MyISAM;

--
-- Table structure for table `sitelog`
--

CREATE TABLE `sitelog` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `typ` enum('torrentupload','torrentedit','torrentdelete','promotion','demotion','addwarn','remwarn','accenabled','accdisabled','accdeleted','waitgrant','waitreject','passkeyreset','torrentgranted') NOT NULL default 'torrentupload',
  `added` datetime default NULL,
  `txt` text,
  PRIMARY KEY  (`id`),
  KEY `added` (`added`)
) TYPE=MyISAM;

--
-- Table structure for table `startstoplog`
--

CREATE TABLE `startstoplog` (
  `userid` int(10) NOT NULL default '0',
  `event` enum('start','stop') NOT NULL default 'start',
  `datetime` datetime NOT NULL default '0000-00-00 00:00:00',
  `torrent` int(10) NOT NULL default '0',
  `ip` varchar(15) NOT NULL default '',
  `peerid` varchar(20) NOT NULL default '',
  `useragent` varchar(255) NOT NULL default ''
) TYPE=MyISAM;

--
-- Table structure for table `stylesheets`
--

CREATE TABLE `stylesheets` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `uri` varchar(255) NOT NULL default '',
  `name` varchar(64) NOT NULL default '',
  `default` enum('yes','no') NOT NULL default 'no',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

--
-- Dumping data for table `stylesheets`
--

INSERT INTO `stylesheets` VALUES (2,'coolblue','CoolBlue','yes');
INSERT INTO `stylesheets` VALUES (3,'nightshift','Nightshift','no');
INSERT INTO `stylesheets` VALUES (1,'attraction','Attraction','no');
INSERT INTO `stylesheets` VALUES (4,'luminair','Luminair','no');
INSERT INTO `stylesheets` VALUES (5,'poison','Poison','no');
INSERT INTO `stylesheets` VALUES (6,'the_shining','The Shining','no');

--
-- Table structure for table `test`
--

CREATE TABLE `test` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `type` enum('radio','checkbox') NOT NULL default 'radio',
  `question` text NOT NULL,
  `answers` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

--
-- Dumping data for table `test`
--

INSERT INTO `test` VALUES (1,'checkbox','Was darf ich in meinem BitBucket speichern?','a:6:{i:0;a:3:{s:2:\"id\";i:1;s:6:\"answer\";s:59:\"Filmplakate, CD-Covers und Sample-Screenshots für Releases.\";s:7:\"correct\";i:1;}i:1;a:3:{s:2:\"id\";i:2;s:6:\"answer\";s:93:\"Dateien und Downloads aller Art, falls mein Free-Webspace bei Arcor etc. nicht mehr ausreicht\";s:7:\"correct\";i:0;}i:2;a:3:{s:2:\"id\";i:3;s:6:\"answer\";s:74:\"Nur Bilder, jedoch keine mit pornographischen oder gewalttätigen Inhalten.\";s:7:\"correct\";i:1;}i:3;a:3:{s:2:\"id\";i:4;s:6:\"answer\";s:69:\"Bilder, Videos und MP3s, jedoch nur bis zu einer Dateigröße von 5 MB.\";s:7:\"correct\";i:0;}i:4;a:3:{s:2:\"id\";i:5;s:6:\"answer\";s:48:\"Nur ein Avatar-Bild in der Größe 150x150 Punkte.\";s:7:\"correct\";i:0;}i:5;a:3:{s:2:\"id\";i:6;s:6:\"answer\";s:75:\"Auch Screenshots und Wallpaper, solange diese nicht größer als 256 KB sind.\";s:7:\"correct\";i:1;}}');
INSERT INTO `test` VALUES (2,'checkbox','Welche Client-Ports sind auf diesem Tracker blockiert?','a:6:{i:0;a:3:{s:2:\"id\";i:1;s:6:\"answer\";s:2:\"80\";s:7:\"correct\";i:0;}i:1;a:3:{s:2:\"id\";i:2;s:6:\"answer\";s:4:\"1214\";s:7:\"correct\";i:1;}i:2;a:3:{s:2:\"id\";i:3;s:6:\"answer\";s:4:\"4662\";s:7:\"correct\";i:1;}i:3;a:3:{s:2:\"id\";i:4;s:6:\"answer\";s:9:\"6881-6889\";s:7:\"correct\";i:1;}i:4;a:3:{s:2:\"id\";i:5;s:6:\"answer\";s:5:\"27005\";s:7:\"correct\";i:0;}i:5;a:3:{s:2:\"id\";i:6;s:6:\"answer\";s:11:\"45300-45350\";s:7:\"correct\";i:0;}}');
INSERT INTO `test` VALUES (3,'radio','Welche Voraussetzung muss erfüllt sein, um eine Wartezeitaufhebung erfolgreich beantragen zu können?','a:4:{i:0;a:3:{s:2:\"id\";i:1;s:6:\"answer\";s:49:\"Es sind weniger als drei Stunden Wartezeit übrig.\";s:7:\"correct\";i:0;}i:1;a:3:{s:2:\"id\";i:2;s:6:\"answer\";s:73:\"Der Benutzer muss den festen Willen haben, diese Datei leechen zu wollen.\";s:7:\"correct\";i:0;}i:2;a:3:{s:2:\"id\";i:3;s:6:\"answer\";s:114:\"Der Benutzer muss einen wichtigen Grund haben, z.B. möchte er den Film etc. seiner Mutter zum Geburtstag schenken.\";s:7:\"correct\";i:0;}i:3;a:3:{s:2:\"id\";i:4;s:6:\"answer\";s:61:\"Der Benutzer hat die Datei schon und möchte diese nur seeden.\";s:7:\"correct\";i:1;}}');
INSERT INTO `test` VALUES (4,'radio','Welche Mindestgröße müssen Torrents haben, die auf den Tracker geladen werden?','a:4:{i:0;a:3:{s:2:\"id\";i:1;s:6:\"answer\";s:5:\"10 MB\";s:7:\"correct\";i:0;}i:1;a:3:{s:2:\"id\";i:2;s:6:\"answer\";s:5:\"30 MB\";s:7:\"correct\";i:1;}i:2;a:3:{s:2:\"id\";i:3;s:6:\"answer\";s:6:\"100 MB\";s:7:\"correct\";i:0;}i:3;a:3:{s:2:\"id\";i:4;s:6:\"answer\";s:4:\"1 GB\";s:7:\"correct\";i:0;}}');
INSERT INTO `test` VALUES (5,'radio','Was sollte auf jeden Fall im Torrentnamen stehen?','a:4:{i:0;a:3:{s:2:\"id\";i:1;s:6:\"answer\";s:17:\"Name der Trackers\";s:7:\"correct\";i:0;}i:1;a:3:{s:2:\"id\";i:2;s:6:\"answer\";s:20:\"Sprache des Releases\";s:7:\"correct\";i:1;}i:2;a:3:{s:2:\"id\";i:3;s:6:\"answer\";s:16:\"Name des Seeders\";s:7:\"correct\";i:0;}i:3;a:3:{s:2:\"id\";i:4;s:6:\"answer\";s:17:\"Gruß an die Mutti\";s:7:\"correct\";i:0;}}');
INSERT INTO `test` VALUES (6,'radio','Nach wie vielen Tagen werden inaktive Accounts automatisch gelöscht?','a:4:{i:0;a:3:{s:2:\"id\";i:1;s:6:\"answer\";s:13:\"Nach 30 Tagen\";s:7:\"correct\";i:0;}i:1;a:3:{s:2:\"id\";i:2;s:6:\"answer\";s:13:\"Nach 42 Tagen\";s:7:\"correct\";i:1;}i:2;a:3:{s:2:\"id\";i:3;s:6:\"answer\";s:13:\"Nach 60 Tagen\";s:7:\"correct\";i:0;}i:3;a:3:{s:2:\"id\";i:4;s:6:\"answer\";s:13:\"Nach 72 Tagen\";s:7:\"correct\";i:0;}}');
INSERT INTO `test` VALUES (7,'radio','Welche Datenmenge darf ein Webseeder maximal hochladen?','a:4:{i:0;a:3:{s:2:\"id\";i:1;s:6:\"answer\";s:62:\"Ca. 200% der Torrentgröße, bei hoher Nachfrage ggf. auch mehr.\";s:7:\"correct\";i:1;}i:1;a:3:{s:2:\"id\";i:2;s:6:\"answer\";s:60:\"So viel wie der Server des Webseeders noch Traffic über hat.\";s:7:\"correct\";i:0;}i:2;a:3:{s:2:\"id\";i:3;s:6:\"answer\";s:49:\"Bis der Webseeder auf Platz 1 der Uploader steht.\";s:7:\"correct\";i:0;}i:3;a:3:{s:2:\"id\";i:4;s:6:\"answer\";s:54:\"Auf diesem Tracker muss gar nichts hochgeladen werden.\";s:7:\"correct\";i:0;}}');
INSERT INTO `test` VALUES (8,'radio','Was sollte man tun, wenn man ein Release fertig runtergeladen hat (100%)?','a:4:{i:0;a:3:{s:2:\"id\";i:1;s:6:\"answer\";s:79:\"Die Daten mehrfach auf CD/DVD brennen und auf dem nächsten Flohmarkt verkaufen.\";s:7:\"correct\";i:0;}i:1;a:3:{s:2:\"id\";i:2;s:6:\"answer\";s:83:\"Das Release noch im Client aktiv lassen und bestenfalls nochmal komplett hochladen.\";s:7:\"correct\";i:1;}i:2;a:3:{s:2:\"id\";i:3;s:6:\"answer\";s:75:\"Sofort stoppen, damit man mehr Bandbreite für das nächste Release frei hat.\";s:7:\"correct\";i:0;}i:3;a:3:{s:2:\"id\";i:4;s:6:\"answer\";s:50:\"Das Release sofort auf anderen Trackern hochladen.\";s:7:\"correct\";i:0;}}');
INSERT INTO `test` VALUES (9,'radio','Was kannst Du machen, wenn Du eine Frage hast oder Dir wegen einer Regel unsicher bist?','a:3:{i:0;a:3:{s:2:\"id\";i:1;s:6:\"answer\";s:93:\"Ich melde mich im Board an, und eröffne in möglichst vielen Foren ein thema mit meiner Frage.\";s:7:\"correct\";i:0;}i:1;a:3:{s:2:\"id\";i:2;s:6:\"answer\";s:76:\"Ich benutze den \"IRC-Chat\"-Link im Menü, und frage die Benutzer dort um Rat.\";s:7:\"correct\";i:1;}i:2;a:3:{s:2:\"id\";i:3;s:6:\"answer\";s:68:\"Ich will hier nur Releases leechen, da bleibt für Fragen keine Zeit.\";s:7:\"correct\";i:0;}}');
INSERT INTO `test` VALUES (10,'radio','Was bedeutet \"Anti-Leech-Tracker\"?','a:3:{i:0;a:3:{s:2:\"id\";i:1;s:6:\"answer\";s:115:\"Auf einem Anti-Leech-Tracker kann man Saugen bis die Leitung glüht, da viele Leute mit dicken Leitungen aktiv sind.\";s:7:\"correct\";i:0;}i:1;a:3:{s:2:\"id\";i:2;s:6:\"answer\";s:112:\"Anti-Leech heißt \"Nehmen und Geben\", sprich es müssen auch Daten hochgeladen werden, um dabei bleiben zu dürfen.\";s:7:\"correct\";i:1;}i:2;a:3:{s:2:\"id\";i:3;s:6:\"answer\";s:135:\"Ein \"Anti-Leech-Tracker\" ist ein neues Fahrzeug zur Bekämpfung.von Blutegeln auf Reisfeldern, damit die Arbeiter nicht gebissen werden.\";s:7:\"correct\";i:0;}}');
INSERT INTO `test` VALUES (11,'radio','Was ist eine Ratio?','a:3:{i:0;a:3:{s:2:\"id\";i:1;s:6:\"answer\";s:103:\"Ratio ist eine Abkürzung von Rotation. Je höher die Rotation, desto schlechter ist es für den Benutzer.\";s:7:\"correct\";i:0;}i:1;a:3:{s:2:\"id\";i:2;s:6:\"answer\";s:86:\"Das ist falsch geschrieben und heißt Ration, ein Wort für eine tägliche Portion Essen.\";s:7:\"correct\";i:0;}i:2;a:3:{s:2:\"id\";i:3;s:6:\"answer\";s:112:\"Die Ratio ist das Verhältnis der hochgeladenen Daten zu den heruntergeladenen. Je höher die Ratio, desto besser.\";s:7:\"correct\";i:1;}}');
INSERT INTO `test` VALUES (12,'radio','Was bedeutet es, wenn Du als nicht erreichbar angezeigt wirst?','a:4:{i:0;a:3:{s:2:\"id\";i:1;s:6:\"answer\";s:97:\"Ein SysOp hat versucht, Dich über Deine E-Mail-Adresse zu erreichen, aber das hat nicht geklappt.\";s:7:\"correct\";i:0;}i:1;a:3:{s:2:\"id\";i:2;s:6:\"answer\";s:65:\"Dein (DSL-)Provider hat die Nutzung von BitTorrent eingeschränkt.\";s:7:\"correct\";i:0;}i:2;a:3:{s:2:\"id\";i:3;s:6:\"answer\";s:104:\"Dein Client-Port ist für Rechner im Internet nicht offen, da Du einen Router oder eine Firewall benutzt.\";s:7:\"correct\";i:1;}i:3;a:3:{s:2:\"id\";i:4;s:6:\"answer\";s:77:\"Nicht erreichbar sein ist gut, denn dann kann man Dich nicht zurückverfolgen.\";s:7:\"correct\";i:0;}}');
INSERT INTO `test` VALUES (13,'checkbox','Welche der folgenden Aussagen treffen zu, damit Du Uploader werden kannst und bleibst?','a:7:{i:0;a:3:{s:2:\"id\";i:1;s:6:\"answer\";s:72:\"Ich muss lediglich einen Moderator bitten, mir diesen Rang zu verleihen.\";s:7:\"correct\";i:0;}i:1;a:3:{s:2:\"id\";i:2;s:6:\"answer\";s:48:\"Ich muss eine gute Upload-Geschwindigkeit haben.\";s:7:\"correct\";i:1;}i:2;a:3:{s:2:\"id\";i:3;s:6:\"answer\";s:64:\"Ich muss mindestens 4 Releases innerhalb von 28 Tagen hochladen.\";s:7:\"correct\";i:1;}i:3;a:3:{s:2:\"id\";i:4;s:6:\"answer\";s:66:\"Wenn ich einen Rootserver besitze, werde ich automatisch Uploader.\";s:7:\"correct\";i:0;}i:4;a:3:{s:2:\"id\";i:5;s:6:\"answer\";s:49:\"Ich muss schon mindestens 5 GB hochgeladen haben.\";s:7:\"correct\";i:1;}i:5;a:3:{s:2:\"id\";i:6;s:6:\"answer\";s:35:\"Meine Ratio muss größer als 1 sein.\";s:7:\"correct\";i:1;}i:6;a:3:{s:2:\"id\";i:7;s:6:\"answer\";s:46:\"T-DSL 6000 ist Minimum, um Uploader zu werden.\";s:7:\"correct\";i:0;}}');
INSERT INTO `test` VALUES (14,'radio','Wo kannst Du Dein Avatar-Bild ablegen?','a:3:{i:0;a:3:{s:2:\"id\";i:1;s:6:\"answer\";s:96:\"Ich lege es nirgends ab, sondern verlinke das Bild direkt von der Seite wo ich es gefunden habe.\";s:7:\"correct\";i:0;}i:1;a:3:{s:2:\"id\";i:2;s:6:\"answer\";s:113:\"Ich lade es auf meine private Website, so können andere Benutzer auch direkt über meine Domain sehen wer ich bin.\";s:7:\"correct\";i:0;}i:2;a:3:{s:2:\"id\";i:3;s:6:\"answer\";s:91:\"Ich lade das Bild auf den Tracker-Server, dort wird mir Platz dafür zur Verfügung gestellt.\";s:7:\"correct\";i:1;}}');
INSERT INTO `test` VALUES (15,'radio','Was machst Du, wenn Du eine Verwarnung wegen zu wenig Upload erhalten hast?','a:4:{i:0;a:3:{s:2:\"id\";i:1;s:6:\"answer\";s:59:\"Ich lösche den Account und lege mir einfach einen Neuen an.\";s:7:\"correct\";i:0;}i:1;a:3:{s:2:\"id\";i:2;s:6:\"answer\";s:97:\"Ich versuche, so lange Daten hochzuladen bis das Verhältnis zum Download wieder ausgeglichen ist.\";s:7:\"correct\";i:1;}i:2;a:3:{s:2:\"id\";i:3;s:6:\"answer\";s:70:\"Ich packe meine DoS-Tools aus und lege den Tracker für einen Tag lahm.\";s:7:\"correct\";i:0;}i:3;a:3:{s:2:\"id\";i:4;s:6:\"answer\";s:72:\"Gar nichts, die Verwarnung wird nach einer Woche automatisch aufgehoben.\";s:7:\"correct\";i:0;}}');
INSERT INTO `test` VALUES (16,'radio','Wie viele Torrents darfst Du gleichzeitig leechen?','a:5:{i:0;a:3:{s:2:\"id\";i:1;s:6:\"answer\";s:97:\"Die Menge ist abhängig davon, wie viel ich bislang hochgeladen habe, und wie gut meine Ratio ist.\";s:7:\"correct\";i:1;}i:1;a:3:{s:2:\"id\";i:2;s:6:\"answer\";s:51:\"Ich darf maximal 10 Torrent zeitgleich runterladen.\";s:7:\"correct\";i:0;}i:2;a:3:{s:2:\"id\";i:3;s:6:\"answer\";s:91:\"Wenn ich gerade mindestens einen Torrent hochlade, darf ich in der Zeit keinen runterladen.\";s:7:\"correct\";i:0;}i:3;a:3:{s:2:\"id\";i:4;s:6:\"answer\";s:148:\"Die Menge ist abhängig von meiner Internetgeschwindigkeit. Ein T-DSL 6000 Benutzer darf z.B. 6 Torrents zeitgleich saugen (Bandbreite in KBit/1000).\";s:7:\"correct\";i:0;}i:4;a:3:{s:2:\"id\";i:5;s:6:\"answer\";s:27:\"Es gibt keine Beschränkung.\";s:7:\"correct\";i:0;}}');

--
-- Table structure for table `topics`
--

CREATE TABLE `topics` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` int(10) unsigned NOT NULL default '0',
  `subject` varchar(40) default NULL,
  `locked` enum('yes','no') NOT NULL default 'no',
  `forumid` int(10) unsigned NOT NULL default '0',
  `lastpost` int(10) unsigned NOT NULL default '0',
  `sticky` enum('yes','no') NOT NULL default 'no',
  `views` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `userid` (`userid`),
  KEY `subject` (`subject`),
  KEY `lastpost` (`lastpost`)
) TYPE=MyISAM;

--
-- Table structure for table `torrents`
--

CREATE TABLE `torrents` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `info_hash` varchar(20) binary NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `filename` varchar(255) NOT NULL default '',
  `save_as` varchar(255) NOT NULL default '',
  `search_text` text NOT NULL,
  `descr` text NOT NULL,
  `ori_descr` text NOT NULL,
  `category` int(10) unsigned NOT NULL default '0',
  `size` bigint(20) unsigned NOT NULL default '0',
  `added` datetime NOT NULL default '0000-00-00 00:00:00',
  `type` enum('single','multi') NOT NULL default 'single',
  `numfiles` int(10) unsigned NOT NULL default '0',
  `numpics` int(1) NOT NULL default '0',
  `comments` int(10) unsigned NOT NULL default '0',
  `views` int(10) unsigned NOT NULL default '0',
  `hits` int(10) unsigned NOT NULL default '0',
  `times_completed` int(10) unsigned NOT NULL default '0',
  `leechers` int(10) unsigned NOT NULL default '0',
  `seeders` int(10) unsigned NOT NULL default '0',
  `last_action` datetime NOT NULL default '0000-00-00 00:00:00',
  `visible` enum('yes','no') NOT NULL default 'yes',
  `banned` enum('yes','no') NOT NULL default 'no',
  `activated` enum('yes','no') NOT NULL default 'yes',
  `owner` int(10) unsigned NOT NULL default '0',
  `gu_agent` int(10) unsigned NOT NULL default '0',
  `numratings` int(10) unsigned NOT NULL default '0',
  `ratingsum` int(10) unsigned NOT NULL default '0',
  `nfo` text NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `info_hash` (`info_hash`),
  KEY `owner` (`owner`),
  KEY `visible` (`visible`),
  KEY `category_visible` (`category`,`visible`),
  FULLTEXT KEY `ft_search` (`search_text`,`ori_descr`)
) TYPE=MyISAM;

--
-- Table structure for table `traffic`
--

CREATE TABLE `traffic` (
  `userid` int(11) unsigned NOT NULL default '0',
  `torrentid` int(11) unsigned NOT NULL default '0',
  `downloaded` bigint(20) unsigned NOT NULL default '0',
  `uploaded` bigint(20) unsigned NOT NULL default '0',
  `downloadtime` int(11) unsigned NOT NULL default '0',
  `uploadtime` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`userid`,`torrentid`)
) TYPE=MyISAM;

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `username` varchar(40) NOT NULL default '',
  `old_password` varchar(40) NOT NULL default '',
  `passhash` varchar(32) NOT NULL default '',
  `passkey` tinyblob NOT NULL,
  `secret` tinyblob NOT NULL,
  `email` varchar(80) NOT NULL default '',
  `status` enum('pending','confirmed') NOT NULL default 'confirmed',
  `added` datetime NOT NULL default '0000-00-00 00:00:00',
  `last_login` datetime NOT NULL default '0000-00-00 00:00:00',
  `last_access` datetime NOT NULL default '0000-00-00 00:00:00',
  `editsecret` tinyblob NOT NULL,
  `privacy` enum('strong','normal','low') NOT NULL default 'normal',
  `stylesheet` int(10) default '1',
  `info` text,
  `acceptpms` enum('yes','friends','no') NOT NULL default 'yes',
  `ip` varchar(15) NOT NULL default '',
  `class` tinyint(3) unsigned NOT NULL default '0',
  `avatar` varchar(100) NOT NULL default '',
  `uploaded` bigint(20) unsigned NOT NULL default '0',
  `downloaded` bigint(20) unsigned NOT NULL default '0',
  `title` varchar(30) NOT NULL default '',
  `country` int(10) unsigned NOT NULL default '0',
  `notifs` varchar(200) NOT NULL default '',
  `enabled` enum('yes','no') NOT NULL default 'yes',
  `accept_rules` enum('yes','no') NOT NULL default 'yes',
  `accept_email` enum('yes','friends','no') NOT NULL default 'friends',
  `avatars` enum('yes','no') NOT NULL default 'yes',
  `donor` enum('yes','no') NOT NULL default 'no',
  `oldtorrentlist` enum('yes','no') NOT NULL default 'no',
  `displaysearch` enum('yes','no') NOT NULL default 'yes',
  `warned` enum('yes','no') NOT NULL default 'no',
  `log_ratio` enum('yes','no') NOT NULL default 'no',
  `statbox` enum('top','bottom','hide') NOT NULL default 'top',
  `warneduntil` datetime NOT NULL default '0000-00-00 00:00:00',
  `torrentsperpage` int(3) unsigned NOT NULL default '0',
  `topicsperpage` int(3) unsigned NOT NULL default '0',
  `postsperpage` int(3) unsigned NOT NULL default '0',
  `deletepms` enum('yes','no') NOT NULL default 'yes',
  `savepms` enum('yes','no') NOT NULL default 'no',
  `hideuseruploads` enum('yes','no') NOT NULL default 'no',
  `wgeturl` enum('yes','no') NOT NULL default 'no',
  `showcols` text NOT NULL,
  `tlimitall` int(10) NOT NULL default '0',
  `tlimitseeds` int(10) NOT NULL default '0',
  `tlimitleeches` int(10) NOT NULL default '0',
  `allowupload` enum('yes','no') NOT NULL default 'yes',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `status_added` (`status`,`added`),
  KEY `ip` (`ip`),
  KEY `uploaded` (`uploaded`),
  KEY `downloaded` (`downloaded`),
  KEY `country` (`country`),
  KEY `last_access` (`last_access`),
  KEY `enabled` (`enabled`),
  KEY `warned` (`warned`)
) TYPE=MyISAM;
