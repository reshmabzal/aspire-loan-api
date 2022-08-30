<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;

class CustomerController extends Controller {
    
    /**
       * 
       * Creates new customer
       *
       * @param Request $request
       * @return json response of created customer status
       */
    public function store(Request $request) {
        if(Customer::where(['email' => $request->input('email')])->first()) {
            $return_data = array('status' => 'error', 'response_text' => 'Email already exist');
            return response()->json($return_data, 400);
        } else {
            $faker = \Faker\Factory::create();
            $request->request->add(['api_key' => $faker->bothify('********************************')]);
            $data = Customer::create($request->all());
            if($data->customer_id) {
                $return_data = array('status' => 'success', 'response_text' => 'Customer created Successfully');
                return response()->json($return_data, 201);
            }
        }
    }

    /**
       * 
       * Returns customer API Key
       *
       * @param Request $request
       * @return json response of customer's api_key
       */
    public function show(Request $request) {
        if($customer = Customer::where(['email' => $request->input('email')])->first()) {
            $return_data = array('status' => 'success', 'api_key' => $customer->api_key);
            return response()->json($return_data, 200);
        } else {
            $return_data = array('status' => 'error', 'response_text' => 'No User found for the given Email');
            return response()->json($return_data, 401);
        }
    }
}

