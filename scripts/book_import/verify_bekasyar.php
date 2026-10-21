<?php

// Read-only DB/owner-preview probes; writes only private evidence under /home/shelf/tmp.
use App\Models\{Collection, User};
use App\Support\WordImport\BekasyarImport;
use Illuminate\Support\Facades\{DB, Schema, Storage};

require $argv[1].'/vendor/autoload.php';
$app = require $argv[1].'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['session.driver'=>'array', 'cache.default'=>'array']);
$root = '/home/shelf/tmp/bekasyar-import-20261020/';
$manifest = $root.'final/manifest.json';
$hash = '5afdd6c4292cf73cac9a341b199e7129aa68c734e85bac26b120dc3be0344e6f';
$mode = $argv[2]; $label = $argv[3];
if (! in_array($label,['staging','production'],true)) { throw new RuntimeException('Invalid evidence label.'); }
$beforePath = $root.$label.'-preservation-before.json';
$tables = ['authors','categories','users','readers','reader_account_actions','personal_access_tokens','purchases','purchase_events','sales_ledger','book_entitlements','purchase_consents','author_share_agreements','accounting_entries','accounting_shares','collections','poems','collection_author','category_collection','app_settings','word_imports','play_product_syncs','book_price_changes','book_status_changes'];
$ids = array_column(json_decode(file_get_contents($manifest),true)['documents'],'id');
$snapshot=[];
foreach ($tables as $table) {
    if (! Schema::hasTable($table)) { continue; }
    $query=DB::table($table);
    if ($table==='collections') { $query->where('id','!=',13); }
    if (in_array($table,['poems','collection_author','category_collection','play_product_syncs','book_price_changes','book_status_changes'],true)) { $query->where('collection_id','!=',13); }
    if ($table==='app_settings') { $query->where('key','!=','content_version'); }
    if ($table==='word_imports') { $query->whereNotIn('id',$ids); }
    $rows=$query->get()->map(fn($row)=>json_encode($row,JSON_THROW_ON_ERROR))->all();
    sort($rows,SORT_STRING);
    $snapshot[$table]=['count'=>count($rows),'sha256'=>hash('sha256',json_encode($rows,JSON_THROW_ON_ERROR))];
}
$cover = Storage::disk('covers')->path(Collection::findOrFail(13)->cover_image);
$snapshot['book13_cover_sha256']=hash_file('sha256',$cover);
if ($mode==='checkpoint') {
    if (file_exists($beforePath)) { throw new RuntimeException('Preservation checkpoint already exists.'); }
    file_put_contents($beforePath,json_encode($snapshot,JSON_PRETTY_PRINT));chmod($beforePath,0600);
    echo "PASS: {$label} other-book/account/purchase/settings/cover fingerprints captured read-only.\n";exit(0);
}
if ($mode!=='verify' || json_decode(file_get_contents($beforePath),true)!=$snapshot) { throw new RuntimeException('Protected records changed; stop and inspect.'); }
$service=app(BekasyarImport::class);
$m=$service->load($manifest,'/home/shelf/tmp/Shelf_Word_Files_Converted_DOCX.zip',$hash);
if (! $service->inspect($m)) { throw new RuntimeException('183-poem import is absent.'); }
$poems=Collection::findOrFail(13)->poems()->get();
$expected=array_merge(...array_column($m['documents'],'items'));
if ($poems->count()!==183 || $poems->whereNull('title')->count()!==172) { throw new RuntimeException('Wrong count/title state.'); }
foreach ($poems as $index=>$poem) {
    if ($poem->body!==$expected[$index]['body'] || $poem->sort_order!==7+$index) { throw new RuntimeException('Body/order mismatch.'); }
}
$kernel=$app->make(Illuminate\Contracts\Http\Kernel::class);
$probe=function(string $path,bool $owner=false) use($kernel) {
    app('auth')->forgetGuards();
    if($owner) { auth()->guard()->setUser(User::where('is_owner',true)->sole()); }
    $request=Illuminate\Http\Request::create(url($path),'GET',[],[],[],['HTTP_ACCEPT'=>'text/html','HTTP_X_SHELF_TEST_KEY'=>(string)config('staging.access_key')]);
    return $kernel->handle($request);
};
$guest=$probe('/admin/collections/13/preview');
if (! in_array($guest->getStatusCode(),[302,401,403],true) || str_contains($guest->getContent(),$expected[0]['body'])) { throw new RuntimeException('Preview guest protection failed.'); }
for ($page=1;$page<=19;$page++) {
    $response=$probe('/admin/collections/13/preview?page='.$page,true);
    if ($response->getStatusCode()!==200 || ! str_contains($response->headers->get('Cache-Control',''),'no-store')) { throw new RuntimeException('Private Admin preview failed on page '.$page); }
    $html=$response->getContent();$position=-1;
    foreach(array_slice($expected,($page-1)*10,10) as $item) {
        $found=strpos($html,e($item['body']));
        if($found===false || $found<=$position) { throw new RuntimeException('Exact preview body/order missing on page '.$page); }
        $position=$found;
    }
}
app('auth')->forgetGuards();
$private=['environment'=>$label,'count'=>183,'untitled'=>172,'source_order_exact'=>true,'all_bodies_exact'=>true,'admin_pages'=>19,'guest_denied'=>true,'protected_fingerprints_unchanged'=>true,'status'=>'draft','manifest_sha256'=>$hash,'checked_at'=>gmdate('c'),'poem_ids'=>$poems->pluck('id')->all()];
file_put_contents($root.$label.'-import-verification.json',json_encode($private,JSON_PRETTY_PRINT));chmod($root.$label.'-import-verification.json',0600);
echo "PASS: {$label} 183 exact ordered bodies; 172 untitled; 19 private Admin preview pages; guest denied; original bin/metadata/cover and all other protected records unchanged.\n";
