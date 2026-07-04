<template>



   <div class="bg-white border rounded-lg border-stroke shadow-default dark:border-white/10 dark:bg-gray-900">
        <div class="flex justify-between px-3 py-4 border-b border-stroke dark:border-white/10">
            <h3>
                <div>
                    <label
   
    :for="'q-' + question.id"
    :class="{
            'gl-label-form': !form.errors.has('q-' + question.id),
            'gl-label-form-invalid': form.errors.has('q-' + question.id),
            required: question.required,
        }"
   
  >
    {{ question.order + ". " + getTranslatedField(question, 'label') }}
  </label>
                      <small  v-if="question.description !== ''" class="block mt-1 ms-4 text-sm font-normal leading-5 text-gray-500">
                       {{ getTranslatedField(question, 'description') }}
                    </small>
                </div>
            </h3>
           
        </div>
        <div class="flex-auto p-3">
            
            <div class="grid grid-cols-12 gap-4 ">
        <div

          v-for="(sub_question, index) in question.children"

            :class="sub_question.question_size_col=='' ? 'col-span-12':sub_question.question_size_col"

          :key="index"
        >
          <general-input
            v-if="sub_question.question_type == 'Short answer'"
            :question="sub_question"
            :form="form"
            :form_data="form_data"
            InputType="text"
          ></general-input>

           <general-input
            v-if="sub_question.question_type == 'Number'"
            :question="sub_question"
            :form="form"
            :form_data="form_data"
            InputType="number"
          ></general-input>


           <general-input
            v-if="sub_question.question_type == 'Email'"
            :question="sub_question"
            :form="form"
            :form_data="form_data"
            InputType="email"
          ></general-input>


           <file-upload-input
            v-if="sub_question.question_type == 'File upload'"
            :question="sub_question"
            :form="form"
            :form_data="form_data"
          ></file-upload-input>

           <general-input
            v-if="sub_question.question_type == 'Date'"
            :question="sub_question"
            :form="form"
            :form_data="form_data"
            InputType="date"
          ></general-input>

          <general-input
            v-if="sub_question.question_type == 'Time'"
            :question="sub_question"
            :form="form"
            :form_data="form_data"
            InputType="time"
          ></general-input>

          <text-area
            v-if="sub_question.question_type == 'Paragraph'"
            :question="sub_question"
            :form="form"
            :form_data="form_data"
          ></text-area>

          <select-option
            v-if="sub_question.question_type == 'Drop-down'"
            :question="sub_question"
            :form="form"
            :form_data="form_data"
          ></select-option>

          <checkbox-list
            v-if="sub_question.question_type == 'Checkboxes'"
            :question="sub_question"
            :form="form"
            :form_data="form_data"
          ></checkbox-list>

          <radio-button
            v-if="sub_question.question_type == 'Multiple choice'"
            :question="sub_question"
            :form="form"
            :form_data="form_data"
          ></radio-button>
        </div>

      </div>
        </div>
    </div>






  <!-- /.card -->
</template>

<script>
//import Group from "./Group.vue";

import FileUploadInput from "./FileUploadShow.vue";

import TextArea from "./TextArea.vue";
import SelectOption from "./Select.vue";
import CheckboxList from "./CheckboxList.vue";
import RadioButton from "./RadioButton.vue";
import GeneralInput from "./GeneralInput.vue";

export default {
  components: {
  //  Group,

    FileUploadInput,
    TextArea,
    SelectOption,
    CheckboxList,
    RadioButton,
    GeneralInput


  },
  props: ["question", "form", "form_data"],

  methods: {

  },
};
</script>

<style scoped lang="scss" >
</style>

