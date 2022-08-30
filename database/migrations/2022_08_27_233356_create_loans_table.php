<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->increments('loan_id');
            $table->integer('customer_id');
            $table->string('loan_amount');
            $table->integer('loan_term');
            $table->enum('loan_state', ['PENDING', 'APPROVED', 'PAID'])->default('PENDING');
            $table->string('due_amount')->nullable(true);
            $table->integer('approved_by')->nullable(true);
            $table->timestamp('approved_at')->nullable(true);
            $table->timestamp('paid_at')->nullable(true);
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
        Schema::dropIfExists('loans');
    }
}
