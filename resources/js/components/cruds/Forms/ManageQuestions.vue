<template>
    <!-- Add Question Modal -->
    <modal :class="`${model}Modal`" :is_open="isOpenAddQuestion" :is_loading="isLoadingAdd"
        @closeModal="closeAdd()"
        title="Add Question" max_width="max-w-xl">
        <template v-slot:body>
            <language-selector :field_name="`${model}`" :trans_selector_name="`${model}Selector`"></language-selector>

            <div class="mb-4">
                <text-translate type="text" :is_required="true" :show="false" v-model="form_data.label"
                    v-model:modelValueTranslate="form_data.label_i18n"
                    @keydown="form_data.errors.clear('label')" :error_message="form_data.errors.get('label')"
                    field_name="label" label_name="Label" />
            </div>

            <div class="mb-4">
                <textarea-translate :is_required="false" :show="false" v-model="form_data.description"
                    v-model:modelValueTranslate="form_data.description_i18n"
                    @keydown="form_data.errors.clear('description')" :error_message="form_data.errors.get('description')"
                    field_name="description" label_name="Description" />
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">CSS Styles</label>
                <textarea v-model="form_data.css_styles" rows="2"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
            </div>

            <div class="mb-4">
                <dropdown :options="questionTypes" :is_required="true" field_name="add_question_type"
                    label_name="Question Type" :show="false" placeholder="Please select an option"
                    @selectionChanged="form_data.errors.clear('question_type')"
                    :error_message="form_data.errors.get('question_type')"
                    v-model="form_data.question_type" />
            </div>

            <div class="mb-4" v-if="form_data.question_type && form_data.question_type !== 'Group'">
                <dropdown :options="questions_group" :is_required="false" field_name="add_parent_id"
                    label_name="Group" :show="false" placeholder="Please select an option"
                    v-model="form_data.parent_id" />
            </div>

            <div class="mb-4">
                <dropdown :options="columnSizes" :is_required="true" field_name="add_question_size_col"
                    label_name="Question Size Column" :show="false" placeholder="Please select an option"
                    @selectionChanged="form_data.errors.clear('question_size_col')"
                    :error_message="form_data.errors.get('question_size_col')"
                    v-model="form_data.question_size_col" />
            </div>

            <div class="mb-4" v-if="form_data.question_type === 'File upload'">
                <dropdown :options="['5MB','10MB','20MB','40MB','50MB','60MB','70MB','90MB','100MB']"
                    :is_required="false" field_name="add_maximum_file_size" label_name="Maximum File Size"
                    :show="false" placeholder="Please select an option"
                    v-model="form_data.maximum_file_size" />
            </div>

            <div class="mb-4" v-if="form_data.question_type === 'File upload'">
                <dropdown :options="['Image','Video','Audio','Pdf','Word','Powerpoint','Excel','Text','Zip']"
                    :is_required="false" field_name="add_allow_only_specific_file_types"
                    label_name="Allow only specific file types" :show="false" placeholder="Please select an option"
                    v-model="form_data.allow_only_specific_file_types" />
            </div>

            <div class="mb-4" v-if="hasOptions(form_data.question_type)">
                <hr class="h-px my-4 bg-stroke dark:bg-strokeDark">
                <div v-for="(opt, index) in form_data.options" :key="index" class="mb-3">
                    <div class="flex gap-4">
                        <div class="w-full">
                            <text-input type="text" :is_required="false" :show="false"
                                v-model="form_data.options[index]"
                                :field_name="'add_option_' + index" :label_name="'Option ' + (index + 1)" />
                        </div>
                        <div class="flex items-center justify-end mt-6">
                            <gl-button @click.prevent="form_data.options.splice(index, 1)" tag="button"
                                button_type="red" icon="fa-solid fa-trash-alt" />
                        </div>
                    </div>
                    <hr class="h-px my-4 bg-stroke dark:bg-strokeDark">
                </div>
                <div class="flex justify-end">
                    <gl-button @click.prevent="form_data.options.push('')" tag="button"
                        button_type="primary" icon="fa-solid fa-plus-circle" />
                </div>
            </div>

            <div class="mb-4">
                <toggle-box :is_required="false" :show="false" field_name="add_required" label_name="Required"
                    v-model="form_data.required" />
            </div>
            <div class="mb-4">
                <toggle-box :is_required="false" :show="false" field_name="add_unique" label_name="Unique"
                    v-model="form_data.unique" />
            </div>
        </template>
        <template v-slot:buttons>
            <gl-button @click="saveData()" :is_loading="isLoadingAddButton" tag="button" button_type="primary"
                icon="fa-solid fa-floppy-disk">Save</gl-button>
        </template>
    </modal>

    <!-- Edit Question Modal -->
    <modal :class="`${model}Modal`" :is_open="isOpenUpdateQuestion" :is_loading="isLoadingUpdate"
        @closeModal="isOpenUpdateQuestion = false"
        title="Edit Question" max_width="max-w-xl">
        <template v-slot:body>
            <language-selector :field_name="`${model}`" :trans_selector_name="`${model}Selector`"></language-selector>

            <div class="mb-4">
                <text-translate type="text" :is_required="true" :show="false" v-model="form_data_update.label"
                    v-model:modelValueTranslate="form_data_update.label_i18n"
                    @keydown="form_data_update.errors.clear('label')"
                    :error_message="form_data_update.errors.get('label')"
                    field_name="edit_label" label_name="Label" />
            </div>

            <div class="mb-4">
                <textarea-translate :is_required="false" :show="false" v-model="form_data_update.description"
                    v-model:modelValueTranslate="form_data_update.description_i18n"
                    @keydown="form_data_update.errors.clear('description')"
                    :error_message="form_data_update.errors.get('description')"
                    field_name="edit_description" label_name="Description" />
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">CSS Styles</label>
                <textarea v-model="form_data_update.css_styles" rows="2"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
            </div>

            <div class="mb-4">
                <dropdown :options="questionTypes" :is_required="true" field_name="edit_question_type"
                    label_name="Question Type" :show="false" placeholder="Please select an option"
                    @selectionChanged="form_data_update.errors.clear('question_type')"
                    :error_message="form_data_update.errors.get('question_type')"
                    v-model="form_data_update.question_type" />
            </div>

            <div class="mb-4" v-if="form_data_update.question_type && form_data_update.question_type !== 'Group'">
                <dropdown :options="questions_group" :is_required="false" field_name="edit_parent_id"
                    label_name="Group" :show="false" placeholder="Please select an option"
                    v-model="form_data_update.parent_id" />
            </div>

            <div class="mb-4">
                <dropdown :options="columnSizes" :is_required="true" field_name="edit_question_size_col"
                    label_name="Question Size Column" :show="false" placeholder="Please select an option"
                    @selectionChanged="form_data_update.errors.clear('question_size_col')"
                    :error_message="form_data_update.errors.get('question_size_col')"
                    v-model="form_data_update.question_size_col" />
            </div>

            <div class="mb-4" v-if="form_data_update.question_type === 'File upload'">
                <dropdown :options="['5MB','10MB','20MB','40MB','50MB','60MB','70MB','90MB','100MB']"
                    :is_required="false" field_name="edit_maximum_file_size" label_name="Maximum File Size"
                    :show="false" placeholder="Please select an option"
                    v-model="form_data_update.maximum_file_size" />
            </div>

            <div class="mb-4" v-if="form_data_update.question_type === 'File upload'">
                <dropdown :options="['Image','Video','Audio','Pdf','Word','Powerpoint','Excel','Text','Zip']"
                    :is_required="false" field_name="edit_allow_only_specific_file_types"
                    label_name="Allow only specific file types" :show="false" placeholder="Please select an option"
                    v-model="form_data_update.allow_only_specific_file_types" />
            </div>

            <div class="mb-4" v-if="hasOptions(form_data_update.question_type)">
                <hr class="h-px my-4 bg-stroke dark:bg-strokeDark">
                <div v-for="(opt, index) in form_data_update.options" :key="index" class="mb-3">
                    <div class="flex gap-4">
                        <div class="w-full">
                            <text-input type="text" :is_required="false" :show="false"
                                v-model="form_data_update.options[index]"
                                :field_name="'edit_option_' + index" :label_name="'Option ' + (index + 1)" />
                        </div>
                        <div class="flex items-center justify-end mt-6">
                            <gl-button @click.prevent="form_data_update.options.splice(index, 1)" tag="button"
                                button_type="red" icon="fa-solid fa-trash-alt" />
                        </div>
                    </div>
                    <hr class="h-px my-4 bg-stroke dark:bg-strokeDark">
                </div>
                <div class="flex justify-end">
                    <gl-button @click.prevent="form_data_update.options.push('')" tag="button"
                        button_type="primary" icon="fa-solid fa-plus-circle" />
                </div>
            </div>

            <div class="mb-4">
                <toggle-box :is_required="false" :show="false" field_name="edit_required" label_name="Required"
                    v-model="form_data_update.required" />
            </div>
            <div class="mb-4">
                <toggle-box :is_required="false" :show="false" field_name="edit_unique" label_name="Unique"
                    v-model="form_data_update.unique" />
            </div>
        </template>
        <template v-slot:buttons>
            <gl-button @click="updateData()" :is_loading="isLoadingUpdateButton" tag="button" button_type="primary"
                icon="fa-solid fa-floppy-disk">Update</gl-button>
        </template>
    </modal>

    <!-- Import HTML Modal -->
    <modal :class="`${model}Modal`" :is_open="isOpenImport" :is_loading="false"
        @closeModal="closeImport()"
        title="Import from HTML" max_width="max-w-2xl">
        <template v-slot:body>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                Paste HTML that contains a form. Inputs, textareas, selects, checkboxes and radios are converted into questions you can then customize.
            </p>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">HTML Code</label>
                <textarea v-model="importHtml" @input="parseHtml" rows="8" spellcheck="false"
                    placeholder="<form>&#10;  <label>Full name</label>&#10;  <input type='text' name='full_name'>&#10;</form>"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-mono bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
            </div>

            <label class="flex items-start gap-2 mb-3 cursor-pointer select-none">
                <input type="checkbox" v-model="importKeepDesign" class="mt-1 rounded border-gray-300 text-primary focus:ring-primary">
                <span class="text-sm text-gray-700 dark:text-gray-300">
                    Keep the original design
                    <span class="block text-xs text-gray-400 dark:text-gray-500">
                        Render the form using the pasted HTML + CSS (including <code>&lt;head&gt;</code> styles and external stylesheet/font links). Turn off to import only the questions into the default layout.
                    </span>
                </span>
            </label>

            <label v-if="importKeepDesign" class="flex items-start gap-2 mb-4 cursor-pointer select-none">
                <input type="checkbox" v-model="importKeepScripts" class="mt-1 rounded border-gray-300 text-primary focus:ring-primary">
                <span class="text-sm text-gray-700 dark:text-gray-300">
                    Interactive form (keep scripts)
                    <span class="block text-xs text-amber-600 dark:text-amber-400">
                        Also keep the pasted <code>&lt;script&gt;</code> tags so multi-step / interactive forms work. This runs the form's own JavaScript on the public page — only enable for code you trust.
                    </span>
                </span>
            </label>

            <div v-if="parsedFields.length" class="mb-2">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Detected fields ({{ parsedFields.length }})
                    </label>
                </div>
                <div class="border border-gray-200 dark:border-gray-700 rounded-md divide-y divide-gray-200 dark:divide-gray-700 max-h-64 overflow-y-auto">
                    <div v-for="(field, i) in parsedFields" :key="i"
                        class="flex items-center gap-3 px-3 py-2 text-sm">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-primary/10 text-primary shrink-0">
                            {{ field.question_type }}
                        </span>
                        <span class="text-gray-800 dark:text-gray-200 truncate">{{ field.label }}</span>
                        <span v-if="field.options && field.options.length"
                            class="text-xs text-gray-400 dark:text-gray-500 shrink-0 ml-auto">
                            {{ field.options.length }} options
                        </span>
                    </div>
                </div>
            </div>

            <p v-else-if="importHtml.trim()" class="text-sm text-amber-600 dark:text-amber-400">
                No form fields detected in the pasted HTML.
            </p>
        </template>
        <template v-slot:buttons>
            <gl-button @click="importQuestions()" :is_loading="isLoadingImportButton"
                :disabled="parsedFields.length === 0" tag="button" button_type="primary"
                icon="fa-solid fa-file-import">
                Import {{ parsedFields.length ? '(' + parsedFields.length + ')' : '' }}
            </gl-button>
        </template>
    </modal>

    <!-- Design / Custom Code Editor Modal -->
    <modal :class="`${designModel}Modal`" :is_open="isOpenDesign" :is_loading="isLoadingDesign"
        @closeModal="isOpenDesign = false"
        title="Edit Form Design" max_width="max-w-4xl">
        <template v-slot:body>
            <language-selector :field_name="`${designModel}`" :trans_selector_name="`${designModel}Selector`"></language-selector>

            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                Edit the raw design of this form. Each field is translatable — switch language above to provide per-language content.
            </p>

            <div class="mb-4 gl-design-field">
                <code-mirror-translate field_name="custom_head" label_name="Head (styles, links, fonts)"
                    language="html" type="text" :is_required="false" :show="false"
                    v-model="design_form.custom_head"
                    v-model:modelValueTranslate="design_form.custom_head_i18n"
                    :error_message="design_form.errors.get('custom_head')" />
            </div>

            <div class="mb-4 gl-design-field">
                <code-mirror-translate field_name="custom_html" label_name="Body HTML"
                    language="html" type="text" :is_required="true" :show="false"
                    v-model="design_form.custom_html"
                    v-model:modelValueTranslate="design_form.custom_html_i18n"
                    @change="design_form.errors.clear('custom_html')"
                    :error_message="design_form.errors.get('custom_html')" />
            </div>

            <div class="mb-4 gl-design-field">
                <code-mirror-translate field_name="custom_js" label_name="Scripts (JavaScript)"
                    language="javascript" type="text" :is_required="false" :show="false"
                    v-model="design_form.custom_js"
                    v-model:modelValueTranslate="design_form.custom_js_i18n"
                    :error_message="design_form.errors.get('custom_js')" />
            </div>
        </template>
        <template v-slot:buttons>
            <gl-button @click="saveDesignEditor()" :is_loading="isLoadingDesignButton" tag="button" button_type="primary"
                icon="fa-solid fa-floppy-disk">Save Design</gl-button>
        </template>
    </modal>

    <!-- Questions Card -->
    <Card>
        <template v-slot:header>
            <div class="flex flex-wrap justify-between items-center gap-3">
                <div class="flex items-center gap-3">
                    <a href="/admin/forms" class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary dark:hover:text-primaryDark">
                        <i class="fa-solid fa-arrow-left mr-1"></i> Back to Forms
                    </a>
                    <span class="text-gray-300 dark:text-gray-600">|</span>
                    <span>Manage Questions</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="inline-flex rounded-md border border-gray-300 dark:border-gray-600 overflow-hidden">
                        <button type="button" @click="viewMode = 'grid'"
                            :class="viewMode === 'grid' ? 'bg-primary text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300'"
                            class="px-3 py-1.5 text-sm font-medium focus:outline-none">
                            <i class="fa-solid fa-list mr-1"></i> Grid
                        </button>
                        <button type="button" @click="viewMode = 'builder'"
                            :class="viewMode === 'builder' ? 'bg-primary text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300'"
                            class="px-3 py-1.5 text-sm font-medium border-l border-gray-300 dark:border-gray-600 focus:outline-none">
                            <i class="fa-solid fa-table-cells-large mr-1"></i> Builder
                        </button>
                    </div>
                    <a :href="`/admin/forms/${form_id}/workflow`">
                        <gl-button tag="button" button_type="secondary" icon="fa-solid fa-diagram-project">
                            Workflow
                        </gl-button>
                    </a>
                    <gl-button @click="openDesignEditor()" tag="button" button_type="secondary" icon="fa-solid fa-pen-ruler">
                        Edit Design
                    </gl-button>
                    <gl-button @click="openImport()" tag="button" button_type="secondary" icon="fa-solid fa-code">
                        Import HTML
                    </gl-button>
                    <gl-button @click="openAdd()" tag="button" button_type="primary" icon="fa-solid fa-plus">
                        Add Question
                    </gl-button>
                </div>
            </div>
        </template>
        <template v-slot:body>
            <div v-if="isLoadingList" class="flex justify-center py-8">
                <i class="fa-solid fa-spinner fa-spin text-2xl text-gray-400"></i>
            </div>

            <template v-else>
            <div v-if="hasCustomDesign" class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-primary/30 bg-primary/5 px-4 py-3">
                <div class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-200">
                    <i class="fa-solid fa-wand-magic-sparkles mt-0.5 text-primary"></i>
                    <span>
                        This form uses a <strong>custom HTML design</strong>. Users see the imported template, not the layout below.
                        Editing questions here won't change the template's wording — re-import to refresh it.
                    </span>
                </div>
                <gl-button @click="removeCustomDesign()" tag="button" button_type="red" icon="fa-solid fa-trash-alt">
                    Remove design
                </gl-button>
            </div>

            <!-- Grid view -->
            <div v-if="viewMode === 'grid'">
                <draggable class="grid grid-cols-12 gap-4" group="children" :sort="true"
                    v-model="questions"
                    :animation="200"
                    item-key="id"
                    @change="questionsUpdateOrder(questions, null)">
                    <template #item="{ element, index }">
                        <div :class="element.question_size_col || 'col-span-12'">
                            <question-renderer :question="element" :index="index"
                                :EditQuestion="editQuestion" :remove_question="removeQuestion"
                                :QuestionsUpdateOrder="questionsUpdateOrderAfterRemove"
                                :delete_base_url="deleteBaseUrl" />
                        </div>
                    </template>
                </draggable>

                <div v-if="questions.length === 0" class="text-center py-8 text-gray-400 dark:text-gray-500">
                    No questions added yet. Click "Add Question" to get started.
                </div>
            </div>

            <!-- Builder view (palette + canvas) -->
            <div v-else class="flex flex-col md:flex-row gap-4">
                <aside class="md:w-60 shrink-0">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-2">
                        Drag a field onto the form
                    </p>
                    <draggable :list="paletteFields" :sort="false" :clone="cloneField" item-key="type"
                        :group="{ name: 'formfields', pull: 'clone', put: false }"
                        class="flex flex-col gap-2">
                        <template #item="{ element }">
                            <div class="flex items-center gap-2 px-3 py-2 rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm text-gray-700 dark:text-gray-200 cursor-grab active:cursor-grabbing hover:border-primary hover:text-primary select-none">
                                <i :class="element.icon" class="w-4 text-center"></i>
                                <span>{{ element.label }}</span>
                            </div>
                        </template>
                    </draggable>
                </aside>

                <div class="flex-1 min-h-[400px] border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-4">
                    <draggable class="flex flex-col gap-4 min-h-40 pb-35"
                        v-model="questions" item-key="id"
                        :group="{ name: 'formfields', pull: true, put: true }"
                        :animation="200" ghost-class="gl-drop-ghost"
                        @add="onPaletteDrop"
                        @update="questionsUpdateOrder(questions, null)">
                        <template #item="{ element, index }">
                            <div>
                                <question-renderer v-if="!element.__palette" :question="element" :index="index"
                                    :EditQuestion="editQuestion" :remove_question="removeQuestion"
                                    :QuestionsUpdateOrder="questionsUpdateOrderAfterRemove"
                                    :delete_base_url="deleteBaseUrl" />
                            </div>
                        </template>
                    </draggable>

                    <div v-if="questions.length === 0" class="flex items-center justify-center h-40 text-gray-400 dark:text-gray-500 text-sm pointer-events-none">
                        Drag a field type here to add your first question.
                    </div>
                </div>
            </div>
            </template>
        </template>
    </Card>
</template>

<script>
import { GlToast } from 'golden-logic-ui';
import draggable from 'vuedraggable';
import QuestionRenderer from './QuestionRenderer.vue';
import { loadMultilingual, destroyMultilingual, prepareDataMultilingual } from '@/services/multilingualService';

const emptyQuestion = {
    label: '', label_i18n: {},
    description: '', description_i18n: {},
    css_styles: '', question_type: '',
    parent_id: null, question_size_col: 'col-span-12',
    maximum_file_size: '', allow_only_specific_file_types: '',
    options: [], required: 0, unique: 0,
};

export default {
    components: { draggable, QuestionRenderer },

    props: ['form_id'],

    data() {
        return {
            model: 'Question',

            viewMode: 'grid',
            pendingDropIndex: null,

            isOpenImport: false,
            isLoadingImportButton: false,
            importHtml: '',
            parsedFields: [],
            importKeepDesign: true,
            importKeepScripts: false,
            hasCustomDesign: false,

            designModel: 'FormDesign',
            isOpenDesign: false,
            isLoadingDesign: false,
            isLoadingDesignButton: false,
            design_form: new Form({
                custom_html: '', custom_html_i18n: {},
                custom_head: '', custom_head_i18n: {},
                custom_js: '', custom_js_i18n: {},
            }),

            questions: [],
            questions_group: [],
            isLoadingList: false,

            isOpenAddQuestion: false,
            isLoadingAdd: false,
            isLoadingAddButton: false,

            isOpenUpdateQuestion: false,
            isLoadingUpdate: false,
            isLoadingUpdateButton: false,

            questionTypes: [
                'Group', 'Short answer', 'Number', 'Email', 'Date', 'Time',
                'Paragraph', 'Drop-down', 'Checkboxes', 'Multiple choice', 'File upload', 'Label',
            ],

            paletteFields: [
                { type: 'Group',           label: 'Group',           icon: 'fa-solid fa-layer-group' },
                { type: 'Short answer',    label: 'Short answer',    icon: 'fa-solid fa-font' },
                { type: 'Paragraph',       label: 'Paragraph',       icon: 'fa-solid fa-align-left' },
                { type: 'Number',          label: 'Number',          icon: 'fa-solid fa-hashtag' },
                { type: 'Email',           label: 'Email',           icon: 'fa-solid fa-envelope' },
                { type: 'Date',            label: 'Date',            icon: 'fa-solid fa-calendar-days' },
                { type: 'Time',            label: 'Time',            icon: 'fa-solid fa-clock' },
                { type: 'Drop-down',       label: 'Drop-down',       icon: 'fa-solid fa-caret-down' },
                { type: 'Checkboxes',      label: 'Checkboxes',      icon: 'fa-solid fa-square-check' },
                { type: 'Multiple choice', label: 'Multiple choice', icon: 'fa-solid fa-circle-dot' },
                { type: 'File upload',     label: 'File upload',      icon: 'fa-solid fa-upload' },
                { type: 'Label',           label: 'Label',           icon: 'fa-solid fa-tag' },
            ],

            columnSizes: [
                { id: 'col-span-1',  name: 'Single Column'   },
                { id: 'col-span-2',  name: 'Two Columns'     },
                { id: 'col-span-3',  name: 'Three Columns'   },
                { id: 'col-span-4',  name: 'Four Columns'    },
                { id: 'col-span-6',  name: 'Six Columns'     },
                { id: 'col-span-8',  name: 'Eight Columns'   },
                { id: 'col-span-9',  name: 'Nine Columns'    },
                { id: 'col-span-10', name: 'Ten Columns'     },
                { id: 'col-span-11', name: 'Eleven Columns'  },
                { id: 'col-span-12', name: 'Twelve Columns'  },
            ],

            form_data: new Form({ ...emptyQuestion }),

            form_data_update: new Form({ id: null, ...emptyQuestion }),
        };
    },

    mounted() {
        this.loadQuestions();
        this.loadDesignState();
    },

    computed: {
        deleteBaseUrl() {
            return `/admin/forms/${this.form_id}/questions`;
        },
    },

    methods: {
        hasOptions(type) {
            return ['Drop-down', 'Checkboxes', 'Multiple choice'].includes(type);
        },

        loadQuestions() {
            this.isLoadingList = true;
            return axios.get(`/admin/forms/${this.form_id}/questions`)
                .then(res => {
                    this.questions = res.data.map(item => this.mapQuestion(item));
                    this.questions_group = this.questions
                        .filter(q => q.question_type === 'Group')
                        .map(q => ({ id: q.id, name: q.label }));
                    this.isLoadingList = false;
                })
                .catch(() => { this.isLoadingList = false; });
        },

        mapQuestion(item) {
            return {
                id:                            item.id,
                parent_id:                     item.parent_id,
                question_type:                 item.question_type,
                label:                         item.label,
                description:                   item.description || '',
                css_styles:                    item.css_styles || '',
                options:                       item.options ? (typeof item.options === 'string' ? JSON.parse(item.options) : item.options) : [],
                form_id:                       item.form_id,
                required:                      item.required,
                unique:                        item.unique,
                question_size_col:             item.question_size_col || 'col-span-12',
                allow_only_specific_file_types: item.allow_only_specific_file_types || '',
                maximum_file_size:             item.maximum_file_size || '',
                order:                         item.order,
                children:                      (item.children || []).map(child => this.mapQuestion(child)),
            };
        },

        openAdd(presetType = '') {
            this.form_data = new Form({ ...emptyQuestion });
            if (typeof presetType === 'string' && presetType) {
                this.form_data.question_type = presetType;
            }
            this.isLoadingAdd = true;
            this.isOpenAddQuestion = true;
            loadMultilingual(this.model, 3, this.isLoadingAdd, (newState) => {
                this.isLoadingAdd = newState;
            });
        },

        closeAdd() {
            this.isOpenAddQuestion = false;
            this.pendingDropIndex = null;
        },

        cloneField(field) {
            return { id: `tmp-${field.type}`, __palette: true, question_type: field.type, question_size_col: 'col-span-12' };
        },

        onPaletteDrop(event) {
            const index = event.newIndex;
            const dropped = this.questions[index];
            // A real field dragged in from a group (not a palette clone): keep it
            // and just persist the new order/parent — don't open the Add modal.
            if (!dropped || !dropped.__palette) {
                this.questionsUpdateOrder(this.questions, null);
                return;
            }
            const type = dropped.question_type;
            this.questions.splice(index, 1);
            this.pendingDropIndex = index;
            this.openAdd(type);
        },

        openImport() {
            this.importHtml = '';
            this.parsedFields = [];
            this.isOpenImport = true;
        },

        closeImport() {
            this.isOpenImport = false;
        },

        parseHtml() {
            const { doc, fields } = this.htmlToFields(this.importHtml);
            this._importDoc = doc;
            this._parsedFieldEls = fields.map(f => f._els);
            this.parsedFields = fields.map(({ _els, ...rest }) => rest);
        },

        htmlToFields(html) {
            if (!html || !html.trim()) {
                return { doc: null, fields: [] };
            }

            const doc = new DOMParser().parseFromString(html, 'text/html');
            const scope = doc.querySelector('form') || doc.body;
            const fields = [];
            const handledRadioGroups = new Set();

            const inputTypeToQuestion = {
                text: 'Short answer', search: 'Short answer', tel: 'Short answer',
                url: 'Short answer', password: 'Short answer',
                email: 'Email', number: 'Number', date: 'Date',
                'datetime-local': 'Date', month: 'Date', week: 'Date',
                time: 'Time', file: 'File upload',
            };

            const elements = scope.querySelectorAll('input, textarea, select');

            elements.forEach((el) => {
                const tag = el.tagName.toLowerCase();
                const cssStyles = el.getAttribute('style') || '';

                if (tag === 'input') {
                    const inputType = (el.getAttribute('type') || 'text').toLowerCase();

                    if (['submit', 'reset', 'button', 'hidden', 'image'].includes(inputType)) {
                        return;
                    }

                    if (inputType === 'radio' || inputType === 'checkbox') {
                        const groupName = el.getAttribute('name') || '';
                        const groupKey = inputType + '::' + groupName;
                        if (groupName && handledRadioGroups.has(groupKey)) {
                            return;
                        }
                        if (groupName) {
                            handledRadioGroups.add(groupKey);
                        }

                        const siblings = groupName
                            ? scope.querySelectorAll(`input[type="${inputType}"][name="${CSS.escape(groupName)}"]`)
                            : [el];

                        const options = Array.from(siblings)
                            .map((s) => this.optionLabelFor(s, doc))
                            .filter((v) => v !== '');

                        fields.push({
                            question_type: inputType === 'radio' ? 'Multiple choice' : 'Checkboxes',
                            label: this.groupLabelFor(el, groupName, doc),
                            options: options.length ? options : [this.labelFor(el, doc)],
                            required: el.hasAttribute('required'),
                            css_styles: cssStyles,
                            question_size_col: 'col-span-12',
                            _els: Array.from(siblings),
                        });
                        return;
                    }

                    fields.push({
                        question_type: inputTypeToQuestion[inputType] || 'Short answer',
                        label: this.labelFor(el, doc),
                        options: [],
                        required: el.hasAttribute('required'),
                        css_styles: cssStyles,
                        question_size_col: 'col-span-12',
                        _els: [el],
                    });
                    return;
                }

                if (tag === 'textarea') {
                    fields.push({
                        question_type: 'Paragraph',
                        label: this.labelFor(el, doc),
                        options: [],
                        required: el.hasAttribute('required'),
                        css_styles: cssStyles,
                        question_size_col: 'col-span-12',
                        _els: [el],
                    });
                    return;
                }

                if (tag === 'select') {
                    const options = Array.from(el.querySelectorAll('option'))
                        .filter((o) => o.getAttribute('value') !== '' && o.textContent.trim() !== '')
                        .map((o) => o.textContent.trim());

                    fields.push({
                        question_type: 'Drop-down',
                        label: this.labelFor(el, doc),
                        options,
                        required: el.hasAttribute('required'),
                        css_styles: cssStyles,
                        question_size_col: 'col-span-12',
                        _els: [el],
                    });
                }
            });

            return { doc, fields };
        },

        labelFor(el, doc) {
            const id = el.getAttribute('id');
            if (id) {
                const forLabel = doc.querySelector(`label[for="${CSS.escape(id)}"]`);
                if (forLabel && forLabel.textContent.trim()) {
                    return forLabel.textContent.trim();
                }
            }

            const wrapping = el.closest('label');
            if (wrapping && wrapping.textContent.trim()) {
                return wrapping.textContent.trim();
            }

            const aria = el.getAttribute('aria-label');
            if (aria && aria.trim()) {
                return aria.trim();
            }

            const placeholder = el.getAttribute('placeholder');
            if (placeholder && placeholder.trim()) {
                return placeholder.trim();
            }

            const name = el.getAttribute('name');
            if (name && name.trim()) {
                return this.prettifyName(name);
            }

            return 'Untitled question';
        },

        optionLabelFor(el, doc) {
            const wrapping = el.closest('label');
            if (wrapping && wrapping.textContent.trim()) {
                return wrapping.textContent.trim();
            }

            const id = el.getAttribute('id');
            if (id) {
                const forLabel = doc.querySelector(`label[for="${CSS.escape(id)}"]`);
                if (forLabel && forLabel.textContent.trim()) {
                    return forLabel.textContent.trim();
                }
            }

            const value = el.getAttribute('value');
            return value && value.trim() ? value.trim() : '';
        },

        groupLabelFor(el, groupName, doc) {
            const fieldset = el.closest('fieldset');
            if (fieldset) {
                const legend = fieldset.querySelector('legend');
                if (legend && legend.textContent.trim()) {
                    return legend.textContent.trim();
                }
            }
            return groupName ? this.prettifyName(groupName) : 'Untitled question';
        },

        prettifyName(name) {
            return name
                .replace(/\[\]$/, '')
                .replace(/[_\-.]+/g, ' ')
                .replace(/([a-z])([A-Z])/g, '$1 $2')
                .trim()
                .replace(/\b\w/g, (c) => c.toUpperCase());
        },

        importQuestions() {
            if (this.parsedFields.length === 0) {
                return;
            }
            this.isLoadingImportButton = true;
            axios.post(`/admin/forms/${this.form_id}/questions/import`, {
                questions: this.parsedFields,
            }).then((res) => {
                const ids = (res.data && res.data.question_ids) || [];
                const count = (res.data && res.data.created) || this.parsedFields.length;

                if (this.importKeepDesign && this._importDoc && ids.length) {
                    return this.saveDesignFromImport(ids)
                        .then(() => this.finishImport(count, true))
                        .catch(() => {
                            this.isLoadingImportButton = false;
                            GlToast.methods.add({ message: 'Questions imported, but saving the design failed.', type: 'error', duration: 4000 });
                        });
                }

                this.finishImport(count, false);
            }).catch(() => {
                this.isLoadingImportButton = false;
                GlToast.methods.add({ message: 'Failed to import questions.', type: 'error', duration: 4000 });
            });
        },

        finishImport(count, savedDesign) {
            this.isLoadingImportButton = false;
            this.isOpenImport = false;
            this.loadQuestions();
            const suffix = savedDesign ? ' Original design saved as the form template.' : '';
            GlToast.methods.add({ message: `${count} question(s) imported successfully.${suffix}`, type: 'success', duration: 4000 });
        },

        saveDesignFromImport(ids) {
            const doc = this._importDoc;
            const groups = this._parsedFieldEls || [];

            groups.forEach((els, i) => {
                const qid = ids[i];
                if (!qid || !els) {
                    return;
                }
                const isMulti = this.parsedFields[i] && this.parsedFields[i].question_type === 'Checkboxes';
                const name = isMulti ? `q-${qid}[]` : `q-${qid}`;
                els.forEach((el) => {
                    el.setAttribute('name', name);
                    el.setAttribute('data-question-id', String(qid));
                });
            });

            // Head: styles, external stylesheet/font links, meta (minus <title>).
            let head = '';
            if (doc.head) {
                const headClone = doc.head.cloneNode(true);
                headClone.querySelectorAll('title').forEach((n) => n.remove());
                head = headClone.innerHTML.trim();
            }

            // Scripts (inline + external <script src>), only when interactive is enabled.
            let js = '';
            if (this.importKeepScripts) {
                js = Array.from(doc.querySelectorAll('script')).map((s) => s.outerHTML).join('\n');
            }

            // Body markup without <script> (scripts live in custom_js).
            const bodyClone = doc.body ? doc.body.cloneNode(true) : null;
            if (bodyClone) {
                bodyClone.querySelectorAll('script').forEach((n) => n.remove());
            }
            const html = bodyClone ? bodyClone.innerHTML.trim() : '';

            return axios.put(`/admin/forms/${this.form_id}/design`, {
                custom_html: html,
                custom_head: head,
                custom_js: js,
            }).then(() => {
                this.hasCustomDesign = true;
            });
        },

        loadDesignState() {
            axios.get(`/admin/forms/${this.form_id}/design`)
                .then((res) => {
                    this.hasCustomDesign = !!(res.data && res.data.has_custom);
                })
                .catch(() => {});
        },

        openDesignEditor() {
            this.isLoadingDesign = true;
            // Fetch the design first, then open the modal, so each CodeMirror editor
            // mounts with its content already set (it reads modelValue on mount).
            axios.get(`/admin/forms/${this.form_id}/design`)
                .then((res) => {
                    const d = res.data || {};
                    const tr = d.all_translation_feilds || {};
                    this.design_form = new Form({
                        custom_html: d.custom_html || '',
                        custom_html_i18n: tr.custom_html_i18n ?? {},
                        custom_head: d.custom_head || '',
                        custom_head_i18n: tr.custom_head_i18n ?? {},
                        custom_js: d.custom_js || '',
                        custom_js_i18n: tr.custom_js_i18n ?? {},
                    });
                    this.isOpenDesign = true;
                
                        loadMultilingual(this.designModel, 4, this.isLoadingDesign, (newState) => {
                            this.isLoadingDesign = newState;
                      
                       });
                })
                .catch(() => {
                    this.isLoadingDesign = false;
                    GlToast.methods.add({ message: 'Failed to load the form design.', type: 'error', duration: 4000 });
                });
        },

        saveDesignEditor() {
            this.isLoadingDesignButton = true;
            prepareDataMultilingual();
            axios.put(`/admin/forms/${this.form_id}/design`, {
                custom_html: this.design_form.custom_html,
                custom_html_i18n: this.design_form.custom_html_i18n,
                custom_head: this.design_form.custom_head,
                custom_head_i18n: this.design_form.custom_head_i18n,
                custom_js: this.design_form.custom_js,
                custom_js_i18n: this.design_form.custom_js_i18n,
            }).then(() => {
                this.isOpenDesign = false;
                this.isLoadingDesignButton = false;
                this.hasCustomDesign = true;
                GlToast.methods.add({ message: 'Design saved successfully.', type: 'success', duration: 4000 });
            }).catch((error) => {
                if (error.response && error.response.data && error.response.data.errors) {
                    this.design_form.errors.record(error.response.data.errors);
                }
                this.isLoadingDesignButton = false;
            });
        },

        removeCustomDesign() {
            axios.delete(`/admin/forms/${this.form_id}/design`)
                .then(() => {
                    this.hasCustomDesign = false;
                    GlToast.methods.add({ message: 'Custom design removed. The form now uses the default layout.', type: 'success', duration: 4000 });
                })
                .catch(() => {
                    GlToast.methods.add({ message: 'Failed to remove custom design.', type: 'error', duration: 4000 });
                });
        },

        removeQuestion(index) {
            this.questions.splice(index, 1);
        },

        saveData() {
            this.isLoadingAddButton = true;
            const idsBeforeSave = this.questions.map(q => q.id);
            const dropIndex = this.pendingDropIndex;
            prepareDataMultilingual();
            axios.post(`/admin/forms/${this.form_id}/questions`, {
                label:                          this.form_data.label,
                label_i18n:                     this.form_data.label_i18n,
                description:                    this.form_data.description || '',
                description_i18n:               this.form_data.description_i18n,
                css_styles:                     this.form_data.css_styles || '',
                question_type:                  this.form_data.question_type,
                required:                       this.form_data.required,
                unique:                         this.form_data.unique,
                question_size_col:              this.form_data.question_size_col || 'col-span-12',
                parent_id:                      this.form_data.parent_id ?? null,
                allow_only_specific_file_types: this.form_data.allow_only_specific_file_types || '',
                maximum_file_size:              this.form_data.maximum_file_size || '',
                options:                        this.form_data.options || [],
            }).then(() => {
                this.form_data = new Form({ ...emptyQuestion });
                this.isOpenAddQuestion = false;
                this.isLoadingAddButton = false;
                this.pendingDropIndex = null;
                this.loadQuestions().then(() => {
                    if (dropIndex !== null && dropIndex >= 0) {
                        const newQuestion = this.questions.find(q => !idsBeforeSave.includes(q.id));
                        if (newQuestion) {
                            const currentIndex = this.questions.indexOf(newQuestion);
                            this.questions.splice(currentIndex, 1);
                            this.questions.splice(Math.min(dropIndex, this.questions.length), 0, newQuestion);
                        }
                    }
                    this.questionsUpdateOrder(this.questions, null);
                });
                GlToast.methods.add({ message: 'Question added successfully.', type: 'success', duration: 4000 });
            }).catch((error) => {
                if (error.response && error.response.data && error.response.data.errors) {
                    this.form_data.errors.record(error.response.data.errors);
                }
                this.isLoadingAddButton = false;
            });
        },

        editQuestion(id) {
            axios.get(`/admin/forms/${this.form_id}/questions/${id}/edit`)
                .then(res => {
                    const q = res.data.data;
                    this.form_data_update = new Form({
                        id:                            q.id,
                        label:                         q.label,
                        label_i18n:                    q.all_translation_feilds?.label_i18n ?? {},
                        description:                   q.description || '',
                        description_i18n:              q.all_translation_feilds?.description_i18n ?? {},
                        css_styles:                    q.css_styles || '',
                        question_type:                 q.question_type,
                        parent_id:                     q.parent_id,
                        question_size_col:             q.question_size_col || 'col-span-12',
                        maximum_file_size:             q.maximum_file_size || '',
                        allow_only_specific_file_types: q.allow_only_specific_file_types || '',
                        options:                       q.options ? (typeof q.options === 'string' ? JSON.parse(q.options) : q.options) : [],
                        required:                      q.required,
                        unique:                        q.unique,
                    });
                    this.isLoadingUpdate = true;
                    this.isOpenUpdateQuestion = true;
                    loadMultilingual(this.model, 3, this.isLoadingUpdate, (newState) => {
                        this.isLoadingUpdate = newState;
                    });
                })
                .catch(() => {});
        },

        updateData() {
            this.isLoadingUpdateButton = true;
            prepareDataMultilingual();
            axios.put(`/admin/forms/${this.form_id}/questions/${this.form_data_update.id}`, {
                label:                          this.form_data_update.label,
                label_i18n:                     this.form_data_update.label_i18n,
                description:                    this.form_data_update.description || '',
                description_i18n:               this.form_data_update.description_i18n,
                css_styles:                     this.form_data_update.css_styles || '',
                question_type:                  this.form_data_update.question_type,
                required:                       this.form_data_update.required,
                unique:                         this.form_data_update.unique,
                question_size_col:              this.form_data_update.question_size_col || 'col-span-12',
                parent_id:                      this.form_data_update.parent_id ?? null,
                allow_only_specific_file_types: this.form_data_update.allow_only_specific_file_types || '',
                maximum_file_size:              this.form_data_update.maximum_file_size || '',
                options:                        this.form_data_update.options || [],
            }).then(() => {
                this.form_data_update = new Form({ ...emptyQuestion });
                this.isOpenUpdateQuestion = false;
                this.isLoadingUpdateButton = false;
                this.loadQuestions().then(() => {
                    this.questionsUpdateOrder(this.questions, null);
                });
                GlToast.methods.add({ message: 'Question updated successfully.', type: 'success', duration: 4000 });
            }).catch((error) => {
                if (error.response && error.response.data && error.response.data.errors) {
                    this.form_data_update.errors.record(error.response.data.errors);
                }
                this.isLoadingUpdateButton = false;
            });
        },

        questionsUpdateOrder(questions_list, parent_id) {
            axios.put('/admin/questions/QuestionsUpdateOrder', {
                Questions: questions_list,
                parent_id: parent_id,
            }).then(() => {
                this.loadQuestions();
            }).catch(() => {
                GlToast.methods.add({ message: 'Failed to save order.', type: 'error', duration: 3000 });
            });
        },

        questionsUpdateOrderAfterRemove(questions = null, parent_id = null) {
            axios.put('/admin/questions/QuestionsUpdateOrder', {
                Questions: questions ?? this.questions,
                parent_id: parent_id ?? null,
            }).then(() => {
                this.loadQuestions();
            }).catch(() => {
                GlToast.methods.add({ message: 'Failed to save order.', type: 'error', duration: 3000 });
            });
        },
    },

    beforeUnmount() {
        destroyMultilingual();
    },
};
</script>

<style>
/* Drag-drop placeholder: a palette field is a small row, so its default ghost
   is a tiny box. Force the drop slot to a full-width, field-sized box so it's
   clear where the field will land. */
.gl-drop-ghost {
    display: block !important;
    width: 100% !important;
    min-height: 5rem;
    border: 2px dashed var(--theme-color, #6366f1);
    border-radius: .5rem;
    background: color-mix(in srgb, var(--theme-color, #6366f1) 8%, transparent);
}
.gl-drop-ghost > * { visibility: hidden; }

/* Give the design-editor CodeMirror instances a usable height (they collapse to
   one line by default inside the modal). Targets the library-rendered editor. */
.gl-design-field .CodeEditor,
.gl-design-field .cm-editor {
    min-height: 200px;
    max-height: 420px;
}
/*
.gl-design-field .CodeEditor {
    border: 1px solid #d1d5db;
    border-radius: 8px;
    overflow: auto;
}
.gl-design-field .cm-editor {
    height: 100%;
}
.gl-design-field .cm-scroller {
    overflow: auto;
}
    */
</style>
