<?php

namespace App\Jobs;

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
        $path = storage_path('app/exports/users.csv');

        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        $file = fopen($path, 'w');

        fputcsv($file, [
            'Nome',
            'E-mail',
            'Data de Nascimento',
            'CPF',
            'RG',
            'Logradouro',
            'CEP',
        ]);

        User::query()
            ->leftJoin('documentos', 'documentos.user_id', '=', 'users.id')
            ->leftJoin('enderecos', 'enderecos.user_id', '=', 'users.id')
            ->select([
                'users.name',
                'users.email',
                'users.data_de_nascimento',
                'documentos.cpf',
                'documentos.rg',
                'enderecos.logradouro',
                'enderecos.cep',
            ])
            ->chunk(1000, function ($users) use ($file) {
                foreach ($users as $user) {
                    fputcsv($file, [
                        $user->name,
                        $user->email,
                        $user->data_de_nascimento,
                        $user->cpf,
                        $user->rg,
                        $user->logradouro,
                        $user->cep,
                    ]);
                }
            });

        fclose($file);
    }
}