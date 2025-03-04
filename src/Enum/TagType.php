<?php

namespace App\Enum;

enum TagType: string
{
    case DURABILITE = '#Durabilité';
    case REUTILISATION = '#Réutilisation';
    case RECYCLAGE_PLASTIQUE = '#RecyclagePlastique';
    case RECYCLAGE_PAPIER = '#RecyclagePapier';
    case RECYCLAGE_METAL = '#RecyclageMétal';
    case RECYCLAGE_VERRE = '#RecyclageVerre';
    case DIY_RECYCLAGE = '#DIYRecyclage';
    case QUESTION = '#Question';
    case RECLAMATION = '#Réclamation';
    case INITIATIVE = '#Initiative';

    public function label(): string
    {
        return match($this) {
            self::DURABILITE => '#Durabilité',
            self::REUTILISATION => '#Réutilisation',
            self::RECYCLAGE_PLASTIQUE => '#Recyclage Plastique',
            self::RECYCLAGE_PAPIER => '#Recyclage Papier',
            self::RECYCLAGE_METAL => '#Recyclage Métal',
            self::RECYCLAGE_VERRE => '#Recyclage Verre',
            self::DIY_RECYCLAGE => '#DIY Recyclage',
            self::QUESTION => '#Question',
            self::RECLAMATION => '#Réclamation',
            self::INITIATIVE => '#Initiative',
        };
    }

    public static function getChoices(): array
    {
        $choices = [];
        foreach (self::cases() as $tag) {
            $choices[$tag->value] = $tag->value;
        }
        return $choices;
    }
}
