<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Customer;

class ValidateRequest {
    /**
     * Handle an incoming request for customers.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next) {
        if($customer = Customer::where(['email' => $request->input('email'), 'api_key' => $request->input('api_key')])->first()) {
            $request->request->add(['customer_id' => $customer->customer_id]);
            return $next($request);
        }
        return response()->json(['status' => 'error', 'status_message' => 'Invalid Email / API Key'], 400);
    }
}
