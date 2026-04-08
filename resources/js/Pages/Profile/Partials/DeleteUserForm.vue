<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.reset();
};
</script>

<template>
    <section class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        <!-- Encabezado Estilo WMC -->
        <header class="bg-red-50/50 p-6 border-b border-red-100">
            <h2 class="text-xl font-black text-red-800 uppercase tracking-tight">
                Eliminar Cuenta
            </h2>
            <p class="mt-1 text-sm text-red-600 font-medium">
                Una vez que tu cuenta sea eliminada, todos sus recursos y datos se borrarán de forma permanente.
            </p>
        </header>

        <div class="p-6 space-y-6">
            <!-- Alerta de Advertencia -->
            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 flex items-start gap-3">
                <svg class="w-5 h-5 text-slate-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-xs text-slate-600 font-medium leading-relaxed">
                    Antes de proceder, asegúrate de haber descargado cualquier información o datos que desees conservar del congreso. Esta acción no se puede deshacer.
                </p>
            </div>

            <div class="flex justify-start">
                <DangerButton
                    @click="confirmUserDeletion"
                    class="bg-red-600 hover:bg-red-700 text-white px-8 py-2.5 rounded-xl font-bold text-xs uppercase tracking-widest shadow-lg shadow-red-900/20 transition-all active:scale-95"
                >
                    Eliminar Mi Cuenta
                </DangerButton>
            </div>
        </div>

        <!-- Modal de Confirmación -->
        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-8 bg-white rounded-3xl">
                <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>

                <h2 class="text-2xl font-black text-slate-800 uppercase tracking-tight">
                    ¿Estás seguro de eliminar tu cuenta?
                </h2>

                <p class="mt-3 text-sm text-slate-500 font-medium leading-relaxed">
                    Por favor, introduce tu contraseña para confirmar que deseas eliminar permanentemente tu cuenta de usuario en el sistema.
                </p>

                <div class="mt-6">
                    <InputLabel for="password" value="Contraseña" class="sr-only" />

                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-full bg-slate-50 border-slate-200 rounded-xl py-3 px-4 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 outline-none transition-all font-medium"
                        placeholder="Escribe tu contraseña aquí"
                        @keyup.enter="deleteUser"
                    />

                    <InputError :message="form.errors.password" class="mt-2 text-[10px] font-bold" />
                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <SecondaryButton
                        @click="closeModal"
                        class="px-6 py-2.5 rounded-xl font-bold text-xs uppercase tracking-widest border-slate-200 text-slate-600 hover:bg-slate-50"
                    >
                        Cancelar
                    </SecondaryButton>

                    <DangerButton
                        class="px-6 py-2.5 rounded-xl font-bold text-xs uppercase tracking-widest bg-red-600 shadow-lg shadow-red-900/20 active:scale-95"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        Confirmar Eliminación
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </section>
</template>
