<?php

return [
    'settings' => [
        'default'          => [
            'auto_activate'    => true,
            'activate_parents' => true,
            'active_class'     => 'active neon-tab-active',
            'restful'          => false,
            'cascade_data'     => true,
            'rest_base'        => '',      // string|array
            'active_element'   => 'link',  // item|link
        ],
        '__master_menu___' => [
            'restful' => true,
        ],
        '_forum'           => [
            'auto_activate' => false,
            'restful'       => true,
        ],
    ],

    '__master_menu___' => [
        'Dla kandydatów'  => ['route' => 'neon.jobOffer.list', 'class' => 'nav-item', 'control' => 'navigationJobBoard'],
        'Dla pracodawców' => ['route' => 'neon.jobOffer.pricing', 'class' => 'nav-item', 'control' => 'navigationForEmployers'],
        'Forum'           => ['route' => 'forum.home', 'forumMenu' => true],
        'Mikroblogi'      => ['route' => 'microblog.home', 'class' => 'nav-item',],
        'Wydarzenia'      => ['class' => 'nav-item d-lg-none', 'url' => 'https://wydarzenia.4programmers.net/'],
        'Kompendium'      => ['class' => 'nav-item d-lg-none', 'route' => 'wiki.home'],
    ],

    // _ na poczatku gdyz ten plugin korzysta z metody share() klasy View, a nazwa "forum" moze
    // wchodzic w konflikt z innymi zmiennymi przekazywanymi do twiga
    '_forum'           => [
        'Kategorie'      => ['route' => 'forum.categories', 'class' => 'nav-item neon-forum-tab'],
        'Wszystkie'      => ['route' => 'forum.all', 'class' => 'nav-item neon-forum-tab'],
        'Obserwowane'    => ['route' => 'forum.subscribes', 'class' => 'nav-item neon-forum-tab', 'data' => ['role' => true]],
        'Biorę udział'   => ['route' => 'forum.mine', 'class' => 'nav-item neon-forum-tab', 'data' => ['role' => true], 'title' => 'Wątki w których brałem udział'],
        'Z moimi tagami' => ['route' => 'forum.interesting', 'class' => 'nav-item neon-forum-tab', 'data' => ['role' => true], 'title' => 'Wątki zawierające moje tagi'],
    ],
];
