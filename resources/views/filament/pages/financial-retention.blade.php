<x-filament-panels::page>
    <x-filament::section heading="Financial retention — report only">
        <p>Keep accounting and tax evidence for three years after every relevant return was actually filed. Legal requirements, disputes and unpaid author balances may hold affected evidence longer.</p>
        <p>Automatic expiry and deletion are OFF. This page records evidence and reports eligibility for preservation review only. It cannot delete records or change ownership. Minimum proof for continuing purchased-book recovery remains protected.</p>
        <p>Missing filings, incomplete mappings and unknown balances block review eligibility. Test history is excluded from real income and remains retained. Enter references only; keep personal information and store tokens out of this journal.</p>
        <p><strong>{{ $eligible }} eligible for preservation review · {{ $blocked }} blocked / retained</strong></p>
    </x-filament::section>
    <x-filament::section heading="Purchase groups">
        @forelse($rows as $row)
            <div class="py-4 border-b" style="overflow-wrap:anywhere">
                <h3><strong>Purchase #{{ $row['purchase_id'] }} · {{ $row['mode'] }} · {{ $row['status'] }}</strong></h3>
                <p>Ledger IDs: {{ implode(', ', $row['ledger_ids']) ?: 'None' }} · Accounting IDs: {{ implode(', ', $row['accounting_ids']) ?: 'None' }}</p>
                <p>Share IDs: {{ implode(', ', $row['share_ids']) ?: 'None' }} · Shared agreement IDs: {{ implode(', ', $row['agreement_ids']) ?: 'None' }} · Provider event evidence: {{ $row['provider_event_count'] }} · Recovery audits: {{ $row['recovery_audit_count'] }}</p>
                <p>Mapping: {{ $row['mapping_id'] ?? 'Missing' }} · Return IDs: {{ implode(', ', $row['return_ids']) ?: 'Missing' }} · Earliest review date (UTC): {{ $row['review_after'] ?? 'Unknown' }}</p>
                @foreach($row['reasons'] as $reason)<p>{{ $reason }}</p>@endforeach
                <p>{{ $row['recovery'] }}</p>
            </div>
        @empty
            <p>No purchase groups yet. Record filing evidence when available. No dates or mappings have been invented.</p>
        @endforelse
    </x-filament::section>
    <x-filament::section heading="Evidence history">
        <p>Corrections use new entries. A new mapping replaces the prior mapping for reporting only. To correct a filing, record its evidenced replacement and remap every affected purchase. New corrections or returns require mapping review.</p>
        @forelse($metadata as $record)
            <div class="py-4 border-b" style="overflow-wrap:anywhere">
                <strong>#{{ $record->id }} · {{ $record->kind }} · Owner #{{ $record->created_by }} · {{ $record->created_at }}</strong>
                @foreach($record->evidence as $key => $value)
                    <p>{{ str_replace('_', ' ', $key) }}: {{ is_array($value) ? json_encode($value) : (is_bool($value) ? ($value ? 'Confirmed' : 'No') : $value) }}</p>
                @endforeach
            </div>
        @empty
            <p>No retention evidence recorded. All existing financial and recovery records remain retained.</p>
        @endforelse
    </x-filament::section>
</x-filament-panels::page>
