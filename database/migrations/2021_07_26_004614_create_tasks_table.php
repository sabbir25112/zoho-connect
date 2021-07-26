<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->longText('description')->nullable();
            $table->unsignedBigInteger('created_by_zpuid')->nullable();
            $table->string('work_form')->nullable();
            $table->boolean('is_comment_added')->nullable();
            $table->string('duration')->nullable();
            $table->unsignedBigInteger('last_updated_time_long')->nullable();
            $table->json('details')->nullable();
            $table->unsignedBigInteger('tasklist_id')->nullable();
            $table->string('key')->nullable();
            $table->string('created_person')->nullable();
            $table->unsignedBigInteger('created_time_long')->nullable();
            $table->date('created_time')->nullable();
            $table->boolean('is_reminder_set')->nullable();
            $table->boolean('is_recurrence_set')->nullable();
            $table->string('created_time_format')->nullable();
            $table->boolean('subtasks')->nullable();
            $table->string('work')->nullable();
            $table->json('link')->nullable();
            $table->json('custom_fields')->nullable();
            $table->string('duration_type')->nullable();
            $table->boolean('isparent')->nullable();
            $table->string('work_type')->nullable();
            $table->boolean('completed')->nullable();
            $table->string('priority')->nullable();
            $table->string('created_by')->nullable();
            $table->string('percent_complete')->nullable();
            $table->date('last_updated_time')->nullable();
            $table->string('name')->nullable();
            $table->boolean('is_docs_assocoated')->nullable();
            $table->string('id_string')->nullable();
            $table->json('log_hours')->nullable();
            $table->string('last_updated_time_format')->nullable();
            $table->string('billingtype')->nullable();
            $table->integer('order_sequence')->nullable();
            $table->json('status')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
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
        Schema::dropIfExists('tasks');
    }
}
