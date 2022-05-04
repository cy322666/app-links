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
        Schema::create('links', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('name')->nullable();
            $table->string('body');
            $table->boolean('is_work')->default(true);
            $table->integer('app_id')->nullable();
            $table->uuid('uuid')->nullable();
            $table->boolean('is_prelanding')->default(false);

            $table->softDeletes();

            $table->index('is_work');
            $table->index('app_id');
            $table->index('uuid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('links');
    }
};
