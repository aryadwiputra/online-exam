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
            title: 'Masuk Peserta',
            description: 'Masuk dengan NISN dan kata sandi Anda.',
        },
    ],
});

const form = useForm({
    nisn: '',
    password: '',
    remember: false,
});

function submit(): void {
    form.post('/student/login', {
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <Head title="Masuk Peserta" />

    <Card class="border-0 bg-transparent shadow-none">
        <CardContent class="space-y-5 px-0">
            <form class="space-y-5" @submit.prevent="submit">
                <div class="space-y-2">
                    <Label for="nisn">NISN</Label>
                    <Input
                        id="nisn"
                        v-model="form.nisn"
                        inputmode="numeric"
                        autocomplete="username"
                        placeholder="Masukkan NISN"
                        required
                    />
                    <p v-if="form.errors.nisn" class="text-sm text-destructive">
                        {{ form.errors.nisn }}
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
                Admin?
                <Link
                    href="/login"
                    class="font-medium text-primary underline-offset-4 hover:underline"
                >
                    Masuk di sini
                </Link>
            </p>
        </CardContent>
    </Card>
</template>
