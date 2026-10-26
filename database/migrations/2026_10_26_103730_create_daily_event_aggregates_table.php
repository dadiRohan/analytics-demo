<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDailyEventAggregatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('daily_event_aggregates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('date');
            $table->string('event_name', 100);
            $table->string('event_category', 50)->nullable();
            $table->unsignedInteger('total_count')->default(0);
            $table->unsignedInteger('unique_visitors')->default(0);
            $table->unique(['date', 'event_name']);
            $table->index('date');            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('daily_event_aggregates');
    }
}
