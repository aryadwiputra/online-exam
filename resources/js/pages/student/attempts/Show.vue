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

    <div class="space-y-1">
        <p class="text-sm text-muted-foreground">{{ exam.session }}</p>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-xl font-semibold tracking-tight">
                {{ exam.title }}
            </h1>
            <div
                :class="
                    cn(
                        'flex items-center gap-2 rounded-lg border px-3 py-1.5 font-mono text-lg font-semibold tabular-nums',
                        isUrgent
                            ? 'border-destructive/40 bg-destructive/10 text-destructive'
                            : '',
                    )
                "
            >
                <Clock class="size-4" />
                {{ formattedTime }}
            </div>
        </div>
    </div>

    <div
        class="flex items-center justify-between text-sm text-muted-foreground"
    >
        <span>Soal {{ question.position }} dari {{ total }}</span>
        <span>{{ answeredCount }} terjawab</span>
    </div>

    <Card>
        <CardContent class="space-y-4">
            <p class="text-base font-medium">{{ question.text }}</p>

            <div class="space-y-2">
                <button
                    v-for="option in question.options"
                    :key="option.index"
                    type="button"
                    :disabled="saving"
                    :class="
                        cn(
                            'flex w-full items-start gap-3 rounded-lg border p-3 text-left transition-colors',
                            question.chosen_index === option.index
                                ? 'border-primary bg-accent'
                                : 'hover:bg-muted/60',
                        )
                    "
                    @click="choose(option.index)"
                >
                    <span
                        :class="
                            cn(
                                'grid size-6 shrink-0 place-items-center rounded-full border text-xs font-semibold',
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
                    'grid size-8 place-items-center rounded-md border text-xs font-medium transition-colors',
                    item.position === question.position
                        ? 'border-primary bg-primary text-primary-foreground'
                        : item.answered
                          ? 'border-emerald-300 bg-emerald-50 text-emerald-700'
                          : 'bg-background hover:bg-accent',
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
