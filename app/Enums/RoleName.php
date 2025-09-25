<?php

namespace App\Enums;

enum RoleName: string
{
    case ADMIN            = 'admin'; // Administradores generales de la palicacion
    case MUNICIPAL_ADMIN  = 'municipal_admin'; // Administradores del ayuntamiento
    case TECHNICIAN       = 'technician'; // Tecnicos municipales con permisos limitados
    case POLICE           = 'police'; // Empleados del ayuntamiento o gobierno encargados de vigilancia de lo aparcamientos
    case USER             = 'user'; // Usuarios normales, usan la aplaición
}
