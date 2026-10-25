<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';

defineOptions({
    layout: [
        AuthLayout,
        {
            title: 'Masuk Admin',
            description: 'Kelola ujian, peserta, dan laporan.',
        },
    ],
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

function submit(): void {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <Head title="Masuk Admin" />

    <Card>
        <CardContent class="space-y-4">
            <form class="space-y-4" @submit.prevent="submit">
                <div class="space-y-2">
                    <Label for="email">Email</Label>
                    <Input
                        id="email"
                        v-model="form.email"
                        type="email"
                        autocomplete="email"
                        placeholder="admin@example.com"
                        required
                    />
                    <p
                        v-if="form.errors.email"
                        class="text-sm text-destructive"
                    >
                        {{ form.errors.email }}
                    </p>
                </div>

                <div class="space-y-2">
                    <Label for="password">Kata Sandi</Label>
                    <Input
                        id="password"
                        v-model="form.password"
                        type="password"
                        autocomplete="current-password"
                        required
                    />
                    <p
                        v-if="form.errors.password"
                        class="text-sm text-destructive"
                    >
                        {{ form.errors.password }}
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Checkbox
                        id="remember"
                        :model-value="form.remember"
                        @update:model-value="form.remember = $event === true"
                    />
                    <Label for="remember" class="font-normal">Ingat saya</Label>
                </div>

                <Button
                    type="submit"
                    class="w-full"
                    :disabled="form.processing"
                >
                    Masuk
                </Button>
            </form>

            <p class="text-center text-sm text-muted-foreground">
                Peserta ujian?
                <Link href="/" class="font-medium text-primary hover:underline">
                    Masuk di sini
                </Link>
            </p>
        </CardContent>
    </Card>
</template>
