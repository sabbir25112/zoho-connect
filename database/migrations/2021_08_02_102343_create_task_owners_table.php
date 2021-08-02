<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskOwnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_owners', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('TaskID')->nullable();
            $table->unsignedBigInteger('OwnerID')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->unsignedBigInteger('zpuid')->nullable();
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
        Schema::dropIfExists('task_owners');
    }
}
