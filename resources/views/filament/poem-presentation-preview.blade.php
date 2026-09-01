@php
    use App\Support\PoetryPresentation;

    $previewBody = is_string($body) ? $body : '';
    $previewMode = is_string($layoutMode) ? $layoutMode : App\Models\Poem::LAYOUT_SOURCE;
    $previewSpacing = $manualSpacing ?? null;
    $blocks = PoetryPresentation::blocks($previewBody, $previewMode, is_array($previewSpacing) ? $previewSpacing : null);
@endphp

<div data-testid="poem-presentation-preview" dir="rtl" style="border: 1px solid rgb(148 163 184 / 35%); border-radius: 0.75rem; padding: 1.25rem; background: rgb(148 163 184 / 6%);">
    <div style="margin-bottom: 1rem; color: rgb(100 116 139); font-size: 0.75rem; text-align: start;">
        د شعر د ښودنې کتنه / Presentation preview
    </div>

    @if (filled($title))
        <div data-testid="preview-title" style="font-family: Vazirmatn, serif; font-size: 1.45rem; line-height: 1.8; font-weight: 700; text-align: center; margin-bottom: 1.25rem;">{{ $title }}</div>
    @else
        <svg data-testid="preview-untitled-marker" role="img" aria-label="This poem has no original title" viewBox="0 0 38 34" style="display: block; height: 2.1rem; width: 2.35rem; margin: 0 auto 1.25rem; opacity: 0.42; fill: none; stroke: currentColor; stroke-width: 1.2; stroke-linecap: round; stroke-linejoin: round;">
            <path d="M5 3h20l6 6v21H5z M25 3v6h6 M10 13h13 M10 17h11 M10 21h8" />
            <path d="M16 29Q25 17 36 7Q35 16 27 21Q22 25 16 29 M18 27L34 9 M28 15h5 M24 20h5" />
        </svg>
    @endif

    <div data-testid="preview-body" style="font-family: Vazirmatn, serif; font-size: 1rem; line-height: 2.2; white-space: pre-wrap; overflow-wrap: anywhere; text-align: start;">
        @foreach ($blocks as $block)
            @if ($block['text'] !== '')<div style="white-space: pre-wrap;">{{ $block['text'] }}</div>@endif
            @if ($block['gap_after_em'] > 0)<div data-gap-em="{{ $block['gap_after_em'] }}" aria-hidden="true" style="height: {{ $block['gap_after_em'] }}em;"></div>@endif
        @endforeach
    </div>

    @if (filled($datePlace))
        <div data-testid="preview-date-place" style="font-family: Vazirmatn, serif; font-size: 0.8rem; line-height: 1.5; opacity: 0.62; text-align: center; margin-top: 1.5rem;">{{ $datePlace }}</div>
    @endif
</div>
