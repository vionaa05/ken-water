<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Services\PointService;
use Barryvdh\DomPDF\Facade\Pdf;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QRMarkupSVG;
use Illuminate\Http\Request;

class MemberCardController extends Controller
{
    public function __construct(private PointService $pointService) {}

    public function show()
    {
        $user = auth()->user();
        $progress = $this->pointService->getProgressToNextLevel($user);
        
        return view('portal.member-card', compact('user', 'progress'));
    }

    public function download()
    {
        $user = auth()->user();
        $progress = $this->pointService->getProgressToNextLevel($user);

        // QR code sebagai base64 SVG
        $options = new QROptions([
            'outputInterface' => QRMarkupSVG::class,
            'eccLevel'        => EccLevel::L,
            'scale'           => 5,
            'outputBase64'    => false,
            'addQuietzone'    => true,
            'quietzoneSize'   => 1,
        ]);
        $qr = new QRCode($options);
        $svgRaw   = $qr->render($user->customer_id);
        $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($svgRaw);

        // Flask SVG icon (sama persis dengan browser) sebagai base64
        $flaskSvg = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>';
        $iconBase64 = 'data:image/svg+xml;base64,' . base64_encode($flaskSvg);

        // CR80: 85.6mm × 54mm = 242.65pt × 153.07pt (ukuran kartu persis)
        $pdf = Pdf::loadView('portal.member-card-pdf', compact('user', 'progress', 'qrBase64', 'iconBase64'))
            ->setPaper([0, 0, 242.65, 153.07])
            ->setOption('isFontSubsettingEnabled', true);

        return $pdf->download("kartu-member-{$user->customer_id}.pdf");
    }
}
