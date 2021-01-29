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
            $table->text('payload_tag')->nullable();
            $table->text('payload_choice')->nullable();
            $table->foreignId('blueteam')->constrained('teams')->onDelete('cascade');
            $table->foreignId('redteam')->constrained('teams')->onDelete('cascade');
            $table->decimal('difficulty'); // 0.0 - 5.0, 0 always succeeds, 5 always fails.
            $table->decimal('detection_risk');    // 0.0 - 5.0, 0 is never detected, 5 is always detected
            $table->decimal('analysis_risk')->nullable();
            $table->decimal('attribution_risk')->nullable();
            $table->decimal('calculated_difficulty');
            $table->decimal('calculated_detection_risk');
            $table->decimal('calculated_analysis_risk')->nullable();
            $table->decimal('calculated_attribution_risk')->nullable();
            $table->boolean('possible')->default('true');
            $table->boolean('success')->nullable();
            $table->integer('detection_level')->nullable(); //0 = unseen, 1 = detected, 2 = analyzed by sec. analyst, etc.
            $table->boolean('notified')->nullable();
            $table->boolean('isNews')->nullable();
            $table->integer('energy_cost');
            $table->integer('blue_loss');
            $table->integer('red_gain');
            $table->integer('reputation_loss');
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
