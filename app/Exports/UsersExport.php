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
        return [
            $user->name,
            $user->email,
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
        ];
    }
}