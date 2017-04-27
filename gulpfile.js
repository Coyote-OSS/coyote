var elixir = require('laravel-elixir');
var path = require('path');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Less
 | file for our application, as well as publishing vendor resources.
 |
 */

function node_module(filename) {
    return path.join('../../../node_modules/', filename);
}

elixir(function (mix) {

    mix
    /*
     | jQuery Ui w okrojonej wersji.
     | ---------------------------------------
     | Wykorzystywany m.in. po zaladowaniu okna powiadomien (mamy mozliwosc zmiany rozmiaru tego okna)
     | oraz w panelu uzytkownika na stronie umiejetnosci gdzie mamy mozliwosc dodawania/usuwania umiejetnosci
     | a takze ich przesuwania wzgledem siebie (za pomoca drag'n'drop)
     */
    .scripts([
        node_module('jquery-ui.1.11.1/ui/core.js'),
        node_module('jquery-ui.1.11.1/ui/widget.js'),
        node_module('jquery-ui.1.11.1/ui/mouse.js'),
        node_module('jquery-ui.1.11.1/ui/resizable.js'),
        node_module('jquery-ui.1.11.1/ui/sortable.js')
    ], 'public/js/jquery-ui.js')

    .scripts(['plugins/geo-ip.js'], 'public/js/geo-ip.js')
    /*
     | auto complete. uzywany m.in. w podczas pisania wiadomosci, czy tez ustalania umiejetnosci
     */
    .scripts(['plugins/auto-complete.js'], 'public/js/auto-complete.js');

    mix.webpack('app.js');

    mix.sass('main.scss')
        .sass('pages/auth.scss')
        .sass('pages/homepage.scss')
        .sass('pages/microblog.scss')
        .sass('pages/forum.scss')
        .sass('pages/wiki.scss')
        .sass('pages/help.scss')
        .sass('pages/user.scss')
        .sass('pages/profile.scss')
        .sass('pages/job.scss')
        .sass('pages/errors.scss')
        .sass('pages/adm.scss')
        .sass('pages/pastebin.scss')
        .sass('pages/search.scss');

    // versioning only on production server
    if (elixir.config.production) {
        mix.version([
            'js/app.js',
            'js/microblog.js',
            'js/forum.js',
            'js/job.js',
            'js/homepage.js',
            'js/pm.js',
            'js/profile.js',
            'js/job-submit.js',
            'js/posting.js',
            'js/wikieditor.js',
            'js/auto-complete.js',
            'js/jquery-ui.js',
            'js/wiki.js',
            'js/geo-ip.js',

            'css/main.css',
            'css/auth.css',
            'css/homepage.css',
            'css/microblog.css',
            'css/forum.css',
            'css/wiki.css',
            'css/user.css',
            'css/profile.css',
            'css/job.css',
            'css/errors.css',
            'css/pastebin.css',
            'css/adm.css',
            'css/help.css',
            'css/search.css'
        ]);
    }
});

