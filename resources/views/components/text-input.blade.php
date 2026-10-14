@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-lg border-line bg-surface-2 text-ink placeholder:text-muted focus:border-accent focus:ring-accent disabled:opacity-50']) }}>
