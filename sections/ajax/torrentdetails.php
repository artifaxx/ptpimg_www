<?
// Sanity

// since it's an ajax response, we don't call error()
if(!isset($_GET['id']) || !is_number($_GET['id'])) die("403");

$DB->query("SELECT
			t.Screens
			FROM torrents AS t
			WHERE t.ID=".$_GET['id']);
list($Screens)=$DB->next_record();
$Screens=explode("\n",$Screens);
$Regexes[]="/http:\/\/(i\.)?imgur\.com\/[\w]+\.[a-zA-Z]{3,4}/";
$Regexes[]="/http:\/\/ptpimg\.me\/[\w]+\.[a-zA-Z]{3,4}/";
foreach($Screens as $Screen) {
	$Pass=false;
	foreach($Regexes as $Regex) {
		if(preg_match($Regex, $Screen)) $Pass=true;
		else continue;
	}
	if(!$Pass) { echo "Invalid screenshot URL:".$Screen; continue; }
	
	echo "<img src='$Screen' /><br />";
}
?>