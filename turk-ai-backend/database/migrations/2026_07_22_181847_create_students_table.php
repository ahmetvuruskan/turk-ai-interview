<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('surname');
            $table->string('number')->unique();
            $table->string('grade');
            $table->string('registration_code')->unique()->nullable();
            $table->uuid('parent_id')->unsigned()->nullable();
            $table->foreign('parent_id')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('students');
    }
};
