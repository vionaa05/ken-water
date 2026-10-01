<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Services\PointService;
use Barryvdh\DomPDF\Facade\Pdf;
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

        $pdf = Pdf::loadView('admin.customers.card-pdf', compact('user', 'progress'))
            ->setPaper([0, 0, 241.89, 153.07]); // ukuran kartu standar 85.6mm x 54mm

        return $pdf->download("kartu-member-{$user->customer_id}.pdf");
    }
}
