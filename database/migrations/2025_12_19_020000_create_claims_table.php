<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
{
    Schema::create('claims', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('staff_id'); 
        $table->string('claim_type');
        $table->decimal('amount', 10, 2);
        $table->text('description');
        $table->string('receipt_path')->nullable();
        $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
        $table->text('rejection_reason')->nullable();
        $table->timestamps();

        // Basic Foreign Key
        $table->foreign('staff_id')->references('staff_id')->on('staff')->onDelete('cascade');
    });
}

    public function down(): void
    {
        Schema::dropIfExists('claims');
    }
};
