<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    });
};
</script>

<template>
    <section class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        <!-- Encabezado con estilo WMC -->
        <header class="bg-slate-50/80 p-6 border-b border-slate-100">
            <h2 class="text-xl font-black text-slate-800 uppercase tracking-tight">
                Actualizar Contraseña
            </h2>
            <p class="mt-1 text-sm text-slate-500 font-medium">
                Asegúrate de que tu cuenta utilice una contraseña larga y aleatoria para mantener la seguridad.
            </p>
        </header>

        <form @submit.prevent="updatePassword" class="p-6 space-y-6">

            <!-- Alerta de Seguridad -->
            <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-4 flex items-start gap-3">
                <svg class="w-5 h-5 text-indigo-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                <p class="text-xs text-indigo-700 font-medium leading-relaxed">
                    La nueva contraseña debe ser diferente a la anterior y contener al menos 8 caracteres para cumplir con los estándares de seguridad del congreso.
                </p>
            </div>

            <div class="space-y-4">
                <!-- Contraseña Actual -->
                <div class="max-w-md">
                    <InputLabel for="current_password" value="Contraseña Actual" class="text-xs font-bold text-slate-700 uppercase ml-1 mb-1" />
                    <TextInput
                        id="current_password"
                        ref="currentPasswordInput"
                        v-model="form.current_password"
                        type="password"
                        class="w-full bg-slate-50 border-slate-200 rounded-xl py-2.5 px-4 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-sm font-medium"
                        autocomplete="current-password"
                        placeholder="••••••••"
                    />
                    <InputError :message="form.errors.current_password" class="mt-2 text-[10px] font-bold" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Nueva Contraseña -->
                    <div>
                        <InputLabel for="password" value="Nueva Contraseña" class="text-xs font-bold text-slate-700 uppercase ml-1 mb-1" />
                        <TextInput
                            id="password"
                            ref="passwordInput"
                            v-model="form.password"
                            type="password"
                            class="w-full bg-slate-50 border-slate-200 rounded-xl py-2.5 px-4 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-sm font-medium"
                            autocomplete="new-password"
                            placeholder="Mínimo 8 caracteres"
                        />
                        <InputError :message="form.errors.password" class="mt-2 text-[10px] font-bold" />
                    </div>

                    <!-- Confirmar Contraseña -->
                    <div>
                        <InputLabel for="password_confirmation" value="Confirmar Nueva Contraseña" class="text-xs font-bold text-slate-700 uppercase ml-1 mb-1" />
                        <TextInput
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            class="w-full bg-slate-50 border-slate-200 rounded-xl py-2.5 px-4 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-sm font-medium"
                            autocomplete="new-password"
                            placeholder="Repite la contraseña"
                        />
                        <InputError :message="form.errors.password_confirmation" class="mt-2 text-[10px] font-bold" />
                    </div>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="flex items-center gap-4 pt-4 border-t border-slate-100">
                <PrimaryButton
                    :disabled="form.processing"
                    class="bg-blue-950 hover:bg-blue-900 text-white px-8 py-2.5 rounded-xl font-bold text-xs uppercase tracking-widest shadow-lg shadow-blue-900/20 transition-all active:scale-95"
                >
                    Actualizar Contraseña
                </PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out duration-300"
                    enter-from-class="opacity-0 translate-x-4"
                    leave-active-class="transition ease-in-out duration-300"
                    leave-to-class="opacity-0"
                >
                    <p v-if="form.recentlySuccessful" class="text-sm font-bold text-green-600 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        Cambio realizado con éxito.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
