<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mensajes de validación personalizados para el proyecto
    |--------------------------------------------------------------------------
    |
    | Aquí defines únicamente lo que necesitas para la validación de
    | municipios. No es necesario duplicar todo el archivo original.
    |
    */

    'custom' => [
        'locales' => [
            'must_include_default' => 'El listado de idiomas debe incluir el idioma por defecto.',
        ],
        'slug' => [
            'alpha_dash' => 'El identificador solo puede contener letras, números y guiones.',
        ],
        'locales.*' => [
            'in' => 'Idioma no permitido. Use: es, en, gl, pt o fr.',
        ],
    ],

    'attributes' => [
        'name'           => 'nombre',
        'slug'           => 'identificador',
        'timezone'       => 'zona horaria',
        'default_locale' => 'idioma por defecto',
        'locales'        => 'idiomas habilitados',
        'sso_domains'    => 'dominios SSO',
        'contact_email'  => 'email de contacto',
        'contact_phone'  => 'teléfono de contacto',
        'status'         => 'estado',
    ],

];
