<script setup lang="ts">
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Plus, Trash2 } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
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

interface QuestionData {
    id?: number;
    question: string;
    options: string[];
    answer_index: number;
}

const props = defineProps<{
    action: string;
    method?: 'post' | 'put';
    examTitle: string;
    question?: QuestionData;
    cancelHref: string;
}>();

const initialOptions = props.question?.options?.length
    ? [...props.question.options]
    : ['', '', '', ''];

const form = useForm({
    question: props.question?.question ?? '',
    options: initialOptions,
    answer_index: props.question?.answer_index ?? 0,
});

const labels = ['A', 'B', 'C', 'D', 'E'];

const answerOptions = computed(() =>
    form.options
        .map((option, index) => ({
            value: index,
            label: `${labels[index]}. ${option || '(kosong)'}`,
        }))
        .filter((_, index) => index < form.options.length),
);

function addOption(): void {
    if (form.options.length < 5) {
        form.options.push('');
    }
}

function removeOption(index: number): void {
    if (form.options.length <= 2) {
        return;
    }

    form.options.splice(index, 1);

    if (form.answer_index >= form.options.length) {
        form.answer_index = form.options.length - 1;
    }
}

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
                    <Label for="question">Pertanyaan</Label>
                    <Textarea
                        id="question"
                        v-model="form.question"
                        rows="3"
                        placeholder="Tulis pertanyaan..."
                        required
                    />
                    <p
                        v-if="form.errors.question"
                        class="text-sm text-destructive"
                    >
                        {{ form.errors.question }}
                    </p>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <Label>Pilihan Jawaban</Label>
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            :disabled="form.options.length >= 5"
                            @click="addOption"
                        >
                            <Plus class="size-4" />
                            Tambah pilihan
                        </Button>
                    </div>

                    <div
                        v-for="(option, index) in form.options"
                        :key="index"
                        class="flex items-start gap-2"
                    >
                        <span
                            class="mt-2 w-4 font-mono text-sm text-muted-foreground"
                            >{{ labels[index] }}</span
                        >
                        <Input
                            v-model="form.options[index]"
                            :placeholder="`Pilihan ${labels[index]}`"
                            required
                        />
                        <Button
                            type="button"
                            variant="ghost"
                            size="icon"
                            :disabled="form.options.length <= 2"
                            aria-label="Hapus pilihan"
                            @click="removeOption(index)"
                        >
                            <Trash2 class="size-4 text-destructive" />
                        </Button>
                    </div>

                    <p
                        v-if="form.errors.options"
                        class="text-sm text-destructive"
                    >
                        {{ form.errors.options }}
                    </p>
                    <p
                        v-for="(error, key) in form.errors"
                        v-show="String(key).startsWith('options.')"
                        :key="String(key)"
                        class="text-sm text-destructive"
                    >
                        {{ error }}
                    </p>
                </div>

                <div class="space-y-2">
                    <Label>Jawaban Benar</Label>
                    <Select v-model="form.answer_index">
                        <SelectTrigger class="max-w-sm">
                            <SelectValue placeholder="Pilih jawaban benar" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="option in answerOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p
                        v-if="form.errors.answer_index"
                        class="text-sm text-destructive"
                    >
                        {{ form.errors.answer_index }}
                    </p>
                </div>

                <div class="flex gap-2">
                    <Button type="submit" :disabled="form.processing">
                        Simpan Soal
                    </Button>
                    <Button as-child variant="outline">
                        <a :href="cancelHref">Batal</a>
                    </Button>
                </div>
            </form>
        </CardContent>
    </Card>
</template>
