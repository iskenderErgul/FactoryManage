<?php

namespace App\Domains\Enums;

enum OrderStatus: string
{
    case Received = 'Sipariş alındı';
    case Preparing = 'Hazırlanıyor';
    case Delivered = 'Teslim edildi';
}
