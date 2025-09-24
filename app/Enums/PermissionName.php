<?php

namespace App\Enums;

enum PermissionName: string
{
    // administración global
    case ADMIN_ACCESS      = 'admin.access';

    // gestión municipal
    case MUNICIPALITY_MANAGE = 'municipality.manage';
    case ZONE_MANAGE         = 'zone.manage';
    case POLICE_MANAGE       = 'police.manage';

    // operaciones
    case OCCUPANCY_VIEW    = 'occupancy.view';
    case PARKING_REGISTER  = 'parking.register';
    case VEHICLE_MANAGE    = 'vehicle.manage';
}
