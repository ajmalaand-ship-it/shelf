<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_retention_records', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 24)->index();
            $table->foreignId('purchase_id')->nullable()->constrained('purchases')->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->json('evidence');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        if (DB::table('financial_retention_records')->exists()) {
            throw new LogicException('Retention evidence exists; preserve it before a separately reviewed rollback.');
        }
        Schema::dropIfExists('financial_retention_records');
    }
};
