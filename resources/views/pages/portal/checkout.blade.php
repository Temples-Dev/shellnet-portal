<x-layouts::portal :title="__('Checkout')">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-white">Checkout</h1>
        <p class="mt-1 text-zinc-400">Enter your details to complete your purchase.</p>
    </div>

    <livewire:portal.checkout :plan="$plan" />
</x-layouts::portal>
