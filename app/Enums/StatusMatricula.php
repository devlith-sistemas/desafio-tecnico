<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum StatusMatricula: string implements HasLabel
{
    case Ativo = 'ativo';
    case Cancelado = 'cancelado';
    case Remanejado = 'remanejado';
    case Transferido = 'transferido';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Ativo => 'Ativo',
            self::Cancelado => 'Cancelado',
            self::Remanejado => 'Remanejado',
            self::Transferido => 'Transferido',
        };
    }
}
