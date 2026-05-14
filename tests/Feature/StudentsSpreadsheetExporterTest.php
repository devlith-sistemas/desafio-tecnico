<?php

namespace Tests\Feature;

use App\Enums\ResultadoFinal;
use App\Enums\SerieEscolar;
use App\Enums\StatusMatricula;
use App\Enums\StudentExportStatus;
use App\Exports\Students\StudentsSpreadsheetExporter;
use App\Models\Escola;
use App\Models\Matricula;
use App\Models\StudentExport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentsSpreadsheetExporterTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_writes_the_spreadsheet_to_the_configured_disk(): void
    {
        Storage::fake('local');

        $student = User::factory()->create(['data_de_nascimento' => '2000-01-01']);
        $school = Escola::create(['nome' => 'School 1']);

        Matricula::create([
            'user_id' => $student->id,
            'escola_id' => $school->id,
            'serie_escolar' => SerieEscolar::Fundamental_1,
            'ano_letivo' => 2024,
            'data_de_criacao' => '2024-01-01',
            'status' => StatusMatricula::Ativo,
            'resultado_final' => ResultadoFinal::Aprovado,
        ]);

        $export = StudentExport::create([
            'requested_by_user_id' => $student->id,
            'status' => StudentExportStatus::Processing,
            'disk' => 'local',
            'path' => 'exports/students/test.xlsx',
            'file_name' => 'test.xlsx',
        ]);

        $rowsProcessed = app(StudentsSpreadsheetExporter::class)->write($export);

        $this->assertSame(1, $rowsProcessed);
        Storage::disk('local')->assertExists('exports/students/test.xlsx');
    }
}
