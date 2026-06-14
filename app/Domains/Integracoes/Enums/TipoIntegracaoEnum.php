<?php

namespace App\Domains\Integracoes\Enums;

enum TipoIntegracaoEnum: string
{
    public const FACTURACAO = self::Facturacao;
    public const WHATSAPP = self::WhatsApp;
    public const SMS = self::Sms;
    public const EMAIL = self::Email;
    public const PAGAMENTOS = self::Pagamentos;
    public const STORAGE = self::Storage;

    case Facturacao = 'facturacao';
    case WhatsApp = 'whatsapp';
    case Sms = 'sms';
    case Email = 'email';
    case Pagamentos = 'pagamentos';
    case Storage = 'storage';

    public function label(): string
    {
        return match ($this) {
            self::Facturacao => 'Facturação',
            self::WhatsApp => 'WhatsApp',
            self::Sms => 'SMS',
            self::Email => 'Email',
            self::Pagamentos => 'Pagamentos',
            self::Storage => 'Storage',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Facturacao => 'blue',
            self::WhatsApp => 'green',
            self::Sms => 'sky',
            self::Email => 'indigo',
            self::Pagamentos => 'amber',
            self::Storage => 'slate',
        };
    }
}
