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

interface Classroom {
    id: number;
    title: string;
}

interface StudentData {
    id?: number;
    classroom_id: number | null;
    nisn: string;
    name: string;
    gender: 'L' | 'P';
}

const props = defineProps<{
    action: string;
    method?: 'post' | 'put';
    classrooms: Classroom[];
    student?: StudentData;
    cancelHref: string;
}>();

const form = useForm({
    classroom_id: props.student?.classroom_id ?? null,
    nisn: props.student?.nisn ?? '',
    name: props.student?.name ?? '',
    gender: props.student?.gender ?? 'L',
    password: '',
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
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="name">Nama Siswa</Label>
                        <Input id="name" v-model="form.name" required />
                        <p
                            v-if="form.errors.name"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="nisn">NISN</Label>
                        <Input
                            id="nisn"
                            v-model="form.nisn"
                            inputmode="numeric"
                            required
                        />
                        <p
                            v-if="form.errors.nisn"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.nisn }}
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

                    <div class="space-y-2">
                        <Label>Jenis Kelamin</Label>
                        <Select v-model="form.gender">
                            <SelectTrigger>
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="L">Laki-laki</SelectItem>
                                <SelectItem value="P">Perempuan</SelectItem>
                            </SelectContent>
                        </Select>
                        <p
                            v-if="form.errors.gender"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.gender }}
                        </p>
                    </div>
                </div>

                <div class="space-y-2">
                    <Label for="password">
                        Kata Sandi
                        <span
                            v-if="student"
                            class="text-xs text-muted-foreground"
                            >(kosongkan jika tidak diubah)</span
                        >
                    </Label>
                    <Input
                        id="password"
                        v-model="form.password"
                        type="password"
                        autocomplete="new-password"
                        :required="!student"
                    />
                    <p
                        v-if="form.errors.password"
                        class="text-sm text-destructive"
                    >
                        {{ form.errors.password }}
                    </p>
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
