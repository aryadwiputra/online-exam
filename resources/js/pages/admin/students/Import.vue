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
import students from '@/routes/admin/students';

const form = useForm<{ file: File | null }>({ file: null });

function submit(): void {
    form.post(students.import.store.url(), {
        forceFormData: true,
    });
}
</script>

<template>
    <Head title="Impor Siswa" />

    <PageHeader
        title="Impor Siswa"
        description="Tambahkan siswa secara massal."
    >
        <Button as-child variant="outline">
            <Link :href="students.index.url()">
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
                    Gunakan header: nisn, name, classroom, gender, password.
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
                    Nama kelas harus sama persis dengan data kelas.
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-3">
                <pre
                    class="overflow-x-auto rounded-md bg-muted p-3 text-xs leading-relaxed"
                >
nisn,name,classroom,gender,password
0012345678,Budi Santoso,Kelas 10A,L,password123
0012345679,Siti Aminah,Kelas 10A,P,password123</pre>
                <p class="text-sm text-muted-foreground">
                    Kolom gender diisi L/P, password minimal 6 karakter. Baris
                    yang gagal akan dilaporkan setelah proses selesai.
                </p>
                <Button as-child variant="outline" size="sm">
                    <a href="/admin/students/import/template">
                        <Download class="size-4" />
                        Unduh template
                    </a>
                </Button>
            </CardContent>
        </Card>
    </div>
</template>
