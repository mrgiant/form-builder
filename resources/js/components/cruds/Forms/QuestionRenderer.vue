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
import Group from '../../fields_departmns/Group.vue';
import GeneralInput from '../../fields_departmns/GeneralInput.vue';
import TextArea from '../../fields_departmns/TextArea.vue';
import SelectOption from '../../fields_departmns/Select.vue';
import CheckboxList from '../../fields_departmns/CheckboxList.vue';
import RadioButton from '../../fields_departmns/RadioButton.vue';
import LabelField from '../../fields_departmns/Label.vue';

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
