import { defineComponent, h } from 'vue';

const escapeHtml = (value) =>
    String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

const money = (value) => `Rp ${new Intl.NumberFormat('id-ID').format(Number(value ?? 0))}`;

const row = (label, value) =>
    value
        ? `<tr><td class="detail-label" style="padding:10px 18px;color:#817783;font-size:13px;">${escapeHtml(label)}</td><td class="detail-value" align="right" style="padding:10px 18px;color:#49365d;font-size:13px;font-weight:bold;">${escapeHtml(value)}</td></tr>`
        : '';

export default defineComponent({
    props: {
        userName: { type: String, required: true },
        userEmail: { type: String, required: true },
        ticket: { type: Object, default: null },
        eventData: { type: Object, required: true },
        portalUrl: { type: String, required: true },
    },
    setup(props) {
        return () =>
            h('div', {
                innerHTML: `
                    <html lang="id">
                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <style>
                            @media only screen and (max-width: 480px) {
                                body { padding: 16px 8px !important; }
                                .email-shell { border-radius: 14px !important; }
                                .email-header, .email-content { padding: 24px 18px !important; }
                                .email-footer { padding: 20px 18px !important; }
                                .email-title { font-size: 24px !important; }
                                .booking-code { font-size: 20px !important; letter-spacing: 1px !important; overflow-wrap: anywhere !important; }
                                .detail-label, .detail-value { display: block !important; width: auto !important; padding: 6px 12px !important; text-align: left !important; }
                                .detail-value { padding-top: 0 !important; overflow-wrap: anywhere !important; }
                                .email-button { display: block !important; padding: 13px 16px !important; }
                            }
                        </style>
                        <title>E-ticket ${escapeHtml(props.eventData.title)}</title>
                    </head>
                    <body style="margin:0;padding:32px 12px;background:#fffaf6;color:#302d3d;font-family:Arial,Helvetica,sans-serif;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td align="center">
                            <table class="email-shell" role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:620px;background:#fffaf6;border:1px solid #e5dcd7;border-radius:20px;overflow:hidden;">
                                <tr><td class="email-header" style="padding:34px 32px;background:#eee8f5;border-bottom:1px solid #ddd0e9;">
                                    <p style="margin:0 0 12px;color:#80629a;font-size:11px;font-weight:bold;letter-spacing:2px;text-transform:uppercase;">Konfirmasi e-ticket</p>
                                    <h1 class="email-title" style="margin:0;color:#49365d;font-family:Georgia,serif;font-size:30px;line-height:1.2;">${escapeHtml(props.eventData.title)}</h1>
                                    <p style="margin:10px 0 0;color:#716a78;font-size:14px;">Satu malam untuk musik, teman, dan cerita baru.</p>
                                </td></tr>
                                <tr><td class="email-content" style="padding:32px;">
                                    <p style="margin:0 0 12px;color:#49365d;font-size:18px;font-weight:bold;">Halo, ${escapeHtml(props.userName)}</p>
                                    <p style="margin:0 0 24px;color:#716a78;font-size:14px;line-height:1.7;">Pendaftaran tiket konser kamu berhasil. Simpan email ini karena kode booking berikut diperlukan saat penukaran wristband.</p>
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;border:2px dashed #cdb7e5;border-radius:14px;background:#eee8f5;"><tr><td align="center" style="padding:22px 14px;">
                                        <p style="margin:0;color:#80629a;font-size:11px;font-weight:bold;letter-spacing:2px;text-transform:uppercase;">Kode booking</p>
                                        <p class="booking-code" style="margin:9px 0;color:#49365d;font-family:monospace;font-size:27px;font-weight:bold;letter-spacing:3px;">${escapeHtml(props.ticket?.booking_code ?? 'NEON-2025-CONFIRMED')}</p>
                                        <p style="margin:0;color:#4c8064;font-size:12px;font-weight:bold;">TERKONFIRMASI</p>
                                    </td></tr></table>
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;border:1px solid #e5dcd7;border-radius:14px;">
                                        <tr><td colspan="2" style="padding:15px 18px;border-bottom:1px solid #e9dfdb;color:#a06c78;font-size:12px;font-weight:bold;letter-spacing:1px;text-transform:uppercase;">Rincian pesanan</td></tr>
                                        ${row('Nama pemesan', props.userName)}
                                        ${row('Email', props.userEmail)}
                                        ${row('NIK / identitas', props.ticket?.identity_number)}
                                        ${row('Nomor WhatsApp', props.ticket?.phone)}
                                        ${row('Kategori', props.ticket ? `${props.ticket.ticket_tier} · ${props.ticket.quantity} tiket` : '-')}
                                        ${row('Pembayaran', props.ticket?.payment_method ?? '-')}
                                        <tr><td class="detail-label" style="padding:14px 18px;border-top:1px solid #e9dfdb;color:#49365d;font-size:13px;font-weight:bold;">Total pembayaran</td><td class="detail-value" align="right" style="padding:14px 18px;border-top:1px solid #e9dfdb;color:#a06c78;font-size:16px;font-weight:bold;">${money(props.ticket?.total_price)}</td></tr>
                                    </table>
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;border-radius:14px;background:#e7f2eb;"><tr><td style="padding:18px 20px;color:#35644c;font-size:13px;line-height:1.7;">
                                        <strong style="display:block;margin-bottom:8px;color:#35644c;">Jadwal dan lokasi</strong>
                                        ${escapeHtml(props.eventData.date)}<br>${escapeHtml(props.eventData.time)}<br>${escapeHtml(props.eventData.venue)}
                                    </td></tr></table>
                                    <p style="margin:0;text-align:center;"><a class="email-button" href="${escapeHtml(props.portalUrl)}" style="display:inline-block;padding:13px 25px;border-radius:10px;background:#a06c78;color:#ffffff;font-size:13px;font-weight:bold;text-decoration:none;">Buka portal tiket</a></p>
                                </td></tr>
                                <tr><td class="email-footer" align="center" style="padding:24px 30px;background:#49365d;color:#bdb0cc;font-size:11px;line-height:1.6;"><strong style="color:#ffffff;">${escapeHtml(props.eventData.title)}</strong><br>Butuh bantuan? Hubungi panitia via WhatsApp: +62 812-3456-7890</td></tr>
                            </table>
                        </td></tr></table>
                    </body>
                    </html>
                `,
            });
    },
});
