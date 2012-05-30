<?
function vx_error() {
	die(json_encode(array(array('status'=>0))));
}
if(!isset($_REQUEST['img']) || empty($_REQUEST['img'])) vx_error(); else $Image=$_REQUEST['img'];
if(!preg_match("%".IMAGE_REGEX."%i", $Image, $Matches)) vx_error(); else $Image=$Matches[0];
$ch=curl_init("http://ptpimg.me/index.php?type=uploadv2&key=QT5LGz7ktGFVZpfFArVHCpEvDcC3qrUZrf0kP&uid=999999&url=c_h_e_c_k_p_o_s_t");

curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "urls=".$Image);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

echo $response;
?>