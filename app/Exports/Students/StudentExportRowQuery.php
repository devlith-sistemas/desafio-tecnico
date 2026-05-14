<?php

namespace App\Exports\Students;

use App\Enums\ResultadoFinal;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StudentExportRowQuery
{
    /**
     * @return Collection<int, int>
     */
    public function nextStudentIds(int $lastUserId, int $limit): Collection
    {
        return DB::table('matriculas')
            ->select('user_id')
            ->where('user_id', '>', $lastUserId)
            ->groupBy('user_id')
            ->orderBy('user_id')
            ->limit($limit)
            ->pluck('user_id')
            ->map(fn (int|string $id): int => (int) $id)
            ->values();
    }

    /**
     * @param  Collection<int, int>|array<int, int>  $studentIds
     * @return Collection<int, object>
     */
    public function rowsForStudentIds(Collection|array $studentIds): Collection
    {
        $studentIds = collect($studentIds)
            ->map(fn (int|string $id): int => (int) $id)
            ->filter()
            ->values()
            ->all();

        if ($studentIds === []) {
            return collect();
        }

        $summaryQuery = DB::table('matriculas')
            ->select('user_id')
            ->selectRaw('MIN(ano_letivo) as first_enrollment_year')
            ->selectRaw('MAX(ano_letivo) as last_enrollment_year')
            ->selectRaw('SUM(CASE WHEN resultado_final = ? THEN 1 ELSE 0 END) as approvals', [ResultadoFinal::Aprovado->value])
            ->selectRaw('SUM(CASE WHEN resultado_final = ? THEN 1 ELSE 0 END) as failures', [ResultadoFinal::Reprovado->value])
            ->whereIntegerInRaw('user_id', $studentIds)
            ->groupBy('user_id');

        $latestEnrollmentQuery = DB::table('matriculas as latest_matriculas')
            ->join('escolas as latest_escolas', 'latest_escolas.id', '=', 'latest_matriculas.escola_id')
            ->select('latest_matriculas.user_id')
            ->selectRaw('latest_escolas.nome as school_name')
            ->selectRaw(
                'ROW_NUMBER() OVER (PARTITION BY latest_matriculas.user_id ORDER BY latest_matriculas.ano_letivo DESC, latest_matriculas.data_de_criacao DESC, latest_matriculas.id DESC) as enrollment_position'
            )
            ->whereIntegerInRaw('latest_matriculas.user_id', $studentIds);

        $latestSchoolsQuery = DB::query()
            ->fromSub($latestEnrollmentQuery, 'ranked_matriculas')
            ->where('enrollment_position', 1);

        return DB::table('users')
            ->joinSub($summaryQuery, 'student_summaries', 'student_summaries.user_id', '=', 'users.id')
            ->leftJoinSub($latestSchoolsQuery, 'latest_student_schools', 'latest_student_schools.user_id', '=', 'users.id')
            ->leftJoin('documentos', 'documentos.user_id', '=', 'users.id')
            ->leftJoin('enderecos', 'enderecos.user_id', '=', 'users.id')
            ->whereIntegerInRaw('users.id', $studentIds)
            ->orderBy('users.id')
            ->get([
                'users.id as user_id',
                'users.name',
                'users.email',
                'users.data_de_nascimento as birth_date',
                'student_summaries.first_enrollment_year',
                'student_summaries.last_enrollment_year',
                'latest_student_schools.school_name',
                'documentos.cpf',
                'documentos.rg',
                DB::raw('COALESCE(enderecos.logradouro, enderecos.rua, \'\') as street_address'),
                'enderecos.cep as zip_code',
                'student_summaries.approvals',
                'student_summaries.failures',
            ]);
    }
}
