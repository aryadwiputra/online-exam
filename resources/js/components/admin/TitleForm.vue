<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps<{
    action: string;
    method?: 'post' | 'put';
    title: string;
    initialValue?: string;
    cancelHref: string;
}>();

const form = useForm({
    title: props.initialValue ?? '',
});

function submit(): void {
    const options = { preserveScroll: true };

    if (props.method === 'put') {
        form.put(props.action, options);

        return;
    }

    form.post(props.action, options);
}
</script>

<template>
    <Card>
        <CardContent>
            <form class="space-y-4" @submit.prevent="submit">
                <div class="space-y-2">
                    <Label for="title">Nama</Label>
                    <Input
                        id="title"
                        v-model="form.title"
                        placeholder="Masukkan nama"
                        autofocus
                        required
                    />
                    <p
                        v-if="form.errors.title"
                        class="text-sm text-destructive"
                    >
                        {{ form.errors.title }}
                    </p>
                </div>

                <div class="flex gap-2">
                    <Button type="submit" :disabled="form.processing">
                        {{ title }}
                    </Button>
                    <Button as-child variant="outline">
                        <a :href="cancelHref">Batal</a>
                    </Button>
                </div>
            </form>
        </CardContent>
    </Card>
</template>
