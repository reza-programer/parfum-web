<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn bg-danger text-white hover:bg-danger/90']) }}>
    {{ $slot }}
</button>
