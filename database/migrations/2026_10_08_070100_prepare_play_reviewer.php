<?php
use App\Models\Reader;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
    public function up(): void {
        if (app()->environment('testing')) { return; }
        $path = \App\Support\Staging::active()
            ? '/home/shelf/secrets/staging/play-reviewer.json'
            : '/home/shelf/secrets/play-reviewer.json';
        if (!is_file($path)) { throw new RuntimeException('Private reviewer credentials are required.'); }
        $data = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        if (($data['email'] ?? '') !== 'play-reviewer@shelf.services'
            || strlen($data['password'] ?? '') < 20) { throw new RuntimeException('Invalid reviewer credential configuration.'); }
        DB::transaction(function () use ($data): void {
            // Never adopt or modify a pre-existing ordinary customer identity.
            if (Reader::where('email', $data['email'])->exists()) {
                throw new RuntimeException('Reviewer email already exists; inspect before reusing.');
            }
            $books = DB::table('collections')->whereBetween('id', [3,8])->where('status','published')->count();
            if ($books !== 6) { throw new RuntimeException('Expected six published launch books.'); }
            $r = new Reader;
            $r->forceFill(['email'=>$data['email'], 'name'=>'Google Play Reviewer',
                'password'=>$data['password'], 'sign_in_method'=>'email',
                'email_verified_at'=>now(), 'buying_blocked'=>true])->save();
            foreach (range(3,8) as $id) {
                DB::table('reviewer_book_grants')->insert(['reader_id'=>$r->id,
                    'collection_id'=>$id, 'purpose'=>'Google Play review: complimentary, no sale',
                    'authorized_by'=>'Ajmal Aand — owner task 8 October 2026', 'granted_at'=>now()]);
            }
        });
    }
    public function down(): void {
        if (app()->environment('testing')) { return; }
        DB::transaction(function (): void {
            $r=Reader::where('email','play-reviewer@shelf.services')->first();
            if (!$r) { return; }
            if ($r->name !== 'Google Play Reviewer' || $r->purchases()->exists()
                || DB::table('reviewer_book_grants')->where('reader_id',$r->id)->count() !== 6) {
                throw new RuntimeException('Reviewer rollback requires a preservation review.');
            }
            $r->tokens()->delete();
            $r->delete();
        });
    }
};
