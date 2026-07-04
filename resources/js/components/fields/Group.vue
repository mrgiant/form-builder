<template>
    <GlDynamicConfirmation ref="ConfirmationDelete"></GlDynamicConfirmation>

    <div class="relative flex flex-col min-w-0 break-words bg-white border border-gray-300 rounded-sm dark:bg-gray-900 dark:border-gray-700">
        <div class="flex justify-between px-3 py-3 mb-0 bg-gray-100 dark:bg-gray-800 border-b border-gray-300 dark:border-gray-700">
            <h3>
                <div>
                    <label class="font-medium text-gray-800 dark:text-gray-200">{{ question.order + ". " + question.label }}</label>
                    <small v-if="question.description != ''" class="block mt-1 text-gray-600 dark:text-gray-400"
                        :class="[question.is_rtl == 1 ? 'mr-4' : 'ml-4']">{{ question.description }}</small>
                </div>
            </h3>
            <div class="flex items-center gap-1">
                <button type="button" v-on:click.prevent="EditQuestion(question.id)"
                    class="inline-block px-2 py-1 ml-1 text-xs font-normal leading-tight text-center text-white bg-blue-600 border rounded select-none hover:bg-blue-700">
                    <i class="fa fa-edit"></i>
                </button>
                <button type="button" v-on:click.prevent="deleteQuestion(question.id)"
                    class="inline-block px-2 py-1 ml-1 text-xs font-normal leading-tight text-center text-white bg-red-600 border rounded select-none hover:bg-red-700">
                    <i class="fa fa-trash"></i>
                </button>
                <button v-on:click="toggleMinus(question.id)" type="button"
                    class="inline-block px-3 py-1 font-normal leading-normal text-center border rounded select-none dark:border-gray-600">
                    <i class="fas" :class="isMinus[question.id] ? 'fa-plus' : 'fa-minus'"></i>
                </button>
            </div>
        </div>
        <div class="flex-auto p-6" v-if="!isMinus[question.id]">
            <draggable class="grid grid-cols-12 gap-4" group="children" :sort="true"
                v-model="question.children"
                :options="{ animation: 200, group: 'children' }"
                item-key="id"
                @change="QuestionsUpdateOrder(question.children, question.id)">
                <template #item="{ element, index }">
                    <div :class="element.question_size_col == '' ? 'col-span-12' : element.question_size_col">
                        <general-input v-if="element.question_type == 'Short answer'" :question="element"
                            :QuestionsUpdateOrder="QuestionsUpdateOrder" :EditQuestion="EditQuestion"
                            :remove_question="remove_question" :index="index" InputType="text"></general-input>

                        <general-input v-if="element.question_type == 'Email'" :question="element"
                            :QuestionsUpdateOrder="QuestionsUpdateOrder" :EditQuestion="EditQuestion"
                            :remove_question="remove_question" :index="index" InputType="email"></general-input>

                        <general-input v-if="element.question_type == 'File upload'" :question="element"
                            :QuestionsUpdateOrder="QuestionsUpdateOrder" :EditQuestion="EditQuestion"
                            :remove_question="remove_question" :index="index" InputType="file"></general-input>

                        <general-input v-if="element.question_type == 'Number'" :question="element"
                            :QuestionsUpdateOrder="QuestionsUpdateOrder" :EditQuestion="EditQuestion"
                            :remove_question="remove_question" :index="index" InputType="number"></general-input>

                        <general-input v-if="element.question_type == 'Date'" :question="element"
                            :QuestionsUpdateOrder="QuestionsUpdateOrder" :EditQuestion="EditQuestion"
                            :remove_question="remove_question" :index="index" InputType="date"></general-input>

                        <general-input v-if="element.question_type == 'Time'" :question="element"
                            :QuestionsUpdateOrder="QuestionsUpdateOrder" :EditQuestion="EditQuestion"
                            :remove_question="remove_question" :index="index" InputType="time"></general-input>

                        <text-area v-if="element.question_type == 'Paragraph'" :question="element"
                            :QuestionsUpdateOrder="QuestionsUpdateOrder" :EditQuestion="EditQuestion"
                            :remove_question="remove_question" :index="index"></text-area>

                        <select-option v-if="element.question_type == 'Drop-down'" :question="element"
                            :QuestionsUpdateOrder="QuestionsUpdateOrder" :EditQuestion="EditQuestion"
                            :remove_question="remove_question" :index="index"></select-option>

                        <checkbox-list v-if="element.question_type == 'Checkboxes'" :question="element"
                            :QuestionsUpdateOrder="QuestionsUpdateOrder" :EditQuestion="EditQuestion"
                            :remove_question="remove_question" :index="index"></checkbox-list>

                        <radio-button v-if="element.question_type == 'Multiple choice'" :question="element"
                            :QuestionsUpdateOrder="QuestionsUpdateOrder" :EditQuestion="EditQuestion"
                            :remove_question="remove_question" :index="index"></radio-button>
                    </div>
                </template>
            </draggable>
        </div>
    </div>
</template>

<script>
import { GlDynamicConfirmation, GlToast } from "golden-logic-ui";
import GeneralInput from "./GeneralInput.vue";
import TextArea from "./TextArea.vue";
import SelectOption from "./Select.vue";
import CheckboxList from "./CheckboxList.vue";
import RadioButton from "./RadioButton.vue";
import draggable from "vuedraggable";

export default {
    components: { GlDynamicConfirmation, GeneralInput, TextArea, SelectOption, CheckboxList, RadioButton, draggable },
    props: ["question", "EditQuestion", "QuestionsUpdateOrder", "remove_question", "index", "delete_base_url"],

    data() {
        return { isMinus: [] };
    },

    methods: {
        toggleMinus(index) {
            this.isMinus[index] = !this.isMinus[index];
        },

        async deleteQuestion(id) {
            const ok = await this.$refs.ConfirmationDelete.show({
                title: "Delete Question",
                message: "Are you sure you want to delete this question? This action cannot be undone.",
                okButton: "Yes, delete it",
            });
            if (ok) {
                axios.post(this.delete_base_url ? `${this.delete_base_url}/${id}` : `/admin/departments/${this.question.department_id}/questions/${id}`, { _method: "DELETE" })
                    .then(() => {
                        this.remove_question(this.index);
                        this.QuestionsUpdateOrder(this.question.children, this.question.id);
                        GlToast.methods.add({ message: "Question deleted successfully.", type: "success", duration: 5000 });
                    });
            }
        },
    },
};
</script>
