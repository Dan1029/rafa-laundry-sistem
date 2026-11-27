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
        Schema::table('layanans', function (Blueprint $table) {
            $table->renameColumn('price_per-unit', 'price_per_unit');
        });

        Schema::create('pesanans', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_id')->constrained('layanans')->restrictOnDelete();
            $table->foreignId('slot_id')->nullable()->constrained('transaksis')->nullOnDelete();
            $table->string('customer_name');
            $table->string('customer_phone', 25);
            $table->string('pickup_address', 500)->nullable();
            $table->date('pickup_date');
            $table->enum('fulfillment_type', ['pickup_delivery', 'drop_off']);
            $table->decimal('quantity', 8, 2);
            $table->decimal('total_price', 12, 2);
            $table->enum('status', ['pending', 'processing', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesanans');

        Schema::table('layanans', function (Blueprint $table) {
            $table->renameColumn('price_per_unit', 'price_per-unit');
        });
    }
};
