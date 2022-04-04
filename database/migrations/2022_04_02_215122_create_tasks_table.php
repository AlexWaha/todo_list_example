<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateTaskTable
 */
class CreateTasksTable extends Migration
{

    /**
     * Run the migrations.
     * @return void
     */

    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('title')->fulltext();
            $table->text('description')->nullable();
            $table->tinyInteger('priority')->default(5);
            $table->unsignedBigInteger('status_id')->default('1')->index();
            $table->unsignedBigInteger('work_time')->nullable()->index();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('tasks');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::dropIfExists('tasks');
    }
}
