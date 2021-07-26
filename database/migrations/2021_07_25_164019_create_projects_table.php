<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('status')->nullable();
            $table->string('is_strict')->nullable();
            $table->string('project_percent')->nullable();
            $table->string('role')->nullable();
            $table->json('bug_count')->nullable();
            $table->boolean('IS_BUG_ENABLED')->nullable();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->string('bug_client_permission')->nullable();
            $table->string('taskbug_prefix')->nullable();
            $table->json('link')->nullable();
            $table->unsignedBigInteger('custom_status_id')->nullable();
            $table->longText('description')->nullable();
            $table->json('milestone_count')->nullable();
            $table->unsignedBigInteger('updated_date_long')->nullable();
            $table->boolean('show_project_overview')->nullable();
            $table->json('task_count')->nullable();
            $table->string('updated_date_format')->nullable();
            $table->string('workspace_id')->nullable();
            $table->string('custom_status_name')->nullable();
            $table->string('owner_zpuid')->nullable();
            $table->string('is_client_assign_bug')->nullable();
            $table->string('bug_defaultview')->nullable();
            $table->string('billing_status')->nullable();
            $table->string('key')->nullable();
            $table->string('owner_name')->nullable();
            $table->unsignedBigInteger('created_date_long')->nullable();
            $table->string('group_name')->nullable();
            $table->string('created_date_format')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('profile_id')->nullable();
            $table->string('is_public')->nullable();
            $table->string('id_string')->nullable();
            $table->date('created_date')->nullable();
            $table->date('updated_date')->nullable();
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
        Schema::dropIfExists('projects');
    }
}
