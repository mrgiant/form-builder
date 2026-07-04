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
    <input
      v-model="form_data['q-' + question.id]"

      :class="{
                        'gl-input-form': !form.errors.has('q-' + question.id),
                        'gl-input-form-invalid': form.errors.has('q-' + question.id),
                       
                    }"


    
      :type="InputType"
      :name="'q-' + question.id"
    
      @keydown="form.errors.clear('q-' + question.id)"
    />

       

     <span class="gl-span-form-error"
      v-if="form.errors.has('q-' + question.id)"
      v-text="form.errors.get('q-' + question.id)"
     ></span>

  </div>
</div>

</template>

<script>
export default {
  components: {},
  props: ["question", "form", "form_data","InputType"],

  methods: {},
};
</script>

<style scoped lang="scss" >
</style>

