<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('issue_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issue_id')->constrained('issues')->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('old_status', 30)->nullable();
            $table->string('status', 30);
            $table->string('title', 120)->nullable();
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['issue_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issue_status_histories');
    }
};
