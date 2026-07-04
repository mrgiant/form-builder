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
      <div class="form-group">
        <input type="time" class="form-control" />
      </div>
    </div>
    <!-- /.card-body -->
  </div>
  <!-- /.card -->
</template>

<script>
export default {
  components: {},
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
              this.QuestionsUpdateOrder();

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

