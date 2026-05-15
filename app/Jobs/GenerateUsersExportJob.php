<?php

namespace App\Jobs;

use App\Enums\ResultadoFinal;
use App\Models\Matricula;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class GenerateUsersExportJob implements ShouldQueue
{
    use Queueable;

    public $timeout = 3600;

    public function handle(): void
    {
        $filename = 'users-' . now()->timestamp . '.csv';

        Storage::disk('public')->makeDirectory('exports');

        $path = storage_path("app/public/exports/{$filename}");

        $file = fopen($path, 'w');

        fputcsv($file, [
            'Nome',
            'E-mail',
            'Data de Nascimento',
            'Range Escolaridade',
            'Escola Mais Recente',
            'CPF',
            'RG',
            'Logradouro',
            'CEP',
            'Aprovações',
            'Reprovações',
        ]);

        $aprovado = ResultadoFinal::Aprovado->value;
        $reprovado = ResultadoFinal::Reprovado->value;

        User::query()

            ->with([
                'documento:user_id,cpf,rg',
                'endereco:user_id,logradouro,cep',
            ])

            ->select([
                'id',
                'name',
                'email',
                'data_de_nascimento',
            ])

            ->orderBy('id')

            ->chunkById(1000, function ($users) use (
                $file,
                $aprovado,
                $reprovado
            ) {

                /**
                 * IDs do chunk atual
                 */
                $userIds = $users->pluck('id');

                /**
                 * AGREGAÇÕES
                 */
                $matriculas = Matricula::query()

                    ->whereIn('user_id', $userIds)

                    ->selectRaw("
                        user_id,

                        MIN(ano_letivo) as primeiro_ano,
                        MAX(ano_letivo) as ultimo_ano,

                        SUM(
                            CASE
                                WHEN resultado_final = '{$aprovado}'
                                THEN 1
                                ELSE 0
                            END
                        ) as aprovacoes,

                        SUM(
                            CASE
                                WHEN resultado_final = '{$reprovado}'
                                THEN 1
                                ELSE 0
                            END
                        ) as reprovacoes
                    ")

                    ->groupBy('user_id')

                    ->get()

                    ->keyBy('user_id');

                /**
                 * MATRÍCULA MAIS RECENTE
                 */
                $ultimaMatricula = Matricula::query()

                    ->whereIn('user_id', $userIds)

                    ->with('escola:id,nome')

                    ->orderByDesc('ano_letivo')

                    ->orderByDesc('id')

                    ->get()

                    ->unique('user_id')

                    ->keyBy('user_id');

                /**
                 * ESCREVE CSV
                 */
                foreach ($users as $user) {

                    $matricula = $matriculas[$user->id] ?? null;

                    $ultima = $ultimaMatricula[$user->id] ?? null;

                    $rangeEscolaridade = '';

                    if (
                        $matricula?->primeiro_ano &&
                        $matricula?->ultimo_ano
                    ) {
                        $rangeEscolaridade =
                            $matricula->primeiro_ano .
                            '-' .
                            $matricula->ultimo_ano;
                    }

                    fputcsv($file, [
                        $user->name,
                        $user->email,

                        optional($user->data_de_nascimento)
                            ?->format('Y-m-d'),

                        $rangeEscolaridade,

                        $ultima?->escola?->nome,

                        $this->formatCpf(
                            $user->documento?->cpf
                        ),

                        $this->formatRg(
                            $user->documento?->rg
                        ),

                        $user->endereco?->logradouro,

                        $this->formatCep(
                            $user->endereco?->cep
                        ),

                        $matricula?->aprovacoes ?? 0,
                        $matricula?->reprovacoes ?? 0,
                    ]);
                }
            });

        fclose($file);

        cache()->put(
            'last_users_export',
            $filename,
            now()->addHour()
        );
    }

    private function formatCpf(?string $cpf): ?string
    {
        if (!$cpf) {
            return null;
        }

        $cpf = preg_replace('/\D/', '', $cpf);

        return preg_replace(
            '/(\d{3})(\d{3})(\d{3})(\d{2})/',
            '$1.$2.$3-$4',
            $cpf
        );
    }

    private function formatRg(?string $rg): ?string
    {
        if (!$rg) {
            return null;
        }

        return $rg;
    }

    private function formatCep(?string $cep): ?string
    {
        if (!$cep) {
            return null;
        }

        $cep = preg_replace('/\D/', '', $cep);

        return preg_replace(
            '/(\d{5})(\d{3})/',
            '$1-$2',
            $cep
        );
    }
}