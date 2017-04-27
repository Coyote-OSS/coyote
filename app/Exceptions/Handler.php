<?php

namespace Coyote\Exceptions;

use Coyote\Repositories\Contracts\PageRepositoryInterface;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exception\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
        ForbiddenException::class,
        TokenMismatchException::class,
        CommandNotFoundException::class
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $e
     * @return void
     */
    public function report(Exception $e)
    {
        if ($this->shouldReport($e)) {
            // log input data and url for further analyse
            logger()->error('+', ['url' => request()->url(), 'input' => request()->all(), 'ip' => request()->ip()]);

            if (app()->environment('production')) {
                // send report to sentry
                app('sentry')->captureException($e);
            }
        }

        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $e
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Exception $e)
    {
        // error handler to AJAX request
        if ($request->isXmlHttpRequest()) { // moze lepiej bedzie uzyc wantsJson()?
            $statusCode = 500;

            if ($this->isHttpException($e)) {
                $statusCode = $e->getStatusCode();
            }

            if ($e instanceof HttpResponseException) {
                return parent::render($request, $e);
            }

            if ($e instanceof ValidationException && $e->getResponse()) {
                return response()->json($e->validator->errors(), $statusCode);
            }

            if ($e instanceof TokenMismatchException) {
                return response()->json(
                    ['error' => 'Twoja sesja wygasła. Proszę odświeżyć stronę i spróbować ponownie.'],
                    $statusCode
                );
            }

            $response = [
                'error' => 'Przepraszamy, ale coś poszło nie tak. Prosimy o kontakt z administratorem.'
            ];

            if (config('app.debug')) {
                $response['exception'] = get_class($e);
                $response['message'] = $e->getMessage();
                $response['trace'] = $e->getTrace();
            }

            return response()->json($response, $statusCode);
        }

        if ($e instanceof ForbiddenException) {
            return $this->renderForbiddenException($e);
        }

        if ($e instanceof TokenMismatchException) {
            return redirect($request->fullUrl())
                ->withInput($request->except('_token'))
                ->with('error', 'Wygląda na to, że nie wysłałeś tego formularza przez dłuższy czas. Spróbuj ponownie!');
        }

        if (($e instanceof HttpException && $e->getStatusCode() === 404) || $e instanceof ModelNotFoundException) {
            return $this->renderHttpErrorException($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * @param ForbiddenException $e
     * @return \Illuminate\Http\Response
     */
    protected function renderForbiddenException(ForbiddenException $e)
    {
        return response()->view('errors.forbidden', $e->firewall->toArray(), 401);
    }

    /**
     * @param Request $request
     * @param HttpException|ModelNotFoundException $e
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|SymfonyResponse
     */
    protected function renderHttpErrorException(Request $request, $e)
    {
        $path = rawurldecode(rtrim($request->getPathInfo(), '/'));
        $page = $this->container[PageRepositoryInterface::class]->findByPath($path);

        if (!$page) {
            return parent::render($request, $e);
        }

        return redirect($page->path, 301);
    }

    /**
     * Get the html response content.
     *
     * @param  \Exception  $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function convertExceptionToResponse(Exception $e)
    {
        if (config('app.debug')) {
            return parent::convertExceptionToResponse($e);
        }

        // on production site, we MUST render "nice" error page
        return SymfonyResponse::create(view('errors.500')->render(), 500);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest('login');
    }
}
