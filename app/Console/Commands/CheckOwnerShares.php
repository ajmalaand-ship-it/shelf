<?php

namespace App\Console\Commands;

use App\Models\{Author, AuthorShareAgreement, User};
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckOwnerShares extends Command
{
    protected $signature = 'shelf:check-owner-shares';
    protected $description = 'Read-only verification of all six D10 agreements and their owner admin screens.';
    public function handle(): int
    {
        $owner=User::where('is_owner',true)->sole();
        $author=Author::where('name','اجمل اند')->sole();
        $record=json_decode(DB::table('app_settings')->where('key','owner_d10_20261001')->value('value') ?? '{}',true);
        foreach (range(3,8) as $id) {
            $agreement=AuthorShareAgreement::where('collection_id',$id)->whereIn('id',$record['agreement_ids']??[])->sole();
            if (! $agreement || $agreement->contributors !== [['author_id'=>$author->id,'percentage'=>100]]
                || $agreement->basis!=='net' || $agreement->created_by!==$owner->id
                || $agreement->starts_at->toDateString()!=='2026-10-01') {
                $this->error('FAIL: book '.$id.' recorded D10 agreement.');return self::FAILURE;
            }
            // Later owner edits append versions; the initial D10 record stays valid.
            $current=AuthorShareAgreement::where('collection_id',$id)->where('starts_at','<=',now())
                ->orderByDesc('starts_at')->orderByDesc('id')->first();
            auth()->guard()->setUser($owner);
            $request=Request::create(url('/admin/collections/'.$id.'/edit'),'GET',[],[],[],[
                'HTTP_X_SHELF_TEST_KEY'=>(string)config('staging.access_key'),'HTTP_ACCEPT'=>'text/html',
            ]);
            $response=app(\Illuminate\Contracts\Http\Kernel::class)->handle($request);
            $basis=$current->basis==='net'?'Net amount received':'Gross income';
            if ($response->getStatusCode()!==200 || ! str_contains($response->getContent(),$basis)) {
                $this->error('FAIL: book '.$id.' admin agreement display.');return self::FAILURE;
            }
            app('auth')->forgetGuards();
            foreach ($current->contributors as $contributor) {
                if (! str_contains($response->getContent(),$contributor['percentage'].'%')) {
                    $this->error('FAIL: book '.$id.' admin percentage display.');return self::FAILURE;
                }
            }
            $this->info('PASS: book '.$id.' D10 records 100% to Ajmal, net received, effective 1 October, owner/time; admin shows current agreement.');
        }
        return self::SUCCESS;
    }
}
