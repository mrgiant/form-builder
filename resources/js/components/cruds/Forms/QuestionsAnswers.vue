<template>
  <div>
    <!-- Closed state -->
    <div v-if="formInfo && formInfo.is_closed" class="py-10 text-center">
      <i class="fa-solid fa-circle-xmark text-5xl text-red-500 mb-4"></i>
      <div class="text-lg text-gray-800 dark:text-gray-100 no-tailwindcss-base"
           v-html="getTranslatedField(formInfo, 'close_message') || 'This form is closed.'">
      </div>
    </div>

    <!-- Not started state -->
    <div v-else-if="formInfo && formInfo.is_not_started" class="py-10 text-center">
      <i class="fa-solid fa-clock text-5xl text-yellow-500 mb-4"></i>
      <div class="text-lg text-gray-800 dark:text-gray-100 no-tailwindcss-base"
           v-html="getTranslatedField(formInfo, 'not_start_message') || 'This form has not started yet.'">
      </div>
    </div>

    <!-- Thank you state -->
    <div v-else-if="submitted" class="py-10 text-center">
      <i class="fa-solid fa-circle-check text-5xl text-green-500 mb-4"></i>
      <div class="text-lg text-gray-800 dark:text-gray-100 no-tailwindcss-base"
           v-html="getTranslatedField(formInfo, 'thank_you_message') || 'Thank you for your response.'">
      </div>
    </div>

    <!-- Form -->
    <form v-else @submit.prevent="saveData()">
      <div class="grid grid-cols-12 gap-4">
        <div
          v-for="(question, index) in questions"
          :class="question.question_size_col || 'col-span-12'"
          :key="index"
          :style="question.css_styles"
        >
          <group
            v-if="question.question_type === 'Group'"
            :question="question" :form="form" :form_data="form_data"
          />
          <general-input
            v-if="question.question_type === 'Short answer'"
            :question="question" :form="form" :form_data="form_data" InputType="text"
          />
          <general-input
            v-if="question.question_type === 'Number'"
            :question="question" :form="form" :form_data="form_data" InputType="number"
          />
          <general-input
            v-if="question.question_type === 'Email'"
            :question="question" :form="form" :form_data="form_data" InputType="email"
          />
          <file-upload-input
            v-if="question.question_type === 'File upload'"
            :question="question" :form="form" :form_data="form_data"
          />
          <general-input
            v-if="question.question_type === 'Date'"
            :question="question" :form="form" :form_data="form_data" InputType="date"
          />
          <general-input
            v-if="question.question_type === 'Time'"
            :question="question" :form="form" :form_data="form_data" InputType="time"
          />
          <text-area
            v-if="question.question_type === 'Paragraph'"
            :question="question" :form="form" :form_data="form_data"
          />
          <select-option
            v-if="question.question_type === 'Drop-down'"
            :question="question" :form="form" :form_data="form_data"
          />
          <checkbox-list
            v-if="question.question_type === 'Checkboxes'"
            :question="question" :form="form" :form_data="form_data"
          />
          <radio-button
            v-if="question.question_type === 'Multiple choice'"
            :question="question" :form="form" :form_data="form_data"
          />
          <div v-if="question.question_type === 'Label'" class="py-2">
            <p class="text-base font-semibold text-gray-800 dark:text-gray-200">{{ getTranslatedField(question, 'label') }}</p>
            <p v-if="question.description" class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ getTranslatedField(question, 'description') }}</p>
          </div>
        </div>
      </div>

      <div class="mt-6" v-if="show !== true && show !== 'true'">
        <gl-button tag="button" type="submit" :is_loading="isSubmitting" button_type="primary" icon="fa-solid fa-paper-plane">
          Submit
        </gl-button>
      </div>
    </form>
  </div>
</template>

<script>
import { GlToast } from 'golden-logic-ui';
import Group       from '../../fieldsAnswer/Group.vue';
import GeneralInput from '../../fieldsAnswer/GeneralInput.vue';
import FileUploadInput from '../../fieldsAnswer/FileUpload.vue';
import TextArea    from '../../fieldsAnswer/TextArea.vue';
import SelectOption from '../../fieldsAnswer/Select.vue';
import CheckboxList from '../../fieldsAnswer/CheckboxList.vue';
import RadioButton from '../../fieldsAnswer/RadioButton.vue';

export default {
  components: { Group, GeneralInput, FileUploadInput, TextArea, SelectOption, CheckboxList, RadioButton },

  props: ['form_id', 'slug', 'show', 'data'],

  data() {
    return {
      questions:   [],
      form:        new Form({}),
      form_data:   {},
      isSubmitting: false,
      submitted:   false,
      formInfo:    null,
    };
  },

  mounted() {
    this.getFormInfo();
    this.getQuestions();
  },

  methods: {
    getFormInfo() {
      axios.get(`/forms/${this.form_id}/info`)
        .then(res => { this.formInfo = res.data; })
        .catch(err => console.error(err));
    },

    parseOptions(options) {
      if (!options) return [];
      if (typeof options === 'string') {
        try { return JSON.parse(options); } catch { return []; }
      }
      return options;
    },

    mapQuestion(item) {
      const children = (item.children || []).map(sub => ({
        id:                            sub.id,
        label_free_text:               sub.label_free_text,
        css_styles:                    sub.css_styles || '',
        parent_id:                     sub.parent_id,
        question_type:                 sub.question_type,
        label:                         sub.label,
        description:                   sub.description || '',
        pattern_validation:            sub.pattern_validation || '',
        pattern_validation_message:    sub.pattern_validation_message || '',
        options:                       this.parseOptions(sub.options),
        form_id:                       sub.form_id,
        required:                      sub.required,
        unique:                        sub.unique,
        question_size_col:             sub.question_size_col || 'col-span-12',
        allow_only_specific_file_types: sub.allow_only_specific_file_types,
        maximum_file_size:             sub.maximum_file_size,
        order:                         sub.order,
        children:                      [],
        all_translation_feilds:       sub.all_translation_feilds || {},
      }));

      return {
        id:                            item.id,
        label_free_text:               item.label_free_text,
        css_styles:                    item.css_styles || '',
        parent_id:                     item.parent_id,
        question_type:                 item.question_type,
        label:                         item.label,
        description:                   item.description || '',
        pattern_validation:            item.pattern_validation || '',
        pattern_validation_message:    item.pattern_validation_message || '',
        options:                       this.parseOptions(item.options),
        form_id:                       item.form_id,
        required:                      item.required,
        unique:                        item.unique,
        question_size_col:             item.question_size_col || 'col-span-12',
        allow_only_specific_file_types: item.allow_only_specific_file_types,
        maximum_file_size:             item.maximum_file_size,
        order:                         item.order,
        children,
        all_translation_feilds:       item.all_translation_feilds || {},
      };
    },

    getQuestions() {
      axios.get(`/forms/${this.form_id}/questions`)
        .then(res => {
          const list = res.data;
          this.questions = list.map(item => this.mapQuestion(item));

          // Pre-fill form_data if existing response data passed
          if (this.data && Object.keys(this.data).length > 0) {
            this.questions.forEach(item => {
              (item.children || []).forEach(sub => {
                if (sub.question_type === 'Checkboxes') {
                  this.form_data['q-' + sub.id] = (this.data[sub.id] || '').toString().split(',');
                } else {
                  this.form_data['q-' + sub.id] = this.data[sub.id] ?? '';
                }
              });
              if (item.question_type === 'Checkboxes') {
                this.form_data['q-' + item.id] = (this.data[item.id] || '').toString().split(',');
              } else {
                this.form_data['q-' + item.id] = this.data[item.id] ?? '';
              }
            });
          }
        })
        .catch(err => console.error(err));
    },

    saveData() {
      if (this.isSubmitting) {
        return;
      }
      this.isSubmitting = true;
      const data = new FormData();
      Object.keys(this.form_data).forEach(key => data.append(key, this.form_data[key]));

      axios.post(`/forms/${this.form_id}/d`, data, { headers: { 'content-type': 'multipart/form-data' } })
        .then(() => {
          this.form.reset();
          this.form_data = {};
          this.isSubmitting = false;
          this.submitted = true;
          GlToast.methods.add({ message: 'Response submitted successfully.', type: 'success', duration: 4000 });
        })
        .catch(error => {
          this.form.errors.record(error.response?.data?.errors || {});
          this.isSubmitting = false;
        });
    },
  },
};
</script>
