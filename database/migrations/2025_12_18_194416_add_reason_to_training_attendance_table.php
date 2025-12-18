<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::table('training_attendance', function (Blueprint $table) {
        // 在 status 字段后面增加 reason 字段，允许为空
        $table->string('reason')->nullable()->after('status');
    });
}

public function down(): void
{
    Schema::table('training_attendance', function (Blueprint $table) {
        $table->dropColumn('reason');
    });
}
};
