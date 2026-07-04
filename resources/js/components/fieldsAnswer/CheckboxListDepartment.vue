<template>
  <div class="form-group">
    <label
      :class="[question.required == true ? 'required' : '']"
      :for="'q-' + question.id"
      >{{  show_number ? question.order + ". " + getTranslatedField(question, 'label') : getTranslatedField(question, 'label') }}</label
    >

    <small
      v-if="question.description != ''"
      class="form-text text-muted ms-4"
      >{{ getTranslatedField(question, 'description') }}</small
    >
    <div class="ms-4 mt-2">


<div class="form-check" v-for="(option, index) in this.question.options" :key="index">
  <input  v-model="form_data['q-' + question.id]"  :name="'q-' + question.id+'[]'" class="form-check-input" type="checkbox"  @input="form.errors.clear('q-' + question.id)"   :id="'Checkbox_'+question.id+'_'+index" :value="option">
  <label class="form-check-label" :for="'Checkbox_'+question.id+'_'+index">
    {{ option }}
  </label>
</div>



      <span
        class="text-danger pt-3 pb-3"
        v-if="form.errors.has('q-' + question.id)"
        v-text="form.errors.get('q-' + question.id)"
      ></span>
    </div>
  </div>
</template>

<script>
export default {
  components: {},
  props: ["question", "form", "form_data", "show_number","show"],

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


   // this.form_data['q-' + this.question.id]=[];
   // console.log(form_data['q-' + question.id]);



  },
};
</script>

<style scoped lang="scss" >
</style>

