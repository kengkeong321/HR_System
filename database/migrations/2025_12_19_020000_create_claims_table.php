<?php
//Dephnie Ong Yan Yee
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claims', function (Blueprint $table) {
            $table->id();
        
            $table->unsignedBigInteger('staff_id');

            $table->string('description');
            $table->decimal('amount', 10, 2); 
            $table->string('receipt_path')->nullable(); 

            $table->string('status')->default('Pending'); 

            $table->unsignedBigInteger('approved_by')->nullable(); 
            $table->timestamp('approved_at')->nullable();

            $table->unsignedBigInteger('rejected_by')->nullable(); 
            $table->text('rejection_reason')->nullable(); 

            $table->timestamps();

            $table->foreign('staff_id')->references('staff_id')->on('staff')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('rejected_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claims');
    }
};