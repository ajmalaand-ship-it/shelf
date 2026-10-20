<x-filament-panels::page>
    <p>Private owner preview. This does not publish the book or approve store purchases. App layout may differ.</p>
    <div class="flex flex-wrap gap-4">
        <x-filament::button tag="a" :href="$editUrl">Back to book</x-filament::button>
        <x-filament::button tag="a" :href="$fullUrl" color="gray">Full book</x-filament::button>
        <x-filament::button tag="a" :href="$sampleUrl" color="gray">Free sample</x-filament::button>
    </div>
    <x-filament::section :heading="$sampleOnly ? 'Free sample' : 'Full book'">
        <h2 dir="auto">{{ $book->title }}</h2>
        <p dir="auto">{{ $book->subtitle }}</p>
        @foreach ($book->credits as $credit)
            <p dir="auto">{{ $credit->author->name }} · {{ \App\Models\BookCredit::ROLES[$credit->role] }}</p>
        @endforeach
        @if ($book->cover_image)
            <img src="{{ $book->coverUrl(true) }}" alt="Book cover" style="max-width:180px; height:auto" />
        @endif
        <p dir="auto" style="white-space:pre-wrap">{{ $book->description }}</p>
        @unless ($sampleOnly)
            @foreach (['dedication' => 'Dedication', 'introduction' => 'Introduction', 'foreword' => 'Foreword', 'publication_info' => 'Publication information'] as $field => $label)
                @if (filled($book->$field))
                    <h3>{{ $label }}</h3>
                    <p dir="auto" style="white-space:pre-wrap">{{ $book->$field }}</p>
                @endif
            @endforeach
        @endunless
    </x-filament::section>
    @forelse ($items as $item)
        <x-filament::section :heading="$item->admin_display_title">
            @unless ($sampleOnly)
                <p>{{ $item->is_active ? 'Visible item' : 'Hidden item — not shown to readers' }}</p>
            @endunless
            <div dir="auto" style="white-space:pre-wrap; overflow-wrap:anywhere; font-size:18px; line-height:2">{{ $sampleOnly ? $item->sampleText() : $item->body }}</div>
        </x-filament::section>
    @empty
        <p>{{ $sampleOnly ? 'No visible sample selected. Choose a sample in the book’s Content tab.' : 'No content yet. Add or import content in the book’s Content tab.' }}</p>
    @endforelse
    {{ $items->links() }}
</x-filament-panels::page>
