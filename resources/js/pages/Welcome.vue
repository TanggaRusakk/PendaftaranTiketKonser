<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ArrowRight, CalendarDays, Check, CheckCircle2, ChevronDown, Clock3, Copy, Menu, Search, ShieldCheck, Ticket, X } from 'lucide-vue-next';
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
}

const props = defineProps<{
    event: EventData;
    totalBookedCount: number;
    recentTicket?: BookedTicket | null;
    flash?: { success?: string; error?: string };
}>();

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

const selectedTier = computed(() => props.event.tiers.find((tier) => tier.id === form.ticket_tier) ?? props.event.tiers[0]);
const subtotal = computed(() => (selectedTier.value?.price ?? 0) * form.quantity);
const taxAmount = computed(() => Math.round(subtotal.value * 0.1));
const totalPrice = computed(() => subtotal.value + taxAmount.value + 5000);
const formatRupiah = (value: number) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);

const activeTicket = ref<BookedTicket | null>(props.recentTicket ?? null);
const receiptOpen = ref(Boolean(props.recentTicket));
const copied = ref(false);
const checkOpen = ref(false);
const searchQuery = ref('');
const searchError = ref('');
const searching = ref(false);
const menuOpen = ref(false);
const openFaq = ref<number | null>(0);

const scrollTo = (id: string) => {
    document.getElementById(id)?.scrollIntoView({ behavior: 'smooth' });
    menuOpen.value = false;
};

const openReceipt = (ticket: BookedTicket) => {
    activeTicket.value = ticket;
    receiptOpen.value = true;
};

const copyCode = async () => {
    if (!activeTicket.value) return;
    await navigator.clipboard.writeText(activeTicket.value.booking_code);
    copied.value = true;
    setTimeout(() => (copied.value = false), 1800);
};

const submitForm = () => {
    form.post('/ticket/register', {
        preserveScroll: true,
        onSuccess: (page) => {
            const ticket = (page.props as { recentTicket?: BookedTicket }).recentTicket;
            if (ticket) openReceipt(ticket);
            form.reset('full_name', 'email', 'phone', 'identity_number', 'notes', 'agree_terms');
            form.quantity = 1;
        },
    });
};

const searchTicket = async () => {
    if (!searchQuery.value.trim()) {
        searchError.value = 'Masukkan kode booking, email, atau NIK.';
        return;
    }
    searching.value = true;
    searchError.value = '';
    try {
        const response = await fetch(`/ticket/check?search=${encodeURIComponent(searchQuery.value.trim())}`);
        const data = await response.json();
        if (!response.ok || !data.success) {
            searchError.value = data.message ?? 'Tiket tidak ditemukan.';
            return;
        }
        checkOpen.value = false;
        searchQuery.value = '';
        router.visit(`/ticket/${encodeURIComponent(data.ticket.booking_code)}`);
    } catch {
        searchError.value = 'Terjadi kesalahan. Silakan coba lagi.';
    } finally {
        searching.value = false;
    }
};

const faqs = [
    [
        'Bagaimana cara menukarkan e-ticket?',
        'Tunjukkan e-ticket dan kartu identitas asli di Ticket Booth Stadion Utama GBK mulai H-2 hingga hari H konser.',
    ],
    [
        'Berapa tiket yang dapat dibeli dalam satu transaksi?',
        'Maksimal empat tiket untuk satu transaksi. Nama pemesan utama harus sesuai dengan kartu identitas.',
    ],
    [
        'Apakah tiket dapat dibatalkan?',
        'Tiket yang sudah dibeli bersifat final dan tidak dapat di-refund, kecuali acara dibatalkan oleh penyelenggara.',
    ],
    ['Berapa usia minimal penonton?', 'Penonton minimal berusia 12 tahun. Penonton di bawah 17 tahun wajib didampingi orang tua atau wali.'],
];
</script>

<template>
    <Head :title="`${event.title} - Pendaftaran Tiket Konser`">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
        <link
            href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap"
            rel="stylesheet"
        />
    </Head>

    <div class="min-h-screen bg-[#fffaf6] text-[#302d3d] selection:bg-[#d9c7eb] selection:text-[#302d3d]" style="font-family: 'DM Sans', sans-serif">
        <header class="sticky top-0 z-30 border-b border-[#e9dfdb] bg-[#fffaf6]/95 backdrop-blur">
            <div class="mx-auto flex h-[76px] max-w-6xl items-center justify-between gap-3 px-4 sm:px-5 lg:px-8">
                <button class="flex items-center gap-3 text-left" @click="scrollTo('top')">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#cdb7e5] text-[#49365d]"><Ticket class="h-5 w-5" /></span>
                    <span class="min-w-0"
                        ><strong
                            class="block truncate font-serif text-base leading-none text-[#49365d] sm:text-lg"
                            style="font-family: 'Playfair Display', serif"
                            >Neon Echoes</strong
                        ><small class="text-[9px] font-semibold uppercase tracking-[0.14em] text-[#a06c78] sm:text-[10px] sm:tracking-[0.18em]"
                            >Festival 2026</small
                        ></span
                    >
                </button>
                <nav class="hidden items-center gap-7 text-sm font-semibold text-[#6e6878] md:flex">
                    <button @click="scrollTo('lineup')">Lineup</button><button @click="scrollTo('tickets')">Tiket</button
                    ><button @click="scrollTo('order')">Pesan</button><button @click="scrollTo('faq')">FAQ</button>
                </nav>
                <div class="flex items-center gap-2">
                    <button
                        class="hidden rounded-full border border-[#d9c8c0] px-4 py-2 text-sm font-semibold text-[#6e6878] transition hover:border-[#a06c78] hover:text-[#a06c78] sm:block"
                        @click="checkOpen = true"
                    >
                        <Search class="mr-1 inline h-4 w-4" /> Cek tiket
                    </button>
                    <button
                        class="rounded-full bg-[#a06c78] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#855968]"
                        @click="scrollTo('order')"
                    >
                        Beli tiket
                    </button>
                    <button class="rounded-lg p-2 md:hidden" @click="menuOpen = !menuOpen"><Menu class="h-5 w-5" /></button>
                </div>
            </div>
            <div v-if="menuOpen" class="border-t border-[#e9dfdb] bg-[#fffaf6] px-5 py-4 md:hidden">
                <div class="flex flex-col gap-4 text-sm font-semibold">
                    <button class="text-left" @click="scrollTo('lineup')">Lineup</button
                    ><button class="text-left" @click="scrollTo('tickets')">Tiket</button
                    ><button class="text-left" @click="scrollTo('order')">Pesan tiket</button
                    ><button class="text-left" @click="scrollTo('faq')">FAQ</button
                    ><button
                        class="text-left text-[#a06c78]"
                        @click="
                            checkOpen = true;
                            menuOpen = false;
                        "
                    >
                        Cek e-ticket
                    </button>
                </div>
            </div>
        </header>

        <div
            v-if="flash?.success && !receiptOpen"
            class="mx-auto mt-5 flex max-w-3xl flex-wrap items-center justify-between gap-3 rounded-2xl border border-[#b9d8c8] bg-[#edf8f1] px-5 py-4 text-sm text-[#35644c]"
        >
            <span><CheckCircle2 class="mr-2 inline h-5 w-5" />{{ flash.success }}</span
            ><button v-if="recentTicket" class="font-bold underline" @click="openReceipt(recentTicket)">Lihat e-ticket</button>
        </div>

        <main id="top">
            <section class="mx-auto grid max-w-6xl gap-12 px-5 pb-20 pt-16 lg:grid-cols-[1.1fr_.9fr] lg:items-center lg:px-8 lg:pt-24">
                <div>
                    <p class="mb-5 inline-flex rounded-full bg-[#f7dfd8] px-4 py-2 text-xs font-bold uppercase tracking-[0.14em] text-[#a05d61]">
                        28 Juni 2027 · GBK Jakarta
                    </p>
                    <h1
                        class="max-w-3xl font-serif text-5xl leading-[1.05] text-[#49365d] sm:text-7xl"
                        style="font-family: 'Playfair Display', serif"
                    >
                        Satu malam, <span class="text-[#a06c78]">banyak cerita.</span>
                    </h1>
                    <p class="mt-6 max-w-xl text-lg leading-8 text-[#716a78]">
                        {{ event.tagline }}. Nikmati musik yang dekat, hangat, dan terasa nyata.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-3 text-sm font-semibold text-[#665e70]">
                        <span class="rounded-xl bg-[#e7f2eb] px-4 py-3"
                            ><CalendarDays class="mr-2 inline h-4 w-4 text-[#5b9375]" />{{ event.date }}</span
                        ><span class="rounded-xl bg-[#eee8f5] px-4 py-3"><Clock3 class="mr-2 inline h-4 w-4 text-[#80629a]" />{{ event.time }}</span>
                    </div>
                    <div class="mt-9 flex flex-wrap items-center gap-4">
                        <button
                            class="rounded-full bg-[#a06c78] px-6 py-3.5 font-bold text-white transition hover:bg-[#855968]"
                            @click="scrollTo('order')"
                        >
                            Pesan tiket <ArrowRight class="ml-2 inline h-4 w-4" /></button
                        ><button class="font-bold text-[#a06c78] underline decoration-[#d9c7c0] underline-offset-4" @click="scrollTo('lineup')">
                            Lihat lineup
                        </button>
                    </div>
                </div>
                <div class="relative rounded-[30px] border border-[#e3d3cc] bg-[#f8e9df] p-5 shadow-[10px_12px_0_#ead8d0]">
                    <div class="rounded-[22px] border border-[#ead4c8] bg-[#fffaf6] p-7">
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#a06c78]">The live music experience</p>
                        <div class="mt-12 flex items-end gap-2">
                            <span class="h-28 w-5 rounded-t-full bg-[#cdb7e5]"></span><span class="h-44 w-5 rounded-t-full bg-[#b9d8c8]"></span
                            ><span class="h-36 w-5 rounded-t-full bg-[#f4c9bd]"></span><span class="h-56 w-5 rounded-t-full bg-[#a06c78]"></span
                            ><span class="h-40 w-5 rounded-t-full bg-[#d8c7e9]"></span>
                        </div>
                        <div class="mt-8 border-t border-[#e9dfdb] pt-5">
                            <p class="font-serif text-2xl text-[#49365d]" style="font-family: 'Playfair Display', serif">
                                Music feels better together.
                            </p>
                            <p class="mt-2 text-sm text-[#817783]">
                                Tiket terverifikasi: <strong class="text-[#a06c78]">{{ totalBookedCount }}+</strong>
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <section id="lineup" class="border-y border-[#e9dfdb] bg-[#f7f1ee]">
                <div class="mx-auto max-w-6xl px-5 py-16 lg:px-8">
                    <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#a06c78]">Lineup pilihan</p>
                    <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                        <div
                            v-for="(artist, index) in event.artists"
                            :key="artist"
                            class="rounded-2xl border border-[#e3d7d2] bg-[#fffaf6] p-4"
                            :class="{ 'bg-[#eee8f5]': index % 3 === 1, 'bg-[#e7f2eb]': index % 3 === 2 }"
                        >
                            <span class="text-xs text-[#9b9098]">0{{ index + 1 }}</span>
                            <p class="mt-7 font-semibold text-[#49365d]">{{ artist }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section id="tickets" class="mx-auto max-w-6xl px-5 py-20 lg:px-8">
                <div class="flex flex-wrap items-end justify-between gap-5">
                    <div>
                        <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#a06c78]">Pilih tempatmu</p>
                        <h2 class="mt-2 font-serif text-4xl text-[#49365d]" style="font-family: 'Playfair Display', serif">Kategori tiket</h2>
                    </div>
                    <p class="max-w-xs text-sm leading-6 text-[#817783]">
                        Semua tiket sudah termasuk akses masuk resmi dan e-ticket yang dikirim ke email.
                    </p>
                </div>
                <div class="mt-10 grid gap-5 md:grid-cols-2 lg:grid-cols-4">
                    <article
                        v-for="tier in event.tiers"
                        :key="tier.id"
                        class="flex flex-col rounded-2xl border p-5 transition"
                        :class="
                            form.ticket_tier === tier.id
                                ? 'border-[#a06c78] bg-[#fff3ef] shadow-[4px_5px_0_#e5c7bf]'
                                : 'border-[#e5dcd7] bg-white hover:border-[#c5a5a3]'
                        "
                        @click="form.ticket_tier = tier.id"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <span class="rounded-full bg-[#eee8f5] px-2.5 py-1 text-[10px] font-bold uppercase text-[#80629a]">{{ tier.badge }}</span
                            ><Check v-if="form.ticket_tier === tier.id" class="h-5 w-5 text-[#a06c78]" />
                        </div>
                        <h3 class="mt-6 min-h-12 font-bold text-[#49365d]">{{ tier.name }}</h3>
                        <p class="mt-2 text-2xl font-bold text-[#a06c78]">{{ formatRupiah(tier.price) }}</p>
                        <p class="mt-4 flex-1 text-sm leading-6 text-[#817783]">{{ tier.description }}</p>
                        <p class="mt-5 border-t border-[#eee4df] pt-3 text-xs font-semibold text-[#a06c78]">{{ tier.quota }} tiket tersedia</p>
                    </article>
                </div>
            </section>

            <section id="order" class="bg-[#eee8f5]">
                <div class="mx-auto grid max-w-6xl gap-10 px-5 py-20 lg:grid-cols-[1fr_.75fr] lg:px-8">
                    <div>
                        <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#80629a]">Amankan tiket</p>
                        <h2 class="mt-2 font-serif text-4xl text-[#49365d]" style="font-family: 'Playfair Display', serif">Buat pesanan</h2>
                        <p class="mt-3 text-[#716a78]">Isi data dengan benar. E-ticket akan dikirim ke alamat email yang kamu masukkan.</p>
                        <form class="mt-8 grid gap-5 sm:grid-cols-2" @submit.prevent="submitForm">
                            <label class="sm:col-span-2"
                                >Nama lengkap<input v-model="form.full_name" type="text" placeholder="Nama sesuai kartu identitas" /><small
                                    v-if="form.errors.full_name"
                                    >{{ form.errors.full_name }}</small
                                ></label
                            ><label
                                >Email<input v-model="form.email" type="email" placeholder="nama@email.com" /><small v-if="form.errors.email">{{
                                    form.errors.email
                                }}</small></label
                            ><label
                                >Nomor WhatsApp<input v-model="form.phone" type="tel" placeholder="081234567890" /><small v-if="form.errors.phone">{{
                                    form.errors.phone
                                }}</small></label
                            ><label
                                >NIK / nomor identitas<input
                                    v-model="form.identity_number"
                                    inputmode="numeric"
                                    maxlength="16"
                                    placeholder="16 digit nomor identitas"
                                /><small v-if="form.errors.identity_number">{{ form.errors.identity_number }}</small></label
                            ><label
                                >Jumlah tiket<select v-model.number="form.quantity">
                                    <option v-for="quantity in 4" :key="quantity" :value="quantity">{{ quantity }} tiket</option>
                                </select></label
                            ><label class="sm:col-span-2"
                                >Metode pembayaran<select v-model="form.payment_method">
                                    <option v-for="method in event.paymentMethods" :key="method.id" :value="method.id">{{ method.name }}</option>
                                </select></label
                            ><label class="sm:col-span-2"
                                >Catatan <span class="font-normal text-[#9b9098]">(opsional)</span
                                ><textarea v-model="form.notes" rows="2" placeholder="Tambahkan catatan jika perlu"></textarea></label
                            ><label class="flex items-start gap-3 text-sm font-normal sm:col-span-2"
                                ><input
                                    v-model="form.agree_terms"
                                    type="checkbox"
                                    class="mt-1 h-4 w-4 rounded border-[#c7b9d1] text-[#a06c78]"
                                /><span>Saya menyetujui syarat dan ketentuan pembelian tiket.</span></label
                            ><small v-if="form.errors.agree_terms" class="sm:col-span-2">{{ form.errors.agree_terms }}</small
                            ><button
                                :disabled="form.processing"
                                class="rounded-xl bg-[#a06c78] px-5 py-3.5 font-bold text-white transition hover:bg-[#855968] disabled:opacity-60 sm:col-span-2"
                            >
                                {{ form.processing ? 'Memproses pesanan...' : 'Konfirmasi pesanan' }}
                            </button>
                        </form>
                    </div>
                    <aside class="h-fit rounded-2xl border border-[#ddd0e9] bg-[#fffaf6] p-6 lg:sticky lg:top-28">
                        <div class="flex items-center justify-between">
                            <h3 class="font-bold text-[#49365d]">Ringkasan</h3>
                            <span class="rounded-full bg-[#e7f2eb] px-3 py-1 text-xs font-bold text-[#4c8064]">Aman</span>
                        </div>
                        <div class="mt-6 border-b border-[#e9dfdb] pb-5">
                            <p class="font-semibold">{{ selectedTier?.name }}</p>
                            <p class="mt-1 text-sm text-[#817783]">{{ form.quantity }} tiket × {{ formatRupiah(selectedTier?.price ?? 0) }}</p>
                        </div>
                        <dl class="space-y-3 py-5 text-sm text-[#817783]">
                            <div class="flex justify-between">
                                <dt>Subtotal</dt>
                                <dd>{{ formatRupiah(subtotal) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt>Pajak hiburan (10%)</dt>
                                <dd>{{ formatRupiah(taxAmount) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt>Biaya admin</dt>
                                <dd>{{ formatRupiah(5000) }}</dd>
                            </div>
                        </dl>
                        <div class="flex justify-between border-t border-[#e9dfdb] pt-5 font-bold text-[#49365d]">
                            <span>Total</span><span class="text-[#a06c78]">{{ formatRupiah(totalPrice) }}</span>
                        </div>
                        <p class="mt-6 text-xs leading-5 text-[#817783]">
                            <ShieldCheck class="mr-1 inline h-4 w-4 text-[#5b9375]" />Data kamu hanya digunakan untuk penerbitan tiket.
                        </p>
                    </aside>
                </div>
            </section>

            <section id="faq" class="mx-auto max-w-3xl px-5 py-20 lg:px-8">
                <p class="text-center text-sm font-bold uppercase tracking-[0.16em] text-[#a06c78]">Pusat bantuan</p>
                <h2 class="mt-2 text-center font-serif text-4xl text-[#49365d]" style="font-family: 'Playfair Display', serif">Pertanyaan umum</h2>
                <div class="mt-10 divide-y divide-[#e9dfdb] border-y border-[#e9dfdb]">
                    <div v-for="(faq, index) in faqs" :key="faq[0]" class="py-5">
                        <button
                            class="flex w-full items-center justify-between text-left font-semibold text-[#49365d]"
                            @click="openFaq = openFaq === index ? null : index"
                        >
                            {{ faq[0] }}<ChevronDown class="h-5 w-5 transition" :class="{ 'rotate-180': openFaq === index }" />
                        </button>
                        <p v-if="openFaq === index" class="mt-3 max-w-2xl text-sm leading-6 text-[#817783]">{{ faq[1] }}</p>
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t border-[#e9dfdb] bg-[#49365d] px-5 py-10 text-center text-sm text-[#e5dcef]">
            <p class="font-serif text-xl text-white" style="font-family: 'Playfair Display', serif">Neon Echoes Festival</p>
            <p class="mt-2">Satu malam untuk musik, teman, dan cerita baru.</p>
            <p class="mt-5 text-xs text-[#bdb0cc]">© 2025 Neon Echoes Festival. Semua hak dilindungi.</p>
        </footer>

        <div v-if="checkOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-[#302d3d]/40 px-5" @click.self="checkOpen = false">
            <div class="w-full max-w-md rounded-2xl bg-[#fffaf6] p-6 shadow-2xl">
                <div class="flex items-center justify-between">
                    <h2 class="font-serif text-2xl text-[#49365d]" style="font-family: 'Playfair Display', serif">Cek e-ticket</h2>
                    <button @click="checkOpen = false"><X class="h-5 w-5" /></button>
                </div>
                <p class="mt-2 text-sm text-[#817783]">Cari dengan kode booking, email, atau NIK.</p>
                <form class="mt-6" @submit.prevent="searchTicket">
                    <input v-model="searchQuery" autofocus type="text" placeholder="Contoh: NEON-2025-AB123" />
                    <p v-if="searchError" class="mt-2 text-sm text-[#a05d61]">{{ searchError }}</p>
                    <button :disabled="searching" class="mt-4 w-full rounded-xl bg-[#a06c78] py-3 font-bold text-white">
                        {{ searching ? 'Mencari...' : 'Cari tiket' }}
                    </button>
                </form>
            </div>
        </div>

        <div
            v-if="receiptOpen && activeTicket"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-[#302d3d]/40 px-5 py-8"
            @click.self="receiptOpen = false"
        >
            <div class="w-full max-w-lg rounded-2xl bg-[#fffaf6] p-6 shadow-2xl">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.15em] text-[#a06c78]">E-ticket terkonfirmasi</p>
                        <h2 class="mt-2 font-serif text-3xl text-[#49365d]" style="font-family: 'Playfair Display', serif">Sampai jumpa di GBK.</h2>
                    </div>
                    <button @click="receiptOpen = false"><X class="h-5 w-5" /></button>
                </div>
                <div class="mt-6 rounded-xl border-2 border-dashed border-[#cdb7e5] bg-[#eee8f5] p-5 text-center">
                    <p class="text-xs font-bold uppercase tracking-[0.15em] text-[#80629a]">Kode booking</p>
                    <p class="mt-2 font-mono text-2xl font-bold tracking-widest text-[#49365d]">{{ activeTicket.booking_code }}</p>
                    <button class="mt-3 text-xs font-bold text-[#80629a]" @click="copyCode">
                        <Copy class="mr-1 inline h-3.5 w-3.5" />{{ copied ? 'Tersalin' : 'Salin kode' }}
                    </button>
                </div>
                <dl class="mt-6 divide-y divide-[#e9dfdb] text-sm">
                    <div class="flex justify-between py-3">
                        <dt class="text-[#817783]">Nama</dt>
                        <dd class="font-semibold">{{ activeTicket.full_name }}</dd>
                    </div>
                    <div class="flex justify-between py-3">
                        <dt class="text-[#817783]">Kategori</dt>
                        <dd class="font-semibold uppercase">{{ activeTicket.ticket_tier }} · {{ activeTicket.quantity }} tiket</dd>
                    </div>
                    <div class="flex justify-between py-3">
                        <dt class="text-[#817783]">Pembayaran</dt>
                        <dd class="font-semibold uppercase">{{ activeTicket.payment_method }}</dd>
                    </div>
                    <div class="flex justify-between py-3">
                        <dt class="text-[#817783]">Total</dt>
                        <dd class="font-bold text-[#a06c78]">{{ formatRupiah(activeTicket.total_price) }}</dd>
                    </div>
                </dl>
                <div class="mt-6 rounded-xl bg-[#e7f2eb] p-4 text-sm leading-6 text-[#35644c]">
                    <CheckCircle2 class="mr-1 inline h-4 w-4" /> E-ticket dikirim ke <strong>{{ activeTicket.email }}</strong
                    >. Bawa kartu identitas asli saat penukaran wristband.
                </div>
                <button class="mt-5 w-full rounded-xl border border-[#d9c8c0] py-3 font-bold text-[#6e6878]" @click="receiptOpen = false">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
label {
    display: flex;
    flex-direction: column;
    gap: 0.45rem;
    font-size: 0.875rem;
    font-weight: 700;
    color: #49365d;
}
input:not([type='checkbox']),
select,
textarea {
    width: 100%;
    border: 1px solid #d9cdd5;
    border-radius: 0.75rem;
    background: #fffaf6;
    padding: 0.75rem 0.9rem;
    color: #302d3d;
    outline: none;
}
input:not([type='checkbox']):focus,
select:focus,
textarea:focus {
    border-color: #a06c78;
    box-shadow: 0 0 0 3px #a06c7820;
}
label small {
    color: #a05d61;
    font-weight: 500;
}
button {
    transition:
        color 0.2s,
        background-color 0.2s,
        border-color 0.2s,
        opacity 0.2s;
}
</style>
