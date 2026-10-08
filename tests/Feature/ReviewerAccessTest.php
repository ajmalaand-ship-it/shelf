<?php
namespace Tests\Feature;
use App\Models\{Collection,Reader};
use App\Services\Purchases\PurchaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{DB,Http,Storage};
use Tests\TestCase;
class ReviewerAccessTest extends TestCase {
    use RefreshDatabase;
    public function test_verified_reviewer_gets_only_explicit_books_without_sales_or_admin(): void {
        config(['reader_auth.enabled'=>true, 'purchases.enabled'=>false]);
        Http::preventStrayRequests();
        Storage::fake('artwork');
        $r=new Reader;
        $r->forceFill(['email'=>'synthetic-reviewer@example.test','password'=>'Synthetic-Review-Password-123!',
            'email_verified_at'=>now(),'buying_blocked'=>true])->save();
        $other=new Reader;
        $other->forceFill(['email'=>'other@example.test','password'=>'Synthetic-Other-Password-123!','email_verified_at'=>now()])->save();
        $books=[];
        foreach (range(1,6) as $n) {
            $b=Collection::create(['title'=>'Synthetic '.$n,'status'=>'published']);
            $p=$b->poems()->create(['body'=>'Private paid text '.$n,'excerpt'=>'','is_active'=>true,'sample_mode'=>'none','artwork_path'=>'synthetic.png']);
            DB::table('reviewer_book_grants')->insert(['reader_id'=>$r->id,'collection_id'=>$b->id,
                'purpose'=>'Synthetic reviewer grant','authorized_by'=>'Synthetic owner','granted_at'=>now()]);
            $books[]=[$b,$p];
        }
        $ungranted=Collection::create(['title'=>'Ungrant','status'=>'published']);
        Storage::disk('artwork')->put('synthetic.png','synthetic image');
        $login=$this->postJson('/api/auth/login',['email'=>$r->email,'password'=>'Synthetic-Review-Password-123!'])
            ->assertOk()->assertJsonPath('user.email_verified',true);
        $h=['Authorization'=>'Bearer '.$login->json('token')];
        $this->assertFalse($r->is_owner);
        $this->getJson('/api/auth/me',$h)->assertOk()->assertJsonPath('user.id',$r->id);
        $this->getJson('/api/library',$h)->assertOk()->assertJsonCount(6,'books');
        foreach ($books as [$b,$p]) {
            $this->getJson('/api/library/books/'.$b->slug.'/content',$h)->assertOk()->assertJsonPath('data.0.locked',false);
            $this->getJson('/api/library/poems/'.$p->id,$h)->assertOk()->assertJsonPath('data.body',$p->body);
            $this->get('/api/library/poems/'.$p->id.'/media/artwork',$h)->assertOk();
            // Purchase-derived refresh must not remove independent complimentary access.
            app(PurchaseService::class)->refreshAccess($r,$b);
            $this->getJson('/api/library/poems/'.$p->id,$h)->assertOk();
        }
        $this->getJson('/api/library/books/'.$ungranted->slug,$h)->assertNotFound();
        app('auth')->forgetGuards();
        $this->getJson('/api/library',[])->assertUnauthorized();
        $otherToken=$other->createToken('mobile',['reader'])->plainTextToken;
        app('auth')->forgetGuards();
        $oh=['Authorization'=>'Bearer '.$otherToken];
        $this->getJson('/api/library',$oh)->assertOk()->assertJsonCount(0,'books');
        $this->getJson('/api/library/poems/'.$books[0][1]->id,$oh)->assertNotFound();
        app('auth')->forgetGuards();
        DB::table('reviewer_book_grants')->where('reader_id',$r->id)->where('collection_id',$books[0][0]->id)->update(['revoked_at'=>now()]);
        $this->getJson('/api/library/poems/'.$books[0][1]->id,$h)->assertNotFound();
        foreach (['purchases','purchase_events','sales_ledger','purchase_consents'] as $table) { $this->assertSame(0,DB::table($table)->count()); }
        $this->getJson('/api/purchases/config')->assertJsonPath('production_checkout_enabled',false);
        $this->get('/admin',$h)->assertRedirect('/admin/login');
    }
}
