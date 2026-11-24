<?php

use App\Enums\UserRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('student_id', 40)->nullable()->unique()->after('name');
            $table->string('role', 20)->default(UserRole::Student->value)->index()->after('password');
            $table->foreignId('department_id')->nullable()->after('role')->constrained()->nullOnDelete();
            $table->string('phone', 30)->nullable()->after('department_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('department_id');
            $table->dropUnique(['student_id']);
            $table->dropIndex(['role']);
            $table->dropColumn(['student_id', 'role', 'phone']);
        });
    }
};
