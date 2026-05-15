<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromQuery, WithHeadings, WithMapping, WithChunkReading
{
    public function query()
    {
        return User::query()
            ->leftJoin('documentos', 'documentos.user_id', '=', 'users.id')
            ->leftJoin('enderecos', 'enderecos.user_id', '=', 'users.id')
            ->select([
                'users.id',
                'users.name',
                'users.email',
                'users.data_de_nascimento',
                'documentos.cpf',
                'documentos.rg',
                'enderecos.logradouro',
                'enderecos.cep',
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
            $user->data_de_nascimento,
            '',
            '',
            $user->cpf,
            $user->rg,
            $user->logradouro,
            $user->cep,
            '',
            '',
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}