<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTimeSheetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('time_sheets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('created_time_long')->nullable();
            $table->integer('hours')->nullable();
            $table->string('notes')->nullable();
            $table->string('owner_name')->nullable();
            $table->string('created_time_format')->nullable();
            $table->integer('minutes')->nullable();
            $table->integer('total_minutes')->nullable();
            $table->string('owner_id')->nullable();
            $table->string('approval_status')->nullable();
            $table->string('end_time')->nullable();
            $table->json('link')->nullable();
            $table->date('last_modified_date')->nullable();
            $table->string('bill_status')->nullable();
            $table->unsignedBigInteger('last_modified_time_long')->nullable();
            $table->string('start_time')->nullable();
            $table->string('last_modified_time_format')->nullable();
            $table->json('task')->nullable();
            $table->json('added_by')->nullable();
            $table->string('id_string')->nullable();
            $table->date('created_date')->nullable();
            $table->string('hours_display')->nullable();
            $table->json('task_list')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('task_id')->nullable();
            $table->string('task_name')->nullable();
            $table->unsignedBigInteger('subtask_id')->nullable();
            $table->string('subtask_name')->nullable();
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
        Schema::dropIfExists('time_sheets');
    }
}
