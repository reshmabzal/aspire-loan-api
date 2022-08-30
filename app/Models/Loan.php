<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

    protected $primaryKey = 'loan_id';
    protected $fillable = ['customer_id', 'loan_amount', 'loan_term', 'due_amount'];
}
