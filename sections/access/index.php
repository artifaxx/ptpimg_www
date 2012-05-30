<?
if(!isset($_GET['code']) || empty($_GET['code'])) die("404");
require SERVER_ROOT.'/classes/class_image.php';
$Image = new Image($_GET['code']);
//echo "Yeah";
//var_dump($Image->populate());
$Image->access(); //flush the contents to the screen

?>