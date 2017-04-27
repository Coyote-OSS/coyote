# Coyote

[![StyleCI](https://styleci.io/repos/30256872/shield)](https://styleci.io/repos/30256872)
[![Build Status](https://travis-ci.org/adam-boduch/coyote.svg?branch=master)](https://travis-ci.org/adam-boduch/coyote)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/adam-boduch/coyote/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/adam-boduch/coyote/?branch=master)

Coyote to nazwa systemu obslugujacego serwis 4programmers.net.

## Wymagania

* PHP 7
    * php-gd
    * php-mongodb
    * php-mcrypt
    * php-curl
    * php-mbstring
    * php-pgsql
    * php-mongodb
    * php-xml
* PostgreSQL >= 9.4
* MongoDB >= 2.7
* composer
* node.js
* npm
* git
* Redis
* Elasticsearch 5.0

### Zalecane

* Supervisor

## Instalacja

1. `sudo apt-get -y install php-gd`
2. `sudo apt-get -y install php-mbstring`
2. `sudo apt-get -y install php-mcrypt`
2. `sudo apt-get -y install php-pear`
2. `sudo apt-get -y install php-curl`
3. `sudo apt-get -y install php-mongodb`
4. `sudo apt-get -y install php-pgsql`
4. `sudo apt-get -y install php-xml`
6. `git clone https://github.com/adam-boduch/coyote.git .`
7. `psql -c 'create database coyote;' -U postgres`
8. `cp .env.default .env` (plik .env zawiera konfiguracje bazy danych PostgreSQL oraz MongoDB)
9. `make install` (na produkcji) lub `make install-dev` (bez minifikacji JS oraz CSS)
10. `php artisan key:generate` (generowanie unikalnego klucza, który posłuży do szyfrowania danych)

### Problemy podczas instalacji
#### Class 'MongoClient' not found

Czy biblioteka mongo jest zainstalowana? Jeżeli tak to `service php7.0-fpm restart`

#### Use of undefined constant MCRYPT_RIJNDAEL_128 - assumed 'MCRYPT_RIJNDAEL_128'

Czy biblioteka mcrypt jest zainstalowana? Jeżeli nie to `apt-get install php-mcrypt`. Jeżeli tak to `service php7.0-fpm restart`.

#### sh: 1: phpize: not found

`sudo apt-get install php7.0-dev`

#### php error: Cannot find OpenSSL's libraries

`sudo apt-get install pkg-config libssl-dev`

## Uruchomienie

Działanie projektu wymaga zainstalowania serwera HTTP takiego jak Apache czy Nginx. PHP udostępnia jednak prosty serwer HTTP, który można wykorzystać, aby sprawdzić działanie aplikacji. Aby go uruchomić należy wykonać polecenie: `php -S localhost:8000 -t public`

## Vagrant

Aby nie instalować wszystkich pakietów lokalnie, można skorzystać z Vagranta. Obecna wersja nie umożliwia jeszcze całkowitej instalacji z wnętrza Vagranta (jeśli wiesz, jak sprawić, by `npm install` i `gulp` działało na Vagrancie pod systemem Windows, daj znać), więc wymagane są następujące narzędzia zainstalowane lokalnie:
* Vagrant
* npm
* gulp (instalowane przez `npm install -g gulp`)

### Instalacja

1. vagrant up
2. npm install
3. gulp
4. w `etc/hosts` dodajemy wpis `192.168.10.10  coyote.dev`
5. w przeglądarce pod adresem `coyote.dev` powinniśmy zobaczyć działającą wersję Coyote

## Testowanie

W pisaniu testów, pomaga nam framework [Codeception](http://codeception.com/). Testy znajdują się w katalogu `tests`, który zawiera testy jednostkowe, funkcjonalne oraz akceptacyjne. Aby uruchomić testy trzeba wejść do katalogu z projektem i wykonać polecenia:

1. `vendor/bin/codecept build` (tylko jednorazowo)
2. `vendor/bin/codecept run`

Jeżeli chcesz uruchomić testy akceptacyjne, to będziesz potrzebował narzędzia takiego jak Selenium czy PhantomJS. W katalogu `tests` znajduje się plik `acceptance.suite.yml.travis` który zawiera konfigurację testów akceptacyjnych i jest wykorzystywany przez Travis CI. Należy zmienić nazwę tego pliku na `acceptance.suite.yml` i zmienić wartość `url` na prawidłowy adres pod którym uruchomiony jest Coyote (np. `http://localhost/`). Zakładamy, że PhantomJS jest uruchomiony (`phantomjs --webdriver=4444`).

## Aktualizacja projektu

`make update` (na produkcji) lub `make update-dev` (na serwerze deweloperskim)

## Konfiguracja

Konfiguracja projektu znajduje się w pliku `.env`. **Szczególnie** zaleca się zmianę sterownika cache na **redis**:

`CACHE_DRIVER=redis`

### Konfiguracja supervisor

Supervisor jest narzędziem monitorującym procesy, działającym w środowisku Linux. W Laravel dostępny jest
mechanizm kolejkowania zadań (np. indeksowanie treści w Elasticsearch), który można uruchomić przy pomocy

`php artisan queue:listen --sleep=10`

Supervisor ma na celu automatyczne uruchamianie tego procesu po starcie systemu i pilnownie, aby zawsze był uruchomiony.
Konfigurację supervisor możesz znaleźć w pliku `supervisor.conf`. Więcej informacji: https://laravel.com/docs/5.2/queues

### Ustawienia crona

W przypadku ustawienia środowiska na `production` w pliku `.env`, konieczne będzie ustawienie crona aby wykonywać
pewne czynności cykliczne.

1. W konsoli wpisz `crontab -e`
2. Dodaj linię: `* * * * * php /var/www/path-to-app/artisan schedule:run >> /dev/null 2>&1`


### Konfiguracja Elasticsearch

Po instalacji Elasticsearch, konieczne jest utworzenie indeksu oraz typów. Wykonaj poniższe polecenia:

1. `php artisan es:create`
2. `php artisan es:mapping`

## Jak mozesz pomoc?

Zachecamy do aktywnego udzialu w rozwoju projektu. Zajrzyj na zakladke *Issues* i zobacz jakie zadanie mozesz zrealizowac. Realizujemy tylko te zadanie ktore jest zaakceptowane i przypisane do wersji 2.0..

1. Utworz fork repozytorium
2. Wprowadz zmiany
3. Dodaj pull request
