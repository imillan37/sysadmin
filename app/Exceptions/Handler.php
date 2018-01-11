<?php

namespace App\Exceptions;

use Exception;
use App\Traits\ApiResponser;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class Handler extends ExceptionHandler
{
    use ApiResponser;
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($exception instanceof ValidationException) {

            return $this->convertValidationExceptionToResponse($exception, $request);
        }

        if ($exception instanceof ModelNotFoundException) {
            
            $modelo = strtolower(class_basename($exception -> getModel()));

            return $this->errorResponse("No existe el registro en {$modelo} ",404);
        }
        //si el usuario no esta autenticado
        if ($exception instanceof AuthenticationException) {
            return $this->unauthenticated($request, $exception);
        }
        //si el usuario no puede entrar a algun area regresamos error msg
        if($exception instanceof AuthorizationException)
        {
            return $this-> errorResponse('No Tienes permisos para ejecutar esta accion',403);
        }
        //error 404 url mal
        if($exception instanceof NotFoundHttpException)
        {
            return $this-> errorResponse ('No se encontro la URL especificada',404);
        }
        // metodo no permitido
        if ($exception instanceof MethodNotAllowedHttpException) {
           return $this->errorResponse('El método especificado en la petición no es válido', 405);
        }
        //cualquier exception no definida 
         if ($exception instanceof HttpException) {
            return $this->errorResponse($exception->getMessage(), $exception->getStatusCode());
        }
        //error al elimiar datos relacionados
         if ($exception instanceof QueryException) {
            $codigo = $exception->errorInfo[1];

            if ($codigo == 1451) {
                return $this->errorResponse('No se puede eliminar de forma permamente el recurso porque está relacionado con algún otro.', 409);
            }
        }
        //error con el csrf o token de verificacion
        if ($exception instanceof TokenMismatchException) {
            return redirect()->back()->withInput($request->input());
        }

        if (config('app.debug')) {
            return parent::render($request, $exception);            
        }
        //fallas inesperadas en el sistema
        return $this->errorResponse('Falla inesperada. Intente luego', 500);
        
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     * cuando los usuarios no estan autenticados
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
       /* if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }*/
        if($this->isFrontend($request))
        {
            return redirect()->guest('login');
        }
        return $this->errorResponse('El usuario no esta autenticado', 401);
       // return redirect()->guest(route('login'));
    }

    /**
     * Create a response object from the given validation exception.
     *  manejamos los mensajes de error 
     * @param  \Illuminate\Validation\ValidationException  $e
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function convertValidationExceptionToResponse(ValidationException $e, $request)
    {
        
        $errors = $e->validator->errors()->getMessages();

        if($this->isFrontend($request))
        {
            return $request->ajax() ? response()->json($errors,422) : redirect()
                    ->back()
                    ->withInput($request->input())
                    ->withErrors($errors);

        }

           return $this->errorResponse($errors,422);
    }

    //verificar si un apeticion es fron end
    private function isFrontend($request)
    {
        return $request->acceptsHtml() && collect($request->route()->middleware())->contains('web');
    }
}
