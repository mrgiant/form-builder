<template>
    <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
        <!-- Card Header -->
        <div class="flex items-start justify-between p-3 bg-gray-50 dark:bg-gray-800 gap-3">
            <div class="flex items-start gap-2 flex-1 min-w-0">
                <i class="fa-solid fa-grip-vertical drag-handle cursor-grab text-gray-400 mt-1 flex-shrink-0"></i>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-medium text-gray-800 dark:text-gray-200">{{ question.order }}. {{ question.label }}</span>
                        <span class="text-xs bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 px-2 py-0.5 rounded">{{ question.question_type }}</span>
                        <span v-if="question.required" class="text-xs bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 px-2 py-0.5 rounded">Required</span>
                        <span v-if="question.unique" class="text-xs bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 px-2 py-0.5 rounded">Unique</span>
                    </div>
                    <p v-if="question.description" class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ question.description }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <gl-button @click="$emit('edit', question.id)" tag="button" button_type="warning" icon="fa-solid fa-pen">Edit</gl-button>
                <gl-button @click="$emit('delete', question.id)" tag="button" button_type="danger" icon="fa-solid fa-trash">Delete</gl-button>
            </div>
        </div>
        <!-- Preview -->
        <div class="p-3 bg-white dark:bg-gray-900">
            <input v-if="['Short answer', 'Number', 'Email'].includes(question.question_type)"
                type="text" disabled
                class="w-full border border-gray-200 dark:border-gray-700 rounded px-3 py-2 text-sm text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-800 cursor-not-allowed"
                :placeholder="question.question_type" />
            <textarea v-if="question.question_type === 'Paragraph'" disabled
                class="w-full border border-gray-200 dark:border-gray-700 rounded px-3 py-2 text-sm text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-800 cursor-not-allowed"
                rows="2" :placeholder="question.question_type"></textarea>
            <select v-if="question.question_type === 'Drop-down'" disabled
                class="w-full border border-gray-200 dark:border-gray-700 rounded px-3 py-2 text-sm text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-800 cursor-not-allowed">
                <option v-for="opt in (question.options || [])" :key="opt">{{ opt }}</option>
            </select>
            <div v-if="question.question_type === 'Checkboxes'" class="space-y-1">
                <label v-for="opt in (question.options || [])" :key="opt" class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 cursor-not-allowed">
                    <input type="checkbox" disabled class="cursor-not-allowed" /> {{ opt }}
                </label>
            </div>
            <div v-if="question.question_type === 'Multiple choice'" class="space-y-1">
                <label v-for="opt in (question.options || [])" :key="opt" class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 cursor-not-allowed">
                    <input type="radio" disabled class="cursor-not-allowed" /> {{ opt }}
                </label>
            </div>
            <div v-if="question.question_type === 'File upload'" class="flex items-center gap-2 text-sm text-gray-400 dark:text-gray-500 py-1">
                <i class="fa-solid fa-file-arrow-up"></i>
                <span>File upload</span>
                <span v-if="question.maximum_file_size" class="text-xs bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded">max {{ question.maximum_file_size }}</span>
                <span v-if="question.allow_only_specific_file_types" class="text-xs bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded">{{ question.allow_only_specific_file_types }}</span>
            </div>
            <input v-if="question.question_type === 'Date'" type="date" disabled
                class="border border-gray-200 dark:border-gray-700 rounded px-3 py-2 text-sm text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-800 cursor-not-allowed" />
            <input v-if="question.question_type === 'Time'" type="time" disabled
                class="border border-gray-200 dark:border-gray-700 rounded px-3 py-2 text-sm text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-800 cursor-not-allowed" />
        </div>
    </div>
</template>

<script>
export default {
    props: ['question'],
    emits: ['edit', 'delete'],
};
</script>
