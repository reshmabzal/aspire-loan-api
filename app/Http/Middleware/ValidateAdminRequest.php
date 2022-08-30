<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Auth;

class ValidateAdminRequest {
    /**
     * Handle an incoming request for Admin users.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next) {
        if(Auth::attempt(['email' => $request->input('email'), 'password' => $request->input('password')])) {
            return $next($request);
        }
        return response()->json(['status' => 'error', 'status_message' => 'Invalid Email / Password'], 400);
    }
}
