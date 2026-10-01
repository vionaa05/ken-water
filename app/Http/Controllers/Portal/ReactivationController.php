<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Services\ReactivationService;
use Illuminate\Http\Request;

class ReactivationController extends Controller
{
    public function __construct(private ReactivationService $reactivationService) {}

    public function show()
    {
        $user = auth()->user();
        
        if ($user->status !== 'tidak_aktif') {
            return redirect()->route('portal.home');
        }
        
        return view('portal.reactivate');
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'agreement' => 'accepted'
        ], [
            'agreement.accepted' => 'Anda harus menyetujui syarat & ketentuan untuk melakukan reaktivasi.'
        ]);

        $user = auth()->user();
        $result = $this->reactivationService->reactivate($user);

        if ($result['success']) {
            return redirect()->route('portal.home')->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }
}
