<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return User::query()
            ->with([
                'documento',
                'endereco',
                'matriculas.escola',
            ]);
    }

    public function headings(): array
    {
        return [
            'Nome',
            'E-mail',
            'Data de Nascimento',
            'Range de Escolaridade',
            'Escola',
            'CPF',
            'RG',
            'Logradouro',
            'CEP',
            'Aprovações',
            'Reprovações',
        ];
    }

    public function map($user): array
    {
        $matriculas = $user->matriculas;

        $primeiraMatricula = $matriculas->min('ano_letivo');

        $ultimaMatricula = $matriculas->max('ano_letivo');

        $rangeEscolaridade = $primeiraMatricula && $ultimaMatricula
            ? "{$primeiraMatricula}-{$ultimaMatricula}"
            : '';

        $matriculaMaisRecente = $matriculas
            ->sortByDesc('ano_letivo')
            ->first();

        $escolaMaisRecente = optional($matriculaMaisRecente?->escola)->nome;

        $aprovacoes = $matriculas
            ->where('resultado_final', 'aprovado')
            ->count();

        $reprovacoes = $matriculas
            ->where('resultado_final', 'reprovado')
            ->count();

        return [
            $user->name,

            $user->email,

            $user->data_de_nascimento?->format('Y-m-d'),

            $rangeEscolaridade,

            $escolaMaisRecente,

            optional($user->documento)->cpf,

            optional($user->documento)->rg,

            optional($user->endereco)->logradouro,

            optional($user->endereco)->cep,

            $aprovacoes,

            $reprovacoes,
        ];
    }


}