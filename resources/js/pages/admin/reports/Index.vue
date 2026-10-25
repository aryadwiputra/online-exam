<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Download } from '@lucide/vue';
import type { Paginated } from '@/types';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import PageHeader from '@/components/PageHeader.vue';
import Pagination from '@/components/Pagination.vue';
import EmptyState from '@/components/EmptyState.vue';
import reports from '@/routes/admin/reports';

interface Attempt {
    id: number;
    score: string | null;
    total_correct: number;
    started_at: string;
    submitted_at: string | null;
    student: {
        name: string;
        nisn: string;
        classroom: { title: string } | null;
    };
    exam_session: { title: string; exam: { title: string } | null };
}

const props = defineProps<{
    attempts: Paginated<Attempt>;
    sessions: {
        id: number;
        title: string;
        start_time: string;
        exam: { title: string } | null;
    }[];
    filters: { session: number | null; search: string };
    summary: {
        total: number;
        average: number | null;
        highest: number | null;
        lowest: number | null;
    };
}>();

const search = ref(props.filters.search);
const session = ref(
    props.filters.session ? String(props.filters.session) : 'all',
);

const query = () => ({
    session: session.value === 'all' ? undefined : session.value,
    search: search.value || undefined,
});

function reload(): void {
    router.get(reports.index.url(), query(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

let searchTimeout: ReturnType<typeof setTimeout> | null = null;

watch(search, () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }

    searchTimeout = setTimeout(reload, 300);
});
watch(session, reload);
</script>

<template>
    <Head title="Laporan" />

    <PageHeader title="Laporan Nilai" description="Rekap hasil ujian peserta.">
        <Button as-child variant="outline">
            <a :href="reports.export.url({ query: query() })">
                <Download class="size-4" />
                Ekspor CSV
            </a>
        </Button>
    </PageHeader>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <Card class="py-4">
            <CardContent>
                <p class="text-sm text-muted-foreground">Pengerjaan selesai</p>
                <p class="text-2xl font-semibold tabular-nums">
                    {{ summary.total }}
                </p>
            </CardContent>
        </Card>
        <Card class="py-4">
            <CardContent>
                <p class="text-sm text-muted-foreground">Rata-rata</p>
                <p class="text-2xl font-semibold tabular-nums">
                    {{ summary.average ?? '—' }}
                </p>
            </CardContent>
        </Card>
        <Card class="py-4">
            <CardContent>
                <p class="text-sm text-muted-foreground">Tertinggi</p>
                <p class="text-2xl font-semibold tabular-nums">
                    {{ summary.highest ?? '—' }}
                </p>
            </CardContent>
        </Card>
        <Card class="py-4">
            <CardContent>
                <p class="text-sm text-muted-foreground">Terendah</p>
                <p class="text-2xl font-semibold tabular-nums">
                    {{ summary.lowest ?? '—' }}
                </p>
            </CardContent>
        </Card>
    </div>

    <Card>
        <CardContent class="space-y-4">
            <div class="flex flex-wrap gap-2">
                <Input
                    v-model="search"
                    placeholder="Cari nama atau NISN..."
                    class="max-w-xs"
                />
                <Select v-model="session">
                    <SelectTrigger class="w-72">
                        <SelectValue placeholder="Semua sesi" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Semua sesi</SelectItem>
                        <SelectItem
                            v-for="item in sessions"
                            :key="item.id"
                            :value="String(item.id)"
                        >
                            {{ item.exam?.title }} — {{ item.title }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <EmptyState
                v-if="attempts.data.length === 0"
                title="Belum ada data"
                description="Belum ada peserta yang mengerjakan ujian pada filter ini."
            />

            <template v-else>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Siswa</TableHead>
                            <TableHead>Kelas</TableHead>
                            <TableHead>Ujian</TableHead>
                            <TableHead>Sesi</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead class="text-right">Benar</TableHead>
                            <TableHead class="text-right">Nilai</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="attempt in attempts.data"
                            :key="attempt.id"
                        >
                            <TableCell>
                                <p class="font-medium">
                                    {{ attempt.student.name }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{ attempt.student.nisn }}
                                </p>
                            </TableCell>
                            <TableCell>{{
                                attempt.student.classroom?.title ?? '—'
                            }}</TableCell>
                            <TableCell>
                                {{ attempt.exam_session.exam?.title }}
                            </TableCell>
                            <TableCell>{{
                                attempt.exam_session.title
                            }}</TableCell>
                            <TableCell>
                                <Badge
                                    :variant="
                                        attempt.submitted_at
                                            ? 'success'
                                            : 'warning'
                                    "
                                >
                                    {{
                                        attempt.submitted_at
                                            ? 'Selesai'
                                            : 'Berlangsung'
                                    }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-right tabular-nums">
                                {{ attempt.total_correct }}
                            </TableCell>
                            <TableCell
                                class="text-right font-medium tabular-nums"
                            >
                                {{ attempt.score ?? '—' }}
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <Pagination
                    :links="attempts.links"
                    :from="attempts.from"
                    :to="attempts.to"
                    :total="attempts.total"
                />
            </template>
        </CardContent>
    </Card>
</template>
