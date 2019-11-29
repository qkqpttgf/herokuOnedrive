<?php
include "phpqrcode.php";

$filename = 'QR-'.date("Ymd-His").'.png';
$errorCorrectionLevel = 'L';
$matrixPointSize = 2;
if($_GET["f"]==1){
QRcode::png($_GET["key"], $filename, $errorCorrectionLevel, $matrixPointSize, 1);
}
//QRcode::png($data);
QRcode::png($_GET["key"], false, $errorCorrectionLevel, $matrixPointSize, 1);

/*
$value=$_GET["key"];
$logo = 'favicon.png';
$QR = "base.png";
$last = "last.png";
$errorCorrectionLevel = 'L';
$matrixPointSize = 10;
QRcode::png($value, $QR, $errorCorrectionLevel, $matrixPointSize, 2);
if($logo !== FALSE){
    $QR = imagecreatefromstring(file_get_contents($QR));
    $logo = imagecreatefromstring(file_get_contents($logo));
    $QR_width = imagesx($QR);
    $QR_height = imagesy($QR);
    $logo_width = imagesx($logo);
    $logo_height = imagesy($logo);
    $logo_qr_width = $QR_width / 5;
    $scale = $logo_width / $logo_qr_width;
    $logo_qr_height = $logo_height / $scale;
    $from_width = ($QR_width - $logo_qr_width) / 2;
    imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
}
imagepng($QR,$last);
echo "<img src=last.png>";
*/
?>
