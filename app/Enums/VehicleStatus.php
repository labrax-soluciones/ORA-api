<?php

namespace App\Enums;

enum VehicleStatus: string {
    case Active = 'active';
    case Inactive = 'inactive';
    case Blocked = 'blocked';
}
