<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class JwtMiddleware
{
    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return ApiResponse::send('error', null, 'User not found', null, Response::HTTP_NOT_FOUND);
            }
        } catch (TokenExpiredException $e) {
            return ApiResponse::send('error', null, 'Token has expired', null, Response::HTTP_UNAUTHORIZED);
        } catch (TokenInvalidException $e) {
            return ApiResponse::send('error', null, 'Token is invalid', null, Response::HTTP_UNAUTHORIZED);
        } catch (JWTException $e) {
            return ApiResponse::send('error', null, 'Token is missing or not provided', null, Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
