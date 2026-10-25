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

        return view('portal.member-card', compact('user', 'progress', 'qrBase64'));
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

        // Water drop SVG icon (sesuai logo Ken Water mobile)
        $waterDropSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/></svg>';
        $iconBase64 = 'data:image/svg+xml;base64,' . base64_encode($waterDropSvg);

        // CR80: 85.6mm × 54mm = 242.65pt × 153.07pt (ukuran kartu persis)
        $pdf = Pdf::loadView('portal.member-card-pdf', compact('user', 'progress', 'qrBase64', 'iconBase64'))
            ->setPaper([0, 0, 242.65, 153.07])
            ->setOption('isFontSubsettingEnabled', true);

        return $pdf->download("kartu-member-{$user->customer_id}.pdf");
    }
}
