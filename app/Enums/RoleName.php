<?php

namespace App\Enums;

enum RoleName: string
{
    case ADMIN            = 'admin';
    case MUNICIPAL_ADMIN  = 'municipal_admin';
    case TECHNICIAN       = 'technician';
    case POLICE           = 'police';
    case USER             = 'user';
}
