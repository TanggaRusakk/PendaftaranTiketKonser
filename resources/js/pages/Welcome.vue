<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import {
    AlertCircle,
    Calendar,
    Check,
    CheckCircle2,
    Clock,
    Copy,
    CreditCard,
    Flame,
    HelpCircle,
    IdCard,
    Info,
    Mail,
    MapPin,
    Music,
    Phone,
    Printer,
    QrCode,
    Search,
    ShieldCheck,
    Sparkles,
    Ticket,
    User,
    X,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface TicketTier {
    id: string;
    name: string;
    price: number;
    badge: string;
    color: string;
    quota: number;
    description: string;
    perks: string[];
}

interface PaymentMethod {
    id: string;
    name: string;
    badge: string;
    desc: string;
}

interface EventData {
    title: string;
    tagline: string;
    date: string;
    time: string;
    venue: string;
    artists: string[];
    tiers: TicketTier[];
    paymentMethods: PaymentMethod[];
}

interface BookedTicket {
    id: number;
    booking_code: string;
    full_name: string;
    email: string;
    phone: string;
    identity_number: string;
    ticket_tier: string;
    ticket_price: number;
    quantity: number;
    subtotal: number;
    tax_amount: number;
    admin_fee: number;
    total_price: number;
    payment_method: string;
    status: string;
    notes?: string;
    created_at?: string;
}

const props = defineProps<{
    event: EventData;
    totalBookedCount: number;
    recentTicket?: BookedTicket | null;
    flash?: {
        success?: string;
        error?: string;
    };
}>();

// Form Registration state
const form = useForm({
    full_name: '',
    email: '',
    phone: '',
    identity_number: '',
    ticket_tier: 'festival',
    quantity: 1,
    payment_method: 'qris',
    notes: '',
    agree_terms: false,
});

// Selected Tier computed
const selectedTier = computed(() => {
    return props.event.tiers.find((t) => t.id === form.ticket_tier) || props.event.tiers[0];
});

// Calculation computed
const unitPrice = computed(() => selectedTier.value?.price || 0);
const subtotal = computed(() => unitPrice.value * form.quantity);
const taxAmount = computed(() => Math.round(subtotal.value * 0.1));
const adminFee = 5000;
const totalPrice = computed(() => subtotal.value + taxAmount.value + adminFee);

// Formatter for Rupiah
const formatRupiah = (val: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val);
};

// Modal Receipt / E-Ticket State
const activeTicket = ref<BookedTicket | null>(props.recentTicket || null);
const isReceiptModalOpen = ref(!!props.recentTicket);
const isCopied = ref(false);

const openReceipt = (ticket: BookedTicket) => {
    activeTicket.value = ticket;
    isReceiptModalOpen.value = true;
};

const closeReceipt = () => {
    isReceiptModalOpen.value = false;
};

const copyBookingCode = (code: string) => {
    navigator.clipboard.writeText(code);
    isCopied.value = true;
    setTimeout(() => {
        isCopied.value = false;
    }, 2000);
};

const printTicket = () => {
    window.print();
};

// Search / Check Ticket state
const searchQuery = ref('');
const isSearching = ref(false);
const searchError = ref('');
const checkModalOpen = ref(false);

const searchTicket = async () => {
    if (!searchQuery.value.trim()) {
        searchError.value = 'Silakan masukkan Kode Booking, Email, atau NIK.';
        return;
    }
    isSearching.value = true;
    searchError.value = '';

    try {
        const response = await fetch(`/ticket/check?search=${encodeURIComponent(searchQuery.value.trim())}`);
        const data = await response.json();

        if (response.ok && data.success) {
            checkModalOpen.value = false;
            openReceipt(data.ticket);
            searchQuery.value = '';
        } else {
            searchError.value = data.message || 'Tiket tidak ditemukan. Periksa kembali data Anda.';
        }
    } catch {
        searchError.value = 'Terjadi kesalahan saat memeriksa tiket. Coba beberapa saat lagi.';
    } finally {
        isSearching.value = false;
    }
};

// Submit handler
const submitForm = () => {
    form.post('/ticket/register', {
        preserveScroll: true,
        onSuccess: (page) => {
            const returnedTicket = (page.props as any).recentTicket;
            if (returnedTicket) {
                openReceipt(returnedTicket);
            }
            form.reset('full_name', 'email', 'phone', 'identity_number', 'notes', 'agree_terms');
            form.quantity = 1;
        },
    });
};

// FAQ accordion state
const openFaq = ref<number | null>(0);
const toggleFaq = (index: number) => {
    openFaq.value = openFaq.value === index ? null : index;
};

const faqs = [
    {
        q: 'Bagaimana cara menukarkan E-Ticket menjadi Wristband fisik?',
        a: 'Penukaran wristband dapat dilakukan H-2 hingga hari H konser di Ticket Booth Stadion Utama GBK dengan menunjukkan E-Ticket (QR Code) dan KTP/kartu identitas asli sesuai data pemesan.',
    },
    {
        q: 'Apakah satu identitas bisa membeli lebih dari satu tiket?',
        a: 'Ya, Anda dapat membeli hingga maksimal 4 tiket dalam satu kali transaksi. Pastikan nama pemesan utama sesuai dengan KTP yang digunakan saat registrasi.',
    },
    {
        q: 'Apakah tiket yang sudah dibeli dapat dibatalkan atau di-refund?',
        a: 'Tiket yang telah dibeli bersifat final dan tidak dapat di-refund, kecuali acara dibatalkan secara sepihak oleh penyelenggara sesuai syarat & ketentuan resmi.',
    },
    {
        q: 'Berapa batasan usia minimal penonton konser ini?',
        a: 'Penonton minimal berusia 12 tahun. Penonton di bawah 17 tahun wajib didampingi oleh orang tua atau wali dewasa yang juga memiliki tiket resmi.',
    },
];

const scrollToSection = (id: string) => {
    const el = document.getElementById(id);
    if (el) {
        el.scrollIntoView({ behavior: 'smooth' });
    }
};
</script>

<template>
    <Head :title="event.title + ' - Pendaftaran Tiket Konser'">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    </Head>

    <div class="min-h-screen bg-[#070913] text-slate-100 selection:bg-fuchsia-500 selection:text-white" style="font-family: 'Plus Jakarta Sans', sans-serif;">
        <!-- Ambient Glowing Background Elements -->
        <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
            <div class="absolute -top-[20%] -left-[10%] w-[650px] h-[650px] rounded-full bg-violet-600/20 blur-[130px]"></div>
            <div class="absolute top-[25%] -right-[15%] w-[600px] h-[600px] rounded-full bg-fuchsia-600/20 blur-[140px]"></div>
            <div class="absolute -bottom-[10%] left-[20%] w-[700px] h-[700px] rounded-full bg-cyan-600/15 blur-[150px]"></div>
            <div class="absolute inset-0 bg-[radial-gradient(#ffffff08_1px,transparent_1px)] [background-size:24px_24px]"></div>
        </div>

        <!-- Sticky Header / Navigation -->
        <header class="sticky top-0 z-40 border-b border-white/10 bg-[#070913]/80 backdrop-blur-xl">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-violet-600 via-fuchsia-500 to-pink-500 p-0.5 shadow-lg shadow-fuchsia-500/25">
                        <div class="w-full h-full bg-[#070913] rounded-[14px] flex items-center justify-center">
                            <Music class="w-5 h-5 text-fuchsia-400 animate-pulse" />
                        </div>
                    </div>
                    <div>
                        <span class="text-xl font-extrabold tracking-wider bg-gradient-to-r from-violet-300 via-fuchsia-300 to-pink-300 bg-clip-text text-transparent">
                            NEON ECHOES
                        </span>
                        <span class="block text-[10px] tracking-widest uppercase text-violet-400 font-semibold">Fest Jakarta 2025</span>
                    </div>
                </div>

                <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-300">
                    <button @click="scrollToSection('lineup')" class="hover:text-fuchsia-400 transition-colors">Lineup Artis</button>
                    <button @click="scrollToSection('kategori')" class="hover:text-fuchsia-400 transition-colors">Kategori & Harga</button>
                    <button @click="scrollToSection('stage')" class="hover:text-fuchsia-400 transition-colors">Denah Panggung</button>
                    <button @click="scrollToSection('form-order')" class="hover:text-fuchsia-400 transition-colors">Pesan Tiket</button>
                    <button @click="scrollToSection('faq')" class="hover:text-fuchsia-400 transition-colors">FAQ</button>
                </nav>

                <div class="flex items-center gap-3">
                    <button
                        @click="checkModalOpen = true"
                        class="px-4 py-2 text-xs sm:text-sm font-semibold rounded-xl border border-white/15 bg-white/5 hover:bg-white/10 text-slate-200 transition-all flex items-center gap-2 shadow-sm"
                    >
                        <Search class="w-4 h-4 text-cyan-400" />
                        <span>Cek E-Ticket</span>
                    </button>
                    <button
                        @click="scrollToSection('form-order')"
                        class="hidden sm:inline-flex px-5 py-2 text-xs sm:text-sm font-bold rounded-xl bg-gradient-to-r from-violet-600 via-fuchsia-600 to-pink-600 hover:from-violet-500 hover:via-fuchsia-500 hover:to-pink-500 text-white shadow-lg shadow-fuchsia-600/30 hover:shadow-fuchsia-600/50 transition-all transform active:scale-95"
                    >
                        Beli Tiket
                    </button>
                </div>
            </div>
        </header>

        <!-- Flash Success Notification if any -->
        <div v-if="props.flash?.success && !isReceiptModalOpen" class="relative z-30 max-w-4xl mx-auto px-4 mt-6">
            <div class="p-4 rounded-2xl bg-emerald-950/80 border border-emerald-500/50 backdrop-blur-md flex items-center justify-between gap-4 text-emerald-200 shadow-xl shadow-emerald-950/50">
                <div class="flex items-center gap-3">
                    <CheckCircle2 class="w-6 h-6 text-emerald-400 flex-shrink-0" />
                    <div>
                        <p class="font-bold text-emerald-100">Registrasi Berhasil!</p>
                        <p class="text-xs text-emerald-300">{{ props.flash.success }}</p>
                    </div>
                </div>
                <button
                    v-if="props.recentTicket"
                    @click="openReceipt(props.recentTicket)"
                    class="px-4 py-1.5 text-xs font-bold rounded-lg bg-emerald-500 hover:bg-emerald-400 text-black transition-colors"
                >
                    Lihat E-Ticket
                </button>
            </div>
        </div>

        <main class="relative z-10">
            <!-- HERO SECTION -->
            <section class="pt-12 pb-16 lg:pt-20 lg:pb-24 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto text-center">
                <div class="inline-flex items-center gap-2.5 px-4 py-1.5 rounded-full border border-violet-500/30 bg-violet-950/40 backdrop-blur-md mb-8">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    <span class="text-xs font-semibold tracking-wide text-violet-300">OFFICIAL PRESALE PHASE 2 • TIKET TERBATAS</span>
                </div>

                <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black tracking-tight leading-tight max-w-5xl mx-auto">
                    RASAKAN ENERGI SPEKTAKULER DI <br />
                    <span class="bg-gradient-to-r from-violet-400 via-fuchsia-400 to-cyan-300 bg-clip-text text-transparent drop-shadow-sm">
                        {{ event.title }}
                    </span>
                </h1>

                <p class="mt-6 text-base sm:text-xl text-slate-400 max-w-3xl mx-auto font-normal leading-relaxed">
                    {{ event.tagline }}. Bergabunglah bersama puluhan ribu penikmat musik dalam malam paling megah tahun ini!
                </p>

                <!-- Event Meta Pills -->
                <div class="mt-8 flex flex-wrap items-center justify-center gap-4 text-sm font-medium">
                    <div class="flex items-center gap-2.5 px-4 py-2.5 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md">
                        <Calendar class="w-4 h-4 text-fuchsia-400" />
                        <span>{{ event.date }}</span>
                    </div>
                    <div class="flex items-center gap-2.5 px-4 py-2.5 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md">
                        <Clock class="w-4 h-4 text-cyan-400" />
                        <span>{{ event.time }}</span>
                    </div>
                    <div class="flex items-center gap-2.5 px-4 py-2.5 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md">
                        <MapPin class="w-4 h-4 text-amber-400" />
                        <span>{{ event.venue }}</span>
                    </div>
                </div>

                <!-- Live Booking Counter Badge -->
                <div class="mt-10 flex items-center justify-center gap-3">
                    <div class="inline-flex items-center gap-3 px-5 py-2.5 rounded-2xl bg-gradient-to-r from-fuchsia-950/60 to-violet-950/60 border border-fuchsia-500/30 text-xs sm:text-sm font-medium text-slate-300">
                        <Flame class="w-5 h-5 text-orange-400 animate-bounce" />
                        <span><strong class="text-white font-extrabold">{{ totalBookedCount }}+</strong> Tiket Terverifikasi • Kuota VIP Tersisa Sedikit!</span>
                    </div>
                </div>

                <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                    <button
                        @click="scrollToSection('form-order')"
                        class="px-8 py-4 rounded-2xl bg-gradient-to-r from-violet-600 via-fuchsia-600 to-pink-600 hover:from-violet-500 hover:via-fuchsia-500 hover:to-pink-500 text-white font-extrabold text-base tracking-wide shadow-xl shadow-fuchsia-600/30 hover:shadow-fuchsia-600/50 hover:-translate-y-0.5 transition-all flex items-center gap-3"
                    >
                        <Ticket class="w-5 h-5" />
                        <span>Pesan Tiket Sekarang</span>
                    </button>
                    <button
                        @click="scrollToSection('kategori')"
                        class="px-8 py-4 rounded-2xl bg-white/5 hover:bg-white/10 border border-white/15 text-white font-semibold text-base transition-all hover:-translate-y-0.5"
                    >
                        Lihat Kategori Tiket
                    </button>
                </div>
            </section>

            <!-- GUEST STARS / LINEUP MARQUEE -->
            <section id="lineup" class="py-12 border-y border-white/10 bg-slate-950/40 backdrop-blur-md">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center mb-8">
                        <span class="text-xs uppercase tracking-widest font-extrabold text-fuchsia-400">Exclusive Lineup</span>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-white mt-1">Bintang Tamu Utama</h2>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                        <div
                            v-for="(artist, index) in event.artists"
                            :key="index"
                            class="group relative rounded-2xl p-5 bg-gradient-to-b from-white/[0.08] to-white/[0.02] border border-white/10 hover:border-fuchsia-500/50 transition-all duration-300 text-center flex flex-col items-center justify-center gap-2 hover:-translate-y-1"
                        >
                            <div class="w-12 h-12 rounded-full bg-violet-600/20 border border-violet-500/30 flex items-center justify-center text-violet-300 group-hover:scale-110 group-hover:bg-fuchsia-600/30 transition-all">
                                <Sparkles class="w-5 h-5" />
                            </div>
                            <span class="font-bold text-sm sm:text-base text-slate-100 group-hover:text-fuchsia-300 transition-colors">
                                {{ artist }}
                            </span>
                            <span class="text-[11px] text-slate-400 font-medium">Live Performance</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- TICKET TIERS SELECTION -->
            <section id="kategori" class="py-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <span class="text-xs uppercase tracking-widest font-extrabold text-violet-400">Tier Selection</span>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-white mt-2">Pilih Kategori Tiket Konser</h2>
                    <p class="mt-4 text-slate-400 text-sm sm:text-base">
                        Semua tiket sudah termasuk akses panggung, wristband resmi konser, dan fasilitas standar keselamatan internasional.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div
                        v-for="tier in event.tiers"
                        :key="tier.id"
                        @click="form.ticket_tier = tier.id; scrollToSection('form-order')"
                        :class="[
                            'relative rounded-3xl p-6 sm:p-7 flex flex-col justify-between cursor-pointer transition-all duration-300 border',
                            form.ticket_tier === tier.id
                                ? 'bg-gradient-to-b from-violet-950/80 via-slate-900/90 to-slate-950 border-fuchsia-500 ring-2 ring-fuchsia-500/50 shadow-2xl shadow-fuchsia-950/80 transform -translate-y-2'
                                : 'bg-slate-900/50 hover:bg-slate-900/80 border-white/10 hover:border-white/25 hover:-translate-y-1'
                        ]"
                    >
                        <!-- Top Badge -->
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <span
                                    :class="[
                                        'px-3 py-1 rounded-full text-[11px] font-extrabold uppercase tracking-wider',
                                        tier.id === 'vip' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/40' :
                                        tier.id === 'festival' ? 'bg-fuchsia-500/20 text-fuchsia-300 border border-fuchsia-500/40' :
                                        tier.id === 'cat1' ? 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/40' :
                                        'bg-indigo-500/20 text-indigo-300 border border-indigo-500/40'
                                    ]"
                                >
                                    {{ tier.badge }}
                                </span>
                                <span class="text-xs text-slate-400">Sisa ~{{ tier.quota }} tiket</span>
                            </div>

                            <h3 class="text-xl font-black text-white">{{ tier.name }}</h3>
                            <p class="text-xs text-slate-400 mt-1 min-h-[36px]">{{ tier.description }}</p>

                            <div class="mt-6 mb-6 pb-6 border-b border-white/10">
                                <div class="text-2xl sm:text-3xl font-black text-white">
                                    {{ formatRupiah(tier.price) }}
                                </div>
                                <span class="text-xs text-slate-400">per tiket (belum termasuk pajak)</span>
                            </div>

                            <!-- Perks List -->
                            <ul class="space-y-3 text-xs sm:text-[13px] text-slate-300 mb-8">
                                <li v-for="(perk, pidx) in tier.perks" :key="pidx" class="flex items-start gap-2.5">
                                    <CheckCircle2 class="w-4 h-4 text-emerald-400 flex-shrink-0 mt-0.5" />
                                    <span>{{ perk }}</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Button Action -->
                        <button
                            type="button"
                            :class="[
                                'w-full py-3 rounded-xl font-bold text-xs uppercase tracking-wider transition-all flex items-center justify-center gap-2',
                                form.ticket_tier === tier.id
                                    ? 'bg-gradient-to-r from-violet-600 to-fuchsia-600 text-white shadow-lg shadow-fuchsia-600/30'
                                    : 'bg-white/10 hover:bg-white/15 text-slate-200'
                            ]"
                        >
                            <span v-if="form.ticket_tier === tier.id">✓ Kategori Dipilih</span>
                            <span v-else>Pilih Kategori Ini</span>
                        </button>
                    </div>
                </div>
            </section>

            <!-- STAGE LAYOUT SECTION -->
            <section id="stage" class="py-16 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="rounded-3xl p-6 sm:p-10 bg-slate-900/60 border border-white/10 backdrop-blur-xl">
                    <div class="text-center max-w-2xl mx-auto mb-8">
                        <span class="text-xs uppercase tracking-widest font-extrabold text-cyan-400">Arena Map</span>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-white mt-1">Denah Panggung & Zona Tempat Duduk</h2>
                        <p class="text-xs sm:text-sm text-slate-400 mt-2">
                            Klik pada zona di bawah untuk memilih kategori tiket langsung ke formulir pemesanan.
                        </p>
                    </div>

                    <!-- Interactive SVG Styled Stage Illustration -->
                    <div class="relative bg-slate-950/80 rounded-2xl border border-white/10 p-6 flex flex-col items-center overflow-hidden">
                        <!-- Stage Box -->
                        <div class="w-full max-w-md py-4 rounded-xl bg-gradient-to-r from-violet-600 via-fuchsia-600 to-pink-600 text-center font-black tracking-widest uppercase text-white shadow-lg shadow-fuchsia-500/20 mb-4 text-sm sm:text-base border border-white/20">
                            🎤 MAIN STAGE (PANGGUNG UTAMA)
                        </div>

                        <!-- VIP Pit -->
                        <div
                            @click="form.ticket_tier = 'vip'; scrollToSection('form-order')"
                            :class="[
                                'w-full max-w-lg py-5 px-4 rounded-xl border text-center cursor-pointer transition-all mb-3',
                                form.ticket_tier === 'vip'
                                    ? 'bg-amber-500/20 border-amber-400 text-amber-200 ring-2 ring-amber-400'
                                    : 'bg-amber-950/20 border-amber-500/40 text-amber-300 hover:bg-amber-950/40'
                            ]"
                        >
                            <span class="font-extrabold text-sm block">★ VIP LOUNGE & PIT ★</span>
                            <span class="text-xs opacity-80">Paling dekat dengan artis & Front Stage • Rp 1.750.000</span>
                        </div>

                        <!-- Festival Pit -->
                        <div
                            @click="form.ticket_tier = 'festival'; scrollToSection('form-order')"
                            :class="[
                                'w-full max-w-xl py-6 px-4 rounded-xl border text-center cursor-pointer transition-all mb-3',
                                form.ticket_tier === 'festival'
                                    ? 'bg-fuchsia-500/20 border-fuchsia-400 text-fuchsia-200 ring-2 ring-fuchsia-400'
                                    : 'bg-fuchsia-950/20 border-fuchsia-500/40 text-fuchsia-300 hover:bg-fuchsia-950/40'
                            ]"
                        >
                            <span class="font-extrabold text-sm block">⚡ FESTIVAL (FREE STANDING) ⚡</span>
                            <span class="text-xs opacity-80">Area berdiri energik menghadap stage • Rp 950.000</span>
                        </div>

                        <!-- Sound Console -->
                        <div class="w-36 py-1.5 rounded-lg bg-slate-800 text-[10px] text-slate-400 uppercase tracking-widest font-bold text-center mb-3">
                            FOH SOUND CONSOLE
                        </div>

                        <!-- CAT 1 Seated -->
                        <div
                            @click="form.ticket_tier = 'cat1'; scrollToSection('form-order')"
                            :class="[
                                'w-full max-w-2xl py-5 px-4 rounded-xl border text-center cursor-pointer transition-all mb-3',
                                form.ticket_tier === 'cat1'
                                    ? 'bg-cyan-500/20 border-cyan-400 text-cyan-200 ring-2 ring-cyan-400'
                                    : 'bg-cyan-950/20 border-cyan-500/40 text-cyan-300 hover:bg-cyan-950/40'
                            ]"
                        >
                            <span class="font-extrabold text-sm block">● CAT 1 (NUMBERED SEATING - LOWER TRIBUNE) ●</span>
                            <span class="text-xs opacity-80">Kursi bernomor pandangan tengah panggung • Rp 650.000</span>
                        </div>

                        <!-- CAT 2 Tribune -->
                        <div
                            @click="form.ticket_tier = 'cat2'; scrollToSection('form-order')"
                            :class="[
                                'w-full max-w-3xl py-5 px-4 rounded-xl border text-center cursor-pointer transition-all',
                                form.ticket_tier === 'cat2'
                                    ? 'bg-indigo-500/20 border-indigo-400 text-indigo-200 ring-2 ring-indigo-400'
                                    : 'bg-indigo-950/20 border-indigo-500/40 text-indigo-300 hover:bg-indigo-950/40'
                            ]"
                        >
                            <span class="font-extrabold text-sm block">▲ CAT 2 (UPPER TRIBUNE) ▲</span>
                            <span class="text-xs opacity-80">Tribun atas pemandangan luas stadion • Rp 400.000</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- REGISTRATION FORM SECTION -->
            <section id="form-order" class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-2xl mx-auto mb-12">
                    <span class="text-xs uppercase tracking-widest font-extrabold text-fuchsia-400">Checkout Online</span>
                    <h2 class="text-3xl sm:text-4xl font-black text-white mt-1">Formulir Pendaftaran Tiket</h2>
                    <p class="text-slate-400 text-sm mt-2">
                        Isi data diri dengan teliti. E-Ticket resmi beserta QR Code akan langsung diterbitkan setelah konfirmasi pemesanan.
                    </p>
                </div>

                <form @submit.prevent="submitForm">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                        <!-- LEFT COLUMN: Form Fields (7 cols) -->
                        <div class="lg:col-span-7 space-y-6">
                            <!-- Card 1: Data Pemesan -->
                            <div class="rounded-3xl p-6 sm:p-8 bg-slate-900/70 border border-white/10 backdrop-blur-xl shadow-xl">
                                <div class="flex items-center gap-3 pb-4 mb-6 border-b border-white/10">
                                    <div class="w-8 h-8 rounded-xl bg-violet-600/20 border border-violet-500/40 flex items-center justify-center text-violet-400 font-bold text-sm">
                                        1
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-white">Data Diri Pemesan</h3>
                                        <p class="text-xs text-slate-400">Data ini akan dicantumkan pada E-Ticket resmi.</p>
                                    </div>
                                </div>

                                <div class="space-y-5">
                                    <!-- Nama Lengkap -->
                                    <div>
                                        <label for="full_name" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">
                                            Nama Lengkap (Sesuai KTP/Identitas) <span class="text-fuchsia-400">*</span>
                                        </label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                                <User class="w-4 h-4" />
                                            </div>
                                            <input
                                                id="full_name"
                                                v-model="form.full_name"
                                                type="text"
                                                placeholder="Contoh: Raditya Danendra"
                                                required
                                                class="w-full pl-10 pr-4 py-3 rounded-xl bg-slate-950/80 border border-white/15 focus:border-fuchsia-500 focus:ring-2 focus:ring-fuchsia-500/20 text-white placeholder-slate-500 text-sm transition-all"
                                                :class="{ 'border-rose-500': form.errors.full_name }"
                                            />
                                        </div>
                                        <p v-if="form.errors.full_name" class="mt-1 text-xs text-rose-400 flex items-center gap-1">
                                            <AlertCircle class="w-3.5 h-3.5" /> {{ form.errors.full_name }}
                                        </p>
                                    </div>

                                    <!-- NIK / Identitas -->
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <label for="identity_number" class="text-xs font-bold uppercase tracking-wider text-slate-300">
                                                Nomor Induk Kependudukan (NIK 16 Digit) <span class="text-fuchsia-400">*</span>
                                            </label>
                                            <span class="text-[11px] text-slate-400">{{ form.identity_number.length }}/16 digit</span>
                                        </div>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                                <IdCard class="w-4 h-4" />
                                            </div>
                                            <input
                                                id="identity_number"
                                                v-model="form.identity_number"
                                                type="text"
                                                maxlength="16"
                                                placeholder="Contoh: 3171012345678901"
                                                required
                                                class="w-full pl-10 pr-4 py-3 rounded-xl bg-slate-950/80 border border-white/15 focus:border-fuchsia-500 focus:ring-2 focus:ring-fuchsia-500/20 text-white placeholder-slate-500 text-sm transition-all"
                                                :class="{ 'border-rose-500': form.errors.identity_number }"
                                            />
                                        </div>
                                        <p v-if="form.errors.identity_number" class="mt-1 text-xs text-rose-400 flex items-center gap-1">
                                            <AlertCircle class="w-3.5 h-3.5" /> {{ form.errors.identity_number }}
                                        </p>
                                        <p class="text-[11px] text-slate-500 mt-1">NIK digunakan untuk verifikasi penukaran wristband di lokasi konser.</p>
                                    </div>

                                    <!-- Email & WhatsApp Grid -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                        <div>
                                            <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">
                                                Email Konfirmasi <span class="text-fuchsia-400">*</span>
                                            </label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                                    <Mail class="w-4 h-4" />
                                                </div>
                                                <input
                                                    id="email"
                                                    v-model="form.email"
                                                    type="email"
                                                    placeholder="nama@email.com"
                                                    required
                                                    class="w-full pl-10 pr-4 py-3 rounded-xl bg-slate-950/80 border border-white/15 focus:border-fuchsia-500 focus:ring-2 focus:ring-fuchsia-500/20 text-white placeholder-slate-500 text-sm transition-all"
                                                    :class="{ 'border-rose-500': form.errors.email }"
                                                />
                                            </div>
                                            <p v-if="form.errors.email" class="mt-1 text-xs text-rose-400 flex items-center gap-1">
                                                <AlertCircle class="w-3.5 h-3.5" /> {{ form.errors.email }}
                                            </p>
                                        </div>

                                        <div>
                                            <label for="phone" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">
                                                No. WhatsApp / HP Aktif <span class="text-fuchsia-400">*</span>
                                            </label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                                    <Phone class="w-4 h-4" />
                                                </div>
                                                <input
                                                    id="phone"
                                                    v-model="form.phone"
                                                    type="tel"
                                                    placeholder="081234567890"
                                                    required
                                                    class="w-full pl-10 pr-4 py-3 rounded-xl bg-slate-950/80 border border-white/15 focus:border-fuchsia-500 focus:ring-2 focus:ring-fuchsia-500/20 text-white placeholder-slate-500 text-sm transition-all"
                                                    :class="{ 'border-rose-500': form.errors.phone }"
                                                />
                                            </div>
                                            <p v-if="form.errors.phone" class="mt-1 text-xs text-rose-400 flex items-center gap-1">
                                                <AlertCircle class="w-3.5 h-3.5" /> {{ form.errors.phone }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 2: Jumlah & Kategori Tiket -->
                            <div class="rounded-3xl p-6 sm:p-8 bg-slate-900/70 border border-white/10 backdrop-blur-xl shadow-xl">
                                <div class="flex items-center gap-3 pb-4 mb-6 border-b border-white/10">
                                    <div class="w-8 h-8 rounded-xl bg-fuchsia-600/20 border border-fuchsia-500/40 flex items-center justify-center text-fuchsia-400 font-bold text-sm">
                                        2
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-white">Pilihan Kategori & Jumlah Tiket</h3>
                                        <p class="text-xs text-slate-400">Pilih kategori dan tentukan banyak tiket yang ingin dipesan.</p>
                                    </div>
                                </div>

                                <div class="space-y-5">
                                    <!-- Quick Radio Cards for Tier -->
                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">
                                            Kategori Tiket <span class="text-fuchsia-400">*</span>
                                        </label>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <div
                                                v-for="tier in event.tiers"
                                                :key="tier.id"
                                                @click="form.ticket_tier = tier.id"
                                                :class="[
                                                    'p-4 rounded-2xl border cursor-pointer transition-all flex items-center justify-between',
                                                    form.ticket_tier === tier.id
                                                        ? 'bg-fuchsia-950/40 border-fuchsia-500 ring-1 ring-fuchsia-500'
                                                        : 'bg-slate-950/50 border-white/10 hover:border-white/20'
                                                ]"
                                            >
                                                <div>
                                                    <span class="font-bold text-sm text-white block">{{ tier.name }}</span>
                                                    <span class="text-xs text-fuchsia-400 font-semibold">{{ formatRupiah(tier.price) }}</span>
                                                </div>
                                                <div
                                                    :class="[
                                                        'w-5 h-5 rounded-full border flex items-center justify-center text-xs',
                                                        form.ticket_tier === tier.id
                                                            ? 'bg-fuchsia-500 border-fuchsia-500 text-white'
                                                            : 'border-slate-600'
                                                    ]"
                                                >
                                                    <Check v-if="form.ticket_tier === tier.id" class="w-3.5 h-3.5 stroke-[3]" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Quantity Stepper -->
                                    <div class="pt-2">
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">
                                            Jumlah Tiket (Maksimal 4 Tiket per Transaksi) <span class="text-fuchsia-400">*</span>
                                        </label>
                                        <div class="flex items-center gap-4">
                                            <div class="inline-flex items-center rounded-2xl border border-white/15 bg-slate-950/80 p-1.5">
                                                <button
                                                    type="button"
                                                    @click="form.quantity = Math.max(1, form.quantity - 1)"
                                                    :disabled="form.quantity <= 1"
                                                    class="w-10 h-10 rounded-xl bg-white/5 hover:bg-white/10 disabled:opacity-30 disabled:hover:bg-white/5 flex items-center justify-center text-lg font-bold text-white transition-all"
                                                >
                                                    -
                                                </button>
                                                <span class="w-16 text-center text-lg font-extrabold text-white">
                                                    {{ form.quantity }}
                                                </span>
                                                <button
                                                    type="button"
                                                    @click="form.quantity = Math.min(4, form.quantity + 1)"
                                                    :disabled="form.quantity >= 4"
                                                    class="w-10 h-10 rounded-xl bg-white/5 hover:bg-white/10 disabled:opacity-30 disabled:hover:bg-white/5 flex items-center justify-center text-lg font-bold text-white transition-all"
                                                >
                                                    +
                                                </button>
                                            </div>
                                            <span class="text-xs text-slate-400">
                                                Total {{ form.quantity }} Tiket ({{ selectedTier.name }})
                                            </span>
                                        </div>
                                        <p v-if="form.errors.quantity" class="mt-1 text-xs text-rose-400">
                                            {{ form.errors.quantity }}
                                        </p>
                                    </div>

                                    <!-- Notes (Optional) -->
                                    <div>
                                        <label for="notes" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">
                                            Catatan Khusus (Opsional)
                                        </label>
                                        <textarea
                                            id="notes"
                                            v-model="form.notes"
                                            rows="2"
                                            placeholder="Contoh: Kebutuhan kursi roda atau permintaan akses ramah difabel."
                                            class="w-full px-4 py-3 rounded-xl bg-slate-950/80 border border-white/15 focus:border-fuchsia-500 focus:ring-2 focus:ring-fuchsia-500/20 text-white placeholder-slate-500 text-sm transition-all"
                                        ></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 3: Metode Pembayaran -->
                            <div class="rounded-3xl p-6 sm:p-8 bg-slate-900/70 border border-white/10 backdrop-blur-xl shadow-xl">
                                <div class="flex items-center gap-3 pb-4 mb-6 border-b border-white/10">
                                    <div class="w-8 h-8 rounded-xl bg-cyan-600/20 border border-cyan-500/40 flex items-center justify-center text-cyan-400 font-bold text-sm">
                                        3
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-white">Metode Pembayaran</h3>
                                        <p class="text-xs text-slate-400">Pilih channel pembayaran yang paling praktis untuk Anda.</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div
                                        v-for="method in event.paymentMethods"
                                        :key="method.id"
                                        @click="form.payment_method = method.id"
                                        :class="[
                                            'p-4 rounded-2xl border cursor-pointer transition-all flex flex-col justify-between',
                                            form.payment_method === method.id
                                                ? 'bg-cyan-950/30 border-cyan-400 ring-1 ring-cyan-400/50 shadow-md shadow-cyan-950/50'
                                                : 'bg-slate-950/50 border-white/10 hover:border-white/20'
                                        ]"
                                    >
                                        <div class="flex items-start justify-between mb-2">
                                            <span class="font-bold text-sm text-white">{{ method.name }}</span>
                                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-cyan-500/20 text-cyan-300 font-semibold border border-cyan-500/30">
                                                {{ method.badge }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-400">{{ method.desc }}</p>
                                    </div>
                                </div>
                                <p v-if="form.errors.payment_method" class="mt-2 text-xs text-rose-400">
                                    {{ form.errors.payment_method }}
                                </p>
                            </div>
                        </div>

                        <!-- RIGHT COLUMN: Order Summary & Checkout Action (5 cols) -->
                        <div class="lg:col-span-5 sticky top-28 space-y-6">
                            <div class="rounded-3xl p-6 sm:p-8 bg-gradient-to-b from-slate-900/90 via-slate-900/80 to-slate-950 border border-white/15 backdrop-blur-xl shadow-2xl">
                                <div class="flex items-center justify-between pb-4 border-b border-white/10">
                                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                                        <Ticket class="w-5 h-5 text-fuchsia-400" />
                                        <span>Ringkasan Pesanan</span>
                                    </h3>
                                    <span class="text-xs px-2.5 py-1 rounded-full bg-violet-500/20 text-violet-300 font-semibold border border-violet-500/30">
                                        Phase 2
                                    </span>
                                </div>

                                <!-- Concert Event Mini Card -->
                                <div class="mt-5 p-4 rounded-2xl bg-white/5 border border-white/10">
                                    <span class="text-xs text-slate-400 block">{{ event.title }}</span>
                                    <span class="font-bold text-sm text-white block mt-0.5">{{ selectedTier.name }}</span>
                                    <span class="text-xs text-slate-400 block mt-1">
                                        {{ event.date }} • GBK Jakarta
                                    </span>
                                </div>

                                <!-- Cost Breakdown -->
                                <div class="mt-6 space-y-3.5 text-sm text-slate-300 pb-6 border-b border-white/10">
                                    <div class="flex items-center justify-between">
                                        <span>Harga Tiket ({{ form.quantity }}x)</span>
                                        <span class="font-semibold text-white">{{ formatRupiah(subtotal) }}</span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs text-slate-400">
                                        <span>Pajak Hiburan Konser (10%)</span>
                                        <span class="font-medium text-slate-300">{{ formatRupiah(taxAmount) }}</span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs text-slate-400">
                                        <span>Biaya Layanan & Sistem</span>
                                        <span class="font-medium text-slate-300">{{ formatRupiah(adminFee) }}</span>
                                    </div>
                                </div>

                                <!-- Total Payment -->
                                <div class="mt-6 flex items-baseline justify-between">
                                    <div>
                                        <span class="text-xs uppercase tracking-wider text-slate-400 block font-bold">Total Pembayaran</span>
                                        <span class="text-[11px] text-emerald-400">Termasuk pajak & biaya admin</span>
                                    </div>
                                    <div class="text-2xl sm:text-3xl font-black bg-gradient-to-r from-violet-300 via-fuchsia-300 to-pink-300 bg-clip-text text-transparent">
                                        {{ formatRupiah(totalPrice) }}
                                    </div>
                                </div>

                                <!-- Agreement Checkbox -->
                                <div class="mt-6 pt-4 border-t border-white/10">
                                    <label class="flex items-start gap-3 cursor-pointer select-none">
                                        <input
                                            type="checkbox"
                                            v-model="form.agree_terms"
                                            required
                                            class="w-4 h-4 mt-0.5 rounded border-white/20 bg-slate-950 text-fuchsia-600 focus:ring-fuchsia-500 focus:ring-offset-0"
                                        />
                                        <span class="text-xs text-slate-400 leading-snug">
                                            Saya menyatakan bahwa data yang diisi sudah benar dan menyetujui
                                            <a href="#faq" class="text-fuchsia-400 hover:underline">Syarat & Ketentuan Konser</a>
                                            serta kebijakan penukaran wristband.
                                        </span>
                                    </label>
                                    <p v-if="form.errors.agree_terms" class="mt-2 text-xs text-rose-400">
                                        {{ form.errors.agree_terms }}
                                    </p>
                                </div>

                                <!-- Submit Button -->
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="mt-6 w-full py-4 rounded-2xl bg-gradient-to-r from-violet-600 via-fuchsia-600 to-pink-600 hover:from-violet-500 hover:via-fuchsia-500 hover:to-pink-500 disabled:opacity-50 text-white font-extrabold text-base tracking-wide shadow-xl shadow-fuchsia-600/30 hover:shadow-fuchsia-600/50 transition-all flex items-center justify-center gap-2 transform active:scale-95"
                                >
                                    <span v-if="form.processing" class="inline-flex items-center gap-2">
                                        <svg class="animate-spin h-5 w-5 text-white" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                        </svg>
                                        Memproses Pendaftaran...
                                    </span>
                                    <span v-else class="inline-flex items-center gap-2">
                                        <CheckCircle2 class="w-5 h-5" />
                                        Daftar & Konfirmasi Tiket
                                    </span>
                                </button>

                                <!-- Trust Badges -->
                                <div class="mt-6 flex items-center justify-center gap-6 text-[11px] text-slate-400">
                                    <div class="flex items-center gap-1.5">
                                        <ShieldCheck class="w-4 h-4 text-emerald-400" />
                                        <span>100% Tiket Resmi</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <QrCode class="w-4 h-4 text-cyan-400" />
                                        <span>E-Ticket QR Otomatis</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </section>

            <!-- FAQ SECTION -->
            <section id="faq" class="py-20 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <span class="text-xs uppercase tracking-widest font-extrabold text-violet-400">Pusat Bantuan</span>
                    <h2 class="text-3xl font-extrabold text-white mt-1">Pertanyaan Seputar Konser (FAQ)</h2>
                    <p class="text-sm text-slate-400 mt-2">Segala informasi penting yang wajib Anda ketahui sebelum hadir ke venue.</p>
                </div>

                <div class="space-y-4">
                    <div
                        v-for="(faq, idx) in faqs"
                        :key="idx"
                        class="rounded-2xl border border-white/10 bg-slate-900/60 backdrop-blur-xl overflow-hidden transition-colors"
                    >
                        <button
                            type="button"
                            @click="toggleFaq(idx)"
                            class="w-full p-5 text-left font-bold text-sm sm:text-base text-slate-100 flex items-center justify-between gap-4 hover:text-fuchsia-300 transition-colors"
                        >
                            <span>{{ faq.q }}</span>
                            <span class="text-xs w-6 h-6 rounded-full bg-white/5 flex items-center justify-center flex-shrink-0">
                                {{ openFaq === idx ? '−' : '+' }}
                            </span>
                        </button>
                        <div v-show="openFaq === idx" class="px-5 pb-5 text-xs sm:text-sm text-slate-400 leading-relaxed border-t border-white/5 pt-3">
                            {{ faq.a }}
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <!-- FOOTER -->
        <footer class="border-t border-white/10 bg-[#05060d] py-12 text-slate-500 text-xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-6 text-center sm:text-left">
                <div>
                    <div class="flex items-center justify-center sm:justify-start gap-2 mb-2">
                        <Music class="w-4 h-4 text-fuchsia-400" />
                        <span class="font-extrabold text-white tracking-wide">NEON ECHOES FESTIVAL 2025</span>
                    </div>
                    <p>© 2025 Promotor Musik Indonesia. Seluruh hak cipta dilindungi undang-undang.</p>
                </div>
                <div class="flex items-center gap-6">
                    <a href="#kategori" class="hover:text-slate-300">Tiket</a>
                    <a href="#stage" class="hover:text-slate-300">Denah</a>
                    <a href="#faq" class="hover:text-slate-300">Ketentuan</a>
                    <button @click="checkModalOpen = true" class="text-fuchsia-400 hover:underline">Cek E-Ticket</button>
                </div>
            </div>
        </footer>

        <!-- MODAL: CEK STATUS TIKET -->
        <div v-if="checkModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md">
            <div class="relative w-full max-w-md rounded-3xl bg-slate-900 border border-white/15 p-6 sm:p-8 shadow-2xl">
                <button
                    @click="checkModalOpen = false; searchError = ''"
                    class="absolute top-5 right-5 text-slate-400 hover:text-white"
                >
                    <X class="w-5 h-5" />
                </button>

                <div class="text-center mb-6">
                    <div class="w-12 h-12 rounded-2xl bg-cyan-500/20 border border-cyan-500/40 text-cyan-400 flex items-center justify-center mx-auto mb-3">
                        <Search class="w-6 h-6" />
                    </div>
                    <h3 class="text-xl font-extrabold text-white">Cari E-Ticket Anda</h3>
                    <p class="text-xs text-slate-400 mt-1">
                        Masukkan Kode Booking (contoh: NEON-2025-XXXXX), Nomor NIK, atau Alamat Email terdaftar.
                    </p>
                </div>

                <form @submit.prevent="searchTicket" class="space-y-4">
                    <div>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Kode Booking / NIK / Email"
                            required
                            class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-white/15 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/20 text-white placeholder-slate-500 text-sm"
                        />
                    </div>

                    <p v-if="searchError" class="text-xs text-rose-400 flex items-center gap-1">
                        <AlertCircle class="w-4 h-4 flex-shrink-0" />
                        <span>{{ searchError }}</span>
                    </p>

                    <button
                        type="submit"
                        :disabled="isSearching"
                        class="w-full py-3 rounded-xl bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 disabled:opacity-50 text-white font-bold text-sm transition-all flex items-center justify-center gap-2"
                    >
                        <span v-if="isSearching">Mencari Tiket...</span>
                        <span v-else>Cari E-Ticket</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- MODAL: E-TICKET RECEIPT DISPLAY -->
        <div v-if="isReceiptModalOpen && activeTicket" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/85 backdrop-blur-md overflow-y-auto">
            <div class="relative w-full max-w-lg my-8 rounded-3xl bg-slate-900 border border-fuchsia-500/40 p-6 sm:p-8 shadow-2xl shadow-fuchsia-950/60 print:m-0 print:border-none print:shadow-none print:bg-white print:text-black">
                <!-- Close Button (Hidden on Print) -->
                <button
                    @click="closeReceipt"
                    class="absolute top-5 right-5 text-slate-400 hover:text-white print:hidden"
                >
                    <X class="w-5 h-5" />
                </button>

                <!-- Ticket Header -->
                <div class="text-center pb-6 border-b border-white/10 print:border-slate-300">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 text-xs font-bold uppercase tracking-wider mb-2 print:border-emerald-600 print:text-emerald-700">
                        <CheckCircle2 class="w-3.5 h-3.5" /> E-TICKET RESMI TERKONFIRMASI
                    </span>
                    <h3 class="text-2xl font-black text-white print:text-black">{{ event.title }}</h3>
                    <p class="text-xs text-slate-400 print:text-slate-600 mt-1">
                        {{ event.date }} • {{ event.time }} • {{ event.venue }}
                    </p>
                </div>

                <!-- Booking Code Box -->
                <div class="my-6 p-4 rounded-2xl bg-slate-950/80 border border-dashed border-fuchsia-500/40 text-center print:bg-slate-100 print:border-slate-400">
                    <span class="text-[11px] uppercase tracking-widest text-slate-400 print:text-slate-600 font-bold block">
                        KODE BOOKING TIKET
                    </span>
                    <div class="flex items-center justify-center gap-3 mt-1">
                        <span class="text-2xl sm:text-3xl font-black tracking-widest text-fuchsia-400 print:text-fuchsia-700">
                            {{ activeTicket.booking_code }}
                        </span>
                        <button
                            type="button"
                            @click="copyBookingCode(activeTicket.booking_code)"
                            class="p-2 rounded-lg bg-white/10 hover:bg-white/20 text-slate-300 transition-colors print:hidden"
                            title="Salin Kode"
                        >
                            <Check v-if="isCopied" class="w-4 h-4 text-emerald-400" />
                            <Copy v-else class="w-4 h-4" />
                        </button>
                    </div>
                    <span v-if="isCopied" class="text-[11px] text-emerald-400 mt-1 block">Kode booking disalin ke clipboard!</span>
                </div>

                <!-- Mock QR Code Barcode -->
                <div class="flex flex-col items-center justify-center p-4 rounded-2xl bg-white text-black mb-6">
                    <!-- SVG Styled QR Code -->
                    <div class="w-36 h-36 border-4 border-black p-2 flex items-center justify-center">
                        <svg class="w-full h-full" viewBox="0 0 100 100" fill="currentColor">
                            <!-- Pattern squares simulating a QR -->
                            <rect x="5" y="5" width="25" height="25" />
                            <rect x="9" y="9" width="17" height="17" fill="white" />
                            <rect x="13" y="13" width="9" height="9" />

                            <rect x="70" y="5" width="25" height="25" />
                            <rect x="74" y="9" width="17" height="17" fill="white" />
                            <rect x="78" y="13" width="9" height="9" />

                            <rect x="5" y="70" width="25" height="25" />
                            <rect x="9" y="74" width="17" height="17" fill="white" />
                            <rect x="13" y="78" width="9" height="9" />

                            <rect x="40" y="10" width="6" height="6" />
                            <rect x="52" y="10" width="6" height="6" />
                            <rect x="40" y="22" width="6" height="6" />
                            <rect x="52" y="22" width="6" height="6" />

                            <rect x="36" y="36" width="28" height="28" />
                            <rect x="42" y="42" width="16" height="16" fill="white" />
                            <rect x="47" y="47" width="6" height="6" />

                            <rect x="10" y="45" width="6" height="6" />
                            <rect x="22" y="52" width="6" height="6" />
                            <rect x="70" y="45" width="8" height="8" />
                            <rect x="82" y="55" width="8" height="8" />

                            <rect x="40" y="72" width="6" height="6" />
                            <rect x="52" y="82" width="6" height="6" />
                            <rect x="72" y="72" width="18" height="6" />
                            <rect x="72" y="82" width="6" height="12" />
                            <rect x="84" y="82" width="8" height="8" />
                        </svg>
                    </div>
                    <span class="text-[10px] tracking-widest font-mono text-slate-700 mt-2">
                        SCAN SAAT PENUKARAN WRISTBAND
                    </span>
                </div>

                <!-- Detail Tiket Table -->
                <div class="space-y-2.5 text-xs text-slate-300 print:text-slate-800 pb-6 border-b border-white/10 print:border-slate-300">
                    <div class="flex justify-between">
                        <span class="text-slate-400 print:text-slate-600">Nama Pemegang:</span>
                        <span class="font-bold text-white print:text-black">{{ activeTicket.full_name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400 print:text-slate-600">NIK (KTP):</span>
                        <span class="font-bold text-white print:text-black">{{ activeTicket.identity_number }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400 print:text-slate-600">Email:</span>
                        <span class="font-medium text-white print:text-black">{{ activeTicket.email }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400 print:text-slate-600">Nomor HP:</span>
                        <span class="font-medium text-white print:text-black">{{ activeTicket.phone }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400 print:text-slate-600">Kategori Tiket:</span>
                        <span class="font-extrabold uppercase text-fuchsia-400 print:text-fuchsia-700">
                            {{ activeTicket.ticket_tier }} ({{ activeTicket.quantity }} Tiket)
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400 print:text-slate-600">Metode Pembayaran:</span>
                        <span class="font-bold uppercase text-white print:text-black">{{ activeTicket.payment_method }}</span>
                    </div>
                    <div class="flex justify-between pt-2 border-t border-white/10 print:border-slate-300">
                        <span class="text-slate-400 print:text-slate-600 font-bold">Total Pembayaran:</span>
                        <span class="font-black text-sm text-emerald-400 print:text-emerald-700">
                            {{ formatRupiah(activeTicket.total_price) }}
                        </span>
                    </div>
                </div>

                <!-- Action Buttons (Print / Download / Close) -->
                <div class="mt-6 flex flex-wrap items-center gap-3 print:hidden">
                    <button
                        type="button"
                        @click="printTicket"
                        class="flex-1 py-3 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 transition-colors"
                    >
                        <Printer class="w-4 h-4" /> Cetak / Simpan PDF
                    </button>
                    <button
                        type="button"
                        @click="closeReceipt"
                        class="flex-1 py-3 rounded-xl bg-gradient-to-r from-violet-600 to-fuchsia-600 text-white font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 transition-colors shadow-lg shadow-fuchsia-600/30"
                    >
                        Selesai
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
@media print {
    body {
        background-color: white !important;
        color: black !important;
    }
}
</style>
