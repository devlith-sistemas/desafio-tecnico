<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Matricula extends Model
{
    /** @use HasFactory<\Database\Factories\MatriculaFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'escola_id',
        'serie_escolar',
        'ano_letivo',
        'data_de_criacao',
        'status',
        'resultado_final',
    ];

    protected function casts(): array
    {
        return [
            'data_de_criacao' => 'date',
            'serie_escolar' => \App\Enums\SerieEscolar::class,
            'status' => \App\Enums\StatusMatricula::class,
            'resultado_final' => \App\Enums\ResultadoFinal::class,
        ];
    }

    public function aluno()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function escola()
    {
        return $this->belongsTo(Escola::class);
    }
}
