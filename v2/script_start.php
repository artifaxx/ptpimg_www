<?
// script_start.php
// This is where everything begins. This isn't meant to be some sort of complex gazelle-inspired system, trust me!
//
// We will prepare the includes, instantiate objects, and handle sessions
//
// Let's go!

// Config
require("/home/ptpimg/config.dat"); // Constants, these aren't pushed to the public

// Regexes (thanks to project-gazelle for these)
define('RESOURCE_REGEX','(https?|ftps?):\/\/');
define('IP_REGEX','(\d{1,3}\.){3}\d{1,3}');
define('DOMAIN_REGEX','(ssl.)?(www.)?[a-z0-9-\.]{1,255}\.[a-zA-Z]{2,6}');
define('PORT_REGEX', '\d{1,5}');
define('URL_REGEX','('.RESOURCE_REGEX.')('.IP_REGEX.'|'.DOMAIN_REGEX.')(:'.PORT_REGEX.')?(\/\S*)*');
define('EMAIL_REGEX','[_a-z0-9-]+([.+][_a-z0-9-]+)*@'.DOMAIN_REGEX);
define('IMAGE_REGEX', URL_REGEX.'\/\S+\.(jpg|jpeg|tif|tiff|png|gif|bmp)(\?\S*)?');

// Dev server:
define('IMG_DIR', '/mnt/ptpimg'); // nfs share @ 192.168.0.23
define('TMP_DIR', '/tmp'); // we don't use a ramdisk on the dev server
// Live server:
//define('IMG_DIR', '/home/ptpimg/public_html/raw'); // Raw image dir
//define('TMP_DIR', '/tmpfs'); // Temporary files are thrown in here

define('TMP_PREFIX', TMP_DIR.'/ptpimg_'); // uniqid() appended to this, used for temporary storage (md5+verify)

// The array keys must match up with the Type field in the uploads table
// 0 is a case of an unknown image type, so you can do whatever you want there
$MimeType=array(0=>'application/octet-stream', 1=>'image/gif', 2=>'image/jpeg', 3=>'image/png');

$ScriptStartTime=microtime(true); //To track how long a page takes to create

ob_start(); //Start a buffer, mainly in case there is a mysql error

// Include our dependencies
require(ASSETS.'/class_debug.php'); //Require the debug class
require(ASSETS."/class_misc.php");
require(ASSETS."/class_mysql.php");
require(ASSETS."/class_cache.php");
require(ASSETS."/class_encrypt.php");
require(ASSETS."/class_useragent.php");
require(ASSETS."/class_time.php");
$DB=NEW DB_MYSQL;
$Cache=NEW CACHE;
$Enc=NEW CRYPT;
$UA=NEW USER_AGENT;

$Debug = new DEBUG;
$Debug->handle_errors();
$Debug->set_flag('Debug constructed');


// throw_error(code/message) - any sort of error will go here
// Technically we shouldn't print all the data from these objects.
// The DB object has all the database credentials
function throw_error($c, $Sneaky=false) {
	echo $c;
	if($Sneaky) {
		global $DB, $Cache;
		print_r($DB);
		print_r($Cache);
	}
}

// API Keys; ptpimg+api@nervex.net
$ApiKeys=array(
				"QT5LGz7ktGFVZpfFArVHCpEvDcC3qrUZrf0kP", // Generic
				"iSQGkh6VJjAtkMjcDQysTPXOUGxiHutVYBw71" // Tdmaker
				);
				

$Browser = $UA->browser($_SERVER['HTTP_USER_AGENT']);
$OperatingSystem = $UA->operating_system($_SERVER['HTTP_USER_AGENT']);
//$Mobile = $UA->mobile($_SERVER['HTTP_USER_AGENT']);


// Get permissions
list($Classes, $ClassLevels) = $Cache->get_value('classes');
if(!$Classes || !$ClassLevels) {
	$DB->query('SELECT ID, Name, Level FROM permissions ORDER BY Level');
	$Classes = $DB->to_array('ID');
	$ClassLevels = $DB->to_array('Level');
	$Cache->cache_value('classes', array($Classes, $ClassLevels), 0);
}

if (isset($_COOKIE['session'])) { $LoginCookie=$Enc->decrypt($_COOKIE['session']); }
if(isset($LoginCookie)) {
	list($SessionID, $LoggedUser['ID'])=explode("|~|",$Enc->decrypt($LoginCookie));
	$LoggedUser['ID'] = (int)$LoggedUser['ID'];

	$UserID=$LoggedUser['ID']; //TODO: UserID should not be LoggedUser

	if (!$LoggedUser['ID'] || !$SessionID) {
		logout();
	}
	
	$UserSessions = $Cache->get_value('users_sessions_'.$UserID);
	if(!is_array($UserSessions)) {
		$DB->query("SELECT
			SessionID,
			Browser,
			OperatingSystem,
			IP,
			LastUpdate
			FROM users_sessions
			WHERE UserID='$UserID'
			AND Active = 1
			ORDER BY LastUpdate DESC");
		$UserSessions = $DB->to_array('SessionID',MYSQLI_ASSOC);
		$Cache->cache_value('users_sessions_'.$UserID, $UserSessions, 0);
	}

	if (!array_key_exists($SessionID,$UserSessions)) {
		logout();
	}
	
	// Check if user is enabled
	$Enabled = $Cache->get_value('enabled_'.$LoggedUser['ID']);
	if($Enabled === false) {
		$DB->query("SELECT Enabled FROM users_main WHERE ID='$LoggedUser[ID]'");
		list($Enabled)=$DB->next_record();
		$Cache->cache_value('enabled_'.$LoggedUser['ID'], $Enabled, 0);
	}
	if ($Enabled==2) {
		
		logout();
	}

	

	// Get info such as username
	$LightInfo = user_info($LoggedUser['ID']);
	$HeavyInfo = user_heavy_info($LoggedUser['ID']);

	// Get user permissions
	$Permissions = get_permissions($LightInfo['PermissionID']);

	// Create LoggedUser array
	$LoggedUser = array_merge($HeavyInfo, $LightInfo, $Permissions);

	if(!isset($LoggedUser['ID'])) {
		$Debug->log_var($LightInfo, 'LightInfo');
		$Debug->log_var($HeavyInfo, 'HeavyInfo');
		$Debug->log_var($Permissions, 'Permissions');
	}

	//Load in the permissions
	$LoggedUser['Permissions'] = get_permissions_for_user($LoggedUser['ID'], $LoggedUser['CustomPermissions']);
	
	//Change necessary triggers in external components
	$Cache->CanClear = check_perms('admin_clear_cache');
	
	// Because we <3 our staff
	if (check_perms('site_disable_ip_history')) { $_SERVER['REMOTE_ADDR'] = '127.0.0.1'; }

	// Update LastUpdate every 10 minutes
	if(strtotime($UserSessions[$SessionID]['LastUpdate'])+600<time()) {
		$DB->query("UPDATE users_main SET LastAccess='".sqltime()."' WHERE ID='$LoggedUser[ID]'");
		$DB->query("UPDATE users_sessions SET IP='".$_SERVER['REMOTE_ADDR']."', Browser='".$Browser."', OperatingSystem='".$OperatingSystem."', LastUpdate='".sqltime()."' WHERE UserID='$LoggedUser[ID]' AND SessionID='".db_string($SessionID)."'");
		$Cache->begin_transaction('users_sessions_'.$UserID);
		$Cache->delete_row($SessionID);
		$Cache->insert_front($SessionID,array(
				'SessionID'=>$SessionID,
				'Browser'=>$Browser,
				'OperatingSystem'=>$OperatingSystem,
				'IP'=>$_SERVER['REMOTE_ADDR'],
				'LastUpdate'=>sqltime()
				));
		$Cache->commit_transaction(0);
	}
	
	// IP changed
	if($LoggedUser['IP'] != $_SERVER['REMOTE_ADDR'] && !check_perms('site_disable_ip_history')) {
		if(site_ban_ip($_SERVER['REMOTE_ADDR'])) {
			error('Your IP has been banned.');
		}

		$CurIP = db_string($LoggedUser['IP']);
		$NewIP = db_string($_SERVER['REMOTE_ADDR']);
		$DB->query("UPDATE users_history_ips SET
				EndTime='".sqltime()."'
				WHERE EndTime IS NULL
				AND UserID='$LoggedUser[ID]'
				AND IP='$CurIP'");
		$DB->query("INSERT IGNORE INTO users_history_ips
				(UserID, IP, StartTime) VALUES
				('$LoggedUser[ID]', '$NewIP', '".sqltime()."')");

		$ipcc = geoip($NewIP);
		$DB->query("UPDATE users_main SET IP='$NewIP', ipcc='".$ipcc."' WHERE ID='$LoggedUser[ID]'");
		$Cache->begin_transaction('user_info_heavy_'.$LoggedUser['ID']);
		$Cache->update_row(false, array('IP' => $_SERVER['REMOTE_ADDR']));
		$Cache->commit_transaction(0);


		// ASN/Country changed?
		$Attributes = get_asn($_SERVER['REMOTE_ADDR']);
		$ASN=$Attributes['asnum'];
		$Country=$Attributes['country'];
		$CIDR=$Attributes['cidr'];

		if(!empty($ASN) && $LoggedUser['ASN']!=$ASN) {
				$CurASN = db_string($LoggedUser['ASN']);
				$NewASN = db_string($ASN);

/*                        if (!empty($CurASN) && !empty($NewASN)) {
						send_irc("privmsg #watched :!mod ASN change? $CurASN -> $NewASN | http://musiceye.tv/user.php?id=$LoggedUser[ID] (".$LoggedUser['Username'].")");
				}*/

				$DB->query("UPDATE users_history_asns SET
								EndTime='".sqltime()."'
								WHERE EndTime IS NULL
								AND UserID='$LoggedUser[ID]'
								AND ASN='$CurASN'");

				$DB->query("INSERT IGNORE INTO users_history_asns
								(UserID, ASN, StartTime) VALUES
								('$LoggedUser[ID]', '$NewASN', '".sqltime()."')");

				$DB->query("UPDATE users_main SET ASN='$NewASN' WHERE ID='$LoggedUser[ID]'");
				$Cache->begin_transaction('user_info_heavy_'.$LoggedUser['ID']);
				$Cache->update_row(false, array('ASN' => $NewASN));
				$Cache->commit_transaction(0);
		}
		if (!empty($Country) && $LoggedUser['Country']!=$Country) {
				$CurCountry = db_string($LoggedUser['Country']);
				$NewCountry = db_string($Country);

				if (!empty($CurCountry) && !empty($NewCountry)) {
						send_irc("privmsg #watched :!mod Country change? $CurCountry -> $NewCountry | http://musiceye.tv/user.php?id=$LoggedUser[ID] (".$LoggedUser['Username'].")");
				}

				$DB->query("UPDATE users_history_country SET
								EndTime='".sqltime()."'
								WHERE EndTime IS NULL
								AND UserID='$LoggedUser[ID]'
								AND CountryCode='$CurCountry'");

				$DB->query("INSERT IGNORE INTO users_history_country
								(UserID, CountryCode, StartTime) VALUES
								('$LoggedUser[ID]', '$NewCountry', '".sqltime()."')");

				$DB->query("UPDATE users_main SET Country='$NewCountry' WHERE ID='$LoggedUser[ID]'");
				$Cache->begin_transaction('user_info_heavy_'.$LoggedUser['ID']);
				$Cache->update_row(false, array('Country' => $NewCountry));
				$Cache->commit_transaction(0);
		}
		if (!empty($Cidr) && $LoggedUser['Cidr']!=$CIDR || !net_match($CIDR,$LoggedUser['IP'])) {
				$CurCidr = db_string($LoggedUser['CIDR']);
				$NewCidr = db_string($CIDR);

				if (!empty($CurCidr) && !empty($NewCidr)) {
						send_irc("privmsg #watched :!mod Cidr change? $CurCidr -> $NewCidr | http://musiceye.tv/user.php?id=$LoggedUser[ID] (".$LoggedUser['Username'].")");
				}
				$DB->query("UPDATE users_history_cidr SET
								EndTime='".sqltime()."'
								WHERE EndTime IS NULL
								AND UserID='$LoggedUser[ID]'
								AND Cidr='$CurCidr'");

				$DB->query("INSERT IGNORE INTO users_history_cidr
								(UserID, Cidr, StartTime) VALUES
								('$LoggedUser[ID]', '$NewCidr', '".sqltime()."')");

				$DB->query("UPDATE users_main SET Cidr='$NewCidr' WHERE ID='$LoggedUser[ID]'");
				$Cache->begin_transaction('user_info_heavy_'.$LoggedUser['ID']);
				$Cache->update_row(false, array('Cidr' => $NewCidr));
				$Cache->commit_transaction(0);
		}

	}
	
	
	
	// Get stylesheets
	$Stylesheets = $Cache->get_value('stylesheets');
	if (!is_array($Stylesheets)) {
		$DB->query('SELECT ID, LOWER(REPLACE(Name," ","_")) AS Name, Name AS ProperName FROM stylesheets');
		$Stylesheets = $DB->to_array('ID', MYSQLI_BOTH);
		$Cache->cache_value('stylesheets', $Stylesheets, 600);
	}

	//A9 TODO: Clean up this messy solution
	$LoggedUser['StyleName']=$Stylesheets[$LoggedUser['StyleID']]['Name'];
	
	if(empty($LoggedUser['Username'])) {
		logout(); // Ghost
	}
}
function check_perms($PermissionName,$MinClass = 0) {
	global $LoggedUser;
	return (isset($LoggedUser['Permissions'][$PermissionName]) && $LoggedUser['Permissions'][$PermissionName] && $LoggedUser['Class']>=$MinClass)?true:false;
}
function user_heavy_info($UserID) {
	global $DB, $Cache;
	$HeavyInfo = $Cache->get_value('user_info_heavy_'.$UserID);

	if(empty($HeavyInfo)) {

		$DB->query("SELECT
			m.Invites,
			m.IP,
			m.CustomPermissions,
			i.AuthKey,
			i.StyleID,
			i.StyleURL,
			i.DisableInvites,
			i.SiteOptions
			FROM users_main AS m
			INNER JOIN users_info AS i ON i.UserID=m.ID
			WHERE m.ID='$UserID'");
		$HeavyInfo = $DB->next_record(MYSQLI_ASSOC, array('CustomPermissions', 'SiteOptions'));

		if (!empty($HeavyInfo['CustomPermissions'])) {
			$HeavyInfo['CustomPermissions'] = unserialize($HeavyInfo['CustomPermissions']);
		} else {
			$HeavyInfo['CustomPermissions'] = array();
		}

		$HeavyInfo['SiteOptions'] = unserialize($HeavyInfo['SiteOptions']);
		if(!empty($HeavyInfo['SiteOptions'])) {
			$HeavyInfo = array_merge($HeavyInfo, $HeavyInfo['SiteOptions']);
		}
		unset($HeavyInfo['SiteOptions']);

		$Cache->cache_value('user_info_heavy_'.$UserID, $HeavyInfo, 0);
	}
	return $HeavyInfo;
}
function get_size($Size, $Levels = 2) {
        $Units = array(' B',' KB',' MB',' GB',' TB',' PB',' EB',' ZB',' YB');
        $Size = (double) $Size;
        for($Steps = 0; abs($Size) >= 1024; $Size /= 1024, $Steps++) {}
        if(func_num_args() == 1 && $Steps >= 4) {
                $Levels++;
        }
        return number_format($Size,$Levels).$Units[$Steps];
}

function get_bytes($Size) {
        list($Value,$Unit) = sscanf($Size, "%f%s");
        $Unit = ltrim($Unit);
        if(empty($Unit)) {
                return $Value ? round($Value) : 0;
        }
        switch(strtolower($Unit[0])) {
                case 'k': return round($Value * 1024);
                case 'm': return round($Value * 1048576);
                case 'g': return round($Value * 1073741824);
                case 't': return round($Value * 1099511627776);
                default: return 0;
        }
}

function user_info($UserID) {
	global $DB, $Cache;
	$UserInfo = $Cache->get_value('user_info_'.$UserID);
	// the !isset($UserInfo['Paranoia']) can be removed after a transition period
	if(empty($UserInfo) || empty($UserInfo['ID']) || !isset($UserInfo['Paranoia'])) {


		$DB->query("SELECT
			m.ID,
			m.Username,
			m.PermissionID,
			i.Artist,
			i.Donor,
			i.Warned,
			i.Avatar,
			m.Enabled,
			i.CatchupTime
			FROM users_main AS m
			INNER JOIN users_info AS i ON i.UserID=m.ID
			WHERE m.ID='$UserID'");
		if($DB->record_count() == 0) { // Deleted user, maybe?
			$UserInfo = array('ID'=>'','Username'=>'','PermissionID'=>0,'Artist'=>false,'Donor'=>false,'Warned'=>'0000-00-00 00:00:00','Avatar'=>'','Enabled'=>0,'Title'=>'', 'CatchupTime'=>0, 'Visible'=>'1');

		} else {
			$UserInfo = $DB->next_record(MYSQLI_ASSOC, array('Paranoia', 'Title'));
			$UserInfo['CatchupTime'] = strtotime($UserInfo['CatchupTime']);
			$UserInfo['Paranoia'] = unserialize($UserInfo['Paranoia']);
			if($UserInfo['Paranoia'] === false) {
				$UserInfo['Paranoia'] = array();
			}
		}
		$Cache->cache_value('user_info_'.$UserID, $UserInfo, 2592000);
	}
	if(strtotime($UserInfo['Warned']) < time()) {
		$UserInfo['Warned'] = '0000-00-00 00:00:00';
		$Cache->cache_value('user_info_'.$UserID, $UserInfo, 2592000);
	}
	
	// Image proxy
	if(check_perms('site_proxy_images') && !empty($UserInfo['Avatar'])) {
		$UserInfo['Avatar'] = 'http'.($SSL?'s':'').'://'.SITE_URL.'/image.php?c=1&amp;avatar='.$UserID.'&amp;i='.urlencode($UserInfo['Avatar']);
	}
	return $UserInfo;
}
function update_site_options($UserID, $NewOptions) {
	if(!is_number($UserID)) {
		error(0);
	}
	if(empty($NewOptions)) {
		return false;
	}
	global $DB, $Cache, $LoggedUser;

	// Get SiteOptions
	$DB->query("SELECT SiteOptions FROM users_info WHERE UserID = $UserID");
	list($SiteOptions) = $DB->next_record(MYSQLI_NUM,false);
	$SiteOptions = unserialize($SiteOptions);

	// Get HeavyInfo
	$HeavyInfo = user_heavy_info($UserID);

	// Insert new/replace old options
	$SiteOptions = array_merge($SiteOptions, $NewOptions);
	$HeavyInfo = array_merge($HeavyInfo, $NewOptions);

	// Update DB
	$DB->query("UPDATE users_info SET SiteOptions = '".db_string(serialize($SiteOptions))."' WHERE UserID = $UserID");

	// Update cache
	$Cache->cache_value('user_info_heavy_'.$UserID, $HeavyInfo, 0);

	// Update $LoggedUser if the options are changed for the current
	if($LoggedUser['ID'] == $UserID) {
		$LoggedUser = array_merge($LoggedUser, $NewOptions);
		$LoggedUser['ID'] = $UserID; // We don't want to allow userid switching
	}
}

function get_permissions($PermissionID) {
	global $DB, $Cache;
	$Permission = $Cache->get_value('perm_'.$PermissionID);
	if(empty($Permission)) {
		$DB->query("SELECT p.Level AS Class, p.Values as Permissions FROM permissions AS p WHERE ID='$PermissionID'");
		$Permission = $DB->next_record(MYSQLI_ASSOC, array('Permissions'));
		$Permission['Permissions'] = unserialize($Permission['Permissions']);
		$Cache->cache_value('perm_'.$PermissionID, $Permission, 2592000);
	}
	return $Permission;
}

function get_permissions_for_user($UserID, $CustomPermissions = false) {
	global $DB;

	$UserInfo = user_info($UserID);
	
	if ($CustomPermissions === false) {
		$DB->query('SELECT um.CustomPermissions FROM users_main AS um WHERE um.ID = '.((int)$UserID));
	
		list($CustomPermissions) = $DB->next_record(MYSQLI_NUM, false);
	}
	
	if (!empty($CustomPermissions) && !is_array($CustomPermissions)) {
		$CustomPermissions = unserialize($CustomPermissions);
	}

	$Permissions = get_permissions($UserInfo['PermissionID']);
	

	// Manage 'special' inherited permissions
	if($UserInfo['Artist']) {
		$ArtistPerms = get_permissions(ARTIST);
	} else {
		$ArtistPerms = array('Permissions' => array());
	}

	if($UserInfo['Donor']) {
		$DonorPerms = get_permissions(DONOR);
	} else {
		$DonorPerms = array('Permissions' => array());
	}

	if(!empty($CustomPermissions)) {
		$CustomPerms = $CustomPermissions;
	} else {
		$CustomPerms = array();
	}

	//Combine the permissions
	return array_merge($Permissions['Permissions'], $DonorPerms['Permissions'], $ArtistPerms['Permissions'], $CustomPerms, array('MaxCollages' => $MaxCollages));
}

// This function is slow. Don't call it unless somebody's logging in.
function site_ban_ip($IP) {
	global $DB, $Cache;
	$IPNum = ip2unsigned($IP);
	$IPBans = $Cache->get_value('ip_bans');
	if(!is_array($IPBans)) {
		$DB->query("SELECT ID, FromIP, ToIP FROM ip_bans");
		$IPBans = $DB->to_array(0, MYSQLI_NUM);
		$Cache->cache_value('ip_bans', $IPBans, 0);
	}
	foreach($IPBans as $Index => $IPBan) {
		list($ID, $FromIP, $ToIP) = $IPBan;
		if($IPNum >= $FromIP && $IPNum <= $ToIP) {
			return true;
		}
	}

	return false;
}

function send_email($To,$Subject,$Body,$From='noreply',$ContentType='text/plain') {
	$Headers='MIME-Version: 1.0'."\r\n";
	$Headers.='Content-type: '.$ContentType.'; charset=iso-8859-1'."\r\n";
	$Headers.='From: '.SITE_NAME.' <'.$From.'@'.NONSSL_SITE_URL.'>'."\r\n";
	$Headers.='Reply-To: '.$From.'@'.NONSSL_SITE_URL."\r\n";
	$Headers.='X-Mailer: Project Gazelle'."\r\n";
	$Headers.='Message-Id: <'.make_secret().'@'.NONSSL_SITE_URL.">\r\n";
	$Headers.='X-Priority: 3'."\r\n";
	mail($To,$Subject,$Body,$Headers,"-f ".$From."@".NONSSL_SITE_URL);
}
function make_secret($Length = 32) {
	$Secret = '';
	$Chars='abcdefghijklmnopqrstuvwxyz0123456789';
	for($i=0; $i<$Length; $i++) {
		$Rand = mt_rand(0, strlen($Chars)-1);
		$Secret .= substr($Chars, $Rand, 1);
	}
	return str_shuffle($Secret);
}

//TODO: Read and add this one
/*
function make_secret($Length = 32) {
	$Secret = '';
	$Chars='abcdefghijklmnopqrstuvwxyz0123456789';
	$CharLen = strlen($Chars)-1;
	for ($i = 0; $i < $Length; ++$i) {
		$Secret .= $Chars[mt_rand(0, $CharLen)];
	}
	return $Secret;
}
*/

// Password hashes, feel free to make your own algorithm here
function make_hash($Str,$Secret) {
	return sha1(md5($Secret).$Str.sha1($Secret).SITE_SALT);
}


function ip2unsigned($IP) {
	return sprintf("%u", ip2long($IP));
}

// Geolocate an IP address. Two functions - a database one, and a dns one.
function geoip($IP) {
	static $IPs = array();
	if (isset($IPs[$IP])) {
		return $IPs[$IP];
	}
	$Long = ip2unsigned($IP);
	if(!$Long || $Long == 2130706433) { // No need to check cc for 127.0.0.1
		return false;
	}
	global $DB;
	$DB->query("SELECT EndIP,Code FROM geoip_country WHERE $Long >= StartIP ORDER BY StartIP DESC LIMIT 1");
	if((!list($EndIP,$Country) = $DB->next_record()) || $EndIP < $Long) {
		$Country = '?';
	}
	$IPs[$IP] = $Country;
	return $Country;
}

function old_geoip($IP) {
	static $Countries = array();
	if(empty($Countries[$IP])) {
		$Country = 0;
		// Reverse IP, so 127.0.0.1 becomes 1.0.0.127
		$ReverseIP = implode('.', array_reverse(explode('.', $IP)));
		$TestHost = $ReverseIP.'.country.netop.org';
		$Return = dns_get_record($TestHost, DNS_TXT);
		if (!empty($Return)) {
			$Country = $Return[0]['txt'];
		}
		if(!$Country) {
			$Return = gethostbyaddr($IP);
			$Return = explode('.',$Return);
			$Return = array_pop($Return);
			if(strlen($Return) == 2 && !is_number($Return)) {
				$Country = strtoupper($Return);
			} else {
				$Country = '?';
			}
		}
		if($Country == 'UK') { $Country = 'GB'; }
		$Countries[$IP] = $Country;
	}
	return $Countries[$IP];
}

function gethostbyip($ip)
{
	$testar = explode('.',$ip);
	if (count($testar)!=4) { 
		return $ip;
	}
	for ($i=0;$i<4;++$i) {
		if (!is_numeric($testar[$i])) {
			return $ip;
		}
	}
	
	$host = `host -W 1 $ip`;
	return (($host ? end ( explode (' ', $host)) : $ip));
}


function get_host($IP) {
	static $ID = 0;
	++$ID;
	return '<span id="host_'.$ID.'">Resolving host...<script type="text/javascript">ajax.get(\'tools.php?action=get_host&ip='.$IP.'\',function(host){$(\'#host_'.$ID.'\').raw().innerHTML=host;});</script></span>';
}

function lookup_ip($IP) {
	//TODO: use the $Cache
	$Output = explode(' ',shell_exec('host -W 1 '.escapeshellarg($IP)));
	if(count($Output) == 1 && empty($Output[0])) {
		//No output at all implies the command failed
		return '';
	}

	if(count($Output) != 5) {
		return false;
	} else {
		return $Output[4];
	}
}

function get_cc($IP) {
	static $ID = 0;
	++$ID;
	return '<span id="cc_'.$ID.'">Resolving CC...<script type="text/javascript">ajax.get(\'tools.php?action=get_cc&ip='.$IP.'\',function(cc){$(\'#cc_'.$ID.'\').raw().innerHTML=cc;});</script></span>';
}

function display_ip($IP) {
	$Line = display_str($IP).' ('.get_cc($IP).') ';
	$Line .= '[<a href="user.php?action=search&amp;ip_history=on&amp;ip='.display_str($IP).'&amp;matchtype=strict" title="Search">S</a>]';
	
	return $Line;
}

function logout() {
	global $SessionID, $LoggedUser, $DB, $Cache;
	setcookie('session','',time()-60*60*24*365,'/','',false);
	setcookie('keeplogged','',time()-60*60*24*365,'/','',false);
	setcookie('session','',time()-60*60*24*365,'/','',false);
	if($SessionID) {
		
		
		$DB->query("DELETE FROM users_sessions WHERE UserID='$LoggedUser[ID]' AND SessionID='".db_string($SessionID)."'");
		
		$Cache->begin_transaction('users_sessions_'.$LoggedUser['ID']);
		$Cache->delete_row($SessionID);
		$Cache->commit_transaction(0);
	}
	$Cache->delete_value('user_info_'.$LoggedUser['ID']);
	$Cache->delete_value('user_info_heavy_'.$LoggedUser['ID']);

	header('Location: login.php');
	
	die();
}

function enforce_login() {
	global $SessionID, $LoggedUser;
	if (!$SessionID || !$LoggedUser) {
		setcookie('redirect',$_SERVER['REQUEST_URI'],time()+60*30,'/','',false);
		logout();
	}
}

// Make sure $_GET['auth'] is the same as the user's authorization key
// Should be used for any user action that relies solely on GET.
function authorize($Ajax = false) {
	global $LoggedUser;
	if(empty($_REQUEST['auth']) || $_REQUEST['auth'] != $LoggedUser['AuthKey']) {
		send_irc("PRIVMSG ".LAB_CHAN." :".$LoggedUser['Username']." just failed authorize on ".$_SERVER['REQUEST_URI']." coming from ".$_SERVER['HTTP_REFERER']);
		error('Invalid authorization key. Go back, refresh, and try again.', $Ajax);
		return false;
	}
	return true;
}
function show_header($PageTitle='',$JSIncludes='') {
	global $Document, $Cache, $DB, $LoggedUser, $Mobile, $Classes;

	if($PageTitle!='') { $PageTitle.=' :: '; }
	$PageTitle .= SITE_NAME;

	if(!is_array($LoggedUser)) {
		require(ASSETS.'/publicheader.php');
	} else {
		require(ASSETS.'/privateheader.php');
	}
}

/*-- show_footer function ------------------------------------------------*/
/*------------------------------------------------------------------------*/
/* This function is to include the footer file on a page.				 */
/* $Options is an optional array that you can pass information to the	 */
/*  header through as well as setup certain limitations				   */
/*  Here is a list of parameters that work in the $Options array:		 */
/*  ['disclaimer']	= [boolean]		Displays the disclaimer in the footer */
/*								  Default is false					  */
/**************************************************************************/
function show_footer($Options=array()) {
	global $ScriptStartTime, $LoggedUser, $Cache, $DB, $SessionID, $UserSessions, $Debug, $Time;
	if (!is_array($LoggedUser)) { require(ASSETS.'/publicfooter.php'); }
	else { require(ASSETS.'/privatefooter.php'); }
}

// Detect what page this is:
$Static=(strpos($_SERVER['REQUEST_URI'],".php")==(strlen($_SERVER['REQUEST_URI'])-4))?false:true;

// Required in absence of session_start()
if(!$Static) {
	header('Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0');
	header('Pragma: no-cache');
} // else, we'll let the cache-control be controlled by the response handler

//Flush to user
ob_end_flush();

$Debug->set_flag('set headers and send to user');


//Attribute profiling
$Debug->profile();


?>