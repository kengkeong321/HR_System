<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
{
    Schema::table('training_attendance', function (Blueprint $table) {
        $table->dropColumn('reason'); // 删掉字段
    });
}

public function down()
{
    Schema::table('training_attendance', function (Blueprint $table) {
        $table->string('reason')->nullable(); // 如果后悔了可以回滚
    });
}
};
