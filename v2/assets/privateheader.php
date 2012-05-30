<?

define('FOOTER_FILE', SERVER_ROOT.'/design/privatefooter.php');
$HTTPS = ($_SERVER['SERVER_PORT'] == 443) ? 'ssl_' : '';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title><?=display_str($PageTitle)?></title>
	<meta http-equiv="X-UA-Compatible" content="chrome=1;IE=edge" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="shortcut icon" href="favicon.ico" />
	<link rel="apple-touch-icon" href="/apple-touch-icon.png" />
	<link rel="search" type="application/opensearchdescription+xml" title="<?=SITE_NAME?> Artists" href="opensearch.php?type=artists" />
	<link rel="search" type="application/opensearchdescription+xml" title="<?=SITE_NAME?> Torrents" href="opensearch.php?type=torrents" />
	<link rel="search" type="application/opensearchdescription+xml" title="<?=SITE_NAME?> Requests" href="opensearch.php?type=requests" />
	<link rel="search" type="application/opensearchdescription+xml" title="<?=SITE_NAME?> Forums" href="opensearch.php?type=forums" />
	<link rel="search" type="application/opensearchdescription+xml" title="<?=SITE_NAME?> Log" href="opensearch.php?type=log" />
	<link rel="search" type="application/opensearchdescription+xml" title="<?=SITE_NAME?> Users" href="opensearch.php?type=users" />
	<link rel="search" type="application/opensearchdescription+xml" title="<?=SITE_NAME?> Wiki" href="opensearch.php?type=wiki" />
	<link rel="alternate" type="application/rss+xml" href="feeds.php?feed=feed_news&amp;user=<?=$LoggedUser['ID']?>&amp;auth=<?=$LoggedUser['RSS_Auth']?>&amp;passkey=<?=$LoggedUser['torrent_pass']?>&amp;authkey=<?=$LoggedUser['AuthKey']?>" title="<?=SITE_NAME?> - News" />
	<link rel="alternate" type="application/rss+xml" href="feeds.php?feed=feed_blog&amp;user=<?=$LoggedUser['ID']?>&amp;auth=<?=$LoggedUser['RSS_Auth']?>&amp;passkey=<?=$LoggedUser['torrent_pass']?>&amp;authkey=<?=$LoggedUser['AuthKey']?>" title="<?=SITE_NAME?> - Blog" />
	<link rel="alternate" type="application/rss+xml" href="feeds.php?feed=torrents_notify_<?=$LoggedUser['torrent_pass']?>&amp;user=<?=$LoggedUser['ID']?>&amp;auth=<?=$LoggedUser['RSS_Auth']?>&amp;passkey=<?=$LoggedUser['torrent_pass']?>&amp;authkey=<?=$LoggedUser['AuthKey']?>" title="<?=SITE_NAME?> - P.T.N." />
<? if(isset($LoggedUser['Notify'])) {
	foreach($LoggedUser['Notify'] as $Filter) {
		list($FilterID, $FilterName) = $Filter;
?>
	<link rel="alternate" type="application/rss+xml" href="feeds.php?feed=torrents_notify_<?=$FilterID?>_<?=$LoggedUser['torrent_pass']?>&amp;user=<?=$LoggedUser['ID']?>&amp;auth=<?=$LoggedUser['RSS_Auth']?>&amp;passkey=<?=$LoggedUser['torrent_pass']?>&amp;authkey=<?=$LoggedUser['AuthKey']?>&amp;name=<?=urlencode($FilterName)?>" title="<?=SITE_NAME?> - <?=display_str($FilterName)?>" />
<? 	}
}?>
	<link rel="alternate" type="application/rss+xml" href="feeds.php?feed=torrents_all&amp;user=<?=$LoggedUser['ID']?>&amp;auth=<?=$LoggedUser['RSS_Auth']?>&amp;passkey=<?=$LoggedUser['torrent_pass']?>&amp;authkey=<?=$LoggedUser['AuthKey']?>" title="<?=SITE_NAME?> - All Torrents" />
	<link rel="alternate" type="application/rss+xml" href="feeds.php?feed=torrents_music&amp;user=<?=$LoggedUser['ID']?>&amp;auth=<?=$LoggedUser['RSS_Auth']?>&amp;passkey=<?=$LoggedUser['torrent_pass']?>&amp;authkey=<?=$LoggedUser['AuthKey']?>" title="<?=SITE_NAME?> - Music Torrents" />
	<link rel="alternate" type="application/rss+xml" href="feeds.php?feed=torrents_apps&amp;user=<?=$LoggedUser['ID']?>&amp;auth=<?=$LoggedUser['RSS_Auth']?>&amp;passkey=<?=$LoggedUser['torrent_pass']?>&amp;authkey=<?=$LoggedUser['AuthKey']?>" title="<?=SITE_NAME?> - Application Torrents" />
	<link rel="alternate" type="application/rss+xml" href="feeds.php?feed=torrents_ebooks&amp;user=<?=$LoggedUser['ID']?>&amp;auth=<?=$LoggedUser['RSS_Auth']?>&amp;passkey=<?=$LoggedUser['torrent_pass']?>&amp;authkey=<?=$LoggedUser['AuthKey']?>" title="<?=SITE_NAME?> - E-Book Torrents" />
	<link rel="alternate" type="application/rss+xml" href="feeds.php?feed=torrents_abooks&amp;user=<?=$LoggedUser['ID']?>&amp;auth=<?=$LoggedUser['RSS_Auth']?>&amp;passkey=<?=$LoggedUser['torrent_pass']?>&amp;authkey=<?=$LoggedUser['AuthKey']?>" title="<?=SITE_NAME?> - Audiobooks Torrents" />
	<link rel="alternate" type="application/rss+xml" href="feeds.php?feed=torrents_evids&amp;user=<?=$LoggedUser['ID']?>&amp;auth=<?=$LoggedUser['RSS_Auth']?>&amp;passkey=<?=$LoggedUser['torrent_pass']?>&amp;authkey=<?=$LoggedUser['AuthKey']?>" title="<?=SITE_NAME?> - E-Learning Video Torrents" />
	<link rel="alternate" type="application/rss+xml" href="feeds.php?feed=torrents_comedy&amp;user=<?=$LoggedUser['ID']?>&amp;auth=<?=$LoggedUser['RSS_Auth']?>&amp;passkey=<?=$LoggedUser['torrent_pass']?>&amp;authkey=<?=$LoggedUser['AuthKey']?>" title="<?=SITE_NAME?> - Comedy Torrents" />
	<link rel="alternate" type="application/rss+xml" href="feeds.php?feed=torrents_comics&amp;user=<?=$LoggedUser['ID']?>&amp;auth=<?=$LoggedUser['RSS_Auth']?>&amp;passkey=<?=$LoggedUser['torrent_pass']?>&amp;authkey=<?=$LoggedUser['AuthKey']?>" title="<?=SITE_NAME?> - Comic Torrents" />
	<link rel="alternate" type="application/rss+xml" href="feeds.php?feed=torrents_mp3&amp;user=<?=$LoggedUser['ID']?>&amp;auth=<?=$LoggedUser['RSS_Auth']?>&amp;passkey=<?=$LoggedUser['torrent_pass']?>&amp;authkey=<?=$LoggedUser['AuthKey']?>" title="<?=SITE_NAME?> - MP3 Torrents" />
	<link rel="alternate" type="application/rss+xml" href="feeds.php?feed=torrents_flac&amp;user=<?=$LoggedUser['ID']?>&amp;auth=<?=$LoggedUser['RSS_Auth']?>&amp;passkey=<?=$LoggedUser['torrent_pass']?>&amp;authkey=<?=$LoggedUser['AuthKey']?>" title="<?=SITE_NAME?> - FLAC Torrents" />
	<link rel="alternate" type="application/rss+xml" href="feeds.php?feed=torrents_vinyl&amp;user=<?=$LoggedUser['ID']?>&amp;auth=<?=$LoggedUser['RSS_Auth']?>&amp;passkey=<?=$LoggedUser['torrent_pass']?>&amp;authkey=<?=$LoggedUser['AuthKey']?>" title="<?=SITE_NAME?> - Vinyl Sourced Torrents" />
	<link rel="alternate" type="application/rss+xml" href="feeds.php?feed=torrents_lossless&amp;user=<?=$LoggedUser['ID']?>&amp;auth=<?=$LoggedUser['RSS_Auth']?>&amp;passkey=<?=$LoggedUser['torrent_pass']?>&amp;authkey=<?=$LoggedUser['AuthKey']?>" title="<?=SITE_NAME?> - Lossless Torrents" />
	<link rel="alternate" type="application/rss+xml" href="feeds.php?feed=torrents_lossless24&amp;user=<?=$LoggedUser['ID']?>&amp;auth=<?=$LoggedUser['RSS_Auth']?>&amp;passkey=<?=$LoggedUser['torrent_pass']?>&amp;authkey=<?=$LoggedUser['AuthKey']?>" title="<?=SITE_NAME?> - 24bit Lossless Torrents" />

	<link href="<?=STATIC_SERVER?>styles/global.css?v=<?=filemtime(SERVER_ROOT.'/static/styles/global.css')?>" rel="stylesheet" type="text/css" />
<? if ($Mobile) { ?>
	<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0, user-scalable=no;"/>
	<link href="<?=STATIC_SERVER ?>styles/mobile/style.css" rel="stylesheet" type="text/css" />
<? } else { ?>
	<? if (empty($LoggedUser['StyleURL'])) { ?>
	<link href="<?=STATIC_SERVER?>styles/<?=$LoggedUser['StyleName']?>/style.css?v=<?=filemtime(SERVER_ROOT.'/static/styles/'.$LoggedUser['StyleName'].'/style.css')?>" title="<?=$LoggedUser['StyleName']?>" rel="stylesheet" type="text/css" media="screen" />
	<? } else { ?>
	<link href="<?=$LoggedUser['StyleURL']?>" title="External CSS" rel="stylesheet" type="text/css" media="screen" />
	<? } ?>
<? } ?>

	<script src="<?=STATIC_SERVER?>functions/sizzle.js" type="text/javascript"></script>
	<script src="<?=STATIC_SERVER?>functions/script_start.js?v=<?=filemtime(SERVER_ROOT.'/static/functions/script_start.js')?>" type="text/javascript"></script>
	<script src="<?=STATIC_SERVER?>functions/class_ajax.js?v=<?=filemtime(SERVER_ROOT.'/static/functions/class_ajax.js')?>" type="text/javascript"></script>
	<script type="text/javascript">//<![CDATA[
		var authkey = "<?=$LoggedUser['AuthKey']?>";
		var userid = <?=$LoggedUser['ID']?>;
	//]]></script>
	<script src="<?=STATIC_SERVER?>functions/global.js?v=<?=filemtime(SERVER_ROOT.'/static/functions/global.js')?>" type="text/javascript"></script>
<?

$Scripts=explode(',',$JSIncludes);

foreach ($Scripts as $Script) {
	if (empty($Script)) { continue; }
?>
	<script src="<?=STATIC_SERVER?>functions/<?=$Script?>.js?v=<?=filemtime(SERVER_ROOT.'/static/functions/'.$Script.'.js')?>" type="text/javascript"></script>
<?
 	if ($Script == 'jquery') { ?>
<script type="text/javascript">
  $.noConflict();
</script>
<?
	} ?>
<?
}
if ($Mobile) { ?>
	<script src="<?=STATIC_SERVER?>styles/mobile/style.js" type="text/javascript"></script>
<?
}

?>
</head>
<body id="<?=$Document == 'collages' ? 'collage' : $Document?>" <?= ((!$Mobile && $LoggedUser['Rippy'] == 'On') ? 'onload="say()"' : '') ?>>
<div id="wrapper">
<h1 class="hidden"><?=SITE_NAME?></h1>

<div id="header">
	<div id="logo"><a href="index.php"></a></div>
	<div id="userinfo">
		<ul id="userinfo_username">
			<li id="nav_userinfo"><a href="user.php?id=<?=$LoggedUser['ID']?>" class="username"><?=$LoggedUser['Username']?></a></li>
			<li id="nav_useredit" class="brackets"><a href="user.php?action=edit&amp;userid=<?=$LoggedUser['ID']?>">Edit</a></li>
			<li id="nav_logout" class="brackets"><a href="logout.php?auth=<?=$LoggedUser['AuthKey']?>">Logout</a></li>
		</ul>
		<ul id="userinfo_major">
			<li id="nav_upload" class="brackets"><a href="upload.php">Upload</a></li>
<?
if(check_perms('site_send_unlimited_invites')) {
	$Invites = ' (∞)';
} elseif ($LoggedUser['Invites']>0) {
	$Invites = ' ('.$LoggedUser['Invites'].')';
} else {
	$Invites = '';
}
?>
			<li id="nav_invite" class="brackets"><a href="user.php?action=invite">Invite<?=$Invites?></a></li>
		</ul>
	</div>
	<div id="menu">
		<h4 class="hidden">Site Menu</h4>
		<ul>
			<li id="nav_index"><a href="index.php">Home</a></li>
			<li id="nav_torrents"><a href="torrents.php">Torrents</a></li>
			<li id="nav_gallery"><a href="gallery.php">Gallery</a></li>
			<li id="nav_staff"><a href="staff.php">Staff</a></li>
		</ul>
	</div>
</div>
<div id="content">
