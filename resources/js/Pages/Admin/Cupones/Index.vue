<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    cupones: Array
});

// --- ESTADOS BÁSICOS ---
const searchQuery = ref('');
const isModalOpen = ref(false);
const editingCupon = ref(null);

const form = useForm({
    codigo_cupon: '',
    tipo_descuento: 'porcentaje',
    valor: 0,
    razon_social: '',
    tipo_documento: '6', // 6 es RUC usualmente
    num_documento: '',
    eci_cod: '',
    limite_usos: 100,
    fecha_inicio: '',
    fecha_fin: '',
    is_active: true
});

// --- ESTADOS PARA ELIMINAR ---
const isDeleteModalOpen = ref(false);
const cuponToDelete = ref(null);

// --- ESTADOS PARA PAGINACIÓN ---
const currentPage = ref(1);
const itemsPerPage = 8;

// --- ESTADO PARA TOAST ---
const toastMessage = ref('');
const showToast = ref(false);

const displayToast = (message) => {
    toastMessage.value = message;
    showToast.value = true;
    setTimeout(() => showToast.value = false, 3000);
};

// --- LÓGICA DE BÚSQUEDA ---
const filteredCupones = computed(() => {
    const query = searchQuery.value.toLowerCase();
    return props.cupones.filter(cupon =>
        (cupon.codigo_cupon && cupon.codigo_cupon.toLowerCase().includes(query)) ||
        (cupon.razon_social && cupon.razon_social.toLowerCase().includes(query))
    );
});

watch(searchQuery, () => { currentPage.value = 1; });

// --- LÓGICA DE PAGINACIÓN ---
const totalPages = computed(() => Math.ceil(filteredCupones.value.length / itemsPerPage));
const paginatedCupones = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    return filteredCupones.value.slice(start, start + itemsPerPage);
});

const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++; };
const prevPage = () => { if (currentPage.value > 1) currentPage.value--; };

// --- MÉTODOS DE CREAR/EDITAR ---
// Helper para dar formato a la fecha para el input datetime-local (YYYY-MM-DDTHH:mm)
const formatDateTime = (dateString) => {
    if (!dateString) return '';
    return dateString.replace(' ', 'T').slice(0, 16);
};

const openModal = (cupon = null) => {
    editingCupon.value = cupon;
    if (cupon) {
        form.codigo_cupon = cupon.codigo_cupon;
        form.tipo_descuento = cupon.tipo_descuento;
        form.valor = cupon.valor;
        form.razon_social = cupon.razon_social || '';
        form.tipo_documento = cupon.tipo_documento || '';
        form.num_documento = cupon.num_documento || '';
        form.eci_cod = cupon.eci_cod || '';
        form.limite_usos = cupon.limite_usos;
        form.fecha_inicio = formatDateTime(cupon.fecha_inicio);
        form.fecha_fin = formatDateTime(cupon.fecha_fin);
        form.is_active = cupon.is_active === 1 || cupon.is_active === true;
    } else {
        form.reset();
        // Fechas por defecto (hoy y fin de año por ejemplo)
        const hoy = new Date();
        form.fecha_inicio = hoy.toISOString().slice(0, 16);
    }
    isModalOpen.value = true;
};

const submit = () => {
    const action = editingCupon.value ? route('cupones.update', editingCupon.value.id) : route('cupones.store');
    const method = editingCupon.value ? 'put' : 'post';
    const successMessage = editingCupon.value ? 'Cupón actualizado correctamente.' : 'Nuevo cupón creado con éxito.';

    form[method](action, {
        onSuccess: () => {
            isModalOpen.value = false;
            displayToast(successMessage);
            form.reset();
        },
        preserveScroll: true
    });
};

// --- MÉTODOS DE ELIMINAR ---
const confirmDelete = (cupon) => {
    cuponToDelete.value = cupon;
    isDeleteModalOpen.value = true;
};

const executeDelete = () => {
    if (cuponToDelete.value) {
        router.delete(route('cupones.destroy', cuponToDelete.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                isDeleteModalOpen.value = false;
                cuponToDelete.value = null;
                displayToast('Cupón eliminado exitosamente.');
            }
        });
    }
};
</script>

<template>
    <Head title="Gestión Comercial - Cupones" />

    <AuthenticatedLayout>
        <div class="space-y-6">

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h2 class="font-black text-2xl text-slate-800 tracking-tight">Gestión de Cupones</h2>
                    <p class="text-sm text-slate-500 mt-1">Administra los descuentos y pases para el congreso.</p>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
                    <div class="relative w-full sm:w-64">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <input v-model="searchQuery" type="text" placeholder="Buscar código o empresa..." class="w-full pl-9 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none shadow-sm text-sm" />
                    </div>
                    <button @click="openModal()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-sm transition-all active:scale-95 whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        Nuevo Cupón
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50/80">
                            <tr>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-slate-500 uppercase tracking-widest">Código y Estado</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-slate-500 uppercase tracking-widest">Empresa / Entidad</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-slate-500 uppercase tracking-widest">Descuento</th>
                                <th class="px-6 py-4 text-center text-[10px] font-black text-slate-500 uppercase tracking-widest">Usos</th>
                                <th class="px-6 py-4 text-right text-[10px] font-black text-slate-500 uppercase tracking-widest">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <tr v-for="cupon in paginatedCupones" :key="cupon.id" class="group hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col gap-1">
                                        <span class="font-black text-sm text-slate-800 tracking-tight">{{ cupon.codigo_cupon }}</span>
                                        <div>
                                            <span v-if="cupon.is_active" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-green-50 text-green-600 text-[9px] font-bold uppercase tracking-widest border border-green-200/60">
                                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Activo
                                            </span>
                                            <span v-else class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100 text-slate-500 text-[9px] font-bold uppercase tracking-widest border border-slate-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Inactivo
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <div v-if="cupon.razon_social">
                                        <p class="font-bold text-xs text-slate-700 uppercase">{{ cupon.razon_social }}</p>
                                        <p class="text-[10px] text-slate-400 font-medium">Doc: {{ cupon.num_documento || 'N/A' }} | ECI: {{ cupon.eci_cod || 'N/A' }}</p>
                                    </div>
                                    <span v-else class="text-xs text-slate-400 italic">Global / Sin asignar</span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="font-black text-indigo-600 text-lg leading-none">
                                            {{ cupon.tipo_descuento === 'porcentaje' ? cupon.valor + '%' : '$' + cupon.valor }}
                                        </span>
                                        <span class="text-[9px] text-slate-400 font-bold uppercase tracking-widest leading-none">{{ cupon.tipo_descuento }}</span>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <div class="inline-flex flex-col items-center">
                                        <span class="text-sm font-bold text-slate-700">
                                            <span :class="cupon.usos_actuales >= cupon.limite_usos ? 'text-red-500' : 'text-indigo-600'">{{ cupon.usos_actuales }}</span>
                                            <span class="text-slate-400 font-normal"> / {{ cupon.limite_usos }}</span>
                                        </span>
                                        <div class="w-16 h-1.5 bg-slate-100 rounded-full mt-1 overflow-hidden">
                                            <div class="h-full bg-indigo-500 rounded-full transition-all" :style="{ width: Math.min((cupon.usos_actuales / cupon.limite_usos) * 100, 100) + '%' }"></div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <div class="flex justify-end gap-2">
                                        <button @click="openModal(cupon)" class="p-2 text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors" title="Editar Cupón">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <button @click="confirmDelete(cupon)" class="p-2 text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors" title="Eliminar Cupón">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filteredCupones.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-slate-500 font-medium">
                                    No se encontraron cupones que coincidan con la búsqueda.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="filteredCupones.length > 0" class="px-6 py-4 border-t border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <span class="text-xs text-slate-500 font-medium">
                        Mostrando <span class="font-bold text-slate-700">{{ (currentPage - 1) * itemsPerPage + 1 }}</span> a
                        <span class="font-bold text-slate-700">{{ Math.min(currentPage * itemsPerPage, filteredCupones.length) }}</span>
                        de <span class="font-bold text-slate-700">{{ filteredCupones.length }}</span> cupones
                    </span>
                    <div class="flex gap-1.5">
                        <button @click="prevPage" :disabled="currentPage === 1" class="px-3 py-1.5 text-xs font-bold rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 disabled:opacity-50 transition-colors">Anterior</button>
                        <button @click="nextPage" :disabled="currentPage === totalPages" class="px-3 py-1.5 text-xs font-bold rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 disabled:opacity-50 transition-colors">Siguiente</button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

    <Teleport to="body">

        <Transition enter-active-class="transform ease-out duration-300 transition" enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2" enter-to-class="translate-y-0 opacity-100 sm:translate-x-0" leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showToast" class="fixed top-6 right-6 z-[200] max-w-sm w-full bg-slate-900 shadow-2xl rounded-2xl pointer-events-auto flex ring-1 ring-black ring-opacity-5 overflow-hidden">
                <div class="p-4 flex items-center w-full">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div class="ml-3 w-0 flex-1 pt-0.5">
                        <p class="text-sm font-bold text-white">{{ toastMessage }}</p>
                    </div>
                </div>
            </div>
        </Transition>

        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="isModalOpen" class="fixed inset-0 z-[150] overflow-y-auto bg-slate-900/60 backdrop-blur-sm">
                <div class="flex min-h-full items-center justify-center p-4 py-10">

                    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-3xl overflow-hidden relative">
                        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                            <h3 class="text-xl font-black text-slate-800 uppercase tracking-tight">{{ editingCupon ? 'Editar Cupón' : 'Crear Nuevo Cupón' }}</h3>
                            <button @click="isModalOpen = false" class="text-slate-400 hover:text-slate-600 bg-white p-2 rounded-full border border-slate-200 transition-colors">✕</button>
                        </div>

                        <form @submit.prevent="submit" class="p-6 space-y-6">

                            <div>
                                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 pb-2 mb-4">Detalles del Descuento</h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="md:col-span-1">
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Código del Cupón</label>
                                        <input v-model="form.codigo_cupon" type="text" placeholder="Ej: MINEX-2026" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 focus:ring-2 focus:ring-indigo-500 outline-none text-sm text-slate-700 font-bold uppercase" required />
                                        <div v-if="form.errors.codigo_cupon" class="text-red-500 text-[10px] mt-1">{{ form.errors.codigo_cupon }}</div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Tipo de Descuento</label>
                                        <select v-model="form.tipo_descuento" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 focus:ring-2 focus:ring-indigo-500 outline-none text-sm text-slate-700" required>
                                            <option value="porcentaje">Porcentaje (%)</option>
                                            <option value="monto">Monto Fijo ($)</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Valor del Descuento</label>
                                        <input v-model="form.valor" type="number" min="1" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 focus:ring-2 focus:ring-indigo-500 outline-none text-sm text-slate-700 font-bold" required />
                                        <div v-if="form.errors.valor" class="text-red-500 text-[10px] mt-1">{{ form.errors.valor }}</div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 pb-2 mb-4">Datos de Empresa / Entidad (Opcional)</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Razón Social</label>
                                        <input v-model="form.razon_social" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 focus:ring-2 focus:ring-indigo-500 outline-none text-sm text-slate-700 uppercase" />
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Tipo Documento</label>
                                        <select v-model="form.tipo_documento" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 focus:ring-2 focus:ring-indigo-500 outline-none text-sm text-slate-700">
                                            <option value="6">RUC (6)</option>
                                            <option value="1">DNI (1)</option>
                                            <option value="4">CE (4)</option>
                                            <option value="">Otro</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Nº Documento</label>
                                        <input v-model="form.num_documento" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 focus:ring-2 focus:ring-indigo-500 outline-none text-sm text-slate-700" />
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Código ECI</label>
                                        <input v-model="form.eci_cod" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 focus:ring-2 focus:ring-indigo-500 outline-none text-sm text-slate-700" />
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 pb-2 mb-4">Límites y Vigencia</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                                    <div class="md:col-span-2 grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 mb-1">Límite de Usos Totales</label>
                                            <input v-model="form.limite_usos" type="number" min="1" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 focus:ring-2 focus:ring-indigo-500 outline-none text-sm text-slate-700" required />
                                        </div>

                                        <div class="flex flex-col justify-center pt-5">
                                            <label class="flex items-center gap-3 cursor-pointer">
                                                <input type="checkbox" v-model="form.is_active" class="w-5 h-5 rounded border-slate-300 text-green-600 focus:ring-green-500" />
                                                <span class="text-sm font-bold text-slate-700">Cupón Activo</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Fecha de Inicio</label>
                                        <input v-model="form.fecha_inicio" type="datetime-local" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 focus:ring-2 focus:ring-indigo-500 outline-none text-sm text-slate-700" required />
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Fecha de Fin</label>
                                        <input v-model="form.fecha_fin" type="datetime-local" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 focus:ring-2 focus:ring-indigo-500 outline-none text-sm text-slate-700" required />
                                        <div v-if="form.errors.fecha_fin" class="text-red-500 text-[10px] mt-1">{{ form.errors.fecha_fin }}</div>
                                    </div>

                                </div>
                            </div>

                            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                                <button type="button" @click="isModalOpen = false" class="px-6 py-2.5 text-sm font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">Cancelar</button>
                                <button type="submit" :disabled="form.processing" class="px-6 py-2.5 text-sm font-bold text-white bg-indigo-600 rounded-xl shadow-lg hover:bg-indigo-700 transition-colors disabled:opacity-50">
                                    {{ editingCupon ? 'Guardar Cambios' : 'Crear Cupón' }}
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </Transition>

        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="isDeleteModalOpen" class="fixed inset-0 z-[150] overflow-y-auto bg-slate-900/60 backdrop-blur-sm">
                <div class="flex min-h-full items-center justify-center p-4">
                    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm p-6 text-center">
                        <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <h3 class="text-xl font-black text-slate-800 mb-2">¿Eliminar Cupón?</h3>
                        <p class="text-sm text-slate-500 mb-6">
                            Vas a eliminar el cupón <span class="font-bold text-slate-800 tracking-tight">{{ cuponToDelete?.codigo_cupon }}</span>. Esta acción no se puede deshacer.
                        </p>
                        <div class="flex gap-3">
                            <button @click="isDeleteModalOpen = false" class="flex-1 py-3 text-sm font-bold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors">Cancelar</button>
                            <button @click="executeDelete" class="flex-1 py-3 text-sm font-bold text-white bg-red-600 rounded-xl shadow-lg hover:bg-red-700 transition-colors">Sí, Eliminar</button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

    </Teleport>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
</style>
