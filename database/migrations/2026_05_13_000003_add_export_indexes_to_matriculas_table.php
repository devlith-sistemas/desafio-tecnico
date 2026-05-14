<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('matriculas', function (Blueprint $table) {
            $table->index(['user_id', 'ano_letivo', 'resultado_final'], 'matriculas_export_summary_idx');
            $table->index(['user_id', 'ano_letivo', 'data_de_criacao', 'id'], 'matriculas_export_latest_idx');
        });
    }

    public function down(): void
    {
        Schema::table('matriculas', function (Blueprint $table) {
            $table->dropIndex('matriculas_export_summary_idx');
            $table->dropIndex('matriculas_export_latest_idx');
        });
    }
};
