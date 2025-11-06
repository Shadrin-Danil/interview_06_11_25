<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PhpParser\Builder\Function_;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                // $table->id();
                $table->unsignedBigInteger('id')->primary(); 
                $table->decimal('balance', 12, 2)->default(0)->comment('Баланс');
                $table->timestamps(); 
            });
        };
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
