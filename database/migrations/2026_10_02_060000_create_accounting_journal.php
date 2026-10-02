<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_entries', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('sales_ledger_id')->constrained('sales_ledger')->restrictOnDelete();
            $t->foreignId('adjusts_id')->nullable()->constrained('accounting_entries')->restrictOnDelete();
            $t->foreignId('reverses_id')->nullable()->unique()->constrained('accounting_entries')->restrictOnDelete();
            $t->string('request_key', 64)->unique();
            $t->string('source_key', 64)->unique();
            $t->string('payload_hash', 64);
            $t->string('kind', 24);
            $t->string('currency', 3);
            $t->string('reference', 160);
            $t->text('note');
            foreach (['gross_amount', 'fees_amount', 'taxes_amount', 'net_amount', 'basis_amount'] as $f) {
                $t->decimal($f, 20, 6)->nullable();
            }
            $t->json('agreement_snapshot');
            $t->timestamp('occurred_at');
            $t->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $t->timestamp('created_at');
        });
        Schema::create('accounting_shares', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('accounting_entry_id')->constrained()->restrictOnDelete();
            $t->foreignId('author_id')->constrained()->restrictOnDelete();
            $t->decimal('percentage', 8, 4);
            foreach (['estimated', 'owed', 'paid'] as $f) {
                $t->decimal($f, 20, 6)->nullable();
            }
            $t->timestamp('created_at');
            $t->unique(['accounting_entry_id', 'author_id']);
        });
    }

    public function down(): void
    {
        if (DB::table('accounting_entries')->exists() || DB::table('accounting_shares')->exists()) {
            throw new RuntimeException('Accounting history is populated: retain it; rollback code without erasing financial records.');
        }
        Schema::dropIfExists('accounting_shares');
        Schema::dropIfExists('accounting_entries');
    }
};
