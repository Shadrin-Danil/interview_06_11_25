<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PhpParser\Builder\Function_;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('transactions')) {
            Schema::create('transactions', function (Blueprint $table) {
                
                $table->id();
                
                $table->unsignedBigInteger('sender_id')->nullable()->comment('Отправитель');
                $table->unsignedBigInteger('receiver_id')->nullable()->comment('Получатель');

                $table->enum('type', ['deposit', 'withdraw', 'transfer_in', 'transfer_out']);
                
                $table->decimal('amount', 12, 2)->comment('Сумма');
                
                $table->text('comment')->nullable()->comment('Комментарий');
            
                $table->timestamps();

                $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');
                
            });
        };
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
