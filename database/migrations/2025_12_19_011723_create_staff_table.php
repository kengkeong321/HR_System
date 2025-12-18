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
    Schema::create('staff', function (Blueprint $table) {
        $table->id('staff_id'); // Primary key for staff
        
        // This links to the 'id' column on the 'users' table
        $table->foreignId('user_id')
              ->constrained('users') 
              ->onDelete('cascade');
        
        $table->string('department')->nullable();
        $table->decimal('base_salary', 10, 2)->default(0);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
