<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRepaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('repayments', function (Blueprint $table) {
            $table->increments('repayment_id');
            $table->integer('loan_id');
            $table->date('schedule');
            $table->string('repayment_amount');
            $table->string('paid_amount')->nullable(true);
            $table->enum('repayment_state', ['PENDING', 'PAID'])->default('PENDING');
            $table->timestamp('repaid_at')->nullable(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('repayments');
    }
}
