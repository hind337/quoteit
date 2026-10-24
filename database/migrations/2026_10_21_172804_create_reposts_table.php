<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Who reposted which quote, and when (a repost brings the quote back to the top of the wall).
     */
    public function up(): void
    {
        Schema::create('reposts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            // A person can repost the same quote only once
            $table->unique(['user_id', 'quote_id']);
            $table->index(['quote_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reposts');
    }
};
