<?php

namespace App;

enum Governorate: int //string
{
    case DAMASCUS = 0 ;//'Damascus';
    case RIF_DAMASCUS = 1;//'Rif_Damascus';
    case ALEPPO = 2;//'Aleppo';
    case HOMS = 3;//'Homs';
    case HAMA = 4;//'Hama';
    case LATakia = 5;//'Latakia';
    case TARTOUS = 6;//'Tartous';
    case IDLIB = 7;//'Idlib';
    case DARAA = 8;//'Daraa';
    case SUWAYDA = 9;//'As_Suwayda';
    case QUNEITRA = 10;//'Quneitra';
    case DEIR_EZZOR = 11;//'Deir_Ezzor';
    case HASAKAH = 12;//'Al_Hasakah';
    case RAQQA = 13;//'Raqqa';
}
// public static function values(): array
//     {
//         return array_map(fn ($case) => $case->value, self::cases());
//     }
// }