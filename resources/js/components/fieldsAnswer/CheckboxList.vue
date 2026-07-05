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



  <small  v-if="question.description !== ''" class="block mt-1 ms-4 text-sm font-normal leading-5 text-gray-500">
             {{ getTranslatedField(question, 'description') }}
  </small>

    <div class="ms-4 mt-2">


   <div class="flex-auto">
            <div class="space-y-2">
                <label v-for="(option, index) in (this.question.options || [])" :key="index" class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <input type="checkbox" :name="'q-' + question.id+'[]'" :disabled="show === true || show === 'true'" @input="form.errors.clear('q-' + question.id)" v-model="form_data['q-' + question.id]"  :id="'Checkbox'+index" :value="option" /> {{ option }}
                </label>
            </div>
    </div>








     <span class="gl-span-form-error"
      v-if="form.errors.has('q-' + question.id)"
      v-text="form.errors.get('q-' + question.id)">
    </span>
    </div>
  </div>
</template>

<script>
export default {
  components: {},
  props: ["question", "form", "form_data", "show"],

  data() {
    return {

        checkedEngagements: [],





    };
  },

  methods: {

  },

  mounted() {
    const key = 'q-' + this.question.id;
    if (!Array.isArray(this.form_data[key])) {
      this.form_data[key] = [];
    }
  },
};
</script>

<style scoped lang="scss" >
</style>

