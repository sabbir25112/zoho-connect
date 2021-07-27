<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('profile_type')->nullable();
            $table->string('role')->nullable();
            $table->string('portal_role_name')->nullable();
            $table->boolean('active')->nullable();
            $table->string('zpuid')->nullable();
            $table->string('profile_id')->nullable();
            $table->string('project_profile_id')->nullable();
            $table->string('portal_profile_name')->nullable();
            $table->string('portal_role_id')->nullable();
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
        Schema::dropIfExists('users');
    }
}
