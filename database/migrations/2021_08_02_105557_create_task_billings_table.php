<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskBillingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_billings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('TaskID')->nullable();
            $table->double('billable_hours', 8, 2)->nullable();
            $table->double('non_billable_hours', 8, 2)->nullable();
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
        Schema::dropIfExists('task_billings');
    }
}
