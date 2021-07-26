<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasklistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasklists', function (Blueprint $table) {
            $table->id();
            $table->string('id_string')->nullable();
            $table->string('name')->nullable();
            $table->unsignedBigInteger('created_time_long')->nullable();
            $table->date('created_time')->nullable();
            $table->string('flag')->nullable();
            $table->string('created_time_format')->nullable();
            $table->json('link')->nullable();
            $table->boolean('completed')->nullable();
            $table->boolean('rolled')->nullable();
            $table->json('task_count')->nullable();
            $table->integer('sequence')->nullable();
            $table->date('last_updated_time')->nullable();
            $table->unsignedBigInteger('last_updated_time_long')->nullable();
            $table->string('last_updated_time_format')->nullable();
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
        Schema::dropIfExists('tasklists');
    }
}
