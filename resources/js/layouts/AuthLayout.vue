<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    title: string;
    description?: string;
}>();

const audience = computed(() =>
    props.title.includes('Admin') ? 'administrator' : 'peserta',
);
</script>

<template>
    <main
        class="min-h-screen bg-background lg:grid lg:grid-cols-[minmax(0,1fr)_minmax(24rem,0.82fr)]"
    >
        <section
            class="relative hidden min-h-screen flex-col justify-between overflow-hidden bg-sidebar p-10 text-sidebar-foreground lg:flex xl:p-16"
        >
            <a
                :href="
                    audience === 'administrator' ? '/login' : '/student/login'
                "
                class="flex items-center gap-3"
                aria-label="Sistem Ujian Online"
            >
                <span
                    class="grid size-10 place-items-center rounded-md bg-primary font-display text-xl font-bold text-primary-foreground"
                    >E</span
                >
                <span class="font-display text-lg font-semibold tracking-tight"
                    >Ujian Online</span
                >
            </a>

            <div class="relative max-w-xl space-y-6">
                <p class="text-sm font-medium text-sidebar-foreground/70">
                    {{
                        audience === 'administrator'
                            ? 'Ruang kerja administrator'
                            : 'Ruang ujian peserta'
                    }}
                </p>
                <h2
                    class="max-w-lg font-display text-4xl leading-tight font-semibold tracking-tight xl:text-5xl"
                >
                    {{
                        audience === 'administrator'
                            ? 'Kelola ujian dengan lebih terarah.'
                            : 'Fokus pada ujian Anda.'
                    }}
                </h2>
                <p
                    class="max-w-md text-base leading-7 text-sidebar-foreground/75"
                >
                    {{
                        audience === 'administrator'
                            ? 'Atur kelas, materi, peserta, dan sesi ujian dari satu tempat.'
                            : 'Temukan jadwal ujian, kerjakan setiap soal, dan pantau hasil Anda.'
                    }}
                </p>
            </div>

            <p class="text-sm text-sidebar-foreground/60">
                Sistem Ujian Online
            </p>
        </section>

        <section
            class="flex min-h-screen items-center justify-center px-5 py-10 sm:px-8"
        >
            <div class="w-full max-w-md space-y-7">
                <div class="space-y-2">
                    <span
                        class="mb-5 grid size-10 place-items-center rounded-md bg-primary font-display text-lg font-bold text-primary-foreground lg:hidden"
                        >E</span
                    >
                    <p class="text-sm font-medium text-primary">
                        {{
                            audience === 'administrator'
                                ? 'Portal administrator'
                                : 'Portal peserta'
                        }}
                    </p>
                    <h1
                        class="font-display text-3xl font-semibold tracking-tight"
                    >
                        {{ title }}
                    </h1>
                    <p
                        v-if="description"
                        class="text-sm leading-6 text-muted-foreground"
                    >
                        {{ description }}
                    </p>
                </div>
                <slot />
                <p class="text-center text-xs text-muted-foreground">
                    Akses khusus {{ audience }} ujian
                </p>
            </div>
        </section>
    </main>
</template>
