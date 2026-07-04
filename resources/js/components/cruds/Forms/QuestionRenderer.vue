<template>
    <group v-if="question.question_type === 'Group'" :question="question"
        :QuestionsUpdateOrder="QuestionsUpdateOrder"
        :EditQuestion="EditQuestion" :remove_question="remove_question" :index="index"
        :delete_base_url="delete_base_url" />
    <general-input v-else-if="inputType" :question="question"
        :QuestionsUpdateOrder="QuestionsUpdateOrder"
        :EditQuestion="EditQuestion" :remove_question="remove_question" :index="index"
        :InputType="inputType" :delete_base_url="delete_base_url" />
    <text-area v-else-if="question.question_type === 'Paragraph'" :question="question"
        :QuestionsUpdateOrder="QuestionsUpdateOrder"
        :EditQuestion="EditQuestion" :remove_question="remove_question" :index="index"
        :delete_base_url="delete_base_url" />
    <select-option v-else-if="question.question_type === 'Drop-down'" :question="question"
        :QuestionsUpdateOrder="QuestionsUpdateOrder"
        :EditQuestion="EditQuestion" :remove_question="remove_question" :index="index"
        :delete_base_url="delete_base_url" />
    <checkbox-list v-else-if="question.question_type === 'Checkboxes'" :question="question"
        :QuestionsUpdateOrder="QuestionsUpdateOrder"
        :EditQuestion="EditQuestion" :remove_question="remove_question" :index="index"
        :delete_base_url="delete_base_url" />
    <radio-button v-else-if="question.question_type === 'Multiple choice'" :question="question"
        :QuestionsUpdateOrder="QuestionsUpdateOrder"
        :EditQuestion="EditQuestion" :remove_question="remove_question" :index="index"
        :delete_base_url="delete_base_url" />
    <label-field v-else-if="question.question_type === 'Label'" :question="question"
        :QuestionsUpdateOrder="QuestionsUpdateOrder"
        :EditQuestion="EditQuestion" :remove_question="remove_question" :index="index"
        :delete_base_url="delete_base_url" />
</template>

<script>
import Group from '@/components/fields/Group.vue';
import GeneralInput from '@/components/fields/GeneralInput.vue';
import TextArea from '@/components/fields/TextArea.vue';
import SelectOption from '@/components/fields/Select.vue';
import CheckboxList from '@/components/fields/CheckboxList.vue';
import RadioButton from '@/components/fields/RadioButton.vue';
import LabelField from '@/components/fields/Label.vue';

const inputTypeMap = {
    'Short answer': 'text',
    Email: 'email',
    'File upload': 'file',
    Number: 'number',
    Date: 'date',
    Time: 'time',
};

export default {
    components: { Group, GeneralInput, TextArea, SelectOption, CheckboxList, RadioButton, LabelField },

    props: {
        question: { type: Object, required: true },
        index: { type: Number, required: true },
        EditQuestion: { type: Function, required: true },
        remove_question: { type: Function, required: true },
        QuestionsUpdateOrder: { type: Function, required: true },
        delete_base_url: { type: String, required: true },
    },

    computed: {
        inputType() {
            return inputTypeMap[this.question.question_type] || null;
        },
    },
};
</script>
