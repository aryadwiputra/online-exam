<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, UserMinus } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import PageHeader from '@/components/PageHeader.vue';
import EmptyState from '@/components/EmptyState.vue';
import FlashMessages from '@/components/FlashMessages.vue';
import examSessions from '@/routes/admin/exam-sessions';
import enrollments from '@/routes/admin/exam-sessions/enrollments';

interface Enrolled {
    id: number;
    name: string;
    nisn: string;
    attempted: boolean;
}

interface Available {
    id: number;
    name: string;
    nisn: string;
}

const props = defineProps<{
    session: {
        id: number;
        title: string;
        exam_title: string;
        classroom_title: string;
        start_time: string;
        end_time: string;
    };
    enrolled: Enrolled[];
    available: Available[];
}>();

const form = useForm<{ student_ids: number[] }>({ student_ids: [] });

function toggle(id: number, checked: boolean): void {
    if (checked) {
        form.student_ids = [...form.student_ids, id];

        return;
    }

    form.student_ids = form.student_ids.filter((studentId) => studentId !== id);
}

function submit(): void {
    form.post(enrollments.store.url(props.session.id), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('student_ids');
        },
    });
}
</script>

<template>
    <Head title="Peserta Sesi" />

    <PageHeader :title="session.title" :description="session.exam_title">
        <Button as-child variant="outline">
            <Link :href="examSessions.index.url()">
                <ArrowLeft class="size-4" />
                Kembali
            </Link>
        </Button>
    </PageHeader>

    <FlashMessages />

    <Card class="py-4">
        <CardContent class="flex flex-wrap gap-x-8 gap-y-2 text-sm">
            <p>
                <span class="text-muted-foreground">Kelas:</span>
                {{ session.classroom_title }}
            </p>
            <p>
                <span class="text-muted-foreground">Jadwal:</span>
                {{ session.start_time }} — {{ session.end_time }}
            </p>
        </CardContent>
    </Card>

    <div class="grid gap-4 lg:grid-cols-2">
        <Card>
            <CardHeader>
                <CardTitle>Peserta terdaftar</CardTitle>
                <CardDescription>{{ enrolled.length }} peserta</CardDescription>
            </CardHeader>
            <CardContent>
                <EmptyState
                    v-if="enrolled.length === 0"
                    title="Belum ada peserta"
                    description="Tambahkan peserta dari daftar siswa pada kelas ujian."
                />
                <Table v-else>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Nama</TableHead>
                            <TableHead>NISN</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead class="text-right">Aksi</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="student in enrolled" :key="student.id">
                            <TableCell class="font-medium">{{
                                student.name
                            }}</TableCell>
                            <TableCell class="font-mono text-xs">{{
                                student.nisn
                            }}</TableCell>
                            <TableCell>
                                <Badge
                                    :variant="
                                        student.attempted
                                            ? 'warning'
                                            : 'secondary'
                                    "
                                >
                                    {{
                                        student.attempted
                                            ? 'Sudah mulai'
                                            : 'Belum mulai'
                                    }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-right">
                                <Button
                                    v-if="!student.attempted"
                                    as-child
                                    variant="ghost"
                                    size="icon"
                                >
                                    <Link
                                        :href="
                                            enrollments.destroy.url({
                                                examSession: session.id,
                                                student: student.id,
                                            })
                                        "
                                        method="delete"
                                        as="button"
                                        aria-label="Keluarkan peserta"
                                    >
                                        <UserMinus
                                            class="size-4 text-destructive"
                                        />
                                    </Link>
                                </Button>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>Tambah peserta</CardTitle>
                <CardDescription>
                    Siswa dari kelas {{ session.classroom_title }} yang belum
                    terdaftar.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <EmptyState
                    v-if="available.length === 0"
                    title="Semua siswa sudah terdaftar"
                    description="Tidak ada siswa lain pada kelas ini."
                />
                <form v-else class="space-y-3" @submit.prevent="submit">
                    <label
                        v-for="student in available"
                        :key="student.id"
                        class="flex cursor-pointer items-center gap-3 rounded-md border p-3"
                    >
                        <Checkbox
                            :model-value="form.student_ids.includes(student.id)"
                            @update:model-value="
                                toggle(student.id, $event === true)
                            "
                        />
                        <span class="flex-1 text-sm font-medium">{{
                            student.name
                        }}</span>
                        <span class="font-mono text-xs text-muted-foreground">{{
                            student.nisn
                        }}</span>
                    </label>

                    <p
                        v-if="form.errors.student_ids"
                        class="text-sm text-destructive"
                    >
                        {{ form.errors.student_ids }}
                    </p>

                    <Button
                        type="submit"
                        :disabled="
                            form.processing || form.student_ids.length === 0
                        "
                    >
                        Tambahkan ({{ form.student_ids.length }})
                    </Button>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
