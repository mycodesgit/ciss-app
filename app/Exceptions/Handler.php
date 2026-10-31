<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Employee modules whose layout (sidebar) wraps the error page: URL prefix => [layout, menu key, breadcrumb].
     */
    private const ERROR_MODULES = [
        'employee/admission' => ['layouts.master_empadmission', 'admission-url', 'Admission'],
        'employee/enrollment' => ['layouts.master_empenrollment', 'enrollment-url', 'Enrollment'],
        'employee/settings' => ['layouts.master_empsetting', 'setting-url', 'Settings'],
    ];

    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Logged-in employees see the error inside their module layout (sidebar + workspace).
     * Guests, or a module layout that fails to render (e.g. database down), get the standalone page.
     */
    protected function renderHttpException(HttpExceptionInterface $e)
    {
        $this->registerErrorViewPaths();

        $shell = $this->employeeErrorShell();

        if ($shell && $view = $this->getHttpExceptionView($e)) {
            try {
                return response()->view($view, [
                    'errors' => new ViewErrorBag,
                    'exception' => $e,
                    'errorShell' => $shell[0],
                    'errorModule' => $shell[1],
                ], $e->getStatusCode(), $e->getHeaders());
            } catch (Throwable $t) {
                $this->report($t);
            }
        }

        return parent::renderHttpException($e);
    }

    /**
     * [layout, breadcrumb] for the current employee page, or null to use the standalone page.
     */
    private function employeeErrorShell(): ?array
    {
        $request = request();

        if ($request->expectsJson() || ! $request->is('employee/*')) {
            return null;
        }

        try {
            $user = Auth::guard('web')->user();

            if (! $user) {
                return null;
            }

            foreach (self::ERROR_MODULES as $prefix => [$layout, $menuKey, $label]) {
                // Only show a module's sidebar to employees who may open that module
                if ($request->is($prefix, "$prefix/*") && $user->canAccessMenu($menuKey)) {
                    return [$layout, $label];
                }
            }
        } catch (Throwable) {
            return null; // session or database unavailable
        }

        return ['layouts.master_empdash', null];
    }
}
