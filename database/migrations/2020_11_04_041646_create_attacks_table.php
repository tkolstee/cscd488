<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attacks', function (Blueprint $table) {
            $table->id();
            $table->text('name');
            $table->text('class_name');
            $table->json('tags');
            $table->json('prereqs');
            $table->foreignId('blueteam')->constrained('teams')->onDelete('cascade');
            $table->foreignId('redteam')->constrained('teams')->onDelete('cascade');
            $table->integer('difficulty'); // 1 - 5. 1 always succeeds, 5 always fails.
            $table->integer('detection_risk');    // 1 - 5. 1 is never detected, 5 is always detected
            $table->boolean('success')->nullable();
            $table->boolean('detected')->nullable();
            $table->integer('energy_cost');
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
        Schema::dropIfExists('attacks');
    }
}
