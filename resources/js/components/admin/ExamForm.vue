<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

interface Option {
    id: number;
    title: string;
}

interface ExamData {
    id?: number;
    lesson_id: number | null;
    classroom_id: number | null;
    title: string;
    duration: number;
    description: string | null;
    random_question: boolean;
    random_answer: boolean;
    show_answer: boolean;
}

const props = defineProps<{
    action: string;
    method?: 'post' | 'put';
    classrooms: Option[];
    lessons: Option[];
    exam?: ExamData;
    cancelHref: string;
}>();

const form = useForm({
    title: props.exam?.title ?? '',
    lesson_id: props.exam?.lesson_id ?? null,
    classroom_id: props.exam?.classroom_id ?? null,
    duration: props.exam?.duration ?? 60,
    description: props.exam?.description ?? '',
    random_question: props.exam?.random_question ?? true,
    random_answer: props.exam?.random_answer ?? true,
    show_answer: props.exam?.show_answer ?? false,
});

const toggles = [
    {
        key: 'random_question',
        label: 'Acak urutan soal',
        hint: 'Urutan soal berbeda untuk setiap peserta.',
    },
    {
        key: 'random_answer',
        label: 'Acak urutan pilihan',
        hint: 'Pilihan jawaban diacak per peserta.',
    },
    {
        key: 'show_answer',
        label: 'Tampilkan jawaban setelah selesai',
        hint: 'Peserta dapat melihat kunci jawaban pada halaman hasil.',
    },
] as const;

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
                    <Label for="title">Judul Ujian</Label>
                    <Input id="title" v-model="form.title" required />
                    <p
                        v-if="form.errors.title"
                        class="text-sm text-destructive"
                    >
                        {{ form.errors.title }}
                    </p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-2">
                        <Label>Mata Pelajaran</Label>
                        <Select v-model="form.lesson_id">
                            <SelectTrigger>
                                <SelectValue
                                    placeholder="Pilih mata pelajaran"
                                />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="lesson in lessons"
                                    :key="lesson.id"
                                    :value="lesson.id"
                                >
                                    {{ lesson.title }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p
                            v-if="form.errors.lesson_id"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.lesson_id }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label>Kelas</Label>
                        <Select v-model="form.classroom_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Pilih kelas" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="classroom in classrooms"
                                    :key="classroom.id"
                                    :value="classroom.id"
                                >
                                    {{ classroom.title }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p
                            v-if="form.errors.classroom_id"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.classroom_id }}
                        </p>
                    </div>
                </div>

                <div class="space-y-2">
                    <Label for="duration">Durasi (menit)</Label>
                    <Input
                        id="duration"
                        v-model.number="form.duration"
                        type="number"
                        min="1"
                        max="1440"
                        required
                    />
                    <p
                        v-if="form.errors.duration"
                        class="text-sm text-destructive"
                    >
                        {{ form.errors.duration }}
                    </p>
                </div>

                <div class="space-y-2">
                    <Label for="description">Deskripsi</Label>
                    <Textarea
                        id="description"
                        v-model="form.description"
                        rows="3"
                        placeholder="Petunjuk atau deskripsi singkat ujian"
                    />
                    <p
                        v-if="form.errors.description"
                        class="text-sm text-destructive"
                    >
                        {{ form.errors.description }}
                    </p>
                </div>

                <div class="space-y-3">
                    <div
                        v-for="toggle in toggles"
                        :key="toggle.key"
                        class="flex items-start gap-3 rounded-lg border p-3"
                    >
                        <Checkbox
                            :id="toggle.key"
                            :model-value="form[toggle.key]"
                            @update:model-value="
                                form[toggle.key] = $event === true
                            "
                        />
                        <div class="space-y-0.5">
                            <Label :for="toggle.key" class="font-medium">
                                {{ toggle.label }}
                            </Label>
                            <p class="text-xs text-muted-foreground">
                                {{ toggle.hint }}
                            </p>
                        </div>
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
