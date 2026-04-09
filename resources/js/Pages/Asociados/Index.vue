
<script setup>
// 1. Importamos el Layout que tiene el Sidebar
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Asociados - WMC" />

    <AuthenticatedLayout></AuthenticatedLayout>
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-6">Panel de Asociados</h1>
        <p class="text-lg text-gray-700">Bienvenido al panel de Asociados. Aquí puedes gestionar los cupones y otras configuraciones del sistema.</p>
    </div>
</template>
