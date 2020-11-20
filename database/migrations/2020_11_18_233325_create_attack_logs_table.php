<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttackLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attack_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attack_id')->constrained('attacks')->onDelete('cascade');
            $table->foreignId('blueteam_id')->constrained('teams')->onDelete('cascade');
            $table->foreignId('redteam_id')->constrained('teams')->onDelete('cascade');
            $table->integer('difficulty');
            $table->float('detection_chance');
            $table->boolean('success');
            $table->boolean('possible');
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
        Schema::dropIfExists('attack_logs');
    }
}
