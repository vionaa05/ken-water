<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Notification;
use App\Services\PointService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private PointService $pointService) {}

    public function index(Request $request)
    {
        $query = Order::with(['user', 'processor'])
            ->where('source', 'online')
            ->latest('order_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%");
            });
        }

        $orders = $query->paginate(20)->withQueryString();

        $statusCounts = [
            'dipesan' => Order::where('source', 'online')->where('status', 'dipesan')->count(),
            'diproses' => Order::where('source', 'online')->where('status', 'diproses')->count(),
            'diantar' => Order::where('source', 'online')->where('status', 'diantar')->count(),
            'siap_diambil' => Order::where('source', 'online')->where('status', 'siap_diambil')->count(),
        ];

        return view('admin.orders.index', compact('orders', 'statusCounts'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'processor']);
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:diproses,diantar,siap_diambil,selesai,dibatalkan',
        ]);

        $oldStatus = $order->status;
        $newStatus = $request->status;

        // Validasi alur status
        $validTransitions = [
            'dipesan' => ['diproses', 'dibatalkan'],
            'diproses' => ['diantar', 'siap_diambil', 'dibatalkan'],
            'diantar' => ['selesai', 'dibatalkan'],
            'siap_diambil' => ['selesai', 'dibatalkan'],
        ];

        if (!isset($validTransitions[$oldStatus]) || !in_array($newStatus, $validTransitions[$oldStatus])) {
            return back()->with('error', 'Perubahan status tidak valid.');
        }

        $updateData = [
            'status' => $newStatus,
            'processed_by' => auth()->id(),
        ];

        if ($newStatus === 'selesai') {
            $updateData['completed_at'] = now();
        }

        $order->update($updateData);

        // Tambah poin saat pesanan selesai (hanya untuk pesanan online)
        if ($newStatus === 'selesai' && $order->source === 'online') {
            $this->pointService->addPoints(
                $order->user,
                $order->gallon_qty,
                "Pesanan #{$order->id} selesai: {$order->gallon_qty} galon",
                $order->id,
                'order'
            );
        }

        // Kirim notifikasi ke pelanggan
        $statusMessages = [
            'diproses' => ['Pesanan Sedang Diproses', 'Pesanan Anda sedang kami siapkan. Mohon tunggu sebentar.'],
            'diantar' => ['Pesanan Sedang Diantar', 'Pesanan Anda sedang dalam perjalanan menuju lokasi Anda.'],
            'siap_diambil' => ['Pesanan Siap Diambil', 'Pesanan Anda sudah siap diambil di depot Ken Water.'],
            'selesai' => ['Pesanan Selesai! 🎉', 'Pesanan Anda telah selesai. Terima kasih sudah berbelanja di Ken Water!'],
            'dibatalkan' => ['Pesanan Dibatalkan', 'Maaf, pesanan Anda telah dibatalkan. Hubungi kami untuk informasi lebih lanjut.'],
        ];

        if (isset($statusMessages[$newStatus])) {
            [$title, $body] = $statusMessages[$newStatus];
            Notification::create([
                'user_id' => $order->user_id,
                'title' => $title,
                'body' => $body,
                'type' => 'order_update',
                'reference_id' => $order->id,
                'reference_type' => 'order',
            ]);
        }

        return back()->with('success', "Status pesanan diperbarui menjadi: {$order->fresh()->status_label}");
    }

    public function createDirect(Request $request, \App\Models\User $customer)
    {
        abort_if($customer->role !== 'pelanggan', 404);
        $pricePerGallon = (float) setting('price_per_gallon', 5000);
        return view('admin.orders.create-direct', compact('customer', 'pricePerGallon'));
    }

    public function storeDirect(Request $request, \App\Models\User $customer)
    {
        // Delegasikan ke CustomerController
        return app(CustomerController::class)->storeDirect($request, $customer);
    }
}
