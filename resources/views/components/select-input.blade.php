<select {{ $attributes->merge(['class' => 'rounded-lg border-line bg-surface-2 text-ink focus:border-accent focus:ring-accent']) }}>
    {{ $slot }}
</select>
