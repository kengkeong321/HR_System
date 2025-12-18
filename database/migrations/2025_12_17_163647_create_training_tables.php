<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. 培训表
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

        // 2. 考勤表
        if (!Schema::hasTable('training_attendance')) {
            Schema::create('training_attendance', function (Blueprint $table) {
                $table->id();
                $table->integer('user_id')->index(); // 用 integer 配合你的旧表
                $table->foreignId('training_program_id')->constrained('training_programs')->onDelete('cascade');
                $table->string('status')->default('Assigned'); 
                $table->timestamps();
                
                // ❌❌❌ 这一行一定要注释掉！防止报错 ❌❌❌
                // $table->foreign('user_id')->references('user_id')->on('user')->onDelete('cascade');
            });
        }

        // 3. 反馈表
        if (!Schema::hasTable('training_feedbacks')) {
            Schema::create('training_feedbacks', function (Blueprint $table) {
                $table->id();
                $table->integer('user_id')->index(); // 用 integer
                $table->foreignId('training_program_id')->constrained('training_programs')->onDelete('cascade');
                $table->text('comments');
                $table->integer('rating');
                $table->timestamps();
                
                // ❌❌❌ 这一行也注释掉！ ❌❌❌
                // $table->foreign('user_id')->references('user_id')->on('user')->onDelete('cascade');
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