<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

interface SessionData {
    id?: number;
    exam_id: number | null;
    title: string;
    start_time: string;
    end_time: string;
}

const props = defineProps<{
    action: string;
    method?: 'post' | 'put';
    exams: { id: number; title: string }[];
    session?: SessionData;
    cancelHref: string;
}>();

const form = useForm({
    exam_id: props.session?.exam_id ?? null,
    title: props.session?.title ?? '',
    start_time: props.session?.start_time ?? '',
    end_time: props.session?.end_time ?? '',
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
                    <Label>Ujian</Label>
                    <Select v-model="form.exam_id">
                        <SelectTrigger>
                            <SelectValue placeholder="Pilih ujian" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="exam in exams"
                                :key="exam.id"
                                :value="exam.id"
                            >
                                {{ exam.title }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p
                        v-if="form.errors.exam_id"
                        class="text-sm text-destructive"
                    >
                        {{ form.errors.exam_id }}
                    </p>
                </div>

                <div class="space-y-2">
                    <Label for="title">Nama Sesi</Label>
                    <Input
                        id="title"
                        v-model="form.title"
                        placeholder="Contoh: Sesi Pagi"
                        required
                    />
                    <p
                        v-if="form.errors.title"
                        class="text-sm text-destructive"
                    >
                        {{ form.errors.title }}
                    </p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="start_time">Mulai</Label>
                        <Input
                            id="start_time"
                            v-model="form.start_time"
                            type="datetime-local"
                            required
                        />
                        <p
                            v-if="form.errors.start_time"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.start_time }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="end_time">Selesai</Label>
                        <Input
                            id="end_time"
                            v-model="form.end_time"
                            type="datetime-local"
                            required
                        />
                        <p
                            v-if="form.errors.end_time"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.end_time }}
                        </p>
                    </div>
                </div>

                <div class="flex gap-2">
                    <Button type="submit" :disabled="form.processing">
                        Simpan
                    </Button>
                    <Button as-child variant="outline">
                        <a :href="cancelHref">Batal</a>
                    </Button>
                </div>
            </form>
        </CardContent>
    </Card>
</template>
