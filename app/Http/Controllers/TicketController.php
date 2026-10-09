<?php

namespace App\Http\Controllers;

use App\Models\ConcertTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class TicketController extends Controller
{
    private function getEventData(): array
    {
        return [
            'title' => 'NEON ECHOES FESTIVAL 2025',
            'tagline' => 'The Biggest Symphony & Indie Live Music Spectacle',
            'date' => 'Sabtu, 28 Juni 2025',
            'time' => '16:00 - 23:30 WIB (Gate Open: 14:30 WIB)',
            'venue' => 'Stadion Utama Gelora Bung Karno (GBK), Senayan, Jakarta',
            'artists' => [
                'Sheila on 7',
                'Hindia',
                'Reality Club',
                'Coldiac',
                'The Adams',
                'Maliq & D\'Essentials',
            ],
            'tiers' => [
                [
                    'id' => 'vip',
                    'name' => 'VIP Lounge & Pit',
                    'price' => 1750000,
                    'badge' => 'Exclusive',
                    'color' => 'amber',
                    'quota' => 50,
                    'description' => 'Akses paling eksklusif dengan kenyamanan maksimal dan pemandangan panggung terbaik.',
                    'perks' => [
                        'Akses Barisan Depan VIP & Lounge Ber-AC',
                        'Free Flow Drinks & Finger Foods',
                        'Exclusive Merch Pack (T-Shirt, Tote Bag & Lanyard)',
                        'Jalur Antrean Masuk Khusus (Fast Track)',
                        'Akses Toilet VIP Khusus',
                    ],
                ],
                [
                    'id' => 'festival',
                    'name' => 'Festival (Free Standing)',
                    'price' => 950000,
                    'badge' => 'Terpopuler',
                    'color' => 'fuchsia',
                    'quota' => 200,
                    'description' => 'Area berdiri interaktif tepat di depan panggung utama untuk pengalaman konser paling energik.',
                    'perks' => [
                        'Area Berdiri Bebas Menghadap Panggung Utama',
                        'Akses F&B Festival Boulevard',
                        'Wristband Glow-in-the-Dark Eksklusif',
                        'Tata Suara 360° Surround Experience',
                    ],
                ],
                [
                    'id' => 'cat1',
                    'name' => 'CAT 1 (Numbered Seated)',
                    'price' => 650000,
                    'badge' => 'Best View',
                    'color' => 'cyan',
                    'quota' => 120,
                    'description' => 'Tempat duduk bernomor di tribun bawah dengan sudut pandang panggung lurus dan lega.',
                    'perks' => [
                        'Kursi Bernomor Terjamin di Tribun Bawah',
                        'Sudut Pandang Panggung Center & Jelas',
                        'Akses Fasilitas dan F&B Booth Tribun',
                        'Sangat Cocok untuk Pengalaman Menonton Santai',
                    ],
                ],
                [
                    'id' => 'cat2',
                    'name' => 'CAT 2 (Upper Tribune)',
                    'price' => 400000,
                    'badge' => 'Best Value',
                    'color' => 'indigo',
                    'quota' => 250,
                    'description' => 'Tempat duduk tribun atas dengan panorama megah seluruh stadion dan tata cahaya konser.',
                    'perks' => [
                        'Kursi Tribun Atas dengan Pemandangan Penuh Arena',
                        'Akses Pintu Masuk Tribun Atas',
                        'Pengalaman Suara Menggelegar Megah',
                        'Pilihan Terbaik Ramah Kantong',
                    ],
                ],
            ],
            'paymentMethods' => [
                [
                    'id' => 'qris',
                    'name' => 'QRIS (Semua E-Wallet / Mobile Banking)',
                    'icon' => 'QrCode',
                    'badge' => 'Instan & Otomatis',
                    'desc' => 'Scan langsung via GoPay, OVO, Dana, BCA Mobile, Livin, dll.',
                ],
                [
                    'id' => 'bca_va',
                    'name' => 'BCA Virtual Account',
                    'icon' => 'CreditCard',
                    'badge' => 'Verifikasi Otomatis',
                    'desc' => 'Nomor VA dibuat otomatis tanpa perlu konfirmasi manual.',
                ],
                [
                    'id' => 'mandiri_va',
                    'name' => 'Mandiri Virtual Account',
                    'icon' => 'Building2',
                    'badge' => 'Verifikasi Otomatis',
                    'desc' => 'Transfer mudah lewat Livin by Mandiri atau ATM.',
                ],
                [
                    'id' => 'gopay',
                    'name' => 'GoPay / GoPay Later',
                    'icon' => 'Smartphone',
                    'badge' => 'Mudah & Cepat',
                    'desc' => 'Pembayaran instan langsung terhubung ke aplikasi Gojek.',
                ],
            ],
        ];
    }

    public function index(Request $request)
    {
        $eventData = $this->getEventData();
        $totalRegistered = ConcertTicket::count();

        $recentTicket = null;
        if ($request->session()->has('recent_ticket_code')) {
            $recentTicket = ConcertTicket::where('booking_code', $request->session()->get('recent_ticket_code'))->first();
        }

        return Inertia::render('Welcome', [
            'event' => $eventData,
            'totalBookedCount' => $totalRegistered + 418, // base real feel count
            'recentTicket' => $recentTicket,
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'min:3', 'max:100'],
            'email' => ['required', 'email', 'max:100'],
            'phone' => ['required', 'string', 'regex:/^(\+62|62|0)8[1-9][0-9]{6,11}$/'],
            'identity_number' => ['required', 'string', 'digits:16'],
            'ticket_tier' => ['required', 'in:vip,festival,cat1,cat2'],
            'quantity' => ['required', 'integer', 'min:1', 'max:4'],
            'payment_method' => ['required', 'in:qris,bca_va,mandiri_va,gopay'],
            'notes' => ['nullable', 'string', 'max:255'],
            'agree_terms' => ['accepted'],
        ], [
            'full_name.required' => 'Nama lengkap wajib diisi sesuai kartu identitas.',
            'email.required' => 'Alamat email wajib diisi untuk pengiriman e-ticket.',
            'email.email' => 'Format email tidak valid.',
            'phone.required' => 'Nomor WhatsApp / HP wajib diisi.',
            'phone.regex' => 'Format nomor HP tidak valid (contoh: 081234567890).',
            'identity_number.required' => 'Nomor NIK / KTP wajib diisi (16 digit).',
            'identity_number.digits' => 'Nomor NIK harus tepat 16 digit.',
            'ticket_tier.required' => 'Silakan pilih kategori tiket konser.',
            'quantity.required' => 'Tentukan jumlah tiket yang dipesan.',
            'quantity.min' => 'Minimal pembelian 1 tiket.',
            'quantity.max' => 'Maksimal pembelian 4 tiket per identitas.',
            'payment_method.required' => 'Pilih salah satu metode pembayaran.',
            'agree_terms.accepted' => 'Anda harus menyetujui syarat & ketentuan pemesanan tiket.',
        ]);

        $prices = [
            'vip' => 1750000,
            'festival' => 950000,
            'cat1' => 650000,
            'cat2' => 400000,
        ];

        $unitPrice = $prices[$validated['ticket_tier']] ?? 950000;
        $quantity = (int) $validated['quantity'];
        $subtotal = $unitPrice * $quantity;
        $taxAmount = (int) round($subtotal * 0.10); // 10% hiburan
        $adminFee = 5000;
        $totalPrice = $subtotal + $taxAmount + $adminFee;

        // Generate unique booking code
        do {
            $bookingCode = 'NEON-2025-' . strtoupper(Str::random(5));
        } while (ConcertTicket::where('booking_code', $bookingCode)->exists());

        $ticket = ConcertTicket::create([
            'booking_code' => $bookingCode,
            'full_name' => $validated['full_name'],
            'email' => strtolower($validated['email']),
            'phone' => $validated['phone'],
            'identity_number' => $validated['identity_number'],
            'ticket_tier' => $validated['ticket_tier'],
            'ticket_price' => $unitPrice,
            'quantity' => $quantity,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'admin_fee' => $adminFee,
            'total_price' => $totalPrice,
            'payment_method' => $validated['payment_method'],
            'status' => 'CONFIRMED',
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('home')
            ->with('success', 'Pendaftaran tiket konser berhasil! E-Ticket dan kode booking Anda telah diterbitkan.')
            ->with('recent_ticket_code', $ticket->booking_code);
    }

    public function check(Request $request)
    {
        $query = trim($request->input('search', ''));

        if (empty($query)) {
            return response()->json([
                'success' => false,
                'message' => 'Masukkan Kode Booking, NIK, atau Email terdaftar.',
            ], 422);
        }

        $ticket = ConcertTicket::where('booking_code', strtoupper($query))
            ->orWhere('identity_number', $query)
            ->orWhere('email', strtolower($query))
            ->latest()
            ->first();

        if (! $ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Tiket dengan data tersebut tidak ditemukan. Mohon periksa kembali kode booking atau email Anda.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'ticket' => $ticket,
        ]);
    }
}
