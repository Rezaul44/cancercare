<?php

namespace App\Enums;

enum HelplineChannel: string
{
    case Phone = 'phone';
    case Whatsapp = 'whatsapp';

    public function labelBn(): string
    {
        return match ($this) {
            self::Phone => 'ফোন',
            self::Whatsapp => 'হোয়াটসঅ্যাপ',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Phone => 'info',
            self::Whatsapp => 'success',
        };
    }
}
