<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    roles: Array,
    permissions: Array
});

// --- ESTADOS BÁSICOS ---
const searchQuery = ref('');
const isModalOpen = ref(false);
const editingRole = ref(null);
const form = useForm({ name: '', permissions: [] });

// --- ESTADOS PARA ELIMINAR ---
const isDeleteModalOpen = ref(false);
const roleToDelete = ref(null);

// --- ESTADOS PARA PAGINACIÓN ---
const currentPage = ref(1);
const itemsPerPage = 5;

// --- ESTADO PARA TOAST (NOTIFICACIONES) ---
const toastMessage = ref('');
const showToast = ref(false);

const displayToast = (message) => {
    toastMessage.value = message;
    showToast.value = true;
    setTimeout(() => {
        showToast.value = false;
    }, 3000); // Se oculta después de 3 segundos
};

// --- LÓGICA DE BÚSQUEDA ---
const filteredRoles = computed(() => {
    return props.roles.filter(role => role.name.toLowerCase().includes(searchQuery.value.toLowerCase()));
});

watch(searchQuery, () => {
    currentPage.value = 1;
});

// --- LÓGICA DE PAGINACIÓN ---
const totalPages = computed(() => Math.ceil(filteredRoles.value.length / itemsPerPage));

const paginatedRoles = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return filteredRoles.value.slice(start, end);
});

const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++; };
const prevPage = () => { if (currentPage.value > 1) currentPage.value--; };

// --- MÉTODOS DE CREAR/EDITAR ---
const openModal = (role = null) => {
    editingRole.value = role;
    if (role) {
        form.name = role.name;
        form.permissions = role.permissions.map(p => p.name);
    } else { form.reset(); }
    isModalOpen.value = true;
};

const submit = () => {
    const action = editingRole.value ? route('roles.update', editingRole.value.id) : route('roles.store');
    const method = editingRole.value ? 'put' : 'post';
    const successMessage = editingRole.value ? 'Rol actualizado correctamente.' : 'Nuevo rol creado con éxito.';

    form[method](action, {
        onSuccess: () => {
            isModalOpen.value = false;
            displayToast(successMessage); // Dispara el toast
        },
        preserveScroll: true
    });
};

// --- MÉTODOS DE ELIMINAR ---
const confirmDelete = (role) => {
    roleToDelete.value = role;
    isDeleteModalOpen.value = true;
};

const executeDelete = () => {
    if (roleToDelete.value) {
        router.delete(route('roles.destroy', roleToDelete.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                isDeleteModalOpen.value = false;
                roleToDelete.value = null;
                displayToast('Rol eliminado exitosamente.'); // Dispara el toast
            }
        });
    }
};
</script>

<template>
    <Head title="Roles y Seguridad" />

    <AuthenticatedLayout>

        <Transition enter-active-class="transform ease-out duration-300 transition" enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2" enter-to-class="translate-y-0 opacity-100 sm:translate-x-0" leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showToast" class="fixed top-24 right-6 z-[110] max-w-sm w-full bg-slate-900 shadow-2xl rounded-2xl pointer-events-auto flex ring-1 ring-black ring-opacity-5 overflow-hidden">
                <div class="p-4 flex items-center w-full">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-3 w-0 flex-1 pt-0.5">
                        <p class="text-sm font-bold text-white">{{ toastMessage }}</p>
                    </div>
                </div>
            </div>
        </Transition>

        <div class="space-y-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h2 class="font-black text-2xl text-slate-800 tracking-tight">Gestión de Roles y Permisos</h2>
                    <p class="text-sm text-slate-500 mt-1">Administra los roles y niveles de acceso de los usuarios.</p>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
                    <div class="relative w-full sm:w-64">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <input v-model="searchQuery" type="text" placeholder="Buscar rol..." class="w-full pl-9 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all outline-none shadow-sm text-sm" />
                    </div>
                    <button @click="openModal()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-sm transition-all active:scale-95 whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        Nuevo Rol
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50/80">
                            <tr>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-slate-500 uppercase tracking-widest">Rol del Sistema</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-slate-500 uppercase tracking-widest">Permisos Asignados</th>
                                <th class="px-6 py-4 text-right text-[10px] font-black text-slate-500 uppercase tracking-widest">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <tr v-for="role in paginatedRoles" :key="role.id" class="group hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4 font-bold uppercase text-sm text-slate-800 whitespace-nowrap">{{ role.name }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1.5">
                                        <span v-for="p in role.permissions" :key="p.id" class="px-2 py-1 bg-slate-100 text-slate-600 text-[10px] font-bold rounded-md uppercase tracking-tight border border-slate-200/60">
                                            {{ p.name }}
                                        </span>
                                        <span v-if="role.permissions.length === 0" class="text-xs text-slate-400 italic">Sin permisos</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <div class="flex justify-end gap-2">
                                        <button @click="openModal(role)" class="p-2 text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors" title="Editar Rol">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <button @click="confirmDelete(role)" class="p-2 text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors" title="Eliminar Rol">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filteredRoles.length === 0">
                                <td colspan="3" class="px-6 py-12 text-center text-slate-500 font-medium">
                                    No se encontraron roles que coincidan con la búsqueda.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="filteredRoles.length > 0" class="px-6 py-4 border-t border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <span class="text-xs text-slate-500 font-medium">
                        Mostrando <span class="font-bold text-slate-700">{{ (currentPage - 1) * itemsPerPage + 1 }}</span> a
                        <span class="font-bold text-slate-700">{{ Math.min(currentPage * itemsPerPage, filteredRoles.length) }}</span>
                        de <span class="font-bold text-slate-700">{{ filteredRoles.length }}</span> roles
                    </span>
                    <div class="flex gap-1.5">
                        <button @click="prevPage" :disabled="currentPage === 1" class="px-3 py-1.5 text-xs font-bold rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            Anterior
                        </button>
                        <button @click="nextPage" :disabled="currentPage === totalPages" class="px-3 py-1.5 text-xs font-bold rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            Siguiente
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="isModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
                <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                        <h3 class="text-xl font-black text-slate-800 uppercase tracking-tight">{{ editingRole ? 'Editar Rol' : 'Crear Nuevo Rol' }}</h3>
                        <button @click="isModalOpen = false" class="text-slate-400 hover:text-slate-600 bg-slate-100 p-2 rounded-full transition-colors">✕</button>
                    </div>
                    <form @submit.prevent="submit" class="p-6 space-y-5">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Nombre del Rol</label>
                            <input v-model="form.name" type="text" placeholder="Ej: Administrador" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 focus:ring-2 focus:ring-indigo-500 outline-none font-bold text-slate-700" required />
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Permisos</label>
                            <div class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto p-4 bg-slate-50 rounded-xl border border-slate-200 custom-scrollbar">
                                <label v-for="perm in permissions" :key="perm.id" class="flex items-center gap-3 p-2 hover:bg-white rounded-lg cursor-pointer transition-colors border border-transparent hover:border-slate-200">
                                    <input type="checkbox" :value="perm.name" v-model="form.permissions" class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
                                    <span class="text-[10px] font-black text-slate-600 uppercase">{{ perm.name }}</span>
                                </label>
                            </div>
                        </div>
                        <div class="flex gap-3 pt-4">
                            <button type="button" @click="isModalOpen = false" class="flex-1 py-3 text-sm font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">Cancelar</button>
                            <button type="submit" :disabled="form.processing" class="flex-1 py-3 text-sm font-bold text-white bg-indigo-600 rounded-xl shadow-lg hover:bg-indigo-700 transition-colors disabled:opacity-50">Guardar Rol</button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>

        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="isDeleteModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
                <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm p-6 text-center">
                    <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <h3 class="text-xl font-black text-slate-800 mb-2">¿Eliminar Rol?</h3>
                    <p class="text-sm text-slate-500 mb-6">
                        Estás a punto de eliminar el rol <span class="font-bold text-slate-800 uppercase">"{{ roleToDelete?.name }}"</span>. Esta acción no se puede deshacer.
                    </p>
                    <div class="flex gap-3">
                        <button @click="isDeleteModalOpen = false" class="flex-1 py-3 text-sm font-bold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors">Cancelar</button>
                        <button @click="executeDelete" class="flex-1 py-3 text-sm font-bold text-white bg-red-600 rounded-xl shadow-lg hover:bg-red-700 transition-colors">Sí, Eliminar</button>
                    </div>
                </div>
            </div>
        </Transition>

    </AuthenticatedLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
</style>
