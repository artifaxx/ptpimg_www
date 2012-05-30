<?
// ptpimg v2 (circa dec 2011)

// Avoid error reporting
//error_reporting(0);




require 'script_start.php';

require ASSETS.'/class_image.php';

show_header();

print_r($LoggedUser);

// Test the verification against getData()
$Verify = new ImageVerification('/home/ptpimg/public_html/raw/d6rkrm');
if(!$Verify->verify()) {
	// It's not valid, you say?
	// Delete image?
	// unlink($f);
} else {
	// 
	var_dump($Verify->EphemeralData);
}

show_footer();

//$Image = new Image('e8219v');
//$Image = new Image('e8219v');
//$Image->access();
?>