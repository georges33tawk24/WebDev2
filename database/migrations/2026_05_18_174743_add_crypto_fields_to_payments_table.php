<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {

            $table->string('crypto_currency')->nullable();

            $table->string('wallet_address')->nullable();

            $table->string('transaction_hash')->nullable();

            $table->decimal('crypto_amount', 18, 8)->nullable();

            $table->timestamp('confirmed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {

            $table->dropColumn([
                'crypto_currency',
                'wallet_address',
                'transaction_hash',
                'crypto_amount',
                'confirmed_at',
            ]);
        });
    }
};