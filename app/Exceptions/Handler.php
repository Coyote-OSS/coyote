<?php
namespace Coyote\Exceptions;

use Coyote\Repositories\Contracts\PageRepositoryInterface;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        ForbiddenException::class,
        CommandNotFoundException::class,
    ];

    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    public function report(Throwable $e)
    {
        if (app()->runningUnitTests()) {
            if ($e instanceof \PHPUnit\Exception) {
                throw $e;
            }
        }
        if ($this->shouldReport($e) && app()->bound('sentry') && app()->environment('production')) {
            // send report to sentry
            app('sentry')->captureException($e);
        }
        parent::report($e);
    }

    protected function context()
    {
        return array_merge(parent::context(), ['url' => request()->url(), 'ip' => request()->ip()]);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param Request $request
     * @param Throwable $e
     * @return Response|JsonResponse|Response
     * @throws Throwable
     */
    public function render($request, Throwable $e)
    {
        if ($e instanceof TokenMismatchException) {
            return $this->renderTokenMismatchException($request, $e);
        }
        if (($e instanceof HttpException && $e->getStatusCode() === 404) || $e instanceof ModelNotFoundException) {
            return $this->renderHttpErrorException($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * @param Request $request
     * @param TokenMismatchException $e
     * @return JsonResponse|RedirectResponse
     */
    protected function renderTokenMismatchException(Request $request, TokenMismatchException $e)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Twoja sesja wygasła. Proszę odświeżyć stronę i spróbować ponownie.'], 403);
        }
        return redirect($request->fullUrl())
            ->withInput($request->except('_token'))
            ->with('error', 'Wygląda na to, że nie wysłałeś tego formularza przez dłuższy czas. Spróbuj ponownie!');
    }

    /**
     * @param Request $request
     * @param Exception $e
     * @return RedirectResponse|SymfonyResponse
     */
    protected function renderHttpErrorException(Request $request, $e)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Uuups. Strona nie istnieje lub została usunięta.'], 404);
        }

        // Case insensitive path lookup.
        // Redirect to correct version if exists
        $path = $this->findCamelCasePath($request);

        if ($path === null) {
            return parent::render($request, $e);
        }

        return redirect($path, 301);
    }

    /**
     * Case insensitive path lookup.
     *
     * @param Request $request
     * @return null|string
     */
    protected function findCamelCasePath(Request $request): ?string
    {
        // try to find correct path for given page
        $path = rawurldecode(rtrim($request->getPathInfo(), '/'));
        $page = $this->container[PageRepositoryInterface::class]->findByPath($path);

        return $page !== null && $page->path !== $path ? $page->path : null;
    }
}
