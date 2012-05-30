<?
require SERVER_ROOT.'/classes/class_image.php';
$Type=0;
//print_r($_SERVER);
//if(isset($_SERVER['HTTP_X_FILENAME']) && !empty($_SERVER['HTTP_X_FILENAME'])) $Type=1;

if($Type==0) {
	if(isset($_GET['type']) && !empty($_GET['type'])) $Type=$_GET['type'];
	else die("not a valid type");
} else {
	// yeah?
}

switch($Type) {
	case 1:
		$Upload = new ImageUpload(1);
		$Upload->auth("iSQGkh6VJjAtkMjcDQysTPXOUGxiHutVYBw71");
		if($Upload->populate()) {
			print_r($Upload->Results);
		} else {
			print_r($Upload);
			die("Upload failed!");
		}
	break;

	case 2:
		$Upload = new ImageUpload(2);
		$Upload->auth("iSQGkh6VJjAtkMjcDQysTPXOUGxiHutVYBw71");
		$Upload->setSpecial($_FILES['fileselect']);
		if($Upload->populate()) {
			print_r($Upload->Results);
		} else {
			print_r($Upload);
			die("Upload failed!");
			
		}
		
	break;
	
	case 3:
		$Upload = new ImageUpload(3);
		$Upload->auth("iSQGkh6VJjAtkMjcDQysTPXOUGxiHutVYBw71");
		$Urls=array("http://ptpimg.me/830y01.jpg",
				"http://ptpimg.me/lo73m5.jpg",
				"http://ptpimg.me/89fbx9.jpg");
		$Upload->setSpecial($Urls);
		if($Upload->populate()) {
			print_r($Upload->Results);
		} else {
			print_r($Upload);
			die("Upload failed!");
		}
	break;
}
?>