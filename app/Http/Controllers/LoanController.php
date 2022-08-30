<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Loan;
use App\Models\Repayment;
use Carbon\Carbon;
use Auth;

class LoanController extends Controller {

    protected $frequency = 7;

    /**
       * 
       * Creates a new loan from Customer's request
       *
       * @param Request $request API request consist of amount_required and loan_term
       * @return json response of loan creation status
       */
    public function store(Request $request) {
        $rules = array(
            'amount_required'   => "required|regex:/^\d+(\.\d{1,3})?$/",
            'loan_term' => "required|numeric"
        );
        $validator = Validator::make($request->all(), $rules);
        if($validator->fails()) {
            return response()->json(['status' => 'error', 'status_message' => $validator->errors()], 400);
        } else {
            $customer   = Customer::where(['email' => $request->input('email'), 'api_key' => $request->input('api_key')])->first();
            $loan_data  = array(
                'customer_id'   => $customer->customer_id,
                'loan_amount'   => $request->input('amount_required'),
                'loan_term'     => $request->input('loan_term')
            );
            $loan = Loan::create($loan_data);
            return response()->json(['status' => 'success', 'status_message' => 'Loan Created Successfully', 'loan_id' => $loan->loan_id], 200);
        }
    }

    /**
       * 
       * Approves the loan created by customer. This is done by admin user where the user email and password will be validated from the Users table
       * Also this creates the repayment records in the repayments table
       *
       * @param Request $request API request consist of loan_id
       * @return json response of loan approval status
       */
    public function approve_loan(Request $request) {
        $rules = array(
            'loan_id' => "required|numeric"
        );
        $validator = Validator::make($request->all(), $rules);
        if($validator->fails()) {
            return response()->json(['status' => 'error', 'status_message' => $validator->errors()], 400);
        } else {
            if($loan = Loan::where(['loan_id' => $request->input('loan_id'), 'loan_state' => 'PENDING'])->first()) {
                Loan::where('loan_id', $request->input('loan_id'))->update([
                    'loan_state'    => 'APPROVED',
                    'due_amount'    => $loan->loan_amount,
                    'approved_by'   => Auth::id(),
                    'approved_at'   => Carbon::now()
                ]);
                $repayment_dues = $this->get_dues($loan->loan_amount, $loan->loan_term);
                $date = $loan->created_at;
                foreach($repayment_dues as $due) {
                    $date = $date->addDays($this->frequency);
                    $repayment_data[] = array(
                        'loan_id'   => $loan->loan_id,
                        'schedule'  => $date->toDateString(),
                        'repayment_amount' => $due,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    );
                }
                Repayment::insert($repayment_data);
                return response()->json(['status' => 'success', 'status_message' => 'Loan Approved Successfully'], 200);
            } else {
                return response()->json(['status' => 'error', 'status_message' => 'Loan not found or already approved'], 400);
            }
        }
    }

    /**
       * 
       * Returns all the loans created by given customer
       *
       * @param Request $request API request consist of customer_id
       * @return json list of loans created by given customer
       */
    public function show(Request $request) {
        if($loans = Loan::where(['customer_id' => $request->input('customer_id')])->get()) {
            $counter = 0;
            foreach($loans as $loan) {
                $repayments = Repayment::where(['loan_id' => $loan->loan_id])->get();
                $loan_data[$counter] = array(
                    'loan_id'       => $loan->loan_id,
                    'loan_amount'   => $loan->loan_amount,
                    'loan_term'     => $loan->loan_term,
                    'due_amount'    => $loan->due_amount,
                    'loan_state'    => $loan->loan_state,
                    'created_date'  => $loan->created_at->toDateString()
                );
                foreach($repayments as $due) {
                    $loan_data[$counter]['repayments'][] = array(
                        'schedule'          => $due->schedule,
                        'repayment_amount'  => $due->repayment_amount,
                        'paid_amount'       => $due->paid_amount,
                        'repayment_state'   => $due->repayment_state
                    );
                }
                $counter++;
            }
            $return_data = array('status' => 'success', 'loans' => $loan_data);
            return response()->json($return_data, 200);
        } else {
            $return_data = array('status' => 'error', 'response_text' => 'No Loan found for the given Email');
            return response()->json($return_data, 401);
        }
    }

    /**
       * 
       * Does the repayment based on the given amount and loan id
       *
       * @param Request $request API request consist of loan_id and amount
       * @return json response of repayment status
       */
    public function repayment(Request $request) {
        $rules = array(
            'loan_id'   => "required|numeric",
            'amount'    => "required|regex:/^\d+(\.\d{1,3})?$/"
        );
        $validator = Validator::make($request->all(), $rules);
        $status = 'error';
        if($validator->fails()) {
            $error_msg = $validator->errors();
        } else {
            if($loan = Loan::where(['loan_id' => $request->input('loan_id'), 'loan_state' => 'APPROVED'])->first()) {
                if($loan->customer_id == $request->input('customer_id')) {
                    $response = $this->do_repayment($loan, $request->input('amount'));
                    if($response['status'] == 'incomplete') {
                        $error_msg = $response['message'];
                    } else {
                        $status = 'success';
                    }
                } else {
                    $error_msg = 'Loan is not belonging to the user';
                }
            } else {
                $error_msg = 'Loan not found or not Approved yet';
            }
        }
        if($status == 'error') {
            return response()->json(['status' => 'error', 'status_message' => $error_msg], 400);
        }
        return response()->json(['status' => 'success', 'status_message' => 'Repayment done successfully. Please Run View Loans API for updated repayments.'], 200);
    }

    /**
       * 
       * Internal function for the process of repayment
       *
       * @param object $loan
       * @param numeric $amount
       * @return array response of repayment status
       */
    private function do_repayment($loan, $amount) {
        if($repayments = Repayment::where(['loan_id' => $loan->loan_id, 'repayment_state' => 'PENDING'])->get()) {
            $due = $repayments->first();
            if($amount > $loan->due_amount) {
                //Given amount is greater than the due amount
                return array('status' => 'incomplete', 'message' => 'Given amount is greater than the due amount');
            } elseif($amount == $loan->due_amount) {
                //Given amount is equal to due amount. So change the loan status as PAID and remove future repayments
                $this->update_repayment($due->repayment_id, $amount);
                $this->update_loan($loan->loan_id, $loan->due_amount, $amount);
                Repayment::where(['loan_id' => $loan->loan_id, 'repayment_state' => 'PENDING'])->update([
                    'repayment_state'   => 'PAID',
                    'paid_amount'       => 0,
                    'repaid_at'         => Carbon::now()
                ]);
            } elseif($amount < $loan->due_amount && $amount == $due->repayment_amount) {
                //Given amount is lesser than due amount and equal to the current repayment amount. Just change the repayment status as PAID
                $this->update_repayment($due->repayment_id, $amount);
                $this->update_loan($loan->loan_id, $loan->due_amount, $amount);
            } elseif($amount < $loan->due_amount && $amount < $due->repayment_amount) {
                //Given amount is lesser than due amount and lesser than current repayment amount. So throw error.
                return array('status' => 'incomplete', 'message' => 'Given amount should be greater than or equal to the Due amount of '.$due->repayment_amount);
            } elseif($amount < $loan->due_amount && $amount > $due->repayment_amount) {
                //Given amount is lesser than due amount and greater than current repayment amount.
                //So update the repayment details and modify future dues according to the new Due amount
                $this->update_repayment($due->repayment_id, $amount);
                $this->update_loan($loan->loan_id, $loan->due_amount, $amount);
                $updated_repayments = Repayment::where(['loan_id' => $loan->loan_id, 'repayment_state' => 'PENDING'])->get();
                $updated_loan = Loan::where(['loan_id' => $loan->loan_id])->first();
                $dues = $this->get_dues($updated_loan->due_amount, $updated_repayments->count());
                $counter = 0;
                foreach($updated_repayments as $updated_due) {
                    Repayment::where('repayment_id', $updated_due->repayment_id)->update([
                        'repayment_amount' => $dues[$counter],
                    ]);
                    $counter++;
                }
            }
            if(!Repayment::where(['loan_id' => $loan->loan_id, 'repayment_state' => 'PENDING'])->first()) {
                Loan::where('loan_id', $loan->loan_id)->update([
                    'loan_state'   => 'PAID',
                    'paid_at'    => Carbon::now()
                ]);
            }
            return array('status' => 'success', 'message' => 'Repayment processed successfully.');
        } else {
            return array('status' => 'incomplete', 'message' => 'No Pending Dues');
        }
    }

    /**
       * 
       * Internal function for updating repayment record
       *
       * @param integer $repayment_id
       * @param numeric $amount
       * @return json response of repayment record update
       */
    private function update_repayment($repayment_id, $amount) {
        return Repayment::where('repayment_id', $repayment_id)->update([
            'repayment_state'   => 'PAID',
            'paid_amount'       => $amount,
            'repaid_at'         => Carbon::now()
        ]);
    }

    /**
       * 
       * Internal function for updating loan record
       *
       * @param integer $loan_id
       * @param numeric $due_amount
       * @param numeric $total_amount
       * @return json response of loan record update
       */
    private function update_loan($loan_id, $due_amount, $total_amount) {
        return Loan::where('loan_id', $loan_id)->update([
            'due_amount'    => round(($due_amount - $total_amount), 2)
        ]);
    }

    /**
       * 
       * Internal function for calculating total dues
       *
       * @param numeric $loan_amount
       * @param integer $loan_term
       * @return array response of loan repayment tenures
       */
    private function get_dues($loan_amount, $loan_term) {
        $repayment_due = round(($loan_amount / $loan_term), 2);
        $last_due = ($loan_amount - (($loan_term - 1) * $repayment_due));
        for($due = 1; $due < $loan_term; $due++) {
            $repayment_due_array[] = round($repayment_due, 2);
        }
        $repayment_due_array[] = round($last_due, 2);
        return $repayment_due_array;
    }
}
