<?php
require 'vendor/autoload.php';

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QRMarkupSVG;

$options = new QROptions([
    'outputInterface' => QRMarkupSVG::class,
    'eccLevel'        => EccLevel::L,
    'scale'           => 6,
    'outputBase64'    => false,
]);
$qr = new QRCode($options);
$svgRaw = $qr->render('KW-9F629D');
$b64 = 'data:image/svg+xml;base64,' . base64_encode($svgRaw);
echo substr($b64, 0, 80) . '...' . PHP_EOL;
echo 'QR OK, length=' . strlen($b64) . PHP_EOL;
