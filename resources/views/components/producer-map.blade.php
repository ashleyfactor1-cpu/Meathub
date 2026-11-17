@php($mapEmbedUrl = $producer->mapEmbedSource())

@if ($producer->address)
    <p class="mt-3 text-sm text-[#514740]">
        <span class="font-semibold">Shop / Pick-up:</span> {{ $producer->address }}
    </p>
@endif

@if ($mapEmbedUrl)
    <div class="mt-4 w-full overflow-hidden rounded-lg border shadow-sm">
        <iframe
            src="{{ $mapEmbedUrl }}"
            title="Google Maps location for {{ $producer->name }}"
            class="h-64 w-full"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            allowfullscreen
        ></iframe>
    </div>
@endif
