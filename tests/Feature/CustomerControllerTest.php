<?php

namespace Tests\Feature;

use Illuminate\Http\Response;
use Tests\TestCase;

class CustomerControllerTest extends TestCase
{

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_can_create_customer() {
        $faker = \Faker\Factory::create();
        $payload = [
            'customer_name' => $faker->name,
            'email' => $faker->safeEmail
        ];
        $this->json('post', "api/add_customer", $payload)->assertStatus(Response::HTTP_CREATED);
        return $payload['email'];
    }

    /**
     * @depends test_can_create_customer
     */
    public function test_can_get_customer_key($email) {
        $payload = [
            'email' => $email
        ];
        $key = $this->json('post', "api/get_key", $payload)->assertStatus(Response::HTTP_OK);
        return array('email' => $email, 'api_key' => $key->decodeResponseJson()['api_key']);
    }

    /**
     * @depends test_can_get_customer_key
     */
    public function test_can_create_loan(array $key) {
        $payload = [
            'email' => $key['email'],
            'api_key' => $key['api_key'],
            'amount_required' => 10000,
            'loan_term' => '4'
        ];
        $loan = $this->json('post', "api/create_loan", $payload)->assertStatus(Response::HTTP_OK);
        return array('email' => $key['email'], 'api_key' => $key['api_key'], 'loan_id' => $loan->decodeResponseJson()['loan_id']);
    }

    /**
     * @depends test_can_create_loan
     */
    public function test_can_approve_loan(array $loan) {
        $payload = [
            'email' => 'admin@test.com',
            'password' => 'aspire@123',
            'loan_id' => $loan['loan_id']
        ];
        $this->json('post', "api/approve_loan", $payload)->assertStatus(Response::HTTP_OK);
        return array('email' => $loan['email'], 'api_key' => $loan['api_key'], 'loan_id' => $loan['loan_id']);
    }

    /**
     * @depends test_can_create_loan
     */
    public function test_can_view_loan(array $loan) {
        $payload = [
            'email' => $loan['email'],
            'api_key' => $loan['api_key']
        ];
        $this->json('post', "api/view_loan", $payload)->assertStatus(Response::HTTP_OK);
    }

    /**
     * @depends test_can_approve_loan
     */
    public function test_can_do_repayment(array $loan) {
        $payload = [
            'email' => $loan['email'],
            'api_key' => $loan['api_key'],
            'loan_id' => $loan['loan_id'],
            'amount' => '2500'
        ];
        $this->json('post', "api/repayment", $payload)->assertStatus(Response::HTTP_OK);
    }

    /**
     * @depends test_can_approve_loan
     */
    public function test_can_do_full_repayment(array $loan) {
        $payload = [
            'email' => $loan['email'],
            'api_key' => $loan['api_key'],
            'loan_id' => $loan['loan_id'],
            'amount' => '7500'
        ];
        $this->json('post', "api/repayment", $payload)->assertStatus(Response::HTTP_OK);
    }
}
