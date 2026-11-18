@props(['attachments'])

@php $items = collect($attachments ?? []); @endphp

@if ($items->isNotEmpty())
    <div {{ $attributes->class(['attachment-gallery']) }}>
        @foreach ($items as $attachment)
            @php
                $url = data_get($attachment, 'url', data_get($attachment, 'public_url'));
                $mime = data_get($attachment, 'mime_type', '');
                $name = data_get($attachment, 'original_name', data_get($attachment, 'name', 'Attachment'));
            @endphp
            @if ($url)
                <a class="attachment-tile" href="{{ $url }}" target="_blank" rel="noopener" aria-label="Open {{ $name }}">
                    @if (str_starts_with($mime, 'image/'))
                        <img src="{{ $url }}" alt="Attachment: {{ $name }}">
                    @else
                        <span class="attachment-file"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M7 3h7l4 4v14H7zM14 3v5h5"/></svg></span>
                    @endif
                    <span class="attachment-name">{{ $name }}</span>
                </a>
            @else
                <div class="attachment-tile" aria-label="Preview attachment {{ $name }}">
                    <span class="attachment-file"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M7 3h7l4 4v14H7zM14 3v5h5"/></svg></span>
                    <span class="attachment-name">{{ $name }}</span>
                </div>
            @endif
        @endforeach
    </div>
@endif
