<template>
    <!-- Action Modal -->
    <modal class="ApprovalActionModal" :is_open="isOpenAction" :is_loading="false"
        @closeModal="isOpenAction = false"
        :title="actionType === 'approve' ? 'Approve response' : 'Reject response'" max_width="max-w-lg">
        <template v-slot:body>
            <p class="text-sm text-gray-600 dark:text-gray-300 mb-3">
                {{ actionType === 'approve' ? 'Approve' : 'Reject' }} the response to
                <strong>{{ activeItem?.form_name }}</strong>.
            </p>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Comment (optional)</label>
            <textarea v-model="comment" rows="3"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
        </template>
        <template v-slot:buttons>
            <gl-button @click="confirmAction()" :is_loading="isActing" tag="button"
                :button_type="actionType === 'approve' ? 'primary' : 'red'"
                :icon="actionType === 'approve' ? 'fa-solid fa-check' : 'fa-solid fa-xmark'">
                {{ actionType === 'approve' ? 'Approve' : 'Reject' }}
            </gl-button>
        </template>
    </modal>

    <Card>
        <template v-slot:header>
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-inbox text-primary"></i>
                <span>My Approvals</span>
                <span v-if="items.length" class="inline-flex items-center justify-center min-w-5 h-5 px-1.5 rounded-full bg-primary text-white text-xs">{{ items.length }}</span>
            </div>
        </template>
        <template v-slot:body>
            <div v-if="isLoading" class="flex justify-center py-8">
                <i class="fa-solid fa-spinner fa-spin text-2xl text-gray-400"></i>
            </div>

            <div v-else-if="items.length === 0" class="text-center py-10 text-gray-400 dark:text-gray-500">
                <i class="fa-solid fa-circle-check text-3xl mb-2"></i>
                <p>Nothing is pending your approval.</p>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 dark:text-gray-400 border-b border-stroke dark:border-white/10">
                            <th class="py-2 pr-4">Form</th>
                            <th class="py-2 pr-4">Step</th>
                            <th class="py-2 pr-4">Submitted</th>
                            <th class="py-2 pr-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in items" :key="item.id" class="border-b border-stroke dark:border-white/10">
                            <td class="py-3 pr-4 font-medium text-gray-800 dark:text-gray-100">{{ item.form_name }}</td>
                            <td class="py-3 pr-4">
                                <span class="px-2 py-0.5 rounded text-xs bg-primary/10 text-primary">{{ item.step_name }}</span>
                            </td>
                            <td class="py-3 pr-4 text-gray-500 dark:text-gray-400">{{ item.submitted_at }}</td>
                            <td class="py-3 pr-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a :href="`/admin/forms/${item.form_id}/responses/${item.id}/single`"
                                        class="inline-flex items-center gap-1 px-2 py-1 rounded border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 text-xs">
                                        <i class="fa-solid fa-eye"></i> View
                                    </a>
                                    <button @click="openAction(item, 'approve')"
                                        class="inline-flex items-center gap-1 px-2 py-1 rounded bg-primary text-white hover:opacity-90 text-xs">
                                        <i class="fa-solid fa-check"></i> Approve
                                    </button>
                                    <button @click="openAction(item, 'reject')"
                                        class="inline-flex items-center gap-1 px-2 py-1 rounded bg-red-600 text-white hover:bg-red-700 text-xs">
                                        <i class="fa-solid fa-xmark"></i> Reject
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </template>
    </Card>
</template>

<script>
import { GlToast } from 'golden-logic-ui';

export default {
    data() {
        return {
            items: [],
            isLoading: false,
            isOpenAction: false,
            actionType: 'approve',
            activeItem: null,
            comment: '',
            isActing: false,
        };
    },

    mounted() {
        this.load();
    },

    methods: {
        load() {
            this.isLoading = true;
            axios.get('/admin/approvals/inbox/data')
                .then(res => { this.items = res.data.items || []; this.isLoading = false; })
                .catch(() => { this.isLoading = false; });
        },

        openAction(item, type) {
            this.activeItem = item;
            this.actionType = type;
            this.comment = '';
            this.isOpenAction = true;
        },

        confirmAction() {
            if (!this.activeItem) { return; }
            this.isActing = true;
            const url = `/admin/approvals/${this.activeItem.id}/${this.actionType}`;
            axios.post(url, { comment: this.comment })
                .then(() => {
                    this.isActing = false;
                    this.isOpenAction = false;
                    this.items = this.items.filter(i => i.id !== this.activeItem.id);
                    GlToast.methods.add({ message: `Response ${this.actionType === 'approve' ? 'approved' : 'rejected'}.`, type: 'success', duration: 4000 });
                })
                .catch(() => {
                    this.isActing = false;
                    GlToast.methods.add({ message: 'Action failed. It may already be resolved.', type: 'error', duration: 4000 });
                });
        },
    },
};
</script>
