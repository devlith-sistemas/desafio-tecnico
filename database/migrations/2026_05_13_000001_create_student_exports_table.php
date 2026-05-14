<?php

use App\Enums\StudentExportStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requested_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default(StudentExportStatus::Pending->value)->index();
            $table->string('disk')->default('local');
            $table->string('path')->nullable();
            $table->string('file_name');
            $table->unsignedBigInteger('rows_processed')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->index(['requested_by_user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_exports');
    }
};
