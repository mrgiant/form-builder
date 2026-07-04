<template>
    <!-- Add Modal -->
    <modal :class="`${model}Modal`" :is_open="isOpen" :is_loading="isLoadingAdd" @closeModal="isOpen = false"
        title="Add Form" max_width="max-w-2xl">
        <template v-slot:body>
            <language-selector :field_name="`${model}`" :trans_selector_name="`${model}Selector`"></language-selector>

            <div class="grid grid-cols-1 gap-4 mt-4">
                <text-translate field_name="name" label_name="Form Name" type="text" :is_required="true" :show="false"
                    v-model="form.name" v-model:modelValueTranslate="form.name_i18n"
                    @keydown="form.errors.clear('name')"
                    :error_message="form.errors.get('name')"></text-translate>

                <textarea-translate field_name="description" label_name="Description" :is_required="false" :show="false"
                    v-model="form.description" v-model:modelValueTranslate="form.description_i18n"
                    @keydown="form.errors.clear('description')"
                    :error_message="form.errors.get('description')"></textarea-translate>

                <div class="grid grid-cols-2 gap-4">
                    <text-input v-model="form.begin_at" type="datetime-local" :is_required="false" :show="false"
                        field_name="add_begin_at" label_name="Begin At" />
                    <text-input v-model="form.end_at" type="datetime-local" :is_required="false" :show="false"
                        field_name="add_end_at" label_name="End At" />
                </div>

                <textarea-translate field_name="thank_you_message" label_name="Thank You Message" :is_required="false"
                    :show="false" v-model="form.thank_you_message"
                    v-model:modelValueTranslate="form.thank_you_message_i18n"
                    @keydown="form.errors.clear('thank_you_message')"
                    :error_message="form.errors.get('thank_you_message')"></textarea-translate>

                <textarea-translate field_name="close_message" label_name="Close Message" :is_required="false"
                    :show="false" v-model="form.close_message"
                    v-model:modelValueTranslate="form.close_message_i18n"
                    @keydown="form.errors.clear('close_message')"
                    :error_message="form.errors.get('close_message')"></textarea-translate>

                <textarea-translate field_name="not_start_message" label_name="Not Started Message" :is_required="false"
                    :show="false" v-model="form.not_start_message"
                    v-model:modelValueTranslate="form.not_start_message_i18n"
                    @keydown="form.errors.clear('not_start_message')"
                    :error_message="form.errors.get('not_start_message')"></textarea-translate>

                <div class="grid grid-cols-2 gap-4">
                    <toggle-box v-model="form.status" :show="false" field_name="add_status" label_name="Active" />
                    <toggle-box v-model="form.show_number" :show="false" field_name="add_show_number" label_name="Show Question Numbers" />
                    <toggle-box v-model="form.email_notifications_new_responses" :show="false"
                        field_name="add_email_notif" label_name="Email Notifications" />
                </div>
            </div>
        </template>
        <template v-slot:buttons>
            <gl-button @click="save()" :is_loading="isLoadingAddButton" tag="button" button_type="primary"
                icon="fa-solid fa-plus">Save</gl-button>
        </template>
    </modal>

    <!-- Edit Modal -->
    <modal :class="`${model}Modal`" :is_open="isOpenUpdate" :is_loading="isLoadingUpdate"
        @closeModal="isOpenUpdate = false" title="Edit Form" max_width="max-w-2xl">
        <template v-slot:body>
            <language-selector :field_name="`${model}`" :trans_selector_name="`${model}Selector`"></language-selector>

            <div class="grid grid-cols-1 gap-4 mt-4">
                <text-translate field_name="name" label_name="Form Name" type="text" :is_required="true" :show="false"
                    v-model="form_update.name" v-model:modelValueTranslate="form_update.name_i18n"
                    @keydown="form_update.errors.clear('name')"
                    :error_message="form_update.errors.get('name')"></text-translate>

                <textarea-translate field_name="description" label_name="Description" :is_required="false" :show="false"
                    v-model="form_update.description" v-model:modelValueTranslate="form_update.description_i18n"
                    @keydown="form_update.errors.clear('description')"
                    :error_message="form_update.errors.get('description')"></textarea-translate>

                <div class="grid grid-cols-2 gap-4">
                    <text-input v-model="form_update.begin_at" type="datetime-local" :is_required="false" :show="false"
                        field_name="edit_begin_at" label_name="Begin At" />
                    <text-input v-model="form_update.end_at" type="datetime-local" :is_required="false" :show="false"
                        field_name="edit_end_at" label_name="End At" />
                </div>

                <textarea-translate field_name="thank_you_message" label_name="Thank You Message" :is_required="false"
                    :show="false" v-model="form_update.thank_you_message"
                    v-model:modelValueTranslate="form_update.thank_you_message_i18n"
                    @keydown="form_update.errors.clear('thank_you_message')"
                    :error_message="form_update.errors.get('thank_you_message')"></textarea-translate>

                <textarea-translate field_name="close_message" label_name="Close Message" :is_required="false"
                    :show="false" v-model="form_update.close_message"
                    v-model:modelValueTranslate="form_update.close_message_i18n"
                    @keydown="form_update.errors.clear('close_message')"
                    :error_message="form_update.errors.get('close_message')"></textarea-translate>

                <textarea-translate field_name="not_start_message" label_name="Not Started Message" :is_required="false"
                    :show="false" v-model="form_update.not_start_message"
                    v-model:modelValueTranslate="form_update.not_start_message_i18n"
                    @keydown="form_update.errors.clear('not_start_message')"
                    :error_message="form_update.errors.get('not_start_message')"></textarea-translate>

                <div class="grid grid-cols-2 gap-4">
                    <toggle-box v-model="form_update.status" :show="false" field_name="edit_status" label_name="Active" />
                    <toggle-box v-model="form_update.show_number" :show="false" field_name="edit_show_number" label_name="Show Question Numbers" />
                    <toggle-box v-model="form_update.email_notifications_new_responses" :show="false"
                        field_name="edit_email_notif" label_name="Email Notifications" />
                </div>
            </div>
        </template>
        <template v-slot:buttons>
            <gl-button @click="update()" :is_loading="isLoadingUpdateButton" tag="button" button_type="primary"
                icon="fa-solid fa-floppy-disk">Update</gl-button>
        </template>
    </modal>

    <Card>
        <template #header>
            <div class="flex justify-between items-center">
                <span>Forms List</span>
                <gl-button v-if="can('create_form')" tag="button" @click="openAdd()"
                    button_type="primary" icon="fa-solid fa-plus">Add Form</gl-button>
            </div>
        </template>
        <template #body>
            <gl-data-table-server-side ref="glDataTableRef" :columns="columns" :xprops="xprops"
                @editAction="edit"
                @deleteAction="() => $refs.glDataTableRef.GetItemLists()">
            </gl-data-table-server-side>
        </template>
    </Card>
</template>

<script>
import { GlToast } from 'golden-logic-ui';
import { markRaw } from 'vue';
import DatatableAction from '@/components/cruds/Forms/DatatableAction.vue';
import { loadMultilingual, destroyMultilingual, prepareDataMultilingual } from "@/services/multilingualService";

const StatusBadge = {
    props: ['row'],
    template: `<span :class="row.status ? 'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'">{{ row.status ? 'Active' : 'Inactive' }}</span>`,
};

const emptyForm = {
    name: '',
    name_i18n: {},
    description: '',
    description_i18n: {},
    thank_you_message: '',
    thank_you_message_i18n: {},
    close_message: '',
    close_message_i18n: {},
    not_start_message: '',
    not_start_message_i18n: {},
    begin_at: '',
    end_at: '',
    status: true,
    show_number: false,
    email_notifications_new_responses: false,
};

export default {
    data() {
        return {
            model: "Form",

            isOpen: false,
            isLoadingAdd: false,
            isLoadingAddButton: false,

            isOpenUpdate: false,
            isLoadingUpdate: false,
            isLoadingUpdateButton: false,

            form: new Form({ ...emptyForm }),

            form_update: new Form({ id: null, ...emptyForm }),

            columns: [
                { field_name: 'id',                field_label: 'ID',        sortable: true  },
                { field_name: 'name',              field_label: 'Name',      sortable: true  },
                { field_name: 'slug',              field_label: 'Slug',      sortable: false },
                { field_name: 'responses_count_f', field_label: 'Responses', sortable: false },
                { field_name: 'begin_at_f',        field_label: 'Begin At',  sortable: false },
                { field_name: 'end_at_f',          field_label: 'End At',    sortable: false },
                { field_name: 'status',            field_label: 'Status',    sortable: true, tdComp: markRaw(StatusBadge) },
                { field_name: 'action',            field_label: 'Action',    tdComp: markRaw(DatatableAction) },
            ],

            xprops: {
                route:          '/admin/forms',
                route_get_data: '/admin/forms/getFormsList',
                permission:     'form',
            },
        };
    },

    methods: {
        openAdd() {
            this.form = new Form({ ...emptyForm, status: true });

            this.isLoadingAdd = true;
            this.isOpen = true;
            loadMultilingual(this.model, 6, this.isLoadingAdd, (newState) => {
                this.isLoadingAdd = newState;
            });
        },

        save() {
            this.isLoadingAddButton = true;
            prepareDataMultilingual();
            axios.post('/admin/forms', {
                name:                               this.form.name,
                name_i18n:                          this.form.name_i18n,
                description:                        this.form.description,
                description_i18n:                   this.form.description_i18n,
                thank_you_message:                  this.form.thank_you_message,
                thank_you_message_i18n:             this.form.thank_you_message_i18n,
                close_message:                      this.form.close_message,
                close_message_i18n:                 this.form.close_message_i18n,
                not_start_message:                  this.form.not_start_message,
                not_start_message_i18n:             this.form.not_start_message_i18n,
                begin_at:                           this.form.begin_at,
                end_at:                             this.form.end_at,
                status:                             this.form.status ? 1 : 0,
                show_number:                        this.form.show_number ? 1 : 0,
                email_notifications_new_responses:  this.form.email_notifications_new_responses ? 1 : 0,
            }).then(() => {
                this.form = new Form({ ...emptyForm });
                this.$refs.glDataTableRef.GetItemLists();
                this.isOpen = false;
                this.isLoadingAddButton = false;
                GlToast.methods.add({ message: 'Form added successfully.', type: 'success', duration: 4000 });
            }).catch((error) => {
                if (error.response && error.response.data && error.response.data.errors) {
                    this.form.errors.record(error.response.data.errors);
                }
                this.isLoadingAddButton = false;
            });
        },

        edit(item) {
            this.form_update = new Form({ id: null, ...emptyForm });

            this.isLoadingUpdate = true;
            this.isOpenUpdate = true;

            this.form_update.id                               = item.id;
            this.form_update.name                             = item.name;
            this.form_update.name_i18n                        = item.all_translation_feilds?.name_i18n ?? {};
            this.form_update.description                      = item.description || '';
            this.form_update.description_i18n                 = item.all_translation_feilds?.description_i18n ?? {};
            this.form_update.thank_you_message                = item.thank_you_message || '';
            this.form_update.thank_you_message_i18n           = item.all_translation_feilds?.thank_you_message_i18n ?? {};
            this.form_update.close_message                    = item.close_message || '';
            this.form_update.close_message_i18n               = item.all_translation_feilds?.close_message_i18n ?? {};
            this.form_update.not_start_message                = item.not_start_message || '';
            this.form_update.not_start_message_i18n           = item.all_translation_feilds?.not_start_message_i18n ?? {};
            this.form_update.begin_at                         = item.begin_at ? item.begin_at.substring(0, 16) : '';
            this.form_update.end_at                           = item.end_at   ? item.end_at.substring(0, 16)   : '';
            this.form_update.status                           = !!item.status;
            this.form_update.show_number                      = !!item.show_number;
            this.form_update.email_notifications_new_responses = !!item.email_notifications_new_responses;

            loadMultilingual(this.model, 6, this.isLoadingUpdate, (newState) => {
                this.isLoadingUpdate = newState;
            });
        },

        update() {
            this.isLoadingUpdateButton = true;
            prepareDataMultilingual();
            axios.put(`/admin/forms/${this.form_update.id}`, {
                name:                               this.form_update.name,
                name_i18n:                          this.form_update.name_i18n,
                description:                        this.form_update.description,
                description_i18n:                   this.form_update.description_i18n,
                thank_you_message:                  this.form_update.thank_you_message,
                thank_you_message_i18n:             this.form_update.thank_you_message_i18n,
                close_message:                      this.form_update.close_message,
                close_message_i18n:                 this.form_update.close_message_i18n,
                not_start_message:                  this.form_update.not_start_message,
                not_start_message_i18n:             this.form_update.not_start_message_i18n,
                begin_at:                           this.form_update.begin_at,
                end_at:                             this.form_update.end_at,
                status:                             this.form_update.status ? 1 : 0,
                show_number:                        this.form_update.show_number ? 1 : 0,
                email_notifications_new_responses:  this.form_update.email_notifications_new_responses ? 1 : 0,
            }).then(() => {
                this.$refs.glDataTableRef.GetItemLists();
                this.isOpenUpdate = false;
                this.isLoadingUpdateButton = false;
                GlToast.methods.add({ message: 'Form updated successfully.', type: 'success', duration: 4000 });
            }).catch((error) => {
                if (error.response && error.response.data && error.response.data.errors) {
                    this.form_update.errors.record(error.response.data.errors);
                }
                this.isLoadingUpdateButton = false;
            });
        },
    },

    beforeUnmount() {
        destroyMultilingual();
    },
};
</script>
