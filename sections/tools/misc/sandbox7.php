<?
						$html='<option value="60s">60s</option> 
						<option value="70s">70s</option> 
						<option value="80s">80s</option> 
						<option value="90s">90s</option> 
						<option value="alternative">alternative</option> 
						<option value="ambient">ambient</option> 
						<option value="apps.mac">apps.mac</option> 
						<option value="apps.sound">apps.sound</option> 
						<option value="apps.windows">apps.windows</option> 
						<option value="audio.books">audio.books</option> 
						<option value="bluegrass">bluegrass</option> 
						<option value="blues">blues</option> 
						<option value="breaks">breaks</option> 
						<option value="classical">classical</option> 
						<option value="comedy">comedy</option> 
						<option value="comics">comics</option> 
						<option value="country">country</option> 
						<option value="dance">dance</option> 
						<option value="drum.and.bass">drum.and.bass</option> 
						<option value="ebooks.fiction">ebooks.fiction</option> 
						<option value="ebooks.non.fiction">ebooks.non.fiction</option> 
						<option value="elearning.videos">elearning.videos</option> 
						<option value="electronic">electronic</option> 
						<option value="emo">emo</option> 
						<option value="experimental">experimental</option> 
						<option value="folk">folk</option> 
						<option value="funk">funk</option> 
						<option value="garage">garage</option> 
						<option value="grunge">grunge</option> 
						<option value="hardcore">hardcore</option> 
						<option value="hardcore.dance">hardcore.dance</option> 
						<option value="hip.hop">hip.hop</option> 
						<option value="house">house</option> 
						<option value="idm">idm</option> 
						<option value="indie">indie</option> 
						<option value="industrial">industrial</option> 
						<option value="jazz">jazz</option> 
						<option value="jpop">jpop</option> 
						<option value="metal">metal</option> 
						<option value="new.age">new.age</option> 
						<option value="pop">pop</option> 
						<option value="post.rock">post.rock</option> 
						<option value="progressive.rock">progressive.rock</option> 
						<option value="psychedelic">psychedelic</option> 
						<option value="psytrance">psytrance</option> 
						<option value="punk">punk</option> 
						<option value="reggae">reggae</option> 
						<option value="rhythm.and.blues">rhythm.and.blues</option> 
						<option value="rock">rock</option> 
						<option value="sheet.music">sheet.music</option> 
						<option value="ska">ska</option> 
						<option value="soul">soul</option> 
						<option value="techno">techno</option> 
						<option value="trance">trance</option> 
						<option value="trip.hop">trip.hop</option> 
						<option value="uk.garage">uk.garage</option> 
						<option value="vanity.house">vanity.house</option> 
						<option value="world.music">world.music</option> ';
preg_match_all('/">([a-z\.]+)</',$html,$tags);
for($i=1;$i<count($tags[1]);$i++) {
	$Name=$tags[1][$i];
	$DB->query("insert into tags(name,tagtype,userid) values('".$Name."','genre',1) on duplicate key update tagtype='genre'");
}
show_footer();
?>