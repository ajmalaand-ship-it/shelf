<div class="space-y-4">
    @if ($preview)
        <p><strong>{{ count($preview['items']) }} {{ $label === 'Chapter' ? 'chapters' : 'poems' }}</strong> from {{ $preview['filename'] }}</p>
        <p>Import adds drafts at the end of this book. Existing content is unchanged. Paragraph boundaries and manual line breaks are kept as newlines. No words or Unicode characters are corrected or normalized.</p>
        <p>No public excerpt or free sample is selected automatically. Review each draft before publishing.</p>
        @if ($preview['warnings'])
            <div role="alert">
                <strong>Warnings — content marked NOT imported will be left out</strong>
                <ul class="list-disc ps-6">
                    @foreach ($preview['warnings'] as $warning)
                        <li>{{ $warning }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="space-y-4" style="max-height: 55vh; overflow-y: auto">
            @foreach ($preview['items'] as $item)
                <section class="rounded-lg border p-4">
                    <h3><strong>{{ $label }} {{ $loop->iteration }}:</strong> <span dir="auto" style="white-space: pre-wrap">{{ $item['title'] === null ? 'Untitled' : $item['title'] }}</span></h3>
                    <p>{{ $item['word_count'] }} words in text</p>
                    <p>First 2 lines:</p>
                    <pre dir="auto" style="white-space: pre-wrap; font: inherit">{{ $item['first_lines'] }}</pre>
                </section>
            @endforeach
        </div>
        <p>Cancel leaves the book unchanged. The original Word file is archived privately only when you click Import.</p>
    @endif
</div>
