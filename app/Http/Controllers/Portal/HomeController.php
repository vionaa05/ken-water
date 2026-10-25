<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Services\PointService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct(private PointService $pointService) {}

    public function index()
    {
        $user = auth()->user();
        
        $activeVouchersCount = $user->userVouchers()->where('status', 'aktif')->count();
        $personalPromosCount = $user->personalPromos()->where('is_used', false)->where('is_expired', false)->count();
        
        $progress = $this->pointService->getProgressToNextLevel($user);

        // QR Code untuk preview kartu member digital
        $options = new \chillerlan\QRCode\QROptions([
            'outputInterface' => \chillerlan\QRCode\Output\QRMarkupSVG::class,
            'eccLevel'        => \chillerlan\QRCode\Common\EccLevel::L,
            'scale'           => 5,
            'outputBase64'    => false,
            'addQuietzone'    => true,
            'quietzoneSize'   => 1,
        ]);
        $qr = new \chillerlan\QRCode\QRCode($options);
        $svgRaw   = $qr->render($user->customer_id);
        $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($svgRaw);

        // Cek jika akun inactive
        if ($user->status === 'tidak_aktif') {
            return view('portal.home-inactive', compact('user', 'progress', 'qrBase64'));
        }

        return view('portal.home', compact('user', 'activeVouchersCount', 'personalPromosCount', 'progress', 'qrBase64'));
    }
}
