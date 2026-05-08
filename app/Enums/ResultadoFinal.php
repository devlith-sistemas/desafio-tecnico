<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ResultadoFinal: string implements HasLabel
{
    case Aprovado = 'aprovado';
    case Reprovado = 'reprovado';
    case EmAberto = 'em_aberto';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Aprovado => 'Aprovado',
            self::Reprovado => 'Reprovado',
            self::EmAberto => 'Em Aberto',
        };
    }
}
