<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('slips');

        Schema::create('slips', function (Blueprint $table) {
        $table->id();

        $table->foreignId('owner_id')->nullable()->constrained('users')->onDelete('set null');
        $table->foreignId('create_by_id')->nullable()->constrained('users')->onDelete('cascade');
        $table->foreignId('approve_by_id')->nullable()->constrained('users')->onDelete('set null');

        // รายการรับและจ่ายเก็บเป็น JSON
        $table->json('receipt_details')->nullable(); 

        $table->json('expenses_details')->nullable();

        // รวมยอด (ไว้แสดงง่าย ๆ)
        $table->decimal('total_receipt', 12, 2)->default(0);
        $table->decimal('total_expenses', 12, 2)->default(0);

        // วันที่และสถานะ
        $table->date('approve_date')->nullable();
        $table->date('monthly')->nullable();
        $table->enum('status', ['รอตรวจสอบ', 'รออนุมัติ', 'อนุมัติแล้ว', 'ไม่อนุมัติ'])->default('รอตรวจสอบ');

        $table->timestamps();
    });

    }

    public function down(): void
    {
        Schema::dropIfExists('slips');
    }
};
