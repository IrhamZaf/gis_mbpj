<?php

return [
    'custom' => [
        /*
        |--------------------------------------------------------------------------
        | Layout & theme
        |--------------------------------------------------------------------------
        */
        'myLayout'            => 'vertical',     // vertical / horizontal / blank / front
        'myTheme'             => 'light',         // light / dark / system
        'myStyle'             => 'light',         // light / dark
        'myRTLSupport'        => false,
        'myRTLMode'           => false,
        'hasCustomizer'       => true,
        'displayCustomizer'   => true,
        'contentLayout'       => 'compact',       // compact / wide
        'headerType'          => 'layout-menu-fixed',
        'navbarType'          => 'layout-navbar-fixed',
        'menuFixed'           => 'layout-menu-fixed',
        'menuCollapsed'       => '',
        'footerFixed'         => '',

        /*
        |--------------------------------------------------------------------------
        | Skins & colours
        |--------------------------------------------------------------------------
        */
        'skins'               => 0,
        'skinName'            => 'default',
        'semiDark'            => false,
        'primaryColor'        => '',
        'color'               => '',

        /*
        |--------------------------------------------------------------------------
        | Customiser controls visible to the end-user
        |--------------------------------------------------------------------------
        */
        'customizerControls'  => [
            'rtl',
            'style',
            'headerType',
            'contentLayout',
            'layoutCollapsed',
            'showDropdownOnHover',
            'layoutNavbarOptions',
            'themes',
        ],

        /*
        |--------------------------------------------------------------------------
        | Dropdown behaviour
        |--------------------------------------------------------------------------
        */
        'showDropdownOnHover' => true,
    ],
];
