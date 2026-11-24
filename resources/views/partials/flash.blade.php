@if (session('status'))
    <div data-toast
         class="fixed end-3 bottom-20 z-[90] flex max-w-sm items-start gap-2 rounded-md border border-olive/30 bg-olive-soft px-4 py-3 text-sm font-semibold text-olive-deep shadow-lg"
         role="status">
        <x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0" />
        <span class="min-w-0 flex-1">{{ session('status') }}</span>
        @if (session('undo'))
            <form method="POST" action="{{ session('undo')['url'] }}" class="shrink-0">
                @csrf
                @method(session('undo')['method'] ?? 'POST')
                <button type="submit" class="underline decoration-olive/40 underline-offset-2 hover:text-charcoal">
                    {{ session('undo')['label'] ?? __('menuqr.common.undo') }}
                </button>
            </form>
        @endif
        <button type="button" class="shrink-0 text-olive-deep/60 hover:text-charcoal" data-toast-close aria-label="{{ __('menuqr.common.close') }}">
            <x-icon name="x" class="h-4 w-4" />
        </button>
    </div>
@endif

@if ($errors->any())
    <div class="flash-error mb-4" role="alert">
        <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0" />
        <div class="space-y-1">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    </div>
@endif
