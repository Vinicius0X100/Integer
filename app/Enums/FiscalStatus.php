<?php

namespace App\Enums;

enum FiscalStatus: string
{
    case PENDING = 'pending';
    case SENT_TO_ACCOUNTING = 'sent_to_accounting';
    case NFSE_RECEIVED = 'nfse_received';
    case SENT_TO_CUSTOMER = 'sent_to_customer';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING            => 'Aguardando envio à contabilidade',
            self::SENT_TO_ACCOUNTING => 'Enviado à contabilidade',
            self::NFSE_RECEIVED      => 'NFS-e recebida',
            self::SENT_TO_CUSTOMER   => 'Enviada ao cliente',
            self::CANCELLED          => 'Cancelada',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING            => 'bg-warning text-dark',
            self::SENT_TO_ACCOUNTING => 'bg-info text-dark',
            self::NFSE_RECEIVED      => 'bg-primary text-white',
            self::SENT_TO_CUSTOMER   => 'bg-success text-white',
            self::CANCELLED          => 'bg-secondary text-white',
        };
    }
}
