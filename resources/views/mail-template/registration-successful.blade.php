<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Ticket Konfirmasi Pendaftaran Konser - NEON ECHOES 2025</title>
    <style>
        /* Email client resets */
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
        body { margin: 0; padding: 0; width: 100% !important; background-color: #080408; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
    </style>
</head>
<body style="margin: 0; padding: 30px 10px; background-color: #080408; color: #f1f5f9;">
    <!-- Main Center Table Container -->
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td align="center">
                <!-- Email Wrapper Box (max 600px) -->
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #120b12; border: 1px solid #f59e0b40; border-radius: 24px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.6);">
                    
                    <!-- Header Banner -->
                    <tr>
                        <td align="center" style="padding: 36px 30px 24px 30px; background: linear-gradient(135deg, #1f111f 0%, #29121a 50%, #150a15 100%); border-bottom: 1px solid #f59e0b30;">
                            <!-- Glowing Music Badge -->
                            <div style="display: inline-block; padding: 6px 16px; border-radius: 9999px; background-color: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.4); margin-bottom: 14px;">
                                <span style="font-size: 11px; font-weight: 800; letter-spacing: 2px; text-transform: uppercase; color: #fbbf24;">
                                    OFFICIAL E-TICKET CONFIRMATION
                                </span>
                            </div>
                            <h1 style="margin: 0; font-size: 26px; font-weight: 900; letter-spacing: 1px; color: #ffffff; text-transform: uppercase;">
                                NEON ECHOES FESTIVAL 2025
                            </h1>
                            <p style="margin: 8px 0 0 0; font-size: 13px; color: #fca5a5; letter-spacing: 0.5px;">
                                Sunset Horizon Tour • Live at Gelora Bung Karno (GBK)
                            </p>
                        </td>
                    </tr>

                    <!-- Body Greeting -->
                    <tr>
                        <td style="padding: 30px 32px 20px 32px;">
                            <p style="margin: 0 0 14px 0; font-size: 17px; font-weight: 700; color: #ffffff;">
                                Halo, <span style="color: #fbbf24;">{{ $user_name }}</span>! 👋
                            </p>
                            <p style="margin: 0 0 24px 0; font-size: 14px; line-height: 1.6; color: #cbd5e1;">
                                Pendaftaran tiket konser Anda telah <strong>berhasil dan terkonfirmasi</strong> di sistem kami. E-Ticket resmi beserta kode booking unik Anda telah aktif dan siap digunakan untuk penukaran wristband.
                            </p>

                            <!-- Booking Code Highlight Box -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #090409; border: 2px dashed #f59e0b; border-radius: 16px; margin-bottom: 26px;">
                                <tr>
                                    <td align="center" style="padding: 20px 16px;">
                                        <span style="display: block; font-size: 11px; font-weight: 800; letter-spacing: 2px; text-transform: uppercase; color: #94a3b8; margin-bottom: 6px;">
                                            KODE BOOKING TIKET
                                        </span>
                                        <span style="display: block; font-size: 28px; font-weight: 900; letter-spacing: 4px; color: #fbbf24; font-family: monospace;">
                                            {{ $ticket->booking_code ?? 'NEON-2025-CONFIRMED' }}
                                        </span>
                                        <span style="display: block; font-size: 11px; color: #34d399; font-weight: 700; margin-top: 6px;">
                                            ✓ STATUS: TERVERIFIKASI / LUNAS
                                        </span>
                                    </td>
                                </tr>
                            </table>

                            <!-- Ticket & Buyer Details -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #170e17; border: 1px solid rgba(255,255,255,0.08); border-radius: 14px; margin-bottom: 26px; font-size: 13px;">
                                <tr>
                                    <td colspan="2" style="padding: 14px 18px; border-bottom: 1px solid rgba(255,255,255,0.08); font-size: 13px; font-weight: 800; text-transform: uppercase; color: #f59e0b; letter-spacing: 1px;">
                                        📋 Rincian E-Ticket
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 18px; color: #94a3b8; width: 40%;">Nama Pemesan</td>
                                    <td style="padding: 10px 18px; color: #ffffff; font-weight: 700; text-align: right;">{{ $user_name }}</td>
                                </tr>
                                @if(!empty($ticket->identity_number))
                                <tr>
                                    <td style="padding: 10px 18px; color: #94a3b8;">NIK (KTP)</td>
                                    <td style="padding: 10px 18px; color: #ffffff; font-family: monospace; text-align: right;">{{ $ticket->identity_number }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td style="padding: 10px 18px; color: #94a3b8;">Email</td>
                                    <td style="padding: 10px 18px; color: #ffffff; text-align: right;">{{ $user_email }}</td>
                                </tr>
                                @if(!empty($ticket->phone))
                                <tr>
                                    <td style="padding: 10px 18px; color: #94a3b8;">No. WhatsApp / HP</td>
                                    <td style="padding: 10px 18px; color: #ffffff; text-align: right;">{{ $ticket->phone }}</td>
                                </tr>
                                @endif
                                @if(!empty($ticket->ticket_tier))
                                <tr>
                                    <td style="padding: 10px 18px; color: #94a3b8;">Kategori Tiket</td>
                                    <td style="padding: 10px 18px; color: #fbbf24; font-weight: 800; text-transform: uppercase; text-align: right;">
                                        {{ $ticket->ticket_tier }} ({{ $ticket->quantity ?? 1 }} Tiket)
                                    </td>
                                </tr>
                                @endif
                                @if(!empty($ticket->payment_method))
                                <tr>
                                    <td style="padding: 10px 18px; color: #94a3b8;">Metode Pembayaran</td>
                                    <td style="padding: 10px 18px; color: #ffffff; font-weight: 700; text-transform: uppercase; text-align: right;">
                                        {{ $ticket->payment_method }}
                                    </td>
                                </tr>
                                @endif
                                @if(!empty($ticket->total_price))
                                <tr>
                                    <td style="padding: 14px 18px; border-top: 1px solid rgba(255,255,255,0.08); color: #cbd5e1; font-weight: 800;">Total Pembayaran</td>
                                    <td style="padding: 14px 18px; border-top: 1px solid rgba(255,255,255,0.08); color: #34d399; font-weight: 900; font-size: 15px; text-align: right;">
                                        Rp {{ number_format($ticket->total_price, 0, ',', '.') }}
                                    </td>
                                </tr>
                                @endif
                            </table>

                            <!-- Event Details Card -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #170e17; border: 1px solid rgba(245, 158, 11, 0.25); border-radius: 14px; margin-bottom: 26px;">
                                <tr>
                                    <td style="padding: 16px 20px;">
                                        <div style="font-size: 12px; font-weight: 800; color: #f59e0b; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">
                                            📍 Jadwal & Lokasi Konser
                                        </div>
                                        <div style="font-size: 14px; font-weight: 700; color: #ffffff; margin-bottom: 4px;">
                                            📅 Sabtu, 28 Juni 2025
                                        </div>
                                        <div style="font-size: 13px; color: #cbd5e1; margin-bottom: 4px;">
                                            ⏰ 16:00 - 23:30 WIB (Open Gate: 14:30 WIB)
                                        </div>
                                        <div style="font-size: 13px; color: #cbd5e1; margin-bottom: 10px;">
                                            🏟️ Stadion Utama Gelora Bung Karno (GBK), Senayan, Jakarta
                                        </div>
                                        <div style="font-size: 12px; color: #94a3b8; border-top: 1px solid rgba(255,255,255,0.06); padding-top: 8px;">
                                            🌟 <strong>Lineup:</strong> Sheila on 7 • Hindia • Reality Club • Coldiac • The Adams • Maliq & D'Essentials
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <!-- Instructions / How to Redeem -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #0d070d; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05); margin-bottom: 26px;">
                                <tr>
                                    <td style="padding: 16px 20px;">
                                        <div style="font-size: 12px; font-weight: 800; color: #ffffff; text-transform: uppercase; margin-bottom: 8px;">
                                            ⚠️ Panduan Penukaran Wristband:
                                        </div>
                                        <ol style="margin: 0; padding-left: 20px; font-size: 12px; line-height: 1.6; color: #94a3b8;">
                                            <li style="margin-bottom: 4px;">Tunjukkan email konfirmasi ini atau <strong>Kode Booking</strong> kepada petugas Ticket Box di GBK.</li>
                                            <li style="margin-bottom: 4px;">Wajib membawa kartu identitas asli (KTP/SIM/Paspor) sesuai data yang didaftarkan.</li>
                                            <li style="margin-bottom: 4px;">Penukaran wristband dapat dilakukan mulai H-2 hingga Hari H di Ticket Booth Stadion GBK.</li>
                                            <li>Dilarang membawa senjata tajam, kembang api, makanan & minuman dari luar ke dalam venue.</li>
                                        </ol>
                                    </td>
                                </tr>
                            </table>

                            <!-- CTA Button -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center" style="padding-bottom: 10px;">
                                        <a href="{{ url('/') }}" target="_blank" style="display: inline-block; padding: 14px 32px; background: linear-gradient(135deg, #f59e0b 0%, #ea580c 50%, #e11d48 100%); color: #080408; font-size: 13px; font-weight: 900; text-decoration: none; text-transform: uppercase; letter-spacing: 1px; border-radius: 12px; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4);">
                                            Buka Portal Tiket Konser
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding: 24px 30px; background-color: #0b050b; border-top: 1px solid rgba(255,255,255,0.08); font-size: 11px; color: #64748b; line-height: 1.5;">
                            <p style="margin: 0 0 6px 0; font-weight: 700; color: #94a3b8;">
                                NEON ECHOES FESTIVAL 2025 • PROMOTOR MUSIK RESMI
                            </p>
                            <p style="margin: 0 0 6px 0;">
                                Butuh bantuan? Hubungi panitia via WhatsApp Hotline: <strong>+62 812-3456-7890</strong>
                            </p>
                            <p style="margin: 0; color: #475569;">
                                Email ini dikirim secara otomatis oleh sistem pendaftaran konser. Harap tidak membalas email ini secara langsung.
                            </p>
                        </td>
                    </tr>

                </table>
                <!-- End Email Wrapper Box -->
            </td>
        </tr>
    </table>
</body>
</html>
