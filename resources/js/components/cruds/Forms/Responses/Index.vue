<template>
    <GlDeleteConfirmationModal
        :isOpen="open_remove_all_modal"
        @confirm-delete="removeAllResponses"
        @cancel-delete="open_remove_all_modal = false"
    />

    <Card>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <a href="/admin/forms"
                        class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary dark:hover:text-primaryDark">
                        <i class="fa-solid fa-arrow-left mr-1"></i> Back to Forms
                    </a>
                    <span class="text-gray-300 dark:text-gray-600">|</span>
                    <span>{{ form_name }} — Responses</span>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <a :href="`/admin/forms/${form_id}/responses/ExportToExcel`"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg bg-green-600 text-white hover:bg-green-700 transition-colors">
                        <i class="fa-solid fa-file-excel"></i> Export Excel
                    </a>
                    <a :href="`/admin/forms/${form_id}/responses/ExportToPDF`"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg bg-red-600 text-white hover:bg-red-700 transition-colors">
                        <i class="fa-solid fa-file-pdf"></i> Export PDF
                    </a>
                    <button v-if="can('delete_form')" type="button"
                        @click="open_remove_all_modal = true"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg bg-red-600 text-white hover:bg-red-700 transition-colors">
                        <i class="fa-solid fa-trash-can"></i> Remove All
                    </button>
                </div>
            </div>
        </template>
        <template #body>
            <gl-data-table-server-side
                ref="glDataTableRef"
                :columns="columns"
                :xprops="xprops"
                @deleteAction="() => $refs.glDataTableRef.GetItemLists()"
            />
        </template>
    </Card>
</template>

<script>
import { markRaw } from 'vue';
import { GlDeleteConfirmationModal, GlToast } from 'golden-logic-ui';
import DatatableAction from './DatatableAction.vue';
import ApprovalStatusCell from './ApprovalStatusCell.vue';

export default {
    components: { GlDeleteConfirmationModal },

    props: {
        form_id:   { type: [String, Number], required: true },
        form_name: { type: String,           default: 'Form' },
    },

    data() {
        return {
            open_remove_all_modal: false,

            columns: [
                { field_name: 'id',           field_label: 'ID',         sortable: true  },
                { field_name: 'ip',           field_label: 'IP Address',  sortable: true  },
                { field_name: 'created_at_f', field_label: 'Submitted At', sortable: false },
                { field_name: 'approval_status', field_label: 'Approval', tdComp: markRaw(ApprovalStatusCell) },
                { field_name: 'action',       field_label: 'Action',      tdComp: markRaw(DatatableAction) },
            ],

            xprops: {
                route:          `/admin/forms/${this.form_id}/responses`,
                route_get_data: `/admin/forms/${this.form_id}/responses/getResponsesList`,
                permission:     'form',
            },
        };
    },

    methods: {
        removeAllResponses() {
            axios.delete(`/admin/forms/${this.form_id}/responses/RemoveAllResponses`)
                .then(() => {
                    this.open_remove_all_modal = false;
                    this.$refs.glDataTableRef.GetItemLists();
                    GlToast.methods.add({ message: 'All responses removed.', type: 'success', duration: 4000 });
                })
                .catch(err => console.log(err));
        },
    },
};
</script>
