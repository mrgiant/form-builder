<template>
    <GlDeleteConfirmationModal
        :isOpen="open_delete_modal"
        @confirm-delete="deleteAction"
        @cancel-delete="open_delete_modal = false"
    />

    <div
        v-if="row"
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
            class="absolute z-10 mt-6 origin-top-right lg:right-0 bg-white divide-y divide-gray-100 rounded-lg shadow w-44 dark:bg-gray-700 dark:divide-gray-600"
        >
            <ul class="py-2 text-sm text-gray-700 dark:text-gray-200">
                <li>
                    <a :href="`/admin/forms/${row.form_id}/responses/${row.id}/single`"
                        class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
                        <i class="mr-1 fa-solid fa-eye opacity-80"></i>
                        View Response
                    </a>
                </li>
                <li>
                    <a :href="`/admin/forms/${row.form_id}/responses/${row.id}/single/form`"
                        class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
                        <i class="mr-1 fa-solid fa-file-lines opacity-80"></i>
                        View Response as Form
                    </a>
                </li>
            </ul>
            <ul v-if="can('delete_form')" class="py-2 text-sm">
                <li>
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

const emit = defineEmits(['deleteAction']);

const open              = ref(false);
const open_delete_modal = ref(false);

const deleteAction = () => {
    axios.delete(`${props.xprops.route}/${props.row.id}`)
        .then(() => {
            open_delete_modal.value = false;
            emit('deleteAction');
            GlToast.methods.add({ message: 'Response deleted successfully.', type: 'success', duration: 4000 });
        })
        .catch((error) => { console.log(error); });
};
</script>
