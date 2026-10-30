<?php

namespace Tests\Feature;

use App\Mail\ConcertTicket as ConcertTicketMail;
use App\Models\ConcertTicket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ConcertTicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_concert_ticket_page_can_be_rendered(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Welcome')
            ->has('event')
            ->has('totalBookedCount')
        );
    }

    public function test_user_can_register_concert_ticket(): void
    {
        Mail::fake();

        $payload = [
            'full_name' => 'Budi Santoso',
            'email' => 'budi.santoso@example.com',
            'phone' => '081234567890',
            'identity_number' => '3201012345670001',
            'ticket_tier' => 'festival',
            'quantity' => 2,
            'payment_method' => 'qris',
            'notes' => 'Tolong tempat dekat sound',
            'agree_terms' => true,
        ];

        $response = $this->post('/ticket/register', $payload);

        $response->assertRedirect('/');
        $response->assertSessionHas('success');
        $response->assertSessionHas('recent_ticket_code');

        Mail::assertSent(ConcertTicketMail::class, function ($mail) {
            return $mail->hasTo('budi.santoso@example.com')
                && $mail->user_name === 'Budi Santoso';
        });

        $this->assertDatabaseHas('concert_tickets', [
            'full_name' => 'Budi Santoso',
            'email' => 'budi.santoso@example.com',
            'phone' => '081234567890',
            'identity_number' => '3201012345670001',
            'ticket_tier' => 'festival',
            'quantity' => 2,
            'ticket_price' => 950000,
            'subtotal' => 1900000,
            'tax_amount' => 190000,
            'admin_fee' => 5000,
            'total_price' => 2095000,
            'status' => 'CONFIRMED',
        ]);
    }

    public function test_ticket_validation_fails_on_missing_fields(): void
    {
        $response = $this->post('/ticket/register', []);

        $response->assertSessionHasErrors([
            'full_name',
            'email',
            'phone',
            'identity_number',
            'ticket_tier',
            'quantity',
            'payment_method',
            'agree_terms',
        ]);
    }

    public function test_user_can_search_booked_ticket(): void
    {
        $ticket = ConcertTicket::create([
            'booking_code' => 'NEON-2025-ABCDE',
            'full_name' => 'Citra Lestari',
            'email' => 'citra@example.com',
            'phone' => '081298765432',
            'identity_number' => '3171012345670002',
            'ticket_tier' => 'vip',
            'ticket_price' => 1750000,
            'quantity' => 1,
            'subtotal' => 1750000,
            'tax_amount' => 175000,
            'admin_fee' => 5000,
            'total_price' => 1930000,
            'payment_method' => 'qris',
            'status' => 'CONFIRMED',
        ]);

        $response = $this->getJson('/ticket/check?search=' . $ticket->booking_code);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'ticket' => [
                'booking_code' => 'NEON-2025-ABCDE',
                'full_name' => 'Citra Lestari',
            ],
        ]);
    }

    public function test_ticket_detail_page_can_be_rendered_from_booking_code(): void
    {
        $ticket = ConcertTicket::create([
            'booking_code' => 'NEON-2027-DETAIL',
            'full_name' => 'Dina Pratama',
            'email' => 'dina@example.com',
            'phone' => '081298765432',
            'identity_number' => '3171012345670002',
            'ticket_tier' => 'festival',
            'ticket_price' => 950000,
            'quantity' => 1,
            'subtotal' => 950000,
            'tax_amount' => 95000,
            'admin_fee' => 5000,
            'total_price' => 1050000,
            'payment_method' => 'qris',
            'status' => 'CONFIRMED',
        ]);

        $response = $this->get(route('ticket.show', ['bookingCode' => $ticket->booking_code]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('TicketDetails')
            ->where('ticket.booking_code', 'NEON-2027-DETAIL')
            ->where('ticket.full_name', 'Dina Pratama')
        );
    }

    public function test_mailable_can_be_rendered(): void
    {
        $ticket = new ConcertTicket([
            'booking_code' => 'NEON-2025-MAIL1',
            'full_name' => 'Raditya Danendra',
            'email' => 'raditya@example.com',
            'phone' => '081234567890',
            'identity_number' => '3171012345670001',
            'ticket_tier' => 'vip',
            'ticket_price' => 1750000,
            'quantity' => 2,
            'subtotal' => 3500000,
            'tax_amount' => 350000,
            'admin_fee' => 5000,
            'total_price' => 3855000,
            'payment_method' => 'qris',
            'status' => 'CONFIRMED',
        ]);

        $mailable = new ConcertTicketMail('raditya@example.com', 'Raditya Danendra', $ticket);

        $html = $mailable->render();

        $this->assertStringContainsString('Raditya Danendra', $html);
        $this->assertStringContainsString('NEON-2025-MAIL1', $html);
        $this->assertStringContainsString('Rp 3.855.000', $html);
    }
}
