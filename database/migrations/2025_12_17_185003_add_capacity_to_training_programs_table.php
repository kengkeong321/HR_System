<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('training_programs', function (Blueprint $table) {
        // 增加 capacity 欄位，整數，預設可能是 20 或者 nullable
        $table->integer('capacity')->after('venue')->default(20); 
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('training_programs', function (Blueprint $table) {
            //
        });
    }
};
