<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { CheckCircle2, ChevronLeft, ChevronRight, Clock } from '@lucide/vue';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { cn } from '@/lib/utils';
import studentRoutes from '@/routes/student';

const props = defineProps<{
    attempt: {
        id: number;
        remaining_seconds: number;
        expires_at: string;
    };
    exam: { title: string; session: string };
    question: {
        id: number;
        text: string;
        position: number;
        chosen_index: number | null;
        options: { index: number; label: string; text: string }[];
    };
    navigation: { position: number; answered: boolean }[];
    total: number;
}>();

const remaining = ref(props.attempt.remaining_seconds);
const saving = ref(false);
const submitting = ref(false);
const confirmOpen = ref(false);
let timer: ReturnType<typeof setInterval> | null = null;

const answeredCount = computed(
    () => props.navigation.filter((item) => item.answered).length,
);

const formattedTime = computed(() => {
    const minutes = Math.floor(remaining.value / 60);
    const seconds = remaining.value % 60;

    return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
});

const isUrgent = computed(() => remaining.value <= 300);

function submit(): void {
    if (submitting.value) {
        return;
    }

    submitting.value = true;

    router.post(
        studentRoutes.attempts.submit.url(props.attempt.id),
        {},
        {
            onFinish: () => {
                submitting.value = false;
            },
        },
    );
}

function tick(): void {
    if (remaining.value <= 0) {
        return;
    }

    remaining.value -= 1;

    if (remaining.value === 0) {
        submit();
    }
}

function choose(optionIndex: number): void {
    if (saving.value || props.question.chosen_index === optionIndex) {
        return;
    }

    saving.value = true;

    router.post(
        studentRoutes.attempts.answer.url(props.attempt.id),
        {
            question_id: props.question.id,
            chosen_index: optionIndex,
        },
        {
            preserveScroll: true,
            preserveState: true,
            onFinish: () => {
                saving.value = false;
            },
        },
    );
}

function pageUrl(page: number): string {
    return studentRoutes.attempts.show.url(props.attempt.id, {
        query: { page },
    });
}

onMounted(() => {
    timer = setInterval(tick, 1000);
});

onUnmounted(() => {
    if (timer) {
        clearInterval(timer);
    }
});
</script>

<template>
    <Head :title="exam.title" />

    <div class="space-y-4 border-b pb-5">
        <p class="text-sm font-medium text-muted-foreground">
            {{ exam.session }}
        </p>
        <div class="flex flex-wrap items-end justify-between gap-3">
            <h1
                class="font-display text-2xl font-semibold tracking-tight sm:text-3xl"
            >
                {{ exam.title }}
            </h1>
            <div
                role="timer"
                aria-live="off"
                :class="
                    cn(
                        'flex items-center gap-2 rounded-md border bg-card px-3 py-2 font-mono text-lg font-semibold tabular-nums',
                        isUrgent
                            ? 'border-destructive/40 bg-destructive/10 text-destructive'
                            : 'text-primary',
                    )
                "
            >
                <Clock class="size-4" />
                {{ formattedTime }}
            </div>
        </div>
        <div class="space-y-2">
            <div class="flex items-center justify-between text-sm">
                <span class="font-medium"
                    >Soal {{ question.position }} dari {{ total }}</span
                >
                <span class="text-muted-foreground"
                    >{{ answeredCount }} terjawab</span
                >
            </div>
            <div
                class="h-1.5 overflow-hidden rounded-full bg-secondary"
                role="progressbar"
                :aria-valuenow="answeredCount"
                :aria-valuemin="0"
                :aria-valuemax="total"
                aria-label="Jumlah soal terjawab"
            >
                <div
                    class="h-full bg-primary transition-[width]"
                    :style="{
                        width: `${total ? (answeredCount / total) * 100 : 0}%`,
                    }"
                />
            </div>
        </div>
    </div>

    <Card class="border-0 bg-transparent shadow-none">
        <CardContent class="space-y-5 px-0">
            <p class="font-display text-xl leading-8 font-medium">
                {{ question.text }}
            </p>

            <div class="space-y-2">
                <button
                    v-for="option in question.options"
                    :key="option.index"
                    type="button"
                    :disabled="saving"
                    :class="
                        cn(
                            'flex w-full items-start gap-3 rounded-md border bg-card p-4 text-left transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2',
                            question.chosen_index === option.index
                                ? 'border-primary bg-primary/5'
                                : 'hover:border-primary/40 hover:bg-secondary/50',
                        )
                    "
                    @click="choose(option.index)"
                >
                    <span
                        :class="
                            cn(
                                'grid size-7 shrink-0 place-items-center rounded-md border text-xs font-semibold',
                                question.chosen_index === option.index
                                    ? 'border-primary bg-primary text-primary-foreground'
                                    : 'text-muted-foreground',
                            )
                        "
                        >{{ option.label }}</span
                    >
                    <span class="text-sm">{{ option.text }}</span>
                </button>
            </div>

            <Alert v-if="remaining <= 0">
                <AlertDescription>
                    Waktu habis. Jawaban sedang dikumpulkan...
                </AlertDescription>
            </Alert>
        </CardContent>
    </Card>

    <div class="flex flex-wrap items-center gap-1">
        <Button as-child variant="outline" size="sm">
            <Link :href="pageUrl(Math.max(1, question.position - 1))">
                <ChevronLeft class="size-4" />
            </Link>
        </Button>

        <Link
            v-for="item in navigation"
            :key="item.position"
            :href="pageUrl(item.position)"
            :class="
                cn(
                    'grid size-9 place-items-center rounded-md border text-xs font-medium tabular-nums transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2',
                    item.position === question.position
                        ? 'border-primary bg-primary text-primary-foreground'
                        : item.answered
                          ? 'border-primary/50 bg-primary/10 text-primary'
                          : 'bg-background hover:bg-secondary',
                )
            "
        >
            {{ item.position }}
        </Link>

        <Button as-child variant="outline" size="sm">
            <Link :href="pageUrl(Math.min(total, question.position + 1))">
                <ChevronRight class="size-4" />
            </Link>
        </Button>
    </div>

    <Dialog v-model:open="confirmOpen">
        <DialogTrigger as-child>
            <Button class="w-full sm:w-auto">
                <CheckCircle2 class="size-4" />
                Kumpulkan Ujian
            </Button>
        </DialogTrigger>
        <DialogContent>
            <DialogTitle>Kumpulkan ujian sekarang?</DialogTitle>
            <DialogDescription>
                {{ answeredCount }} dari {{ total }} soal terjawab. Setelah
                dikumpulkan, jawaban tidak dapat diubah.
            </DialogDescription>
            <DialogFooter>
                <Button variant="outline" @click="confirmOpen = false">
                    Periksa lagi
                </Button>
                <Button :disabled="submitting" @click="submit">
                    Ya, kumpulkan
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
