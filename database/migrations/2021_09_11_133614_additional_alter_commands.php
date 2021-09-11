<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AdditionalAlterCommands extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // ALTER TABLE `zoho-connect`.`tasks`
        // CHANGE COLUMN `order_sequence` `order_sequence` VARCHAR(30) NULL DEFAULT NULL ;


        // ALTER TABLE `zoho-connect`.`sub_tasks`
        // CHANGE COLUMN `percent_complete` `percent_complete` VARCHAR(10) NULL DEFAULT NULL ;

        // ALTER TABLE `zoho-connect`.`sub_tasks`
        // CHANGE COLUMN `order_sequence` `order_sequence` VARCHAR(30) NULL DEFAULT NULL ;
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
