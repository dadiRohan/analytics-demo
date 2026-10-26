<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('event_id', 36)->unique();
            $table->string('event_name', 100);
            $table->string('event_category', 50)->nullable();
            $table->json('properties')->nullable();
            $table->string('anonymous_id', 100);
            $table->string('session_id', 100);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('page_url', 2048)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('occurred_at');
            $table->timestamp('received_at')->useCurrent();

            $table->index(['event_name', 'occurred_at']);
            $table->index(['anonymous_id', 'occurred_at']);
            $table->index('session_id');
            $table->index('user_id');
            $table->index('occurred_at');            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('events');
    }
}
