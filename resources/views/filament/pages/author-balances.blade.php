<x-filament-panels::page>
    <x-filament::section>
        <p>Real transactions only. Test purchases never count as income, owed amounts or payments.</p>
        <p>Estimated earnings are provisional. Confirmed owed amounts require supporting figures. Payments are records of money already paid; Shelf sends no money.</p>
        <p>Each currency is separate. “Unknown” means figures are incomplete. “Unknown” also means no payable balance can be calculated yet. A supported payment already made can still be recorded.</p>
        @if($unassigned)<p role="alert">{{ $unassigned }} real financial entries have no historical agreement. Their rights-holder balance is unknown; review Sales ledger.</p>@endif
        <div class="mt-4 flex flex-wrap gap-4">
            <x-filament::button tag="a" href="{{ \App\Filament\Resources\Accounting\AccountingEntryResource::getUrl('create') }}">Record financial entry</x-filament::button>
            <x-filament::button tag="a" color="gray" href="{{ \App\Filament\Resources\Accounting\AccountingEntryResource::getUrl('index') }}">Accounting journal</x-filament::button>
            <x-filament::button tag="a" color="gray" href="{{ \App\Filament\Resources\Sales\SaleResource::getUrl('index') }}">Sales ledger</x-filament::button>
        </div>
    </x-filament::section>
    @forelse($balances as $row)
        <x-filament::section :heading="$row['book'].' · '.$row['rights_holder'].' · '.$row['currency']">
            <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div><dt>Estimated earnings</dt><dd>{{ $row['estimated'] ?? 'Unknown' }}</dd></div>
                <div><dt>Confirmed owed</dt><dd>{{ $row['confirmed_owed'] ?? 'Unknown' }}</dd></div>
                <div><dt>Payments recorded</dt><dd>{{ $row['paid'] }}</dd></div>
                <div><dt>Payable balance</dt><dd>{{ $row['payable'] ?? 'Unknown — figures incomplete' }}</dd></div>
            </dl>
            @if($row['unknown'])<p class="mt-4">{{ $row['unknown'] }} sale/refund entries still need confirmed figures. Known owed portion: {{ $row['known_owed'] }} {{ $row['currency'] }}; this is not a complete payable balance.</p>@endif
            @if($row['payable'] !== null && str_starts_with($row['payable'],'-'))<p class="mt-4">Payments exceed the corrected amount owed. Review the journal; no repayment or transfer is made automatically.</p>@endif
        </x-filament::section>
    @empty
        <x-filament::section heading="No real author balances yet"><p>Test purchases stay in Sales ledger, labelled Test. No real income or payment is recorded from them.</p></x-filament::section>
    @endforelse
</x-filament-panels::page>
