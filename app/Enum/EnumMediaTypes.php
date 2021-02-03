<?php

namespace App\Enum;

abstract class EnumMediaTypes
{
    const ERROR = 0;
    const VIDEO = 1;
    const PDF = 2;
    const TEXT = 3;

    const TYPES_ARRAY = [
        self::VIDEO => 'Vídeo',
        self::PDF => 'PDF',
        self::TEXT => 'Texto',
        self::ERROR => 'Erro',
    ];

    const TYPES = [
        ['value' => self::VIDEO, 'text' => 'Vídeo'],
        // ['value' => self::PDF, 'text' => 'PDF'],
        ['value' => self::TEXT, 'text' => 'Texto'],
    ];
}
