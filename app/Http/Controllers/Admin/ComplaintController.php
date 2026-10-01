<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Notification;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    public function index(Request $request)
    {
        $query = Complaint::with(['user', 'handler'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%");
            })->orWhere('category', 'like', "%{$request->search}%");
        }

        $complaints = $query->paginate(20)->withQueryString();

        $counts = [
            'baru' => Complaint::where('status', 'baru')->count(),
            'diproses' => Complaint::where('status', 'diproses')->count(),
            'selesai' => Complaint::where('status', 'selesai')->count(),
        ];

        return view('admin.complaints.index', compact('complaints', 'counts'));
    }

    public function show(Complaint $complaint)
    {
        $complaint->load(['user', 'handler']);
        return view('admin.complaints.show', compact('complaint'));
    }

    public function update(Request $request, Complaint $complaint)
    {
        $request->validate([
            'status' => 'required|in:diproses,selesai',
            'action_taken' => 'nullable|string|max:1000',
            'response' => 'nullable|string|max:1000',
        ]);

        $updateData = [
            'status' => $request->status,
            'action_taken' => $request->action_taken,
            'response' => $request->response,
            'handled_by' => auth()->id(),
        ];

        if ($request->status === 'selesai') {
            $updateData['resolved_at'] = now();
        }

        $complaint->update($updateData);

        // Kirim notifikasi ke pelanggan
        if ($request->status === 'selesai' && $request->filled('response')) {
            Notification::create([
                'user_id' => $complaint->user_id,
                'title' => 'Keluhan Anda Telah Ditanggapi',
                'body' => "Keluhan tentang \"{$complaint->category}\" telah diselesaikan. Tanggapan: {$request->response}",
                'type' => 'complaint_update',
                'reference_id' => $complaint->id,
                'reference_type' => 'complaint',
            ]);
        } elseif ($request->status === 'diproses') {
            Notification::create([
                'user_id' => $complaint->user_id,
                'title' => 'Keluhan Sedang Ditangani',
                'body' => "Keluhan Anda tentang \"{$complaint->category}\" sedang ditangani oleh tim kami.",
                'type' => 'complaint_update',
                'reference_id' => $complaint->id,
                'reference_type' => 'complaint',
            ]);
        }

        return back()->with('success', 'Status keluhan berhasil diperbarui.');
    }
}
