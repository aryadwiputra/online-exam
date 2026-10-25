<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Trash2 } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';

const props = defineProps<{
    href: string;
    title?: string;
    description?: string;
}>();

const open = ref(false);

function destroy(): void {
    router.delete(props.href, {
        preserveScroll: true,
        onFinish: () => {
            open.value = false;
        },
    });
}
</script>

<template>
    <Dialog v-model:open="open">
        <DialogTrigger as-child>
            <Button variant="ghost" size="icon" aria-label="Hapus">
                <Trash2 class="size-4 text-destructive" />
            </Button>
        </DialogTrigger>
        <DialogContent>
            <DialogTitle>{{ title ?? 'Hapus data ini?' }}</DialogTitle>
            <DialogDescription>
                {{
                    description ??
                    'Tindakan ini tidak dapat dibatalkan. Data terkait juga akan terhapus.'
                }}
            </DialogDescription>
            <DialogFooter>
                <Button variant="outline" @click="open = false">Batal</Button>
                <Button variant="destructive" @click="destroy">Hapus</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
