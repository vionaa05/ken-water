<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Services\PointService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class CustomerController extends Controller
{
    public function __construct(private PointService $pointService) {}

    public function index(Request $request)
    {
        $query = User::customers()->withCount(['completedOrders as order_count'])
            ->withSum('completedOrders as total_spend', 'total_price');

        // Filter pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('customer_id', 'like', "%{$search}%");
            });
        }

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter level
        if ($request->filled('level')) {
            $query->where('loyalty_level', $request->level);
        }

        $customers = $query->orderByDesc('registered_at')->paginate(20)->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function create()
    {
        return view('admin.customers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20|unique:users,phone',
            'address' => 'required|string|max:255',
            'birth_date' => 'nullable|date|before:today',
            'password' => 'required|string|min:6',
        ]);

        $now = Carbon::now();

        User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'birth_date' => $request->birth_date,
            'password' => Hash::make($request->password),
            'role' => 'pelanggan',
            'customer_id' => User::generateCustomerId(),
            'status' => 'aktif',
            'loyalty_level' => 'bronze',
            'points' => 0,
            'current_period_start' => $now,
            'registered_at' => $now,
        ]);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    public function show(User $customer)
    {
        $this->authorizeCustomer($customer);

        // Riwayat transaksi HANYA periode akun saat ini
        $orders = $customer->orders()
            ->where('period_start', '>=', $customer->current_period_start)
            ->where('status', '!=', 'dibatalkan')
            ->with('processor')
            ->latest('order_date')
            ->paginate(15);

        $recentComplaints = $customer->complaints()->latest()->take(5)->get();
        $pointMutations = $customer->pointMutations()->latest()->take(10)->get();
        $activeVouchers = $customer->userVouchers()->where('status', 'aktif')->with('voucher')->get();
        $activeRedemptions = $customer->rewardRedemptions()->where('status', 'pending')->with('reward')->get();

        $progress = $this->pointService->getProgressToNextLevel($customer);

        return view('admin.customers.show', compact(
            'customer', 'orders', 'recentComplaints', 'pointMutations',
            'activeVouchers', 'activeRedemptions', 'progress'
        ));
    }

    public function edit(User $customer)
    {
        $this->authorizeCustomer($customer);
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, User $customer)
    {
        $this->authorizeCustomer($customer);

        $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20|unique:users,phone,' . $customer->id,
            'address' => 'required|string|max:255',
            'birth_date' => 'nullable|date|before:today',
        ]);

        $customer->update($request->only('name', 'phone', 'address', 'birth_date'));

        return redirect()->route('admin.customers.show', $customer)
            ->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    public function destroy(User $customer)
    {
        $this->authorizeCustomer($customer);
        $customer->delete();
        return redirect()->route('admin.customers.index')
            ->with('success', 'Pelanggan berhasil dihapus.');
    }

    /**
     * Input transaksi langsung oleh staf (pelanggan datang ke depot)
     */
    public function storeDirect(Request $request, User $customer)
    {
        $this->authorizeCustomer($customer);

        if ($customer->status === 'tidak_aktif') {
            return back()->with('error', 'Pelanggan tidak aktif. Tidak bisa melakukan transaksi.');
        }

        $request->validate([
            'gallon_qty' => 'required|integer|min:1|max:100',
            'payment_method' => 'required|in:tunai,transfer',
            'delivery_method' => 'required|in:antar,ambil_sendiri',
        ]);

        $pricePerGallon = (float) setting('price_per_gallon', 5000);
        $gallons = (int) $request->gallon_qty;
        $total = $gallons * $pricePerGallon;

        $order = Order::create([
            'user_id' => $customer->id,
            'order_date' => now(),
            'gallon_qty' => $gallons,
            'unit_price' => $pricePerGallon,
            'total_price' => $total,
            'delivery_method' => $request->delivery_method,
            'delivery_address' => $request->delivery_address,
            'payment_method' => $request->payment_method,
            'notes' => $request->notes,
            'status' => 'selesai',
            'source' => 'langsung',
            'period_start' => $customer->current_period_start,
            'processed_by' => auth()->id(),
            'completed_at' => now(),
        ]);

        // Tambah poin langsung (transaksi langsung menambah poin saat disimpan)
        $this->pointService->addPoints(
            $customer,
            $gallons,
            "Transaksi langsung: {$gallons} galon",
            $order->id,
            'order'
        );

        return redirect()->route('admin.customers.show', $customer)
            ->with('success', "Transaksi berhasil dicatat. {$gallons} galon, total Rp " . number_format($total, 0, ',', '.'));
    }

    /**
     * Cetak/unduh kartu loyalitas PDF
     */
    public function printCard(User $customer)
    {
        $this->authorizeCustomer($customer);
        $progress = $this->pointService->getProgressToNextLevel($customer);

        $pdf = Pdf::loadView('admin.customers.card-pdf', compact('customer', 'progress'))
            ->setPaper([0, 0, 241.89, 153.07]); // ukuran kartu standar 85.6mm x 54mm

        return $pdf->download("kartu-member-{$customer->customer_id}.pdf");
    }

    private function authorizeCustomer(User $customer): void
    {
        abort_if($customer->role !== 'pelanggan', 404);
    }
}
