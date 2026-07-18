<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-gradient-to-b from-brand-950 to-brand-800 pt-6 sm:justify-center sm:pt-0"
    >
        <div>
            <Link href="/">
                <img src="/logo.svg" alt="IBIG FactPro" class="h-16 w-auto rounded-lg bg-white/95 p-2 shadow" />
            </Link>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-white px-6 py-6 shadow-xl sm:max-w-md sm:rounded-xl"
        >
            <slot />
        </div>

        <p class="mt-6 pb-6 text-center text-xs text-white/50">
            © {{ new Date().getFullYear() }} IBIG SARL — factpro.ibigsoft.com
        </p>
    </div>
</template>
