<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBonusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bonuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->nullable()->constrained('teams')->onDelete('cascade');   
            $table->foreignId('target_id')->nullable()->constrained('teams')->onDelete('cascade');       
            $table->text('payload_name')->nullable();     
            $table->json('tags');
            $table->integer('percentRevDeducted')->nullable();
            $table->integer('percentRepDeducted')->nullable();
            $table->integer('percentDetDeducted')->nullable();
            $table->integer('percentAnalDeducted')->nullable();
            $table->integer('percentDiffDeducted')->nullable();
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
        Schema::dropIfExists('bonuses');
    }
}
