<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buildings', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('code', 20)->nullable()->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->foreignId('building_id')
                ->nullable()
                ->after('id')
                ->constrained('buildings')
                ->restrictOnDelete();

            $table->index(['building_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropIndex(['building_id', 'is_active']);
            $table->dropConstrainedForeignId('building_id');
        });

        Schema::dropIfExists('buildings');
    }
};
