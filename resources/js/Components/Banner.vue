<script setup>
import ApplicationMark from '@/Components/ApplicationMark.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <nav class="bg-white border-b-1 border-black shadow-md">
        <!-- Primary Navigation Menu -->
        <div class="px-4 mx-auto max-w-7xl md:px-6 lg:px-8">
            <div class="flex justify-between mx-auto w-full">
                <div class="flex mx-auto w-full">
                    <div class="flex justify-between h-16 mx-auto w-full">
                        <!-- Logo -->
                        <div class="flex items-center shrink-0">
                            <a href="https://wmc2026.org/"  rel="noopener noreferrer">
                                <!-- <ApplicationMark class="block w-auto h-9" /> -->
                                <img src="/images/logo-wmc.png" alt="World Mining" class="h-12 w-auto" />
                            </a>
                        </div>
                        <!-- Idiomas -->
                        <!-- <div class="flex">
                            <ul class="flex space-x-4 items-center " style="display :none;">
                                <li class="border-r border-r-2 border-black pr-3">ES</li>
                                <li>EN</li>
                            </ul>
                        </div> -->

                    </div>

                </div>
            </div>
        </div>
    </nav>
</template>
