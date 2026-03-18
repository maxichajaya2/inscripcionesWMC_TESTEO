<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from 'primevue/button';
import { router } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import '../../../css/inscripciones.css';

const props = defineProps({
    pago: Object // Recibe el registro de la tabla 'niubiz'
})

const retry = () => {
    // Intenta regresar al formulario anterior o al inicio
    if (window.history.length > 1) {
        window.history.back();
    } else {
        router.visit(route('inscripcion.index'));
    }
};

const goStart = () => router.get(route('inscripcion.index'));

onMounted(() => {
    if (window.fbq) {
        window.fbq('track', 'Error de pago'); // O 'Purchase' si ya hubo pago
    }
});

</script>

<template>
    <AppLayout title="Payment Error" class="bg-gradient-wmc">
        <form id="FormPaymentError" @submit.prevent>
            <div class="min-h-screen flex flex-col items-center py-12 px-4 ">
                <div class="w-full max-w-2xl shadow-2xl rounded-2xl overflow-hidden border-t-8 border-red-600 bg-white">

                    <div class="p-8 text-center bg-red-50 border-b border-red-100">
                        <div
                            class="inline-flex items-center justify-center w-20 h-20 bg-red-100 text-red-600 rounded-full mb-4">
                            <i class="pi pi-times-circle text-5xl"></i>
                        </div>
                        <h2 class="text-2xl font-black text-red-900 uppercase">Payment Declined</h2>
                        <p class="text-red-700 font-medium">We could not process your transaction.</p>
                    </div>

                    <div class="p-8 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-red-500 uppercase tracking-widest mb-1">Order
                                    Number</span>
                                <span class="text-lg text-gray-800 font-mono font-bold">{{ pago.num_orden }}</span>
                            </div>

                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-red-500 uppercase tracking-widest mb-1">Action
                                    Code</span>
                                <span class="text-lg text-gray-800 font-mono font-bold">{{ pago.estado }}</span>
                            </div>

                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-red-500 uppercase tracking-widest mb-1">Date and
                                    Time</span>
                                <span class="text-lg text-gray-800 font-semibold">{{ pago.fecha }} {{ pago.hora
                                    }}</span>
                            </div>

                            <div class="flex flex-col">
                                <span
                                    class="text-xs font-bold text-red-500 uppercase tracking-widest mb-1">Amount</span>
                                <span class="text-lg text-gray-800 font-bold">USD {{ pago.monto }}</span>
                            </div>
                        </div>

                        <div class="p-5 bg-red-50 rounded-xl border-l-4 border-red-500">
                            <span class="text-xs text-red-500 uppercase font-bold tracking-tighter">Response
                                Message:</span>
                            <p class="text-red-900 font-bold text-lg mt-1 leading-tight">
                                {{ pago.detalle }}
                            </p>
                        </div>

                        <p class="text-sm text-gray-500 italic text-center border-t pt-4">
                            Please check your card balance, expiration date, or contact your bank to authorize online
                            payments.
                        </p>
                    </div>

                    <div class="p-8 bg-gray-50 flex flex-col sm:flex-row justify-center gap-4 border-t">
                        <Button label="Try with another card" icon="pi pi-refresh" @click="retry"
                            class="p-button-danger p-button-rounded px-8" />
                        <Button label="Go to Home" icon="pi pi-home" @click="goStart"
                            class="p-button-secondary p-button-outlined p-button-rounded px-8" />

                    </div>

                    <div class="mt-8 p-8 border-t border-gray-100">
                        <div class="flex flex-col md:flex-row gap-8 md:gap-0">

                            <div class="flex-1 md:pr-8">
                                <p class="text-sm text-gray-500 font-medium mb-3">
                                    For any further inquiries, please contact us:
                                </p>
                                <div class="space-y-2">
                                    <a href="mailto:inscripciones.wmc@iimp.org.pe"
                                        class="flex items-center gap-2 text-blue-600 hover:text-blue-800 transition-colors">
                                        <i class="pi pi-envelope text-lg"></i>
                                        <span class="text-sm font-bold">inscripciones.wmc@iimp.org.pe</span>
                                    </a>
                                    <a href="https://wa.me/51951294314" target="_blank"
                                        class="flex items-center gap-2 text-green-600 hover:text-green-800 transition-colors">
                                        <i class="pi pi-whatsapp text-lg font-bold"></i>
                                        <span class="text-sm font-bold">+51 951 294 314 (Helen Loaiza)</span>
                                    </a>
                                </div>
                            </div>

                            <div class="hidden md:block w-px bg-gray-200"></div>

                            <div class="flex-1 md:pl-8">
                                <p class="text-sm text-gray-500 font-medium mb-3">
                                    For accommodation with preferential rates, contact:
                                </p>
                                <div class="space-y-2">
                                    <a href="mailto:reservas@iimp.org.pe"
                                        class="flex items-center gap-2 text-blue-600 hover:text-blue-800 transition-colors">
                                        <i class="pi pi-envelope text-lg"></i>
                                        <span class="text-sm font-bold">reservas@iimp.org.pe</span>
                                    </a>
                                    <a href="https://wa.me/51942797524" target="_blank"
                                        class="flex items-center gap-2 text-green-600 hover:text-green-800 transition-colors">
                                        <i class="pi pi-whatsapp text-lg font-bold"></i>
                                        <span class="text-sm font-bold">+51 942 797 254 (Melisa Ramos)</span>
                                    </a>
                                </div>
                            </div>

                        </div>
                    </div>


                </div>

            </div>
        </form>
    </AppLayout>
</template>
