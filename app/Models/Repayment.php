<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Repayment extends Model
{
    use HasFactory;
    protected $primaryKey = 'repayment_id';
    protected $fillable = ['loan_id', 'schedule', 'repayment_amount'];
}
