<?php

namespace Tests\Feature;

use App\Enums\ResultadoFinal;
use App\Enums\SerieEscolar;
use App\Enums\StatusMatricula;
use App\Exports\Students\StudentExportRowQuery;
use App\Models\Documento;
use App\Models\Endereco;
use App\Models\Escola;
use App\Models\Matricula;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentExportRowQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_student_export_rows_with_aggregated_enrollment_data(): void
    {
        $student = User::factory()->create([
            'name' => 'Student 1',
            'email' => 'student@example.com',
            'data_de_nascimento' => '2000-01-01',
        ]);

        User::factory()->create();

        $oldSchool = Escola::create(['nome' => 'School 1']);
        $recentSchool = Escola::create(['nome' => 'School 2']);

        Documento::create([
            'user_id' => $student->id,
            'cpf' => '11111111111',
            'rg' => '111111111',
        ]);

        Endereco::create([
            'user_id' => $student->id,
            'logradouro' => 'Street Address',
            'cep' => '99999999',
        ]);

        Matricula::create([
            'user_id' => $student->id,
            'escola_id' => $oldSchool->id,
            'serie_escolar' => SerieEscolar::Fundamental_1,
            'ano_letivo' => 2020,
            'data_de_criacao' => '2020-01-01',
            'status' => StatusMatricula::Ativo,
            'resultado_final' => ResultadoFinal::Aprovado,
        ]);

        Matricula::create([
            'user_id' => $student->id,
            'escola_id' => $recentSchool->id,
            'serie_escolar' => SerieEscolar::Fundamental_2,
            'ano_letivo' => 2024,
            'data_de_criacao' => '2024-01-01',
            'status' => StatusMatricula::Ativo,
            'resultado_final' => ResultadoFinal::Reprovado,
        ]);

        $query = app(StudentExportRowQuery::class);
        $studentIds = $query->nextStudentIds(0, 10);
        $row = $query->rowsForStudentIds($studentIds)->first();

        $this->assertSame([$student->id], $studentIds->all());
        $this->assertSame('Student 1', $row->name);
        $this->assertSame('student@example.com', $row->email);
        $this->assertSame(2020, (int) $row->first_enrollment_year);
        $this->assertSame(2024, (int) $row->last_enrollment_year);
        $this->assertSame('School 2', $row->school_name);
        $this->assertSame(1, (int) $row->approvals);
        $this->assertSame(1, (int) $row->failures);
    }
}
