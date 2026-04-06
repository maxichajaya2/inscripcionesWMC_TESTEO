<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
// Importamos tus estilos globales donde está la clase bg-gradient-wmc
import "../../../css/inscripciones.css";

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

// Agregamos la variable para controlar la visibilidad de la contraseña
const showPassword = ref(false);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>

    <Head title="Iniciar Sesión | WMC 2026" />

    <div class="min-h-screen bg-gradient-wmc flex items-center justify-center p-4 relative overflow-hidden font-sans">

        <div
            class="w-full max-w-[420px] bg-white rounded-[2.5rem] shadow-[0_20px_50px_rgba(0,0,0,0.3)] relative z-10 overflow-hidden border border-white/20">

            <div class="h-2 w-full bg-gradient-to-r from-blue-700 via-blue-500 to-blue-700"></div>

            <div class="p-8 sm:p-10">

                <div class="text-center mb-8">
                    <a href="https://wmc2026.org/" rel="noopener noreferrer"
                        class="inline-block mb-5 transition-transform hover:scale-105">
                        <img src="/images/logo-wmc.png" alt="World Mining Congress 2026"
                            class="h-16 w-auto cursor-pointer drop-shadow-md" />
                    </a>

                    <h2 class="text-xl font-black text-slate-800 tracking-tight">Administrador</h2>
                    <p class="text-sm font-bold text-slate-400 tracking-widest">Ingrese sus credenciales</p>
                </div>

                <div v-if="status"
                    class="mb-6 p-4 rounded-xl bg-green-50 border border-green-100 text-sm font-bold text-green-600 text-center">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-4">

                    <div>
                        <label
                            class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5 ml-1">Correo
                            Electrónico</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </span>
                            <input id="email" type="email" v-model="form.email" required autofocus
                                autocomplete="username" placeholder="admin@wmc2026.com"
                                class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-sm font-medium text-slate-700 placeholder-slate-400" />
                        </div>
                        <div v-if="form.errors.email" class="text-red-500 text-[11px] font-bold mt-1.5 ml-1">{{
                            form.errors.email }}</div>
                    </div>

                    <div>
                        <label
                            class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5 ml-1">Contraseña</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 pointer-events-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </span>

                            <input id="password" :type="showPassword ? 'text' : 'password'" v-model="form.password" required
                                autocomplete="current-password" placeholder="••••••••"
                                class="w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-sm font-medium text-slate-700 placeholder-slate-400" />

                            <button type="button" @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-blue-600 transition-colors focus:outline-none">
                                <svg v-if="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                        <div v-if="form.errors.password" class="text-red-500 text-[11px] font-bold mt-1.5 ml-1">{{
                            form.errors.password }}</div>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center gap-2 cursor-pointer group">
                            <input type="checkbox" v-model="form.remember"
                                class="w-3.5 h-3.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 transition-colors" />
                            <span
                                class="text-[11px] font-bold text-slate-500 group-hover:text-slate-700 transition-colors">Recordarme</span>
                        </label>

                        <Link v-if="canResetPassword" :href="route('password.request')"
                            class="text-[11px] font-bold text-blue-600 hover:text-blue-800 transition-colors">
                            ¿Olvidaste tu contraseña?
                        </Link>
                    </div>

                    <div class="pt-3">
                        <button type="submit" :disabled="form.processing"
                            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-black rounded-xl text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500/50 transition-all active:scale-95 shadow-md shadow-blue-200 overflow-hidden disabled:opacity-70">

                            <div
                                class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-yellow-300/20 to-transparent -translate-x-full group-hover:animate-[shimmer_1.5s_infinite]">
                            </div>

                            <span v-if="!form.processing">INGRESAR AL PANEL</span>
                            <span v-else class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                VALIDANDO...
                            </span>
                        </button>
                    </div>
                </form>

            </div>

            <div class="bg-slate-50 px-8 py-4 border-t border-slate-100 text-center">
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">
                    Sistema Asegurado &copy; {{ new Date().getFullYear() }}
                </p>
            </div>
        </div>
    </div>
</template>

<style>
@keyframes shimmer {
    100% {
        transform: translateX(100%);
    }
}
</style>
