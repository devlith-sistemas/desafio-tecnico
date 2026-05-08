<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SerieEscolar: string implements HasLabel
{
    case Creche_I = 'creche_i';
    case Creche_II = 'creche_ii';
    case Creche_III = 'creche_iii';
    case Pre_I = 'pre_i';
    case Pre_II = 'pre_ii';
    case Fundamental_1 = 'fundamental_1';
    case Fundamental_2 = 'fundamental_2';
    case Fundamental_3 = 'fundamental_3';
    case Fundamental_4 = 'fundamental_4';
    case Fundamental_5 = 'fundamental_5';
    case Fundamental_6 = 'fundamental_6';
    case Fundamental_7 = 'fundamental_7';
    case Fundamental_8 = 'fundamental_8';
    case Fundamental_9 = 'fundamental_9';
    case Medio_1 = 'medio_1';
    case Medio_2 = 'medio_2';
    case Medio_3 = 'medio_3';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Creche_I => 'Creche I',
            self::Creche_II => 'Creche II',
            self::Creche_III => 'Creche III',
            self::Pre_I => 'Pré I',
            self::Pre_II => 'Pré II',
            self::Fundamental_1 => '1º Ano Fundamental',
            self::Fundamental_2 => '2º Ano Fundamental',
            self::Fundamental_3 => '3º Ano Fundamental',
            self::Fundamental_4 => '4º Ano Fundamental',
            self::Fundamental_5 => '5º Ano Fundamental',
            self::Fundamental_6 => '6º Ano Fundamental',
            self::Fundamental_7 => '7º Ano Fundamental',
            self::Fundamental_8 => '8º Ano Fundamental',
            self::Fundamental_9 => '9º Ano Fundamental',
            self::Medio_1 => '1º Ano Médio',
            self::Medio_2 => '2º Ano Médio',
            self::Medio_3 => '3º Ano Médio',
        };
    }
}
