<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
     
        if (!Schema::hasTable('training_programs')) {
            Schema::create('training_programs', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();
                $table->dateTime('start_time');
                $table->dateTime('end_time');
                $table->string('venue');
                $table->timestamps();
            });
        }

      
        if (!Schema::hasTable('training_attendance')) {
            Schema::create('training_attendance', function (Blueprint $table) {
                $table->id();
                $table->integer('user_id')->index(); 
                $table->foreignId('training_program_id')->constrained('training_programs')->onDelete('cascade');
                $table->string('status')->default('Assigned'); 
                $table->timestamps();
                
             
            });
        }

      
        if (!Schema::hasTable('training_feedbacks')) {
            Schema::create('training_feedbacks', function (Blueprint $table) {
                $table->id();
                $table->integer('user_id')->index(); // 用 integer
                $table->foreignId('training_program_id')->constrained('training_programs')->onDelete('cascade');
                $table->text('comments');
                $table->integer('rating');
                $table->timestamps();
                
               
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('training_feedbacks');
        Schema::dropIfExists('training_attendance');
        Schema::dropIfExists('training_programs');
    }
};