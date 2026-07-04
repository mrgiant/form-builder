<template>
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">
        <div class="form-group">
          <label class="" for="">{{
             show_number ? question.order + ". " + question.label: question.label
          }}</label>
            <small  v-if="question.description!=''" class="form-text text-muted" :class="[question.is_rtl == 1 ? ' mr-4 ' : ' ml-4']">{{ question.description }}</small>
        </div>
      </h3>

      <div class="card-tools">
        <!-- Collapse Button -->
        <button
          type="button"
          name="edit"
          v-on:click.prevent="EditQuestion(question.id)"
          class="edit btn btn-primary btn-sm ml-1"
        >
          <i class="fa fa-edit" aria-hidden="true"></i>
        </button>

        <button
          class="btn btn-danger btn-sm ml-1"
          type="button"
          v-on:click.prevent="deleteQuestion(question.id)"
        >
          <i class="fa fa-trash" aria-hidden="true"></i>
        </button>

        <button type="button" class="btn btn-tool" data-card-widget="collapse">
          <i class="fas fa-minus"></i>
        </button>
      </div>
      <!-- /.card-tools -->
    </div>
    <!-- /.card-header -->
    <div class="card-body">


             <draggable
          class="row"
          group="children"
          :sort="true"
          v-model="question.children"
          :options="{ animation: 200,group: 'children' }"
          item-key="id"

          @change="QuestionsUpdateOrder(question.children,question.id)"
        >

       <template #item="{element, index}">
          <div


             :class="element.question_size_col=='' ? 'col-md-12':element.question_size_col"
              class="col-12"

          >




           <group
              v-if="element.question_type == 'Group'"
              :question="element"
              :QuestionsUpdateOrder="QuestionsUpdateOrder"
              :EditQuestion="EditQuestion"
              :remove_question="remove_question"
              :index="index"
              :show_number="show_number"
            ></group>




            <text-input
              v-if="element.question_type == 'Short answer'"
              :question="element"
              :QuestionsUpdateOrder="QuestionsUpdateOrder"
              :EditQuestion="EditQuestion"
              :remove_question="remove_question"
              :index="index"
              :show_number="show_number"
            ></text-input>

            <email-input
              v-if="element.question_type == 'Email'"
              :question="element"
              :QuestionsUpdateOrder="QuestionsUpdateOrder"
              :EditQuestion="EditQuestion"
              :remove_question="remove_question"
              :index="index"
              :show_number="show_number"
            ></email-input>

            <file-upload-input
              v-if="element.question_type == 'File upload'"
              :question="element"
              :QuestionsUpdateOrder="QuestionsUpdateOrder"
              :EditQuestion="EditQuestion"
              :remove_question="remove_question"
              :index="index"
              :show_number="show_number"
            ></file-upload-input>

            <number-input
              v-if="element.question_type == 'Number'"
              :question="element"
              :QuestionsUpdateOrder="QuestionsUpdateOrder"
              :EditQuestion="EditQuestion"
              :remove_question="remove_question"
              :index="index"
              :show_number="show_number"
            ></number-input>

            <date-input
              v-if="element.question_type == 'Date'"
              :question="element"
              :QuestionsUpdateOrder="QuestionsUpdateOrder"
              :EditQuestion="EditQuestion"
              :remove_question="remove_question"
              :index="index"
              :show_number="show_number"
            ></date-input>

            <text-area
              v-if="element.question_type == 'Paragraph'"
              :question="element"
              :QuestionsUpdateOrder="QuestionsUpdateOrder"
              :EditQuestion="EditQuestion"
              :remove_question="remove_question"
              :index="index"
              :show_number="show_number"
            ></text-area>

            <select-option
              v-if="element.question_type == 'Drop-down'"
              :question="element"
              :QuestionsUpdateOrder="QuestionsUpdateOrder"
              :EditQuestion="EditQuestion"
              :remove_question="remove_question"
              :index="index"
              :show_number="show_number"
            ></select-option>

            <checkbox-list
              v-if="element.question_type == 'Checkboxes'"
              :question="element"
              :QuestionsUpdateOrder="QuestionsUpdateOrder"
              :EditQuestion="EditQuestion"
              :remove_question="remove_question"
              :index="index"
              :show_number="show_number"
            ></checkbox-list>

            <radio-button
              v-if="element.question_type == 'Multiple choice'"
              :question="element"
              :QuestionsUpdateOrder="QuestionsUpdateOrder"
              :EditQuestion="EditQuestion"
              :remove_question="remove_question"
              :index="index"
              :show_number="show_number"
            ></radio-button>

            <label-freetext
                v-if="element.question_type == 'Label'"
                :question="element"
              :QuestionsUpdateOrder="QuestionsUpdateOrder"
              :EditQuestion="EditQuestion"
              :remove_question="remove_question"
              :index="index"
              :show_number="show_number"
              ></label-freetext>
          </div>
           </template>
        </draggable>









    </div>
    <!-- /.card-body -->
  </div>
  <!-- /.card -->
</template>

<script>
//import Group from "./Group.vue";
import TextInput from "./Text.vue";
import NumberInput from "./Number.vue";
import EmailInput from "./Email.vue";
import FileUploadInput from "./FileUpload.vue";
import DateInput from "./Date.vue";
import TextArea from "./TextArea.vue";
import SelectOption from "./Select.vue";
import CheckboxList from "./CheckboxList.vue";
import RadioButton from "./RadioButton.vue";
import draggable from "vuedraggable";
import LabelFreetext from "./LabelFreetext.vue";

export default {
  components: {
  //  Group,
    TextInput,
    NumberInput,
    EmailInput,
    FileUploadInput,
    DateInput,
    TextArea,
    SelectOption,
    CheckboxList,
    RadioButton,
    draggable,
    LabelFreetext
  },
  props: [
    "question",
    "EditQuestion",
    "QuestionsUpdateOrder",
    "remove_question",
    "index",
    "show_number",
  ],

  methods: {
    deleteQuestion(id) {
      this.$swal({
        title: "Are you sure?",
        text: "You can't revert your action",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes Delete it!",
        cancelButtonText: "No, Keep it!",
        showCloseButton: true,
        showLoaderOnConfirm: true,
      }).then((result) => {
        if (result.value) {
          axios

            .post(
              "/admin/forms/" + this.question.form_id + "/questions/" + id + "",
              { _method: "DELETE" }
            )
            .then((res) => {
              //this.$parent.$options.methods.getQuestions();
              //this.getQuestions();

              //this.$parent.$options.methods.getQuestions();

              this.remove_question(this.index);
              this.QuestionsUpdateOrder(this.question.children,this.question.id);

              this.$swal(
                "Deleted",
                "You successfully deleted this question",
                "success"
              );
            })
            .catch((error) => {
              //this.form.errors.record(error.response.data.errors);
            });
        } else {
          this.$swal("Cancelled", "Your question is still intact", "info");
        }
      });
    },
  },
};
</script>

<style scoped lang="scss" >
</style>

