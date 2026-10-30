<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ArrowLeft, CalendarDays, CheckCircle2, Clock3, Copy, MapPin, Ticket as TicketIcon } from 'lucide-vue-next';
import { ref } from 'vue';

interface EventData {
    title: string;
    date: string;
    time: string;
    venue: string;
}

interface Ticket {
    booking_code: string;
    full_name: string;
    email: string;
    phone: string;
    identity_number: string;
    ticket_tier: string;
    quantity: number;
    ticket_price: number;
    subtotal: number;
    tax_amount: number;
    admin_fee: number;
    total_price: number;
    payment_method: string;
    status: string;
}

defineProps<{ event: EventData; ticket: Ticket }>();
const copied = ref(false);

const formatRupiah = (value: number) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);

const copyCode = async (code: string) => {
    await navigator.clipboard.writeText(code);
    copied.value = true;
    setTimeout(() => (copied.value = false), 1800);
};
</script>

<template>
    <Head :title="`Detail E-ticket - ${ticket.booking_code}`" />

    <div class="min-h-screen bg-[#fffaf6] text-[#302d3d]">
        <header class="border-b border-[#e9dfdb] bg-[#fffaf6]">
            <div class="mx-auto flex h-[76px] max-w-5xl items-center justify-between gap-3 px-4 sm:px-5 lg:px-8">
                <button class="flex items-center gap-3 text-left" @click="router.visit('/')">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#cdb7e5] text-[#49365d]"
                        ><TicketIcon class="h-5 w-5"
                    /></span>
                    <span class="min-w-0"
                        ><strong
                            class="block truncate font-serif text-base leading-none text-[#49365d] sm:text-lg"
                            style="font-family: 'Playfair Display', serif"
                            >Neon Echoes</strong
                        ><small class="text-[10px] font-semibold uppercase tracking-[0.18em] text-[#a06c78]">Festival 2027</small></span
                    >
                </button>
                <button class="flex shrink-0 items-center gap-2 text-sm font-bold text-[#a06c78]" @click="router.visit('/')">
                    <ArrowLeft class="h-4 w-4" /> Portal tiket
                </button>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-5 py-12 lg:px-8 lg:py-16">
            <div class="mb-8">
                <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#a06c78]">E-ticket resmi</p>
                <h1 class="mt-2 font-serif text-4xl text-[#49365d] sm:text-5xl" style="font-family: 'Playfair Display', serif">Rincian tiket kamu</h1>
                <p class="mt-3 text-[#817783]">Simpan halaman ini dan tunjukkan kode booking saat penukaran wristband.</p>
            </div>

            <section class="grid gap-6 lg:grid-cols-[1.1fr_.9fr]">
                <article class="rounded-2xl border border-[#ddd0e9] bg-[#eee8f5] p-6 sm:p-8">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.15em] text-[#80629a]">Status pesanan</p>
                            <p class="mt-2 flex items-center gap-2 font-bold text-[#4c8064]"><CheckCircle2 class="h-5 w-5" /> {{ ticket.status }}</p>
                        </div>
                        <span class="rounded-full bg-[#e7f2eb] px-3 py-1 text-xs font-bold text-[#4c8064]">Terverifikasi</span>
                    </div>
                    <div class="mt-8 rounded-xl border-2 border-dashed border-[#cdb7e5] bg-[#fffaf6] p-6 text-center">
                        <p class="text-xs font-bold uppercase tracking-[0.15em] text-[#80629a]">Kode booking</p>
                        <p class="mt-3 break-all font-mono text-2xl font-bold tracking-widest text-[#49365d] sm:text-3xl">
                            {{ ticket.booking_code }}
                        </p>
                        <button class="mt-4 text-sm font-bold text-[#80629a]" @click="copyCode(ticket.booking_code)">
                            <Copy class="mr-1 inline h-4 w-4" /> {{ copied ? 'Kode tersalin' : 'Salin kode' }}
                        </button>
                    </div>
                    <div class="mt-8 grid gap-4 sm:grid-cols-2">
                        <div>
                            <p class="text-xs text-[#817783]">Nama pemesan</p>
                            <p class="mt-1 font-bold text-[#49365d]">{{ ticket.full_name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-[#817783]">Email</p>
                            <p class="mt-1 break-words font-bold text-[#49365d]">{{ ticket.email }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-[#817783]">Kategori</p>
                            <p class="mt-1 font-bold uppercase text-[#49365d]">{{ ticket.ticket_tier }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-[#817783]">Jumlah tiket</p>
                            <p class="mt-1 font-bold text-[#49365d]">{{ ticket.quantity }} tiket</p>
                        </div>
                        <div>
                            <p class="text-xs text-[#817783]">Nomor WhatsApp</p>
                            <p class="mt-1 font-bold text-[#49365d]">{{ ticket.phone }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-[#817783]">Pembayaran</p>
                            <p class="mt-1 font-bold uppercase text-[#49365d]">{{ ticket.payment_method }}</p>
                        </div>
                    </div>
                </article>

                <aside class="h-fit rounded-2xl border border-[#e5dcd7] bg-white p-6 sm:p-8">
                    <h2 class="font-serif text-2xl text-[#49365d]" style="font-family: 'Playfair Display', serif">Jadwal acara</h2>
                    <div class="mt-6 space-y-4 text-sm text-[#716a78]">
                        <p><CalendarDays class="mr-2 inline h-5 w-5 text-[#a06c78]" />{{ event.date }}</p>
                        <p><Clock3 class="mr-2 inline h-5 w-5 text-[#a06c78]" />{{ event.time }}</p>
                        <p><MapPin class="mr-2 inline h-5 w-5 text-[#a06c78]" />{{ event.venue }}</p>
                    </div>
                    <div class="mt-8 border-t border-[#e9dfdb] pt-6">
                        <h3 class="font-bold text-[#49365d]">Ringkasan pembayaran</h3>
                        <dl class="mt-4 space-y-3 text-sm text-[#817783]">
                            <div class="flex flex-wrap justify-between gap-x-4 gap-y-1">
                                <dt>Subtotal</dt>
                                <dd>{{ formatRupiah(ticket.subtotal) }}</dd>
                            </div>
                            <div class="flex flex-wrap justify-between gap-x-4 gap-y-1">
                                <dt>Pajak hiburan</dt>
                                <dd>{{ formatRupiah(ticket.tax_amount) }}</dd>
                            </div>
                            <div class="flex flex-wrap justify-between gap-x-4 gap-y-1">
                                <dt>Biaya admin</dt>
                                <dd>{{ formatRupiah(ticket.admin_fee) }}</dd>
                            </div>
                            <div class="flex flex-wrap justify-between gap-x-4 gap-y-1 border-t border-[#e9dfdb] pt-4 font-bold text-[#49365d]">
                                <dt>Total</dt>
                                <dd class="text-[#a06c78]">{{ formatRupiah(ticket.total_price) }}</dd>
                            </div>
                        </dl>
                    </div>
                </aside>
            </section>

            <section class="mt-6 rounded-2xl border border-[#c8dfd0] bg-[#e7f2eb] p-6 text-sm leading-7 text-[#35644c]">
                <h2 class="font-bold text-[#35644c]">Panduan penukaran wristband</h2>
                <ol class="mt-2 list-inside list-decimal">
                    <li>Tunjukkan kode booking di halaman ini kepada petugas Ticket Booth GBK.</li>
                    <li>Bawa kartu identitas asli yang sesuai dengan data pemesan.</li>
                    <li>Penukaran tersedia mulai H-2 hingga hari H konser.</li>
                </ol>
            </section>
        </main>
    </div>
</template>
