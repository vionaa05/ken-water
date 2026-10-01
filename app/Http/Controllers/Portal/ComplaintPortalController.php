<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use Illuminate\Http\Request;

class ComplaintPortalController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $complaints = $user->complaints()->latest()->paginate(10);
            
        return view('portal.complaints.index', compact('complaints'));
    }

    public function create()
    {
        return view('portal.complaints.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string|max:100',
            'description' => 'required|string|max:1000',
        ]);

        Complaint::create([
            'user_id' => auth()->id(),
            'category' => $request->category,
            'description' => $request->description,
            'status' => 'baru',
        ]);

        return redirect()->route('portal.complaints.index')
            ->with('success', 'Keluhan berhasil dikirim. Tim kami akan segera menindaklanjuti.');
    }

    public function show(Complaint $complaint)
    {
        if ($complaint->user_id !== auth()->id()) {
            abort(403);
        }
        
        return view('portal.complaints.show', compact('complaint'));
    }
}
