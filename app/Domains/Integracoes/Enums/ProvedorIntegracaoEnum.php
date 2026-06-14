<?php

namespace App\Domains\Integracoes\Enums;

enum ProvedorIntegracaoEnum: string
{
    public const HONGAYETU_FACTURACAO = self::HongayetuFacturacao;
    public const META_WHATSAPP = self::MetaWhatsApp;
    public const GENERIC_WHATSAPP = self::GenericWhatsApp;
    public const SMTP_CUSTOM = self::SmtpCustom;
    public const APPY_PAY = self::AppyPay;
    public const S3_CUSTOM = self::S3Custom;

    case HongayetuFacturacao = 'hongayetu_facturacao';
    case MetaWhatsApp = 'meta_whatsapp';
    case GenericWhatsApp = 'generic_whatsapp';
    case GenericSms = 'generic_sms';
    case SmtpCustom = 'smtp_custom';
    case AppyPay = 'appy_pay';
    case S3Custom = 's3_custom';

    public function label(): string
    {
        return match ($this) {
            self::HongayetuFacturacao => 'Hongayetu Facturação',
            self::MetaWhatsApp => 'Meta WhatsApp',
            self::GenericWhatsApp => 'WhatsApp Genérico',
            self::GenericSms => 'SMS Genérico',
            self::SmtpCustom => 'SMTP Custom',
            self::AppyPay => 'AppyPay',
            self::S3Custom => 'S3 Custom',
        };
    }

    public function color(): string
    {
        return match ($this->tipo()) {
            TipoIntegracaoEnum::Facturacao => 'blue',
            TipoIntegracaoEnum::WhatsApp => 'green',
            TipoIntegracaoEnum::Sms => 'sky',
            TipoIntegracaoEnum::Email => 'indigo',
            TipoIntegracaoEnum::Pagamentos => 'amber',
            TipoIntegracaoEnum::Storage => 'slate',
        };
    }

    public function tipo(): TipoIntegracaoEnum
    {
        return match ($this) {
            self::HongayetuFacturacao => TipoIntegracaoEnum::Facturacao,
            self::MetaWhatsApp, self::GenericWhatsApp => TipoIntegracaoEnum::WhatsApp,
            self::GenericSms => TipoIntegracaoEnum::Sms,
            self::SmtpCustom => TipoIntegracaoEnum::Email,
            self::AppyPay => TipoIntegracaoEnum::Pagamentos,
            self::S3Custom => TipoIntegracaoEnum::Storage,
        };
    }
}
