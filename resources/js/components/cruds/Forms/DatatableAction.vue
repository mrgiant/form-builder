<template>
    <GlDeleteConfirmationModal
        :isOpen="open_delete_modal"
        @confirm-delete="deleteAction"
        @cancel-delete="open_delete_modal = false"
    />

    <GlDeleteConfirmationModal
        :isOpen="open_remove_responses_modal"
        @confirm-delete="removeAllResponses"
        @cancel-delete="open_remove_responses_modal = false"
    />

    <div
        v-if="row && (can('edit_form') || can('delete_form') || can('view_form_form') || can('view_responses_form') || can('view_charts_form') || can('export_responses_form') || can('access_question_form'))"
        v-click-outside="() => open = false"
        class="flex lg:justify-center"
    >
        <button
            @click="open = !open"
            class="inline-flex items-center p-2 text-sm font-medium text-center text-gray-900 bg-white rounded-lg hover:bg-gray-100 focus:ring-4 focus:outline-none dark:text-white focus:ring-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700 dark:focus:ring-gray-600"
            type="button"
        >
            <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 4 15">
                <path d="M3.5 1.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm0 6.041a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm0 5.959a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
            </svg>
        </button>

        <div
            v-show="open"
            class="absolute z-10 mt-6 origin-top-right lg:right-0 bg-white divide-y divide-gray-100 rounded-lg shadow w-56 dark:bg-gray-700 dark:divide-gray-600"
        >
            <!-- View Group -->
            <ul v-if="can('view_form_form')" class="py-2 text-sm text-gray-700 dark:text-gray-200">
                <li>
                    <a :href="`/admin/forms/${row.id}/view`" target="_blank"
                        class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
                        <i class="mr-1 fa-solid fa-eye opacity-80"></i>
                        View inside Form
                    </a>
                </li>
                <li>
                    <a :href="`/forms/${row.slug}`" target="_blank"
                        class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
                        <i class="mr-1 fa-solid fa-globe opacity-80"></i>
                        View Online Form
                    </a>
                </li>
            </ul>

            <!-- Edit / Manage Group -->
            <ul v-if="can('edit_form') || can('access_question_form')" class="py-2 text-sm text-gray-700 dark:text-gray-200">
                <li v-if="can('edit_form')">
                    <a href="#" @click.prevent="emit('editAction', row)"
                        class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
                        <i class="mr-1 fa-solid fa-pen-to-square opacity-80"></i>
                        Edit
                    </a>
                </li>
                <li v-if="can('access_question_form')">
                    <a :href="`/admin/forms/${row.id}/questions`"
                        class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
                        <i class="mr-1 fa-solid fa-list-check opacity-80"></i>
                        Manage Questions
                    </a>
                </li>
            </ul>

            <!-- Responses Group -->
            <ul v-if="can('view_responses_form') || can('view_charts_form') || can('export_responses_form')" class="py-2 text-sm text-gray-700 dark:text-gray-200">
                <li v-if="can('view_responses_form')">
                    <a :href="`/admin/forms/${row.id}/responses`"
                        class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
                        <i class="mr-1 fa-solid fa-inbox opacity-80"></i>
                        View Responses
                    </a>
                </li>
                <li v-if="can('view_charts_form')">
                    <a :href="`/admin/forms/${row.id}/responses/charts`"
                        class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
                        <i class="mr-1 fa-solid fa-chart-bar opacity-80"></i>
                        View Charts
                    </a>
                </li>
                <li v-if="can('export_responses_form')">
                    <a :href="`/admin/forms/${row.id}/responses/ExportToExcel`"
                        class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
                        <i class="mr-1 fa-solid fa-file-excel opacity-80"></i>
                        Export To Excel
                    </a>
                </li>
                <li v-if="can('export_responses_form')">
                    <a :href="`/admin/forms/${row.id}/responses/ExportToPDF`"
                        class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
                        <i class="mr-1 fa-solid fa-file-pdf opacity-80"></i>
                        Export To PDF
                    </a>
                </li>
            </ul>

            <!-- Danger Group -->
            <ul class="py-2 text-sm">
                <li v-if="can('delete_form')">
                    <a href="#" @click.prevent="open_remove_responses_modal = true"
                        class="block px-4 py-2 text-orange-600 hover:text-white hover:bg-orange-600 dark:text-orange-400 dark:hover:text-white dark:hover:bg-orange-600">
                        <i class="mr-1 fa-solid fa-trash-can opacity-80"></i>
                        Remove All Responses
                    </a>
                </li>
                <li v-if="can('delete_form')">
                    <a href="#" @click.prevent="open_delete_modal = true"
                        class="block px-4 py-2 text-red-600 hover:text-white hover:bg-red-600 dark:text-red-500 dark:hover:text-white dark:hover:bg-red-600">
                        <i class="mr-1 fa-solid fa-trash-can opacity-80"></i>
                        Delete
                    </a>
                </li>
            </ul>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { GlDeleteConfirmationModal, GlToast } from 'golden-logic-ui';

const props = defineProps({
    field:     { type: String,          default: '' },
    route_url: { type: String,          default: '' },
    row:       { type: [Array, Object], default: () => ({}) },
    xprops:    { type: Object,          default: () => ({}) },
    tdProps:   { type: Object,          default: () => ({}) },
});

const emit = defineEmits(['editAction', 'deleteAction']);

const open                      = ref(false);
const open_delete_modal         = ref(false);
const open_remove_responses_modal = ref(false);

const deleteAction = () => {
    axios.delete(`${props.xprops.route}/${props.row.id}`)
        .then(() => {
            open_delete_modal.value = false;
            emit('deleteAction');
            GlToast.methods.add({ message: 'Form deleted successfully.', type: 'success', duration: 4000 });
        })
        .catch((error) => { console.log(error); });
};

const removeAllResponses = () => {
    axios.delete(`/admin/forms/${props.row.id}/responses/RemoveAllResponses`)
        .then(() => {
            open_remove_responses_modal.value = false;
            GlToast.methods.add({ message: 'All responses removed successfully.', type: 'success', duration: 4000 });
        })
        .catch((error) => { console.log(error); });
};
</script>
