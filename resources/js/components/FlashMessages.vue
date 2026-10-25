<script setup lang="ts">
import type { PageProps } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { AlertCircle, CheckCircle2 } from '@lucide/vue';
import { Alert, AlertDescription } from '@/components/ui/alert';

const page = usePage<PageProps>();

const errors = computed(() => page.props.flash.import_errors ?? []);
</script>

<template>
    <div class="space-y-3">
        <Alert v-if="page.props.flash.success" variant="success">
            <CheckCircle2 />
            <AlertDescription>{{ page.props.flash.success }}</AlertDescription>
        </Alert>

        <Alert v-if="page.props.flash.error" variant="destructive">
            <AlertCircle />
            <AlertDescription>{{ page.props.flash.error }}</AlertDescription>
        </Alert>

        <Alert v-if="errors.length" variant="destructive">
            <AlertCircle />
            <AlertDescription>
                <p class="font-medium">Sebagian baris gagal diproses:</p>
                <ul class="list-disc space-y-1 pl-4">
                    <li v-for="error in errors" :key="error">{{ error }}</li>
                </ul>
            </AlertDescription>
        </Alert>
    </div>
</template>
