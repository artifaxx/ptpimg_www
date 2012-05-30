<?
$DT = new DateTime();
$DT->setTimestamp(time());
if(array_key_exists($_REQUEST['country'],$Countries)) {
        $Country=db_string($_REQUEST['country']);
} else {
        $Country='';
?>
<select name="timezone" id="timezone">
	<option value="none" selected>None / Anonymous</option>
	<option value="none" disabled>Select a country</option>
</select>
<?
die();
}
?>
<select name="timezone" id="timezone">
	<option value="none">None / Anonymous</option>
<? 
	$Master=get_timezones($Country);
	foreach($Master as $Continent) {
		foreach($Continent as $TZ => $City) {
			if((count($Master)==1 && count($Continent)==1)) $Select=true;
			$DT->setTimezone(new DateTimeZone($TZ)); ?>
	<option value="<?=$TZ?>" <?=($Select)?' selected':''?>><?=$TZ?> <?=$DT->format('H:i (O)')?></option>
<? }
	} ?>
</select>