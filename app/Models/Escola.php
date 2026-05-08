<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Escola extends Model
{
    /** @use HasFactory<\Database\Factories\EscolaFactory> */
    use HasFactory;

    protected $fillable = [
        'nome',
    ];

    public function matriculas()
    {
        return $this->hasMany(Matricula::class);
    }
}
