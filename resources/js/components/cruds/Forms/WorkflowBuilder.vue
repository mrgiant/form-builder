<template>
    <!-- Node config modal -->
    <modal class="WorkflowNodeModal" :is_open="isOpenNode" :is_loading="false"
        @closeModal="isOpenNode = false" :title="`${typeMeta[editType].label} node`" max_width="max-w-lg">
        <template v-slot:body>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                <input v-model="editNode.name" type="text" :placeholder="typeMeta[editType].label"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary">
            </div>

            <template v-if="editType === 'approval'">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Approval rule</label>
                    <div class="inline-flex rounded-md border border-gray-300 dark:border-gray-600 overflow-hidden">
                        <button type="button" @click="editNode.mode = 'any'"
                            :class="editNode.mode === 'any' ? 'bg-primary text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300'"
                            class="px-3 py-1.5 text-sm font-medium">Any one approves</button>
                        <button type="button" @click="editNode.mode = 'all'"
                            :class="editNode.mode === 'all' ? 'bg-primary text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300'"
                            class="px-3 py-1.5 text-sm font-medium border-l border-gray-300 dark:border-gray-600">All must approve</button>
                    </div>
                </div>
                <div class="mb-4">
                    <multi-select-dropdown :options="userOptions" :is_required="true" field_name="node_users"
                        :label_name="'Approvers'" :show="false" v-model="editNode.userIds" placeholder="Select approvers" />
                </div>
            </template>

            <template v-else-if="editType === 'notification'">
                <div class="mb-4">
                    <multi-select-dropdown :options="userOptions" :is_required="true" field_name="node_users"
                        :label_name="'Recipients'" :show="false" v-model="editNode.userIds" placeholder="Select recipients" />
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Message</label>
                    <textarea v-model="editNode.config.message" rows="3" placeholder="Message to send…"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
                </div>
            </template>

            <template v-else-if="editType === 'update'">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">What to update</label>
                    <div class="inline-flex rounded-md border border-gray-300 dark:border-gray-600 overflow-hidden">
                        <button type="button" @click="editNode.config.target = 'approval_status'"
                            :class="editNode.config.target === 'approval_status' ? 'bg-primary text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300'"
                            class="px-3 py-1.5 text-sm font-medium">Approval status</button>
                        <button type="button" @click="editNode.config.target = 'answer'"
                            :class="editNode.config.target === 'answer' ? 'bg-primary text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300'"
                            class="px-3 py-1.5 text-sm font-medium border-l border-gray-300 dark:border-gray-600">Answer field</button>
                    </div>
                </div>
                <div v-if="editNode.config.target === 'approval_status'" class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Set status to</label>
                    <select v-model="editNode.config.status"
                        class="appearance-none w-full px-2 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>
                <div v-else class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fields to update</label>
                    <div class="space-y-2">
                        <div v-for="(u, ui) in editNode.config.updates" :key="ui" class="flex items-center gap-2">
                            <select v-model="u.field"
                                class="appearance-none flex-1 min-w-0 px-2 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                                <option :value="null" disabled>Question…</option>
                                <option v-for="q in questions" :key="q.id" :value="q.id">{{ q.label }}</option>
                            </select>
                            <span class="shrink-0 text-gray-400 text-sm">=</span>
                            <input v-model="u.value" type="text" placeholder="New value"
                                class="flex-1 min-w-0 px-2 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                            <button type="button" @click="removeUpdate(ui)" :disabled="editNode.config.updates.length === 1"
                                class="shrink-0 w-8 h-9 flex items-center justify-center rounded-md text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 disabled:opacity-30 disabled:hover:text-gray-400 disabled:hover:bg-transparent" title="Remove field"><i class="fa-solid fa-xmark"></i></button>
                        </div>
                    </div>
                    <button type="button" @click="addUpdate()" class="mt-2 inline-flex items-center gap-1 text-sm text-primary hover:underline"><i class="fa-solid fa-plus text-xs"></i> Add field</button>
                </div>
                <p class="text-xs text-gray-400">Runs automatically, then continues to the next node — or ends the workflow if left unconnected.</p>
            </template>

            <template v-else-if="editType === 'terminate'">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Final status</label>
                    <select v-model="editNode.config.status"
                        class="appearance-none w-full px-2 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                        <option value="rejected">Rejected</option>
                        <option value="approved">Approved</option>
                        <option value="terminated">Terminated</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Message to submitter (optional)</label>
                    <textarea v-model="editNode.config.message" rows="2" placeholder="Reason shown to the submitter…"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
                </div>
                <p class="text-xs text-gray-400">Ends the workflow immediately — this node has no outgoing connection.</p>
            </template>

            <template v-else-if="editType === 'http'">
                <div class="mb-4 flex gap-2">
                    <div class="w-28 shrink-0">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Method</label>
                        <select v-model="editNode.config.method"
                            class="appearance-none w-full px-2 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                            <option v-for="m in httpMethods" :key="m" :value="m">{{ m }}</option>
                        </select>
                    </div>
                    <div class="flex-1 min-w-0">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">URL</label>
                        <input v-model="editNode.config.url" type="text" placeholder="https://api.example.com/endpoint"
                            @focus="setHttpTarget(editNode.config, 'url', $event)"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                </div>

                <div class="mb-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 p-3">
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Insert a field placeholder into the box you last clicked</label>
                    <select @change="insertField($event.target.value); $event.target.value = ''"
                        class="appearance-none w-full px-2 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                        <option value="" disabled selected>Choose a field…</option>
                        <option value="response_id">Response ID</option>
                        <option v-for="q in questions" :key="q.id" :value="'q-' + q.id">{{ q.label }}</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Headers</label>
                    <div class="space-y-2">
                        <div v-for="(h, hi) in editNode.config.headers" :key="hi" class="flex items-center gap-2">
                            <input v-model="h.key" type="text" placeholder="Header"
                                class="flex-1 min-w-0 px-2 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                            <input v-model="h.value" type="text" placeholder="Value"
                                @focus="setHttpTarget(h, 'value', $event)"
                                class="flex-1 min-w-0 px-2 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                            <button type="button" @click="removeKv('headers', hi)"
                                class="shrink-0 w-8 h-9 flex items-center justify-center rounded-md text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30" title="Remove header"><i class="fa-solid fa-xmark"></i></button>
                        </div>
                    </div>
                    <button type="button" @click="addKv('headers')" class="mt-2 inline-flex items-center gap-1 text-sm text-primary hover:underline"><i class="fa-solid fa-plus text-xs"></i> Add header</button>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Query parameters</label>
                    <div class="space-y-2">
                        <div v-for="(qp, qi) in editNode.config.query" :key="qi" class="flex items-center gap-2">
                            <input v-model="qp.key" type="text" placeholder="Name"
                                class="flex-1 min-w-0 px-2 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                            <input v-model="qp.value" type="text" placeholder="Value"
                                @focus="setHttpTarget(qp, 'value', $event)"
                                class="flex-1 min-w-0 px-2 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                            <button type="button" @click="removeKv('query', qi)"
                                class="shrink-0 w-8 h-9 flex items-center justify-center rounded-md text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30" title="Remove parameter"><i class="fa-solid fa-xmark"></i></button>
                        </div>
                    </div>
                    <button type="button" @click="addKv('query')" class="mt-2 inline-flex items-center gap-1 text-sm text-primary hover:underline"><i class="fa-solid fa-plus text-xs"></i> Add parameter</button>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Authentication</label>
                    <select v-model="editNode.config.auth.type"
                        class="appearance-none w-full px-2 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                        <option value="none">None</option>
                        <option value="basic">Basic Auth</option>
                        <option value="bearer">Bearer Token</option>
                    </select>
                    <div v-if="editNode.config.auth.type === 'basic'" class="mt-2 flex gap-2">
                        <input v-model="editNode.config.auth.username" type="text" placeholder="Username"
                            class="flex-1 min-w-0 px-2 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                        <input v-model="editNode.config.auth.password" type="password" placeholder="Password"
                            class="flex-1 min-w-0 px-2 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                    </div>
                    <div v-else-if="editNode.config.auth.type === 'bearer'" class="mt-2">
                        <input v-model="editNode.config.auth.token" type="text" placeholder="Token"
                            @focus="setHttpTarget(editNode.config.auth, 'token', $event)"
                            class="w-full px-2 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Body</label>
                    <select v-model="editNode.config.body_type"
                        class="appearance-none w-full px-2 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                        <option value="none">None</option>
                        <option value="json">JSON</option>
                        <option value="form">Form data</option>
                        <option value="answers">All answers (JSON)</option>
                        <option value="raw">Raw</option>
                    </select>
                    <div v-if="editNode.config.body_type === 'answers'" class="mt-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Key answers by</label>
                        <div class="inline-flex rounded-md border border-gray-300 dark:border-gray-600 overflow-hidden">
                            <button type="button" @click="editNode.config.answers_key = 'label'"
                                :class="editNode.config.answers_key !== 'id' ? 'bg-primary text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300'"
                                class="px-3 py-1.5 text-sm font-medium">Question name</button>
                            <button type="button" @click="editNode.config.answers_key = 'id'"
                                :class="editNode.config.answers_key === 'id' ? 'bg-primary text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300'"
                                class="px-3 py-1.5 text-sm font-medium border-l border-gray-300 dark:border-gray-600">Question ID</button>
                        </div>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Sends every answer as JSON under <code v-pre class="px-1 rounded bg-gray-100 dark:bg-gray-700">{ response_id, form_id, form_name, answers }</code>, keyed by the {{ editNode.config.answers_key === 'id' ? 'question id (q-ID)' : 'question name' }}.</p>
                    </div>
                    <template v-if="editNode.config.body_type === 'json' || editNode.config.body_type === 'form'">
                        <div class="space-y-2 mt-2">
                            <div v-for="(b, bi) in editNode.config.body" :key="bi" class="flex items-center gap-2">
                                <input v-model="b.key" type="text" placeholder="Field"
                                    class="flex-1 min-w-0 px-2 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                                <input v-model="b.value" type="text" placeholder="Value"
                                    @focus="setHttpTarget(b, 'value', $event)"
                                    class="flex-1 min-w-0 px-2 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                                <button type="button" @click="removeKv('body', bi)"
                                    class="shrink-0 w-8 h-9 flex items-center justify-center rounded-md text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30" title="Remove field"><i class="fa-solid fa-xmark"></i></button>
                            </div>
                        </div>
                        <button type="button" @click="addKv('body')" class="mt-2 inline-flex items-center gap-1 text-sm text-primary hover:underline"><i class="fa-solid fa-plus text-xs"></i> Add field</button>
                    </template>
                    <textarea v-else-if="editNode.config.body_type === 'raw'" v-model="editNode.config.raw_body" rows="4" placeholder='{ "key": "value" }'
                        @focus="setHttpTarget(editNode.config, 'raw_body', $event)"
                        class="mt-2 w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-mono bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Timeout (seconds)</label>
                    <input v-model.number="editNode.config.timeout" type="number" min="1" max="120"
                        class="w-28 px-2 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                </div>

                <p class="text-xs text-gray-400">Runs automatically, then continues to the next node. Click any box above, then pick a question from <strong>“Insert a field”</strong> to drop its answer in — no need to know IDs.</p>
            </template>

            <template v-else>
                <div class="mb-3 flex items-center gap-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Match</span>
                    <div class="inline-flex rounded-md border border-gray-300 dark:border-gray-600 overflow-hidden">
                        <button type="button" @click="editNode.config.match = 'all'"
                            :class="editNode.config.match !== 'any' ? 'bg-primary text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300'"
                            class="px-3 py-1.5 text-sm font-medium">All (AND)</button>
                        <button type="button" @click="editNode.config.match = 'any'"
                            :class="editNode.config.match === 'any' ? 'bg-primary text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300'"
                            class="px-3 py-1.5 text-sm font-medium border-l border-gray-300 dark:border-gray-600">Any (OR)</button>
                    </div>
                </div>
                <div class="space-y-2 mb-3">
                    <div v-for="(rule, ri) in editNode.config.rules" :key="ri" class="flex items-center gap-2">
                        <span class="w-8 shrink-0 text-[10px] uppercase font-semibold text-primary">{{ ri === 0 ? 'If' : (editNode.config.match === 'any' ? 'Or' : 'And') }}</span>
                        <select v-model="rule.field"
                            class="appearance-none flex-1 min-w-0 px-2 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                            <option :value="null" disabled>Question…</option>
                            <option v-for="q in questions" :key="q.id" :value="q.id">{{ q.label }}</option>
                        </select>
                        <select v-model="rule.operator"
                            class="appearance-none w-28 shrink-0 px-2 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                            <option v-for="op in operators" :key="op.id" :value="op.id">{{ op.name }}</option>
                        </select>
                        <input v-model="rule.value" type="text" placeholder="Value"
                            class="w-24 shrink-0 px-2 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                        <button type="button" @click="removeRule(ri)" :disabled="editNode.config.rules.length === 1"
                            class="shrink-0 w-8 h-9 flex items-center justify-center rounded-md text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 disabled:opacity-30 disabled:hover:text-gray-400 disabled:hover:bg-transparent" title="Remove rule"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                </div>
                <button type="button" @click="addRule()" class="inline-flex items-center gap-1 text-sm text-primary hover:underline"><i class="fa-solid fa-plus text-xs"></i> Add rule</button>
                <p class="text-xs text-gray-400 mt-3">The <strong>True</strong> / <strong>False</strong> ports route the flow based on whether {{ editNode.config.match === 'any' ? 'any' : 'all' }} rule(s) match. A branch left unconnected ends the workflow.</p>
            </template>
        </template>
        <template v-slot:buttons>
            <gl-button @click="applyNode()" tag="button" button_type="primary" icon="fa-solid fa-check">Done</gl-button>
        </template>
    </modal>

    <Card>
        <template v-slot:header>
            <div class="flex flex-wrap justify-between items-center gap-3">
                <div class="flex items-center gap-3">
                    <a href="/admin/forms" class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary"><i class="fa-solid fa-arrow-left mr-1"></i> Back to Forms</a>
                    <span class="text-gray-300 dark:text-gray-600">|</span>
                    <span>Workflow</span>
                    <a :href="`/admin/forms/${form_id}/questions`" class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary"><i class="fa-solid fa-list-check mr-1"></i> Manage Questions</a>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs flex items-center gap-1" :class="saveState === 'error' ? 'text-red-500' : 'text-gray-400'">
                        <template v-if="saveState === 'saving'"><i class="fa-solid fa-spinner fa-spin"></i> Saving…</template>
                        <template v-else-if="saveState === 'saved'"><i class="fa-solid fa-check text-green-500"></i> Saved</template>
                        <template v-else-if="saveState === 'error'"><i class="fa-solid fa-triangle-exclamation"></i> Save failed</template>
                        <template v-else><i class="fa-solid fa-cloud"></i> Auto-saves</template>
                    </span>
                    <button type="button" @click="addNode('approval')" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-md text-sm border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"><i class="fa-solid fa-user-check"></i> Approval</button>
                    <button type="button" @click="addNode('notification')" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-md text-sm border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"><i class="fa-solid fa-bell"></i> Notification</button>
                    <button type="button" @click="addNode('condition')" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-md text-sm border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"><i class="fa-solid fa-code-branch"></i> Condition</button>
                    <button type="button" @click="addNode('update')" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-md text-sm border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"><i class="fa-solid fa-pen-to-square"></i> Update</button>
                    <button type="button" @click="addNode('http')" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-md text-sm border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"><i class="fa-solid fa-globe"></i> HTTP Request</button>
                    <button type="button" @click="addNode('terminate')" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-md text-sm border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"><i class="fa-solid fa-circle-stop"></i> Terminate</button>
                </div>
            </div>
        </template>

        <template v-slot:body>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                Drag nodes to arrange. Click an <span class="text-primary">output dot</span> then a node's
                <span class="text-primary">input dot</span> to connect them. Conditions have True / False outputs.
                A node with no outgoing connection ends the workflow.
            </p>

            <div v-if="warnings.length" class="mb-4 rounded-lg border border-amber-300 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20 px-4 py-3">
                <p class="flex items-center gap-2 text-sm font-medium text-amber-700 dark:text-amber-300 mb-1">
                    <i class="fa-solid fa-triangle-exclamation"></i> {{ warnings.length }} issue{{ warnings.length > 1 ? 's' : '' }} to fix
                </p>
                <ul class="list-disc ps-6 text-xs text-amber-700 dark:text-amber-300 space-y-0.5">
                    <li v-for="(msg, i) in warnings" :key="i">{{ msg }}</li>
                </ul>
            </div>

            <div v-if="isLoading" class="flex justify-center py-8"><i class="fa-solid fa-spinner fa-spin text-2xl text-gray-400"></i></div>

            <div v-else ref="canvas"
                class="relative w-full h-135 overflow-auto rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 bg-[radial-gradient(circle,#00000012_1px,transparent_1px)] dark:bg-[radial-gradient(circle,#ffffff14_1px,transparent_1px)] bg-size-[22px_22px]"
                @mouseup="cancelConnect">
                <div ref="surface" class="relative" :style="{ width: surfaceW + 'px', height: surfaceH + 'px' }">
                    <svg class="absolute inset-0 pointer-events-none" :width="surfaceW" :height="surfaceH">
                        <g v-for="(link, i) in renderedEdges" :key="i">
                            <path :d="link.d" fill="none" stroke-width="2.5"
                                :class="link.branch === 'false' ? 'stroke-red-400' : (link.branch === 'true' ? 'stroke-green-400' : 'stroke-gray-400 dark:stroke-gray-500')" />
                        </g>
                        <path v-if="connecting" :d="tempPath" fill="none" stroke-width="2.5" stroke-dasharray="5 4" class="stroke-blue-500 dark:stroke-blue-400" />
                    </svg>

                    <!-- delete-edge badges (need pointer events, so outside the svg) -->
                    <button v-for="(link, i) in renderedEdges" :key="'x' + i" type="button" @click="removeEdge(link.ref)"
                        class="absolute -translate-x-1/2 -translate-y-1/2 w-5 h-5 rounded-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-400 hover:text-red-600 text-[10px] flex items-center justify-center shadow-sm"
                        :style="{ left: link.mx + 'px', top: link.my + 'px' }" title="Remove connection"><i class="fa-solid fa-xmark"></i></button>

                    <!-- Start -->
                    <div class="absolute w-52 select-none rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm cursor-move"
                        :ref="el => registerEl(el, '__start__')"
                        :style="posStyle(startPos)" @mousedown="startDrag($event, startPos)">
                        <div class="flex items-center gap-2 px-4 py-2.5"><i class="fa-solid fa-paper-plane text-gray-400"></i><span class="font-semibold text-gray-700 dark:text-gray-200">Submitted</span></div>
                        <span class="absolute w-3 h-3 rounded-full bg-primary border-2 border-white cursor-crosshair z-10" :style="portStyle(outPos('__start__', 'next'))" @mousedown.stop="startConnect($event, '__start__', 'next')" title="Drag to connect"></span>
                    </div>

                    <!-- Typed nodes -->
                    <div v-for="(node, index) in nodes" :key="node.key"
                        :ref="el => registerEl(el, node.key)"
                        class="absolute w-52 select-none rounded-xl border shadow-sm cursor-move bg-white dark:bg-gray-900 hover:shadow-md transition-shadow"
                        :class="[typeBorder(node.type), nodeHasIssue(node) ? 'ring-2 ring-amber-400' : '']" :style="posStyle(node)" @mousedown="startDrag($event, node)">
                        <div class="flex items-center justify-between gap-2 px-3 py-2 border-b border-gray-100 dark:border-gray-700">
                            <div class="flex items-center gap-2 min-w-0">
                                <i :class="['fa-solid', typeMeta[node.type].icon, typeText(node.type)]"></i>
                                <span class="font-semibold text-gray-800 dark:text-gray-100 truncate">{{ node.name || typeMeta[node.type].label }}</span>
                            </div>
                            <div class="flex items-center gap-0.5 shrink-0">
                                <button type="button" @mousedown.stop @click="editExisting(index)" class="inline-flex items-center justify-center w-7 h-7 rounded-md text-gray-400 hover:text-primary hover:bg-primary/10 transition-colors" title="Edit"><i class="fa-solid fa-pen text-xs"></i></button>
                                <button type="button" @mousedown.stop @click="removeNode(index)" class="inline-flex items-center justify-center w-7 h-7 rounded-md text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors" title="Delete"><i class="fa-solid fa-trash-alt text-xs"></i></button>
                            </div>
                        </div>
                        <div class="px-3 py-2.5 text-xs">
                            <template v-if="node.type === 'approval'">
                                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-medium" :class="node.mode === 'all' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300'">{{ node.mode === 'all' ? 'All must approve' : 'Any one approves' }}</span>
                                <div class="mt-2 flex flex-wrap gap-1"><span v-for="uid in node.userIds" :key="uid" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-[11px] text-gray-700 dark:text-gray-200"><i class="fa-solid fa-user text-[9px]"></i> {{ userName(uid) }}</span><span v-if="!node.userIds.length" class="text-[11px] text-red-500">No approvers</span></div>
                            </template>
                            <template v-else-if="node.type === 'notification'">
                                <div class="text-gray-500 dark:text-gray-400 truncate">{{ node.config.message || 'No message' }}</div>
                                <div class="mt-2 flex flex-wrap gap-1"><span v-for="uid in node.userIds" :key="uid" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-[11px] text-gray-700 dark:text-gray-200"><i class="fa-solid fa-user text-[9px]"></i> {{ userName(uid) }}</span><span v-if="!node.userIds.length" class="text-[11px] text-red-500">No recipients</span></div>
                            </template>
                            <template v-else-if="node.type === 'update'">
                                <div v-if="node.config.target === 'answer'" class="text-gray-700 dark:text-gray-200 space-y-0.5">
                                    <div v-for="(u, ui) in updateFields(node)" :key="ui">Set <span class="font-medium">{{ questionLabel(u.field) }}</span> = <span class="font-medium">{{ u.value || '…' }}</span></div>
                                </div>
                                <div v-else class="text-gray-700 dark:text-gray-200">Status → <span class="font-medium capitalize">{{ node.config.status || 'approved' }}</span></div>
                            </template>
                            <template v-else-if="node.type === 'http'">
                                <div class="flex items-center gap-1.5">
                                    <span class="inline-block px-2 py-0.5 rounded text-[11px] font-semibold bg-cyan-100 text-cyan-700 dark:bg-cyan-900/40 dark:text-cyan-300">{{ node.config.method || 'GET' }}</span>
                                    <span class="min-w-0 truncate text-gray-600 dark:text-gray-300" :title="node.config.url">{{ node.config.url || 'No URL' }}</span>
                                </div>
                            </template>
                            <template v-else-if="node.type === 'terminate'">
                                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-medium capitalize" :class="node.config.status === 'approved' ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' : (node.config.status === 'rejected' ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' : 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300')">{{ node.config.status || 'terminated' }}</span>
                                <div v-if="node.config.message" class="mt-1 text-gray-500 dark:text-gray-400 truncate">{{ node.config.message }}</div>
                            </template>
                            <template v-else>
                                <div class="text-gray-700 dark:text-gray-200 space-y-0.5">
                                    <div v-for="(rule, ri) in conditionRules(node)" :key="ri">
                                        <span v-if="ri > 0" class="text-[9px] uppercase font-semibold text-primary mr-1">{{ node.config.match === 'any' ? 'or' : 'and' }}</span>
                                        <span class="font-medium">{{ questionLabel(rule.field) }}</span> <span class="text-primary">{{ opLabel(rule.operator) }}</span> <span class="font-medium">{{ rule.value || '…' }}</span>
                                    </div>
                                </div>
                                <div class="mt-1 flex gap-3 text-[10px]"><span class="text-green-600">▲ true</span><span class="text-red-500">▼ false</span></div>
                            </template>
                        </div>

                        <!-- ports -->
                        <span class="absolute w-3 h-3 rounded-full bg-primary border-2 border-white cursor-crosshair z-10" :style="portStyle(inPos(node))" @mousedown.stop @mouseup.stop="finishConnect(node.key)" title="Input"></span>
                        <template v-if="node.type === 'condition'">
                            <span class="absolute w-3 h-3 rounded-full bg-green-500 border-2 border-white cursor-crosshair z-10" :style="portStyle(outPos(node.key, 'true'))" @mousedown.stop="startConnect($event, node.key, 'true')" title="True →"></span>
                            <span class="absolute w-3 h-3 rounded-full bg-red-500 border-2 border-white cursor-crosshair z-10" :style="portStyle(outPos(node.key, 'false'))" @mousedown.stop="startConnect($event, node.key, 'false')" title="False →"></span>
                        </template>
                        <span v-else-if="node.type !== 'terminate'" class="absolute w-3 h-3 rounded-full bg-primary border-2 border-white cursor-crosshair z-10" :style="portStyle(outPos(node.key, 'next'))" @mousedown.stop="startConnect($event, node.key, 'next')" title="Output →"></span>
                    </div>
                </div>
            </div>

            <p v-if="!isLoading && nodes.length === 0" class="text-center py-3 text-gray-400 dark:text-gray-500 text-sm">No nodes yet — add an Approval, Notification, or Condition node to build the workflow.</p>
        </template>
    </Card>
</template>

<script>
import { GlToast } from 'golden-logic-ui';

const W = 208;
const AY = 34;
let keySeq = 1;

export default {
    props: ['form_id'],

    data() {
        return {
            nodes: [],
            edges: [],
            userOptions: [],
            questions: [],
            isLoading: false,
            saveState: 'idle',

            startPos: { x: 30, y: 220, key: '__start__' },
            heights: {},

            drag: null,
            connecting: null,
            tempEnd: { x: 0, y: 0 },

            isOpenNode: false,
            editType: 'approval',
            editIndex: null,
            editNode: { name: '', mode: 'any', userIds: [], config: {} },

            typeMeta: {
                approval: { icon: 'fa-user-check', label: 'Approval' },
                notification: { icon: 'fa-bell', label: 'Notification' },
                condition: { icon: 'fa-code-branch', label: 'Condition' },
                update: { icon: 'fa-pen-to-square', label: 'Update' },
                http: { icon: 'fa-globe', label: 'HTTP Request' },
                terminate: { icon: 'fa-circle-stop', label: 'Terminate' },
            },
            httpMethods: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
            operators: [
                { id: '=', name: 'equals' }, { id: '!=', name: 'not equals' },
                { id: '>', name: 'greater than' }, { id: '<', name: 'less than' },
                { id: '>=', name: '≥' }, { id: '<=', name: '≤' },
                { id: 'contains', name: 'contains' }, { id: 'not_contains', name: 'not contains' },
            ],
        };
    },

    computed: {
        surfaceW() {
            const xs = [this.startPos.x, ...this.nodes.map(n => n.x)];
            return Math.max(1100, Math.max(...xs) + W + 80);
        },
        surfaceH() {
            const ys = [this.startPos.y, ...this.nodes.map(n => n.y)];
            return Math.max(500, Math.max(...ys) + 220);
        },
        renderedEdges() {
            return this.edges.map(e => {
                const s = this.outPos(e.from, e.branch);
                const t = this.inPos(this.byKey(e.to));
                if (!s || !t) { return null; }
                const dx = Math.max(40, Math.abs(t.x - s.x) / 2);
                return {
                    ref: e, branch: e.branch,
                    d: `M ${s.x} ${s.y} C ${s.x + dx} ${s.y}, ${t.x - dx} ${t.y}, ${t.x} ${t.y}`,
                    mx: (s.x + t.x) / 2, my: (s.y + t.y) / 2,
                };
            }).filter(Boolean);
        },
        tempPath() {
            if (!this.connecting) { return ''; }
            const s = this.outPos(this.connecting.fromKey, this.connecting.branch);
            const t = this.tempEnd;
            const dx = Math.max(40, Math.abs(t.x - s.x) / 2);
            return `M ${s.x} ${s.y} C ${s.x + dx} ${s.y}, ${t.x - dx} ${t.y}, ${t.x} ${t.y}`;
        },
        reachable() {
            const adj = {};
            this.edges.forEach(e => { (adj[e.from] = adj[e.from] || []).push(e.to); });
            const seen = new Set();
            const stack = ['__start__'];
            while (stack.length) {
                const k = stack.pop();
                if (seen.has(k)) { continue; }
                seen.add(k);
                (adj[k] || []).forEach(t => stack.push(t));
            }
            return seen;
        },
        warnings() {
            const w = [];
            if (this.nodes.length && !this.edges.some(e => e.from === '__start__')) {
                w.push('No node is connected from "Submitted" — the workflow will not run.');
            }
            this.nodes.forEach(n => {
                if (!this.reachable.has(n.key)) { w.push(`"${n.name}" is not reachable from "Submitted".`); }
            });
            return w;
        },
    },

    created() {
        this._els = {};
        if (typeof ResizeObserver !== 'undefined') {
            this._ro = new ResizeObserver(this.onResize);
        }
    },

    mounted() {
        this.load();
    },

    beforeUnmount() {
        this.detachDrag();
        if (this._ro) { this._ro.disconnect(); }
        clearTimeout(this._saveTimer);
        clearTimeout(this._savedTimer);
    },

    methods: {
        userName(id) { const u = this.userOptions.find(o => o.id === id); return u ? u.name : '#' + id; },
        questionLabel(id) { const q = this.questions.find(o => o.id === id); return q ? q.label : 'field'; },
        opLabel(id) { const o = this.operators.find(x => x.id === id); return o ? o.name : '='; },
        typeMetaOf(t) { return this.typeMeta[t]; },
        typeBorder(t) { return { approval: 'border-blue-200 dark:border-blue-800', notification: 'border-indigo-200 dark:border-indigo-800', condition: 'border-amber-200 dark:border-amber-800', update: 'border-emerald-200 dark:border-emerald-800', http: 'border-cyan-200 dark:border-cyan-800', terminate: 'border-rose-200 dark:border-rose-800' }[t]; },
        typeText(t) { return { approval: 'text-blue-500', notification: 'text-indigo-500', condition: 'text-amber-500', update: 'text-emerald-500', http: 'text-cyan-500', terminate: 'text-rose-500' }[t]; },
        nodeHasIssue(node) {
            return !this.reachable.has(node.key);
        },

        byKey(key) {
            if (key === '__start__') { return this.startPos; }
            return this.nodes.find(n => n.key === key);
        },
        posStyle(node) { return { left: node.x + 'px', top: node.y + 'px' }; },
        portStyle(pos) { return pos ? { left: (pos.rel.x) + 'px', top: (pos.rel.y) + 'px' } : {}; },

        // Track each node's rendered height so ports sit at its vertical centre.
        registerEl(el, key) {
            if (!el) { return; }
            el.dataset.k = key;
            if (this._els[key] !== el) {
                this._els[key] = el;
                if (this._ro) { this._ro.observe(el); }
            }
            const h = el.offsetHeight;
            if (h && this.heights[key] !== h) { this.heights[key] = h; }
        },
        onResize(entries) {
            entries.forEach(e => {
                const k = e.target.dataset.k;
                const h = e.target.offsetHeight;
                if (k && this.heights[k] !== h) { this.heights[k] = h; }
            });
        },
        nodeHeight(key) { return this.heights[key] || AY * 2; },

        // absolute (surface) + relative (within node) coords for a port
        inPos(node) {
            if (!node) { return null; }
            const cy = this.nodeHeight(node.key) / 2;
            return { x: node.x, y: node.y + cy, rel: { x: -6, y: cy - 6 } };
        },
        outPos(key, branch) {
            const node = this.byKey(key);
            if (!node) { return null; }
            const cy = this.nodeHeight(key) / 2;
            let dy = cy;
            if (branch === 'true') { dy = cy - 10; }
            if (branch === 'false') { dy = cy + 10; }
            return { x: node.x + W, y: node.y + dy, rel: { x: W - 6, y: dy - 6 } };
        },

        load() {
            this.isLoading = true;
            axios.get('/admin/approvals/users').then(r => { this.userOptions = r.data.users || []; }).catch(() => {});
            axios.get(`/admin/forms/${this.form_id}/workflow/data`).then(res => {
                this.questions = res.data.questions || [];
                const rawNodes = res.data.nodes || [];
                const idToKey = {};
                this.nodes = rawNodes.map((n, i) => {
                    const key = 'k' + keySeq++;
                    idToKey[n.id] = key;
                    return {
                        key, id: n.id, type: n.type, name: n.name, mode: n.mode || 'any',
                        userIds: (n.users || []).map(u => u.id),
                        config: n.config || {},
                        x: n.pos_x != null ? n.pos_x : 320 + i * 260,
                        y: n.pos_y != null ? n.pos_y : 220,
                    };
                });
                this.edges = (res.data.edges || []).map(e => ({
                    from: e.from == null ? '__start__' : (idToKey[e.from] || null),
                    to: idToKey[e.to] || null,
                    branch: e.branch,
                })).filter(e => e.from && e.to);
                this.startPos.x = 30;
                this.isLoading = false;
            }).catch(() => { this.isLoading = false; });
        },

        // ---- node editing ---
        addNode(type) {
            this.editType = type;
            this.editIndex = null;
            this.editNode = { name: this.typeMeta[type].label, mode: 'any', userIds: [], config: this.defaultConfig(type) };
            this.isOpenNode = true;
        },
        defaultConfig(type) {
            if (type === 'condition') { return { match: 'all', rules: [{ field: null, operator: '=', value: '' }] }; }
            if (type === 'update') { return { target: 'approval_status', status: 'approved', updates: [{ field: null, value: '' }], message: '' }; }
            if (type === 'http') { return { method: 'GET', url: '', headers: [{ key: '', value: '' }], query: [{ key: '', value: '' }], body_type: 'none', body: [{ key: '', value: '' }], raw_body: '', answers_key: 'label', auth: { type: 'none', username: '', password: '', token: '' }, timeout: 30 }; }
            if (type === 'terminate') { return { status: 'rejected', message: '' }; }
            return { message: '' };
        },
        editExisting(index) {
            const n = this.nodes[index];
            this.editType = n.type;
            this.editIndex = index;
            const config = { ...(n.config || {}) };
            if (n.type === 'condition') { this.normalizeConditionConfig(config); }
            if (n.type === 'update') { this.normalizeUpdateConfig(config); }
            if (n.type === 'http') { this.normalizeHttpConfig(config); }
            this.editNode = { name: n.name, mode: n.mode || 'any', userIds: [...n.userIds], config };
            this.isOpenNode = true;
        },
        normalizeUpdateConfig(config) {
            const updates = Array.isArray(config.updates) && config.updates.length
                ? config.updates
                : [{ field: config.field ?? null, value: config.value || '' }];
            config.updates = updates.map(u => ({ field: u.field ?? null, value: u.value || '' }));
            config.target = config.target === 'answer' ? 'answer' : 'approval_status';
            if (!config.status) { config.status = 'approved'; }
            delete config.field;
            delete config.value;
        },
        addUpdate() { this.editNode.config.updates.push({ field: null, value: '' }); },
        removeUpdate(ui) { if (this.editNode.config.updates.length > 1) { this.editNode.config.updates.splice(ui, 1); } },
        normalizeHttpConfig(config) {
            const pairs = list => (Array.isArray(list) && list.length ? list.map(p => ({ key: p.key || '', value: p.value || '' })) : [{ key: '', value: '' }]);
            config.method = config.method || 'GET';
            config.url = config.url || '';
            config.headers = pairs(config.headers);
            config.query = pairs(config.query);
            config.body = pairs(config.body);
            config.body_type = config.body_type || 'none';
            config.raw_body = config.raw_body || '';
            config.answers_key = config.answers_key === 'id' ? 'id' : 'label';
            const a = config.auth || {};
            config.auth = { type: a.type || 'none', username: a.username || '', password: a.password || '', token: a.token || '' };
            config.timeout = config.timeout || 30;
        },
        addKv(list) { this.editNode.config[list].push({ key: '', value: '' }); },
        removeKv(list, i) { this.editNode.config[list].splice(i, 1); if (!this.editNode.config[list].length) { this.editNode.config[list].push({ key: '', value: '' }); } },
        setHttpTarget(obj, key, ev) { this._httpTarget = { obj, key, el: ev.target }; },
        insertField(token) {
            if (!token) { return; }
            const target = this._httpTarget || { obj: this.editNode.config, key: 'url', el: null };
            const placeholder = '{{' + token + '}}';
            const current = target.obj[target.key] != null ? String(target.obj[target.key]) : '';
            const el = target.el;
            const pos = el && typeof el.selectionStart === 'number' ? el.selectionStart : current.length;
            target.obj[target.key] = current.slice(0, pos) + placeholder + current.slice(pos);
            this.$nextTick(() => {
                if (el) { const caret = pos + placeholder.length; el.focus(); try { el.setSelectionRange(caret, caret); } catch (e) { /* non-text input */ } }
            });
        },
        updateFields(node) {
            const c = node.config || {};
            if (Array.isArray(c.updates) && c.updates.length) { return c.updates; }
            if (c.field != null) { return [{ field: c.field, value: c.value || '' }]; }
            return [];
        },
        normalizeConditionConfig(config) {
            const rules = Array.isArray(config.rules) && config.rules.length
                ? config.rules
                : [{ field: config.field ?? null, operator: config.operator || '=', value: config.value || '' }];
            config.rules = rules.map(r => ({ field: r.field ?? null, operator: r.operator || '=', value: r.value || '' }));
            config.match = config.match === 'any' ? 'any' : 'all';
            delete config.field;
            delete config.operator;
            delete config.value;
        },
        addRule() { this.editNode.config.rules.push({ field: null, operator: '=', value: '' }); },
        removeRule(ri) { if (this.editNode.config.rules.length > 1) { this.editNode.config.rules.splice(ri, 1); } },
        conditionRules(node) {
            const c = node.config || {};
            if (Array.isArray(c.rules) && c.rules.length) { return c.rules; }
            if (c.field != null) { return [{ field: c.field, operator: c.operator || '=', value: c.value || '' }]; }
            return [];
        },
        applyNode() {
            if (!this.editNode.name || !this.editNode.name.trim()) { GlToast.methods.add({ message: 'Name is required.', type: 'error', duration: 3000 }); return; }
            if ((this.editType === 'approval' || this.editType === 'notification') && this.editNode.userIds.length === 0) {
                GlToast.methods.add({ message: 'Select at least one user.', type: 'error', duration: 3000 }); return;
            }
            if (this.editType === 'condition' && !this.editNode.config.rules.some(r => r.field)) {
                GlToast.methods.add({ message: 'Pick a field for at least one rule.', type: 'error', duration: 3000 }); return;
            }
            if (this.editType === 'condition') {
                this.editNode.config.rules = this.editNode.config.rules.filter(r => r.field);
            }
            if (this.editType === 'update' && this.editNode.config.target === 'answer') {
                if (!this.editNode.config.updates.some(u => u.field)) {
                    GlToast.methods.add({ message: 'Pick a field to update.', type: 'error', duration: 3000 }); return;
                }
                this.editNode.config.updates = this.editNode.config.updates.filter(u => u.field);
            }
            if (this.editType === 'http') {
                if (!this.editNode.config.url || !this.editNode.config.url.trim()) {
                    GlToast.methods.add({ message: 'URL is required.', type: 'error', duration: 3000 }); return;
                }
                ['headers', 'query', 'body'].forEach(list => {
                    this.editNode.config[list] = this.editNode.config[list].filter(kv => (kv.key && kv.key.trim()) || (kv.value && kv.value.trim()));
                });
            }
            const payload = { type: this.editType, name: this.editNode.name.trim(), mode: this.editNode.mode, userIds: [...this.editNode.userIds], config: { ...this.editNode.config } };
            if (this.editIndex === null) {
                const maxX = this.nodes.length ? Math.max(...this.nodes.map(n => n.x)) : 60;
                this.nodes.push({ key: 'k' + keySeq++, ...payload, x: maxX + 260, y: 220 });
            } else {
                Object.assign(this.nodes[this.editIndex], payload);
            }
            this.isOpenNode = false;
            this.autoSave();
        },
        removeNode(index) {
            const key = this.nodes[index].key;
            this.nodes.splice(index, 1);
            this.edges = this.edges.filter(e => e.from !== key && e.to !== key);
            this.autoSave();
        },

        // ---- edges ---
        startConnect(e, fromKey, branch) {
            this.connecting = { fromKey, branch };
            this.setTempFromEvent(e);
            this._cmove = this.onConnectMove.bind(this);
            document.addEventListener('mousemove', this._cmove);
        },
        onConnectMove(e) { this.setTempFromEvent(e); },
        setTempFromEvent(e) {
            const rect = this.$refs.surface.getBoundingClientRect();
            this.tempEnd = { x: e.clientX - rect.left, y: e.clientY - rect.top };
        },
        finishConnect(toKey) {
            if (!this.connecting) { return; }
            const { fromKey, branch } = this.connecting;
            if (fromKey !== toKey) {
                this.edges = this.edges.filter(ed => !(ed.from === fromKey && ed.branch === branch));
                this.edges.push({ from: fromKey, to: toKey, branch });
                this.autoSave();
            }
            this.cancelConnect();
        },
        cancelConnect() {
            this.connecting = null;
            if (this._cmove) { document.removeEventListener('mousemove', this._cmove); this._cmove = null; }
        },
        removeEdge(edge) {
            this.edges = this.edges.filter(e => e !== edge);
            this.autoSave();
        },

        // ---- dragging ---
        startDrag(e, node) {
            if (e.button !== 0) { return; }
            const rect = this.$refs.surface.getBoundingClientRect();
            this.drag = { node, grabX: e.clientX - rect.left - node.x, grabY: e.clientY - rect.top - node.y, moved: false };
            this._move = this.onDragMove.bind(this);
            this._up = this.endDrag.bind(this);
            document.addEventListener('mousemove', this._move);
            document.addEventListener('mouseup', this._up);
            e.preventDefault();
        },
        onDragMove(e) {
            if (!this.drag) { return; }
            const rect = this.$refs.surface.getBoundingClientRect();
            this.drag.node.x = Math.max(0, e.clientX - rect.left - this.drag.grabX);
            this.drag.node.y = Math.max(0, e.clientY - rect.top - this.drag.grabY);
            this.drag.moved = true;
        },
        endDrag() {
            const moved = this.drag && this.drag.moved;
            this.drag = null;
            this.detachDrag();
            if (moved) { this.autoSave(); }
        },
        detachDrag() {
            if (this._move) { document.removeEventListener('mousemove', this._move); this._move = null; }
            if (this._up) { document.removeEventListener('mouseup', this._up); this._up = null; }
        },

        // ---- save ---
        autoSave() { clearTimeout(this._saveTimer); this._saveTimer = setTimeout(() => this.performSave(), 700); },
        performSave() {
            this.saveState = 'saving';
            axios.put(`/admin/forms/${this.form_id}/workflow`, {
                nodes: this.nodes.map(n => ({ key: n.key, type: n.type, name: n.name, mode: n.mode, config: n.config, users: n.userIds, pos_x: Math.round(n.x), pos_y: Math.round(n.y) })),
                edges: this.edges.map(e => ({ from: e.from === '__start__' ? null : e.from, to: e.to, branch: e.branch })),
            }).then(() => {
                this.saveState = 'saved';
                clearTimeout(this._savedTimer);
                this._savedTimer = setTimeout(() => { if (this.saveState === 'saved') { this.saveState = 'idle'; } }, 2500);
            }).catch((err) => {
                this.saveState = 'error';
                GlToast.methods.add({ message: err.response?.data?.message || 'Failed to save workflow.', type: 'error', duration: 4000 });
            });
        },
    },
};
</script>
