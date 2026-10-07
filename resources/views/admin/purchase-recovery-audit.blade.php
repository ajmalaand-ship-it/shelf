<div class="space-y-4">
<p>Original sale and agreement records remain unchanged.</p>
@forelse($recoveries as $recovery)
<p>Case {{ $recovery->case_reference }} · Reader {{ $recovery->reader_id }} · Owner {{ $recovery->owner_id }} · {{ $recovery->created_at }} · Audit {{ $recovery->id }}</p>
@empty
<p>No support-assisted recovery recorded for this purchase.</p>
@endforelse
</div>
