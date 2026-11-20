<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('issue_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issue_id')->constrained('issues')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->string('original_name', 255);
            $table->string('disk', 30)->default('local');
            $table->string('file_path', 500);
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issue_attachments');
    }
};
