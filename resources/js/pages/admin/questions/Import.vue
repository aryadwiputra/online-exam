<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Download } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import PageHeader from '@/components/PageHeader.vue';
import FlashMessages from '@/components/FlashMessages.vue';
import exams from '@/routes/admin/exams';
import examQuestions from '@/routes/admin/exams/questions';

const props = defineProps<{
    exam: { id: number; title: string };
}>();

const form = useForm<{ file: File | null }>({ file: null });

function submit(): void {
    form.post(examQuestions.import.store.url(props.exam.id), {
        forceFormData: true,
    });
}
</script>

<template>
    <Head title="Impor Soal" />

    <PageHeader title="Impor Soal" :description="exam.title">
        <Button as-child variant="outline">
            <Link :href="exams.show.url(exam.id)">
                <ArrowLeft class="size-4" />
                Kembali
            </Link>
        </Button>
    </PageHeader>

    <FlashMessages />

    <div class="grid gap-4 lg:grid-cols-2">
        <Card>
            <CardHeader>
                <CardTitle>Unggah file CSV</CardTitle>
                <CardDescription>
                    Satu baris mewakili satu soal pilihan ganda.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <form class="space-y-4" @submit.prevent="submit">
                    <div class="space-y-2">
                        <Label for="file">File CSV</Label>
                        <input
                            id="file"
                            type="file"
                            accept=".csv,text/csv"
                            class="flex h-9 w-full min-w-0 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none file:mr-3 file:border-0 file:bg-transparent file:text-sm file:font-medium"
                            @input="
                                form.file =
                                    ($event.target as HTMLInputElement)
                                        .files?.[0] ?? null
                            "
                        />
                        <p
                            v-if="form.errors.file"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.file }}
                        </p>
                    </div>

                    <Button type="submit" :disabled="form.processing">
                        Impor
                    </Button>
                </form>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>Format file</CardTitle>
                <CardDescription>
                    Kolom answer berisi nomor pilihan benar (1–5). Pilihan
                    kosong diabaikan.
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-3">
                <pre
                    class="overflow-x-auto rounded-md bg-muted p-3 text-xs leading-relaxed"
                >
question,option_1,option_2,option_3,option_4,option_5,answer
Ibu kota Indonesia adalah?,Jakarta,Bandung,Surabaya,Medan,,1</pre>
                <Button as-child variant="outline" size="sm">
                    <a :href="examQuestions.import.template.url(exam.id)">
                        <Download class="size-4" />
                        Unduh template
                    </a>
                </Button>
            </CardContent>
        </Card>
    </div>
</template>
