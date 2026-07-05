<template>
  <div class="relative mb-4">
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

    <small
      v-if="question.description !== ''"
      class="block mt-1 ms-4 text-sm font-normal leading-5 text-gray-500"
    >
      {{ getTranslatedField(question, 'description') }}
    </small>

    <div class="ms-4 mt-2">
      <input
        v-if="!(show === true || show === 'true')"
        :id="'q-' + question.id"
        :name="'q-' + question.id"
        type="file"
        :class="form.errors.has('q-' + question.id) ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
        class="block w-full text-sm text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border rounded-md cursor-pointer focus:outline-none focus:ring-2 focus:ring-primary file:me-3 file:py-2 file:px-4 file:rounded-l-md file:rounded-r-none file:border-0 file:text-sm file:font-medium file:bg-primary file:text-white hover:file:bg-primary/90"
        @input="onFileChange"
        @keydown="form.errors.clear('q-' + question.id)"
      />

      <a
        v-if="isDownloadable"
        :href="form_data['q-' + question.id]"
        target="_blank"
        class="inline-block mt-2 text-sm text-primary hover:underline"
      >
        <i class="fa-solid fa-download mr-1"></i> Download current file
      </a>

      <span v-else-if="show === true || show === 'true'" class="block mt-1 text-sm text-gray-400 italic">
        No file uploaded
      </span>

      <span
        class="gl-span-form-error"
        v-if="form.errors.has('q-' + question.id)"
        v-text="form.errors.get('q-' + question.id)"
      ></span>
    </div>
  </div>
</template>

<script>
export default {
  components: {},
  props: ["question", "form", "form_data", "show"],

  computed: {
    isDownloadable() {
      const v = this.form_data['q-' + this.question.id];
      return typeof v === 'string' && v.length > 0;
    },
  },

  methods: {
    onFileChange(event) {
      this.form_data['q-' + this.question.id] = event.target.files[0] || '';
    },
  },
};
</script>
