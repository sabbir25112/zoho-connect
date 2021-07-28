<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBugsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bugs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('updated_time_long')->nullable();
            $table->string('comment_count')->nullable();
            $table->string('updated_time')->nullable();
            $table->unsignedBigInteger('assignee_zpuid')->nullable();
            $table->string('flag')->nullable();
            $table->string('updated_time_format')->nullable();
            $table->json('link')->nullable();
            $table->string('title')->nullable();
            $table->string('assignee_name')->nullable();
            $table->string('reporter_id')->nullable();
            $table->string('escalation_level')->nullable();
            $table->string('key')->nullable();
            $table->unsignedBigInteger('created_time_long')->nullable();
            $table->json('severity')->nullable();
            $table->string('created_time')->nullable();
            $table->string('created_time_format')->nullable();
            $table->json('reproducible')->nullable();
            $table->json('module')->nullable();
            $table->json('classification')->nullable();
            $table->json('GROUP_NAME')->nullable();
            $table->string('bug_number')->nullable();
            $table->string('reporter_non_zuser')->nullable();
            $table->string('reported_person')->nullable();
            $table->string('reporter_email')->nullable();
            $table->string('id_string')->nullable();
            $table->boolean('closed')->nullable();
            $table->string('bug_prefix')->nullable();
            $table->string('attachment_count')->nullable();
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
        Schema::dropIfExists('bugs');
    }
}
