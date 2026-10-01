<?php

namespace App\Console\Commands;

use App\Models\{Author, AuthorShareAgreement, User};
use Illuminate\Console\Command;
use Illuminate\Http\Request;

class CheckOwnerShares extends Command
{
    protected $signature = 'shelf:check-owner-shares';
    protected $description = 'Read-only verification of all six D10 agreements and their owner admin screens.';
    public function handle(): int
    {
        $owner=User::where('is_owner',true)->sole();
        $author=Author::where('name','اجمل اند')->sole();
        foreach (range(3,8) as $id) {
            $agreement=AuthorShareAgreement::where('collection_id',$id)->where('starts_at','<=',now())
                ->orderByDesc('starts_at')->orderByDesc('id')->first();
            if (! $agreement || $agreement->contributors !== [['author_id'=>$author->id,'percentage'=>100]]
                || $agreement->basis!=='net' || $agreement->created_by!==$owner->id
                || $agreement->starts_at->toDateString()!=='2026-10-01') {
                $this->error('FAIL: book '.$id.' current agreement.');return self::FAILURE;
            }
            auth()->guard()->setUser($owner);
            $request=Request::create(url('/admin/collections/'.$id.'/edit'),'GET',[],[],[],[
                'HTTP_X_SHELF_TEST_KEY'=>(string)config('staging.access_key'),'HTTP_ACCEPT'=>'text/html',
            ]);
            $response=app(\Illuminate\Contracts\Http\Kernel::class)->handle($request);
            if ($response->getStatusCode()!==200 || ! str_contains($response->getContent(),'100%')
                || ! str_contains($response->getContent(),'Net amount received')) {
                $this->error('FAIL: book '.$id.' admin agreement display.');return self::FAILURE;
            }
            app('auth')->forgetGuards();
            $this->info('PASS: book '.$id.' admin shows 100% to Ajmal, net received, effective 1 October, owner/time recorded.');
        }
        return self::SUCCESS;
    }
}
