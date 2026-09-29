<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fail before DDL if the approved single-owner assumption has changed.
        if (DB::table('users')->count() > 1) {
            throw new RuntimeException('Expected one existing owner. Review user ownership before migrating.');
        }
        Schema::table('collections', function (Blueprint $table): void {
            $table->string('status')->default('draft')->index();
            $table->unsignedBigInteger('status_changed_by')->nullable();
            $table->timestamp('status_changed_at')->nullable();
        });
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('is_owner')->default(false);
            $table->text('app_authentication_secret')->nullable();
            $table->text('app_authentication_recovery_codes')->nullable();
        });
        Schema::create('book_status_changes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('collection_id')->index();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->timestamp('changed_at');
        });
        DB::transaction(function (): void {
            $owner = DB::table('users')->value('id');
            if ($owner !== null) {
                DB::table('users')->where('id', $owner)->update(['is_owner' => true]);
            }
            // Every existing book becomes Draft; keep legacy is_active untouched for rollback.
            foreach (DB::table('collections')->get(['id', 'is_active']) as $book) {
                DB::table('collections')->where('id', $book->id)->update([
                    'status' => 'draft', 'status_changed_by' => $owner, 'status_changed_at' => now(),
                ]);
                DB::table('book_status_changes')->insert([
                    'collection_id' => $book->id, 'from_status' => $book->is_active ? 'legacy_active' : 'legacy_inactive',
                    'to_status' => 'draft', 'changed_by' => $owner, 'changed_at' => now(),
                ]);
            }
            DB::table('app_settings')->where('key', 'content_version')->increment('value');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_status_changes');
        Schema::table('collections', fn (Blueprint $table) => $table->dropIndex(['status']));
        Schema::table('collections', fn (Blueprint $table) => $table->dropColumn(['status', 'status_changed_by', 'status_changed_at']));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['is_owner', 'app_authentication_secret', 'app_authentication_recovery_codes']));
        DB::table('app_settings')->where('key', 'content_version')->increment('value');
    }
};
