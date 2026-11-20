<?php

use App\Enums\IssuePriority;
use App\Enums\IssueStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('issues', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number', 32)->unique();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->foreignId('location_id')->constrained('locations')->restrictOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 120);
            $table->text('description');
            $table->string('priority', 20)->default(IssuePriority::Medium->value)->index();
            $table->string('status', 30)->default(IssueStatus::Submitted->value)->index();
            $table->text('admin_note')->nullable();
            $table->text('resolution_note')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['reporter_id', 'status']);
            $table->index(['assigned_to', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issues');
    }
};
