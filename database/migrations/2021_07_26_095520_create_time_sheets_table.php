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
            $table->longText('notes')->nullable();
            $table->longText('owner_name')->nullable();
            $table->longText('created_time_format')->nullable();
            $table->integer('minutes')->nullable();
            $table->integer('total_minutes')->nullable();
            $table->longText('owner_id')->nullable();
            $table->longText('approval_status')->nullable();
            $table->longText('end_time')->nullable();
            $table->json('link')->nullable();
            $table->date('last_modified_date')->nullable();
            $table->longText('bill_status')->nullable();
            $table->unsignedBigInteger('last_modified_time_long')->nullable();
            $table->longText('start_time')->nullable();
            $table->longText('last_modified_time_format')->nullable();
            $table->json('task')->nullable();
            $table->json('added_by')->nullable();
            $table->longText('id_string')->nullable();
            $table->date('created_date')->nullable();
            $table->longText('hours_display')->nullable();
            $table->json('task_list')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('task_id')->nullable();
            $table->longText('task_name')->nullable();
            $table->unsignedBigInteger('subtask_id')->nullable();
            $table->longText('subtask_name')->nullable();
            $table->longText('approver_name')->nullable();
            $table->date('log_date')->nullable();
            $table->unsignedBigInteger('log_date_long')->nullable();
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
