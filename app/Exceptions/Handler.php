<?php
declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    /**
     * Handler constructor.
     */
    public function __construct()
    {
        $this->dontReport = [
            AuthorizationException::class,
            HttpException::class,
            ModelNotFoundException::class,
            ValidationException::class
        ];
    }

    public function render($request, Exception $e)
    {
        $exception = null;
        if( $e instanceof \App\Containers\Exceptions\ListNotFoundException )
            $exception = $e;
        
        if( $e instanceof \App\Containers\Exceptions\MemberListNotFoundException )
            $exception = $e;

        if( $exception )    
            return \response()->json( [ 'message' => $e->getMessage() ] , 404);

        return \response()->json( [ 'message' => $e->getMessage() ], 500 );
    }

    public function report(Exception $e)
    {
        return parent::report($e);
    }
}
