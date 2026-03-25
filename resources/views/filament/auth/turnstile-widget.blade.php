<div x-data wire:ignore>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>

    <div class="mt-2">
        <div
            class="cf-turnstile"
            data-sitekey="{{ config('services.turnstile.site_key') }}"
            data-callback="filamentTurnstileCallback"
            data-expired-callback="filamentTurnstileExpired"
            data-theme="dark"
        ></div>
    </div>

    @error('turnstileResponse')
        <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
    @enderror
</div>

<script>
    function filamentTurnstileCallback(token) {
        var component = Livewire.find(
            document.querySelector('[wire\\:id]').getAttribute('wire:id')
        );
        component.set('turnstileResponse', token);
    }

    function filamentTurnstileExpired() {
        var component = Livewire.find(
            document.querySelector('[wire\\:id]').getAttribute('wire:id')
        );
        component.set('turnstileResponse', '');
    }
</script>
