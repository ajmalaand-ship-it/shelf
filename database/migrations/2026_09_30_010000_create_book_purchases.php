<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('collections', function (Blueprint $t): void {
            $t->decimal('price_usd', 10, 2)->nullable();
            $t->string('purchase_original_product_id')->nullable();
        });
        DB::table('collections')->orderBy('id')->each(function ($book): void {
            DB::table('collections')->where('id', $book->id)->update([
                'purchase_original_product_id' => $book->product_id,
                'product_id' => 'shelf_book_'.$book->id,
            ]);
        });
        Schema::table('collections', fn (Blueprint $t) => $t->unique('product_id'));
        Schema::table('readers', function (Blueprint $t): void {
            $t->boolean('buying_blocked')->default(false);
            $t->unsignedBigInteger('buying_blocked_by')->nullable();
            $t->timestamp('buying_blocked_at')->nullable();
        });
        Schema::create('author_share_agreements', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('collection_id')->constrained()->restrictOnDelete();
            $t->json('contributors');
            $t->string('basis', 8);
            $t->text('deductions');
            $t->text('sharing_terms');
            $t->timestamp('starts_at');
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamp('created_at');
        });
        Schema::create('purchase_events', function (Blueprint $t): void {
            $t->id();
            $t->string('provider_event_id')->unique();
            $t->string('event_type', 40);
            $t->string('transaction_id');
            $t->foreignId('collection_id')->constrained()->restrictOnDelete();
            $t->unsignedBigInteger('reader_id')->nullable()->index();
            $t->string('environment', 16);
            $t->json('details');
            $t->timestamp('occurred_at');
            $t->timestamp('created_at');
        });
        Schema::create('purchases', function (Blueprint $t): void {
            $t->id();
            // Retain money history when a reader deletes their identity.
            $t->unsignedBigInteger('reader_id')->nullable()->index();
            $t->foreignId('collection_id')->constrained()->restrictOnDelete();
            $t->string('store', 20);
            $t->string('transaction_id');
            $t->string('environment', 16);
            $t->string('product_id');
            $t->timestamp('purchased_at');
            $t->timestamp('created_at');
            $t->unique(['store', 'environment', 'transaction_id']);
        });
        Schema::create('book_entitlements', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('reader_id')->constrained()->cascadeOnDelete();
            $t->foreignId('collection_id')->constrained()->restrictOnDelete();
            $t->boolean('active')->default(false);
            $t->timestamp('checked_at')->nullable();
            $t->timestamps();
            $t->unique(['reader_id', 'collection_id']);
        });
        Schema::create('sales_ledger', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('purchase_id')->constrained()->restrictOnDelete();
            $t->foreignId('event_id')->nullable()->constrained('purchase_events')->restrictOnDelete();
            $t->string('entry_key')->unique();
            $t->string('status', 24);
            $t->string('currency', 3)->nullable();
            $t->decimal('amount', 20, 6)->nullable();
            $t->string('fees_status', 20)->default('unknown');
            $t->string('taxes_status', 20)->default('unknown');
            $t->string('earnings_status', 20)->default('unknown');
            $t->json('agreement_snapshot')->nullable();
            $t->json('estimated_earnings')->nullable();
            $t->decimal('confirmed_owed', 20, 6)->nullable();
            $t->decimal('payments_made', 20, 6)->nullable();
            $t->timestamp('occurred_at');
            $t->timestamp('created_at');
        });
        Schema::create('purchase_consents', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('reader_id')->constrained()->cascadeOnDelete();
            $t->foreignId('collection_id')->constrained()->restrictOnDelete();
            $t->string('wording');
            $t->timestamp('created_at');
        });
    }

    public function down(): void
    {
        // Preserve sandbox as well as real money history (R8).
        if (DB::table('purchases')->exists()) {
            throw new RuntimeException('Payment history exists; rollback requires an approved preservation plan.');
        }
        foreach (['purchase_consents', 'sales_ledger', 'book_entitlements', 'purchases', 'purchase_events', 'author_share_agreements'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('readers', fn (Blueprint $t) => $t->dropColumn(['buying_blocked', 'buying_blocked_by', 'buying_blocked_at']));
        Schema::table('collections', fn (Blueprint $t) => $t->dropUnique(['product_id']));
        DB::table('collections')->update(['product_id' => DB::raw('purchase_original_product_id')]);
        Schema::table('collections', fn (Blueprint $t) => $t->dropColumn(['price_usd', 'purchase_original_product_id']));
    }
};
