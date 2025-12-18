<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            
            // Link to the staff member making the claim
            $table->unsignedBigInteger('staff_id');

            // Claim Details
            $table->string('description');
            $table->decimal('amount', 10, 2); // Supports values up to 99,999,999.99
            $table->string('receipt_path')->nullable(); // Stores the file path for the image/PDF

            // Workflow Status Management
            $table->string('status')->default('Pending'); // Values: Pending, Approved, Rejected

            // Audit Trail: Approval
            $table->unsignedBigInteger('approved_by')->nullable(); // Links to the HR user (User 7)
            $table->timestamp('approved_at')->nullable();

            // Audit Trail: Rejection
            $table->unsignedBigInteger('rejected_by')->nullable(); // Links to the HR user
            $table->text('rejection_reason')->nullable(); // Required if status is Rejected

            $table->timestamps();

            // Foreign Key Constraints (Data Integrity)
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