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
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('type')->change(); // Change enum to string for more flexibility
            $table->foreignId('related_account_id')->nullable()->constrained('accounts')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['related_account_id']);
            $table->dropColumn('related_account_id');
            // Reverting to enum is hard to do generically without knowing the driver, 
            // but for this project we'll leave it as string or skip reversion for now.
        });
    }
};
