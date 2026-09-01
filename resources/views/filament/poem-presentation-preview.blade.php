@php
    use App\Support\PoetryPresentation;

    $previewBody = is_string($body) ? $body : '';
    $previewMode = is_string($layoutMode) ? $layoutMode : App\Models\Poem::LAYOUT_SOURCE;
    $blocks = PoetryPresentation::blocks($previewBody, $previewMode);
@endphp

<div data-testid="poem-presentation-preview" dir="rtl" style="border: 1px solid rgb(148 163 184 / 35%); border-radius: 0.75rem; padding: 1.25rem; background: rgb(148 163 184 / 6%);">
    <div style="margin-bottom: 1rem; color: rgb(100 116 139); font-size: 0.75rem; text-align: start;">
        د شعر د ښودنې کتنه / Presentation preview
    </div>

    @if (filled($title))
        <div data-testid="preview-title" style="font-family: Vazirmatn, serif; font-size: 1.45rem; line-height: 1.8; font-weight: 700; text-align: center; margin-bottom: 1.25rem;">{{ $title }}</div>
    @else
        <div data-testid="preview-untitled-marker" role="img" aria-label="This poem has no original title" style="height: 1.25rem; width: 2.75rem; margin: 0 auto 1.25rem; border-bottom: 1px solid currentColor; border-radius: 50%; opacity: 0.35;"></div>
    @endif

    <div data-testid="preview-body" style="font-family: Vazirmatn, serif; font-size: 1rem; line-height: 2.2; white-space: pre-wrap; overflow-wrap: anywhere; text-align: start;">
        @foreach ($blocks as $block)
            @if ($block['text'] !== '')<div style="white-space: pre-wrap;">{{ $block['text'] }}</div>@endif
            @if ($block['gap_after_lines'] > 0)<div aria-hidden="true" style="height: {{ $block['gap_after_lines'] * 2.2 }}em;"></div>@endif
        @endforeach
    </div>

    @if (filled($datePlace))
        <div data-testid="preview-date-place" style="font-family: Vazirmatn, serif; font-size: 0.8rem; line-height: 1.5; opacity: 0.62; text-align: center; margin-top: 1.5rem;">{{ $datePlace }}</div>
    @endif
</div>
