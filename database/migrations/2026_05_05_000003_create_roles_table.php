<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('role_id')->nullable()->constrained()->nullOnDelete();
            $table->string('phone')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('id_document_path')->nullable();
            $table->timestamp('two_factor_verified_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('role_id');
            $table->dropColumn(['phone', 'date_of_birth', 'id_document_path', 'two_factor_verified_at']);
        });

        Schema::dropIfExists('roles');
    }
};
