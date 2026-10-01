<?php

namespace Tests\Feature;

use App\Models\{Author, AuthorShareAgreement, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OwnerSharesTest extends TestCase
{
    use RefreshDatabase;
    public function test_owner_decision_adds_versions_admin_shows_each_book_and_unused_rollback_preserves_old_agreements(): void
    {
        $owner = User::factory()->state(['is_owner'=>true])->create();
        $author = Author::create(['name'=>'اجمل اند']);
        foreach (range(3,8) as $id) {
            DB::table('collections')->insert(['id'=>$id,'title'=>'Synthetic '.$id,'slug'=>'synthetic-'.$id,'status'=>'draft','product_id'=>'shelf_book_'.$id]);
        }
        $old = AuthorShareAgreement::create(['collection_id'=>3,'contributors'=>[['author_id'=>$author->id,'percentage'=>25]],
            'basis'=>'gross','deductions'=>'none','sharing_terms'=>'Old terms','starts_at'=>'2026-09-01']);
        $migration = require database_path('migrations/2026_10_01_040000_record_owner_book_shares.php');
        $migration->up();
        $this->assertSame(7,DB::table('author_share_agreements')->count());
        $this->assertSame(25,$old->fresh()->contributors[0]['percentage']);
        $this->actingAs($owner);
        $this->travelTo(\Carbon\Carbon::parse('2026-10-01 12:00:00'));
        foreach (range(3,8) as $id) {
            $new = AuthorShareAgreement::where('collection_id',$id)->orderByDesc('id')->firstOrFail();
            $this->assertSame('net',$new->basis);$this->assertSame($owner->id,$new->created_by);
            $this->assertSame([['author_id'=>$author->id,'percentage'=>100]],$new->contributors);
            $this->get('/admin/collections/'.$id.'/edit')->assertOk()->assertSee('100%')->assertSee('Net amount received');
        }
        $this->assertStringContainsString('written permission',AuthorShareAgreement::where('collection_id',6)->first()->sharing_terms);
        $migration->down();
        $this->assertSame(1,DB::table('author_share_agreements')->count());
        $this->assertSame($old->id,AuthorShareAgreement::first()->id);
    }
}
