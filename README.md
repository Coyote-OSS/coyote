<p align="center">
    <img src="public/img/logo-light.svg">
</p>

<p align="center">
    <img src="https://github.com/pradoslaw/coyote/workflows/Tests/badge.svg" alt="Build status"/>
    <img src="https://img.shields.io/github/last-commit/pradoslaw/coyote/master.svg" alt="last commit">
    <img src="https://img.shields.io/github/commit-activity/y/pradoslaw/coyote.svg" alt="commit activity">
    <img src="https://img.shields.io/badge/PR-welcome-brightgreen.svg?style=popout" alt="make a PR">
</p>

# Coyote

Coyote is an open-source library to host forums, e.g. https://4programmers.net/.

[4programmers.net]: https://4programmers.net/

## How to report security problems?

We kindly ask you to report security problems to: support@4programmers.net 

## How can you help?

We highly encourage you to participate in the library development! Checkout current issues and find something for yourself
to work on.

1. Fork the repository
2. Push your change
3. Raise a pull request for us to review!

## Installation

1. Create a local file `.env`, based on `.env.default`
   ```bash
   cp .env.default .env
   ```
2. Bring up the application in docker containers:
   ```
   docker compose up
   ```
3. Install dependencies:
   - Developer mode (debug, source maps, hmr):
     ```
     make file-permission composer-install migrate seed es-create es-index
     make yarn-install yarn-prod
     make install-passport        # Optional
     make install-push            # Optional
     ```
   
   Should the starting fail on Windows, remove `-u nginx` from commands in `Makefile`.
4. The application is running
   - Visit `localhost:8880`
   - Login to administrator account with credentials: `admin`/`admin`.
   - Login to user account with credentials: `user`/`user`.
5. Terminate the application:
   ```
   docker compose stop
   ```

### Konfiguracja debuggera

By skorzystać z xdebug, w pliku `.env` należy dodać ustawienia xdebug:

```
ENABLE_XDEBUG=1
XDEBUG_HOST=host.docker.internal
XDEBUG_PORT=9003
```

Nastepnie należy zrestartować wszystkie kontenery (a dokładniej kontener `php`, żeby `./entrypoint.sh` został uruchomiony ponownie).

Skonfiguruj IDE by łączyło się do istniejącego kontenera, a nie tworzyło nowy.

#### IDE od JetBrains

Jeśli korzystasz z PhpStorm, konieczne może być ustawienie zmiennej środowiskowej:

```bash
# from /bin/bash
export PHP_IDE_CONFIG="serverName=your_server_name"
```
```cmd
REM from cmd.exe
set PHP_IDE_CONFIG="serverName=your_server_name"
```
```ps
# from powershell.exe
$env:PHP_IDE_CONFIG = 'serverName=your_server_name'
```

Nazwa `your_server_name` powinna odpowiadać nazwie serwera w sekcji "path mappings".

### Praca z CSS oraz JS

W projekcie korzystamy z `yarn` oraz `webpack`. Aby skompilować pliki źródłowe do postaci finalnej, 
należy wykonać polecenie:
 - Wersja developerska
   ```
   docker-compose exec php yarn run dev
   ```
   lub
   ```
   docker-compose exec php yarn run watch
   ```

   Polecenie `docker-compose exec php yarn run watch` powoduje stałe monitorowanie zmian w plikach źródłowych. 
   Jakiekolwiek zmiany w tych plikach spowodują wygenerowanie nowych plików wynikowych CSS oraz JS.

 - Wersja produkcyjna:
   ```
   docker-compose exec php yarn run prod
   ```

### Testowanie

Aby uruchomić testy w laravel:

```
docker-compose exec php php vendor/bin/phpunit
```

### Troubleshooting

1. Running tests causes a lot of failed tests, with CSRF token fail.
   - Can be fixed with running this command in `php` container:
     ```
     php artisan config:clear
     ```

2. Niektóre widoki powodują błąd związany z ElasticSearch i brakującymi polami.
   - Prawdopodobnie początkowe tworzenie indexów się nie powiodło.
     ```
     php artisan es:drop
     ```
     A następnie stwórz indexy od nowa
     ```
     php artisan es:create --force
     php artisan es:index --force
     ```
   - ElasticSearch przełącza się w tryb readonly, kiedy dysk ma zajęte 95% miejsca.
     Spowoduje to błędy w dodawaniu nowych wartości. Rozwiązaniem na to jest
     oczywiście zwolnienie miejsca na dysku.
   - ElasticSearch możliwe że jest trybie readonly, i sam z niego nie wyjdzie.
     Wtedy należy się przełączyć na kontener, i wykonać
     ```
     curl -XPUT -H "Content-Type: application/json" http://localhost:9200/_all/_settings -d '{"index.blocks.read_only_allow_delete": null}'
     ```

3. Running containers listening on high ports fails.

   If you're running Windows, it's possible that random dynamic ports for IANA are
   blocking docker containers.

   To verify:
   ```
   netsh int ipv4 show dynamicport tcp
   ```
   If the starting port is low-ish (between 1000-2000), then it's possible that the
   dynamic ports are blocking the docker containers.

   To change:
   1. ```
      netsh int ipv4 set dynamic tcp start=49152 num=16384
      netsh int ipv6 set dynamic tcp start=49152 num=16384
      ```
   2. Reboot.
4. Random unrelated tests start to fail.
   
   Many Dusk tests aren't properly constructed to expect debug features like Debugbar.

   To fix:
   ```
   APP_DEBUG=false
   ```

5. Problem z uruchomieniem `docker compose` na Windows:
   ```
   Error response from daemon: Ports are not available: exposing port TCP 0.0.0.0:8025 -> 0.0.0.0:0: listen tcp 0.0.0.0:8025: bind: Została podjęta próba uzyskania dostępu do gniazda w sposób zabroniony przez przypisane do niego uprawnienia dostępu.
   ```
   Rozwiązanie:
   1. Otwórz PowerShell jako administrator
   2. ```
      net stop winnat
      ```

6. Brak połączenia z internetem z WSL:
   1. `wsl --shutdown`
   2. ```
      netsh winsock reset 
      netsh int ip reset all
      netsh winhttp reset proxy
      ipconfig /flushdns
      ```
   3. Reboot
   4. Start `wsl`
   
   If the steps didn't work, repeat the steps, but also run `netsh winsock reset` after `ipconfig /flushdns`.

7. Różnice w środowiskach

   Pamiętaj, że uruchomienie `docker compose up` (bez przekazania `-f`) domyślnie
   skorzysta z plików `docker-compose.yaml` **oraz** `docker-compose.override.yaml`.

8. Running `yarn watch` causes SPA view not to render at all.

   If in developer console there are errors concerning loading `.js` files, then the most likely problem an outdated
   `manifest.json` file. Webpack normally rebuilds it live when working with `yarn watch`, but that happens
   as the very last step. If there is any error during building (e.g. incorrect unix permissions) then the building
   doesn't proceed, the file `manifest.json` is not updated, view is attempted to be shown with outdated `manifest.json`
   which fails.

### Zadania uruchomiane w tle

Na serwerze produkcyjnym niektóre zadanie wykonywane są w tle. Dodawane są one do kolejki oraz 
wykonywane przez proces działający w tle. Domyślnie, na serwerze lokalnym zadania nie są dodawane
do kolejki (w pliku `.env` ustawienie `QUEUE_DRIVER=sync`).

Jeżeli jednak chciałbyś przetestować działanie mechanizmu kolejek, ustaw wartość zmiennej środowiskowej `QUEUE_DRIVER` na `redis`.

Aby uruchomić mechanizm kolejek skorzystaj z następującego polecenia:

```
docker-compose exec php php artisan queue:listen --sleep=10
```
