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
        Schema::create('actions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('type')->nullable();
            $table->integer('link_id')->nullable();
            $table->integer('app_id')->nullable();
            $table->string('os')->nullable();
            $table->string('country')->nullable();
            $table->float('cost')->nullable();
            $table->string('campaign_id')->nullable();
            $table->string('click_id')->nullable();

            $table->index('cost');
            $table->index('campaign_id');
            $table->index('click_id');
            $table->index('country');
            $table->index('os');
            $table->index('app_id');
            $table->index('link_id');
            $table->index('type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('actions');
    }
};
