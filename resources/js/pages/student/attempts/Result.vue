<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Check, X } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { cn } from '@/lib/utils';

interface AnsweredQuestion {
    position: number;
    question: string;
    options: string[];
    chosen_index: number | null;
    answer_index: number;
    is_correct: boolean;
}

defineProps<{
    result: {
        score: string | null;
        total_correct: number;
        total_questions: number;
        started_at: string;
        submitted_at: string | null;
    };
    exam: { title: string; session: string; show_answer: boolean };
    answers: AnsweredQuestion[];
}>();

const labels = ['A', 'B', 'C', 'D', 'E'];
</script>

<template>
    <Head title="Hasil Ujian" />

    <div class="space-y-1 text-center">
        <p class="text-sm text-muted-foreground">{{ exam.session }}</p>
        <h1 class="text-2xl font-semibold tracking-tight">{{ exam.title }}</h1>
    </div>

    <Card>
        <CardContent class="space-y-4 text-center">
            <div class="space-y-1">
                <p class="text-sm text-muted-foreground">Nilai Akhir</p>
                <p class="text-5xl font-bold tabular-nums">
                    {{ result.score ?? 0 }}
                </p>
            </div>
            <p class="text-sm text-muted-foreground">
                {{ result.total_correct }} benar dari
                {{ result.total_questions }} soal
            </p>
            <div
                class="flex justify-center gap-2 text-xs text-muted-foreground"
            >
                <span>Mulai {{ result.started_at }}</span>
                <span v-if="result.submitted_at"
                    >· Selesai {{ result.submitted_at }}</span
                >
            </div>
            <Button as-child variant="outline">
                <Link href="/student/dashboard">Kembali ke dashboard</Link>
            </Button>
        </CardContent>
    </Card>

    <Card v-if="exam.show_answer">
        <CardHeader>
            <CardTitle>Pembahasan Jawaban</CardTitle>
            <CardDescription>
                Jawaban benar ditandai hijau, jawaban Anda yang salah ditandai
                merah.
            </CardDescription>
        </CardHeader>
        <CardContent>
            <ol class="space-y-4">
                <li
                    v-for="answer in answers"
                    :key="answer.position"
                    class="rounded-lg border p-4"
                >
                    <div class="flex items-start justify-between gap-3">
                        <p class="font-medium">
                            {{ answer.position }}. {{ answer.question }}
                        </p>
                        <Badge
                            :variant="
                                answer.is_correct ? 'success' : 'destructive'
                            "
                        >
                            <component
                                :is="answer.is_correct ? Check : X"
                                class="size-3"
                            />
                            {{ answer.is_correct ? 'Benar' : 'Salah' }}
                        </Badge>
                    </div>

                    <ul class="mt-2 space-y-1 text-sm">
                        <li
                            v-for="(option, index) in answer.options"
                            :key="index"
                            :class="
                                cn(
                                    'flex gap-2 rounded px-1',
                                    index === answer.answer_index
                                        ? 'font-medium text-success-foreground'
                                        : '',
                                    index === answer.chosen_index &&
                                        index !== answer.answer_index
                                        ? 'text-destructive line-through'
                                        : '',
                                )
                            "
                        >
                            <span class="font-mono">{{ labels[index] }}.</span>
                            <span>{{ option }}</span>
                            <span
                                v-if="index === answer.chosen_index"
                                class="text-xs"
                                >(jawaban Anda)</span
                            >
                            <span
                                v-if="index === answer.answer_index"
                                class="text-xs"
                                >(kunci)</span
                            >
                        </li>
                    </ul>
                </li>
            </ol>
        </CardContent>
    </Card>
</template>
