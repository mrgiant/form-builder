<template>
    <GlDynamicConfirmation ref="ConfirmationDelete"></GlDynamicConfirmation>

    <div class="bg-white border rounded-lg border-stroke shadow-default dark:border-white/10 dark:bg-gray-900">
        <div class="flex justify-between px-3 py-4 border-b border-stroke dark:border-white/10">
            <h3>
                <div>
                    <label>{{ question.order + ". " + question.label }}</label>
                    <small v-if="question.description != ''" class="block mt-1 text-gray-700 dark:text-gray-400"
                        :class="[question.is_rtl == 1 ? 'mr-4' : 'ml-4']">{{ question.description }}</small>
                </div>
            </h3>
            <div class="flex items-center gap-1">
                <button type="button" v-on:click.prevent="EditQuestion(question.id)"
                    class="inline-block w-8 h-8 mb-1 mr-1 leading-8 text-center text-white bg-blue-600 rounded-full cursor-pointer hover:bg-blue-700">
                    <i class="fa fa-edit"></i>
                </button>
                <button type="button" v-on:click.prevent="deleteQuestion(question.id)"
                    class="inline-block w-8 h-8 mb-1 mr-1 leading-8 text-center text-white bg-red-600 rounded-full cursor-pointer hover:bg-red-700">
                    <i class="fa fa-trash"></i>
                </button>
                <button v-on:click="toggleMinus(question.id)" type="button"
                    class="inline-block w-8 h-8 mb-1 mr-1 leading-8 text-center rounded-full cursor-pointer border border-gray-300 dark:border-gray-600">
                    <i class="fas" :class="isMinus[question.id] ? 'fa-plus' : 'fa-minus'"></i>
                </button>
            </div>
        </div>
        <div class="flex-auto p-6" v-if="!isMinus[question.id]">
            <p class="text-base font-medium text-gray-800 dark:text-gray-200">{{ question.label }}</p>
        </div>
    </div>
</template>

<script>
import { GlDynamicConfirmation, GlToast } from "golden-logic-ui";

export default {
    components: { GlDynamicConfirmation },
    props: ["question", "EditQuestion", "QuestionsUpdateOrder", "remove_question", "index", "delete_base_url"],

    data() {
        return { isMinus: [] };
    },

    methods: {
        toggleMinus(index) {
            this.isMinus[index] = !this.isMinus[index];
        },

        async deleteQuestion(id) {
            const ok = await this.$refs.ConfirmationDelete.show({
                title: "Delete Question",
                message: "Are you sure you want to delete this question? This action cannot be undone.",
                okButton: "Yes, delete it",
            });
            if (ok) {
                axios.post(`${this.delete_base_url}/${id}`, { _method: "DELETE" })
                    .then(() => {
                        this.remove_question(this.index);
                        this.QuestionsUpdateOrder();
                        GlToast.methods.add({ message: "Question deleted successfully.", type: "success", duration: 5000 });
                    });
            }
        },
    },
};
</script>
