<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => 'The :attribute must be accepted.',
    'active_url'           => 'The :attribute is not a valid URL.',
    'after'                => 'The :attribute must be a date after :date.',
    'alpha'                => 'The :attribute may only contain letters.',
    'alpha_dash'           => 'The :attribute may only contain letters, numbers, and dashes.',
    'alpha_num'            => 'The :attribute may only contain letters and numbers.',
    'array'                => 'The :attribute must be an array.',
    'before'               => 'The :attribute must be a date before :date.',
    'between'              => [
        'numeric' => 'The :attribute must be between :min and :max.',
        'file'    => 'The :attribute must be between :min and :max kilobytes.',
        'string'  => 'The :attribute must be between :min and :max characters.',
        'array'   => 'The :attribute must have between :min and :max items.',
    ],
    'boolean'              => 'The :attribute field must be true or false.',
    'confirmed'            => 'The :attribute confirmation does not match.',
    'date'                 => 'The :attribute is not a valid date.',
    'date_format'          => 'The :attribute does not match the format :format.',
    'different'            => 'The :attribute and :other must be different.',
    'digits'               => 'The :attribute must be :digits digits.',
    'digits_between'       => 'The :attribute must be between :min and :max digits.',
    'email'                => 'Adres e-mail jest nieprawidłowy.',
    'exists'               => 'The selected :attribute is invalid.',
    'filled'               => 'The :attribute field is required.',
    'image'                => 'The :attribute must be an image.',
    'in'                   => 'The selected :attribute is invalid.',
    'integer'              => 'Pole :attribute must musi być liczbą całkowitą.',
    'ip'                   => 'The :attribute must be a valid IP address.',
    'json'                 => 'The :attribute must be a valid JSON string.',
    'max'                  => [
        'numeric' => 'The :attribute may not be greater than :max.',
        'file'    => 'Plik nie może być większy niż :max kilobajtów.',
        'string'  => 'Maksymalna długość tego pola to :max znaków.',
        'array'   => 'The :attribute may not have more than :max items.',
    ],
    'mimes'                => 'The :attribute must be a file of type: :values.',
    'min'                  => [
        'numeric' => 'Minimalna wartość dla tego pola to :min.',
        'file'    => 'The :attribute must be at least :min kilobytes.',
        'string'  => 'To pole musi mieć :min znaków.',
        'array'   => 'The :attribute must have at least :min items.',
        'words'   => 'Być może powinieneś dodać więcej informacji?',
    ],
    'not_in'               => 'The selected :attribute is invalid.',
    'numeric'              => 'The :attribute must be a number.',
    'regex'                => 'The :attribute format is invalid.',
    'required'             => 'Pole :attribute jest wymagane.',
    'required_if'          => 'To pole jest wymagane.',
    'required_with'        => 'To pole jest wymagane.',
    'required_with_all'    => 'The :attribute field is required when :values is present.',
    'required_without'     => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'same'                 => 'The :attribute and :other must match.',
    'size'                 => [
        'numeric' => 'The :attribute must be :size.',
        'file'    => 'The :attribute must be :size kilobytes.',
        'string'  => 'The :attribute must be :size characters.',
        'array'   => 'The :attribute must contain :size items.',
    ],
    'string'               => 'The :attribute must be a string.',
    'timezone'             => 'The :attribute must be a valid zone.',
    'unique'               => 'The :attribute has already been taken.',
    'url'                  => 'Pole musi zawierać prawidłowy link.',
    'uploaded'             => 'Nie można dodać załącznika. Format lub rozmiar pliku jest nieprawidłowy.',

    'username'             => 'Nazwa użytkownika może zawierać litery, cyfry oraz znaki ._ -',
    'user_exist'           => 'Użytkownik o podanej nazwie nie istnieje.',
    'user_unique'          => 'Konto o podanej nazwie użytkownika już istnieje.',
    'user_active'          => 'Konto o tym loginie zostało zablokowane.',
    'user_access'          => 'Dostęp do konta z tego adresu IP jest niemożliwy.',
    'password'             => 'Podano nieprawidłowe hasło.',
    'reputation'           => 'Potrzebujesz minimum :point punktów reputacji aby zmienić zawartość tego pola.',
    'spam_link'            => 'Potrzebujesz minimum :point punktów reputacji, aby umieścić link w tym polu.',
    'spam_chinese'         => 'Z powodu spamu, tekst nie może zawierać chińskich znaków.',
    'spam_foreign'         => 'Musisz napisać min. :posts post, aby umieścić linki w tym polu.',
    'tag'                  => 'Podany tag zawiera nieprawidłowe znaki.',
    'tag_creation'         => 'Potrzebujesz :point punktów reputacji, aby utworzyć nowy tag. Wybierz już istniejący.',
    'throttle'             => 'Musisz odczekać jeszcze :delay przed dodaniem kolejnego wpisu.',
    'city'                 => '":value" nie jest prawidłową nazwą miejscowości.',
    'wiki_unique'          => 'Uuups. Strona o tym tytule już istnieje w tej kategorii. Proszę wpisać inny tytuł.',
    'wiki_route'           => 'Nie można dodać strony o tym tytule ponieważ koliduje z już istniejącą.',
    'email_unique'         => 'Podany adres e-mail przypisany jest do innego użytkownika.',
    'email_exists'         => 'Podany adres e-mail nie istnieje.',
    'email_confirmed'      => 'Ten adres e-mail nie został zweryfikowany. <a href=":link">Kliknij tutaj</a> by go potwierdzić.',
    'recaptcha'            => 'Wartość pola recaptcha jest nieprawidłowa. Prosimy o kontakt.',
    'host'                 => 'Podany link musi pochodzić z serwera :host.',
    'tag_deleted'          => 'Tag ":value" został usunięty i jego ponowne użycie nie jest możliwe.',
    'tag_not_exist'        => 'Tag <strong>:value</strong> nie istnieje ale możesz go utworzyć.<br><br>Czy jesteś pewien, że jest to <strong>tag techniczny</strong> i chcesz go dodać?',
    'invalid_vat_id'       => 'NIP/VAT ID nie jest poprawny.',
    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'name' => [
            'unique'        => 'Konto o tym loginie już istnieje'
        ],
        'coupon' => [
            'exists'        => 'Ten kod promocyjny nie istnieje lub został już wykorzystany.'
        ],
        'email' => [
            'unique'        => 'Ten e-mail jest już przypisany do innego konta',
            'exists'        => 'Podany adres e-mail nie istnieje.'
        ],
        'password' => [
            'confirmed'     => 'Hasło w obu polach musi być identyczne'
        ],
        'password_old' => [
            'required'      => 'Wymagane jest podanie obecnego hasła'
        ],
        // m.in. w mikroblogach
        'text' => [
            'required'      => 'Proszę wpisać treść.',
            'max'           => 'Maksymalna długość tekstu to :max znaków.'
        ],
        // m.in. w wiadomosciach prywatnych
        'recipient' => [
            'required'      => 'Proszę wpisać nadawcę wiadomości.',
            'exists'        => 'Użytkownik o tej nazwie nie istnieje.',
            'different'     => 'Nie można wysłać wiadomości do samego siebie.'
        ],
        // tagi, np. na forum
        'tags' => [
            'required'      => 'Wymagane jest przypisanie minimum jednego tagu do tego wątku.',
            'max'           => 'Możesz przypisać maksymalnie :max tagów.'
        ],
        'tags.*' => [
            'max'           => 'Tag nie może być dłuższy niż :max znaków.'
        ],
        // w formularzu forum
        'user_name' => [
            'required'      => 'Proszę wpisać nazwę użytkownika.',
            'unique'        => 'Ta nazwa jest zajęta przez innego użytkownika.',
        ],
        // formularz pracy oraz forum
        'title' => [
            'required'      => 'Tytuł jest wymagany.',
            'min'           => 'Tytuł jest zbyt krótki. Musi mieć minimum 3 znaki długości.'
        ],
        'recruitment' => [
            'required'   => 'Proszę podać informacje w jaki sposób można składać aplikacje na to stanowisko.'
        ],
        'salary_from' => [
            'integer'       => 'Wynagrodzenie "od" musi być liczbą całkowitą.'
        ],
        'salary_to' => [
            'integer'       => 'Wynagrodzenie "do" musi być liczbą całkowitą.'
        ],
        // walidacja poprawnosci tagu
        't' => [
            'max'           => 'Maksymalna długośc tagu to :max znaków.'
        ],
        'salary' => [
            'integer'       => 'Wynagrodzenie musi być liczbą całkowitą.'
        ],
        'post_id' => [
            'exists'        => 'Post nie istnieje lub został usunięty.'
        ],
        'parent_id' => [
            'exists'        => 'Wpis nie istnieje lub został usunięty.'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [
        'password'                  => 'hasło',
        'name'                      => 'nazwa użytkownika',
        'firm.name'                 => 'nazwa firmy',
        'invoice.country_id'        => 'kraj',
        'invoice.name'              => 'nazwa',
        'invoice.vat_id'            => 'NIP',
        'invoice.address'           => 'adres',
        'invoice.postal_code'       => 'kod pocztowy',
        'invoice.city'              => 'miasto'
    ],
];
