<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('concert_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code')->unique();
            $table->string('full_name');
            $table->string('email');
            $table->string('phone');
            $table->string('identity_number');
            $table->string('ticket_tier');
            $table->unsignedInteger('ticket_price');
            $table->unsignedSmallInteger('quantity');
            $table->unsignedInteger('subtotal');
            $table->unsignedInteger('tax_amount');
            $table->unsignedInteger('admin_fee')->default(5000);
            $table->unsignedInteger('total_price');
            $table->string('payment_method');
            $table->string('status')->default('CONFIRMED');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('concert_tickets');
    }
};
