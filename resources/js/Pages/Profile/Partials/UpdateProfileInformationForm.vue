<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <section class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        <!-- Encabezado con estilo WMC -->
        <header class="bg-slate-50/80 p-6 border-b border-slate-100">
            <h2 class="text-xl font-black text-slate-800 uppercase tracking-tight">
                Información del Perfil
            </h2>
            <p class="mt-1 text-sm text-slate-500 font-medium">
                Actualiza los datos personales de tu cuenta y tu dirección de correo electrónico.
            </p>
        </header>

        <!-- Formulario -->
        <form @submit.prevent="form.patch(route('profile.update'))" class="p-6 space-y-6">

            <!-- Alerta informativa similar a la de Usuarios -->
            <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-xs text-blue-700 font-medium leading-relaxed">
                    Asegúrate de utilizar una dirección de correo electrónico institucional o válida para recibir notificaciones del congreso.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nombre -->
                <div class="space-y-1">
                    <InputLabel for="name" value="Nombre Completo" class="text-xs font-bold text-slate-700 uppercase ml-1" />
                    <TextInput
                        id="name"
                        type="text"
                        class="w-full bg-slate-50 border-slate-200 rounded-xl py-2.5 px-4 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-sm font-medium"
                        v-model="form.name"
                        required
                        autofocus
                        autocomplete="name"
                        placeholder="Ingresa tu nombre"
                    />
                    <InputError class="mt-2 text-[10px] font-bold" :message="form.errors.name" />
                </div>

                <!-- Correo -->
                <div class="space-y-1">
                    <InputLabel for="email" value="Correo Electrónico" class="text-xs font-bold text-slate-700 uppercase ml-1" />
                    <TextInput
                        id="email"
                        type="email"
                        class="w-full bg-slate-50 border-slate-200 rounded-xl py-2.5 px-4 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-sm font-medium"
                        v-model="form.email"
                        required
                        autocomplete="username"
                        placeholder="correo@ejemplo.com"
                    />
                    <InputError class="mt-2 text-[10px] font-bold" :message="form.errors.email" />
                </div>
            </div>

            <!-- Verificación de Email (Si aplica) -->
            <div v-if="mustVerifyEmail && user.email_verified_at === null" class="bg-yellow-50 border border-yellow-200 rounded-2xl p-4">
                <p class="text-sm text-yellow-800 font-medium">
                    Tu dirección de correo no ha sido verificada.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="block mt-1 underline text-sm text-yellow-900 hover:text-yellow-700 font-bold focus:outline-none transition"
                    >
                        Haz clic aquí para reenviar el correo de verificación.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-3 font-bold text-sm text-green-600 flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    Se ha enviado un nuevo enlace de verificación a tu correo.
                </div>
            </div>

            <!-- Botón Guardar -->
            <div class="flex items-center gap-4 pt-4 border-t border-slate-100">
                <PrimaryButton
                    :disabled="form.processing"
                    class="bg-blue-950 hover:bg-blue-900 text-white px-8 py-2.5 rounded-xl font-bold text-xs uppercase tracking-widest shadow-lg shadow-blue-900/20 transition-all active:scale-95"
                >
                    Guardar Cambios
                </PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out duration-300"
                    enter-from-class="opacity-0 translate-x-4"
                    leave-active-class="transition ease-in-out duration-300"
                    leave-to-class="opacity-0"
                >
                    <p v-if="form.recentlySuccessful" class="text-sm font-bold text-green-600 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        ¡Guardado con éxito!
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
