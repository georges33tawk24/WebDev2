<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offices', function (Blueprint $table): void {
            $table->string('name_ar')->nullable()->after('name');
            $table->string('municipality_ar')->nullable()->after('municipality');
            $table->string('address_ar')->nullable()->after('address');
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->string('name_ar')->nullable()->after('name');
            $table->text('description_ar')->nullable()->after('description');
        });

        Schema::table('services', function (Blueprint $table): void {
            $table->string('name_ar')->nullable()->after('name');
            $table->text('description_ar')->nullable()->after('description');
            $table->json('required_documents_ar')->nullable()->after('required_documents');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            $table->dropColumn(['name_ar', 'description_ar', 'required_documents_ar']);
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->dropColumn(['name_ar', 'description_ar']);
        });

        Schema::table('offices', function (Blueprint $table): void {
            $table->dropColumn(['name_ar', 'municipality_ar', 'address_ar']);
        });
    }
};
