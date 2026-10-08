<?php
require __DIR__ . '/vendor/autoload.php';

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

$options = new QROptions([
    'outputInterface' => \chillerlan\QRCode\Output\QROutputInterface::MARKUP_SVG,
    'outputBase64'    => true,
    'scale'           => 4,
]);

$qr = (new QRCode($options))->render('KW-9F629D');
echo "QR Length: " . strlen($qr) . "\n";
echo "QR Start: " . substr($qr, 0, 40) . "\n";
