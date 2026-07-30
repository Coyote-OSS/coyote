<?php

return [
    'twig'       => [
        /*
        |--------------------------------------------------------------------------
        | Extension
        |--------------------------------------------------------------------------
        |
        | File extension for Twig view files.
        |
        */
        'extension' => 'twig',

        'environment' => [
            // An absolute path where to store the compiled templates, or false to disable caching. If null
            // then the cache file path is used.
            // default: cache file storage path
            'cache'            => null,

            // When developing with Twig, it's useful to recompile the template
            // whenever the source code changes. If you don't provide a value
            // for the auto_reload option, it will be determined automatically based on the debug value.
            'auto_reload'      => true,

            // If set to false, Twig will silently ignore invalid variables
            // (variables and or attributes/methods that do not exist) and
            // replace them with a null value. When set to true, Twig throws an exception instead.
            // default: false
            'strict_variables' => false,

            // If set to true, auto-escaping will be enabled by default for all templates.
            // default: 'html'
            'autoescape'       => 'html',
        ],

        'safe_classes' => [
            \Coyote\Domain\Html::class => ['html'],
        ],
    ],
    'extensions' => [
        'enabled'   => [
            'TwigBridge\Extension\Loader\Facades',
            'TwigBridge\Extension\Loader\Filters',
            'TwigBridge\Extension\Loader\Functions',
            'TwigBridge\Extension\Laravel\Auth',
            'TwigBridge\Extension\Laravel\Config',
            'TwigBridge\Extension\Laravel\Input',
            'TwigBridge\Extension\Laravel\Session',
            'TwigBridge\Extension\Laravel\Str',
            'TwigBridge\Extension\Laravel\Translator',
            'TwigBridge\Extension\Laravel\Form',
            'TwigBridge\Extension\Laravel\Html',
            'TwigBridge\Extension\Loader\Globals',
            'TwigBridge\Extension\Laravel\Event',

            'Coyote\Services\TwigBridge\Extensions\User',
            'Coyote\Services\TwigBridge\Extensions\DateTime',
            'Coyote\Services\TwigBridge\Extensions\Misc',
            'Coyote\Services\TwigBridge\Extensions\Block',
            'Coyote\Services\TwigBridge\Extensions\FormBuilder',
            'Coyote\Services\TwigBridge\Extensions\Media',
            'Coyote\Services\TwigBridge\Extensions\Icon',
            'Coyote\Services\TwigBridge\Extensions\Vue',
            'Coyote\Services\TwigBridge\Extensions\Formatting',
            'Coyote\Services\TwigBridge\Extensions\Types\NestedAttribute',
        ],

        /*
        |--------------------------------------------------------------------------
        | Functions
        |--------------------------------------------------------------------------
        |
        | Available functions. Access like `{{ secure_url(...) }}`.
        |
        | Each function can take an optional array of options. These options are
        | passed directly to `Twig_SimpleFunction`.
        |
        | So for example, to mark a function as safe you can do the following:
        |
        | <code>
        |     'link_to' => [
        |         'is_safe' => ['html']
        |     ]
        | </code>
        |
        | The options array also takes a `callback` that allows you to name the
        | function differently in your Twig templates than what it's actually called.
        |
        | <code>
        |     'link' => [
        |         'callback' => 'link_to'
        |     ]
        | </code>
        |
        */
        'functions' => [
            'config',
            'head',
            'last',
            'excerpt',
            'request',
            'cdn',
            'route',
            'url',
            'asset',
            'keywords',
            'secure_asset',
            'grid'        => ['is_safe' => ['html']],
            'grid_column' => ['is_safe' => ['html']],
            'grid_row'    => ['is_safe' => ['html']],
            'grid_cell'   => ['is_safe' => ['html']],
            'grid_filter' => ['is_safe' => ['html']],
            'grid_empty'  => ['is_safe' => ['html']],
        ],
    ],
];
