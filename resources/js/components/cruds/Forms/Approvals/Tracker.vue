<template>
    <div v-if="loaded && status !== 'not_required'"
        class="rounded-xl bg-white dark:bg-gray-900 border border-stroke dark:border-white/10 shadow-default p-6 mb-6 print:hidden">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800 dark:text-white">
                <i class="fa-solid fa-diagram-project mr-1 text-primary"></i> Workflow Progress
            </h3>
            <span class="px-3 py-1 rounded-full text-xs font-semibold" :class="statusBadgeClass">{{ statusLabel }}</span>
        </div>

        <ol class="relative border-s-2 border-gray-200 dark:border-gray-700 ms-3">
            <li class="mb-6 ms-6">
                <span class="absolute -inset-s-3 flex items-center justify-center w-6 h-6 rounded-full bg-gray-100 dark:bg-gray-800 ring-4 ring-white dark:ring-gray-900">
                    <i class="fa-solid fa-paper-plane text-gray-400 text-xs"></i>
                </span>
                <p class="font-medium text-gray-700 dark:text-gray-200">Submitted</p>
            </li>

            <li v-for="(ev, i) in events" :key="i" class="mb-6 ms-6">
                <span class="absolute -inset-s-3 flex items-center justify-center w-6 h-6 rounded-full ring-4 ring-white dark:ring-gray-900" :class="ev.dot">
                    <i class="text-xs" :class="ev.icon"></i>
                </span>
                <div class="flex flex-wrap items-center gap-2">
                    <p class="font-medium text-gray-800 dark:text-gray-100">{{ ev.title }}</p>
                    <span v-if="ev.tag" class="px-2 py-0.5 rounded text-xs" :class="ev.tagClass">{{ ev.tag }}</span>
                </div>
                <p v-if="ev.detail" class="text-xs mt-0.5" :class="ev.detailClass">{{ ev.detail }}</p>
            </li>

            <li v-if="pendingNode" class="mb-6 ms-6">
                <span class="absolute -inset-s-3 flex items-center justify-center w-6 h-6 rounded-full bg-blue-500 animate-pulse ring-4 ring-white dark:ring-gray-900">
                    <i class="fa-solid fa-hourglass-half text-white text-xs"></i>
                </span>
                <p class="font-medium text-gray-800 dark:text-gray-100">{{ pendingNode.name }}</p>
                <p class="text-sm text-blue-600 dark:text-blue-400 mt-0.5">Pending with: {{ pendingWith.join(', ') }}</p>
            </li>

            <li class="ms-6">
                <span class="absolute -inset-s-3 flex items-center justify-center w-6 h-6 rounded-full ring-4 ring-white dark:ring-gray-900"
                    :class="status === 'approved' ? 'bg-green-500' : (status === 'rejected' ? 'bg-red-500' : (status === 'terminated' ? 'bg-rose-500' : 'bg-gray-200 dark:bg-gray-700'))">
                    <i class="text-xs" :class="status === 'rejected' ? 'fa-solid fa-xmark text-white' : (status === 'terminated' ? 'fa-solid fa-circle-stop text-white' : ('fa-solid fa-flag-checkered ' + (status === 'approved' ? 'text-white' : 'text-gray-400')))"></i>
                </span>
                <p class="font-medium" :class="status === 'approved' ? 'text-green-700 dark:text-green-300' : (status === 'rejected' ? 'text-red-700 dark:text-red-300' : (status === 'terminated' ? 'text-rose-700 dark:text-rose-300' : 'text-gray-500'))">
                    {{ status === 'rejected' ? 'Rejected' : (status === 'terminated' ? 'Terminated' : 'Approved') }}
                </p>
            </li>
        </ol>
    </div>
</template>

<script>
export default {
    props: ['form_id', 'response_id'],

    data() {
        return { loaded: false, status: 'not_required', nodes: [], pendingWith: [] };
    },

    mounted() {
        this.load();
    },

    computed: {
        statusLabel() {
            return { pending: 'Pending', approved: 'Approved', rejected: 'Rejected', terminated: 'Terminated' }[this.status] || 'Pending';
        },
        statusBadgeClass() {
            return { pending: 'bg-blue-100 text-blue-700', approved: 'bg-green-100 text-green-700', rejected: 'bg-red-100 text-red-700', terminated: 'bg-rose-100 text-rose-700' }[this.status] || 'bg-gray-100 text-gray-600';
        },
        pendingNode() {
            return this.nodes.find(n => n.status === 'current') || null;
        },
        events() {
            const list = [];
            this.nodes.forEach(node => {
                (node.actions || []).forEach(a => {
                    list.push({ node, action: a, acted_at: a.acted_at });
                });
            });
            list.sort((x, y) => String(x.acted_at).localeCompare(String(y.acted_at)));
            return list.map(({ node, action }) => this.describe(node, action));
        },
    },

    methods: {
        describe(node, a) {
            const when = a.acted_at ? ` · ${a.acted_at}` : '';
            if (a.action === 'notified') {
                return { title: `Notification: ${node.name}`, tag: 'sent', tagClass: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300', icon: 'fa-solid fa-bell text-white', dot: 'bg-indigo-500', detail: when.replace(' · ', ''), detailClass: 'text-gray-400' };
            }
            if (a.action === 'condition_true' || a.action === 'condition_false') {
                const t = a.action === 'condition_true';
                return { title: `Condition: ${node.name}`, tag: t ? 'true' : 'false', tagClass: t ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300', icon: 'fa-solid fa-code-branch text-white', dot: 'bg-amber-500', detail: when.replace(' · ', ''), detailClass: 'text-gray-400' };
            }
            if (a.action === 'updated') {
                return { title: `Update: ${node.name}`, tag: 'updated', tagClass: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300', icon: 'fa-solid fa-pen-to-square text-white', dot: 'bg-emerald-500', detail: a.comment ? `${a.comment}${when}` : when.replace(' · ', ''), detailClass: 'text-gray-400' };
            }
            if (a.action === 'terminated') {
                return { title: `Terminated: ${node.name}`, tag: 'ended', tagClass: 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300', icon: 'fa-solid fa-circle-stop text-white', dot: 'bg-rose-500', detail: `${a.comment ? `${a.comment}${when}` : when.replace(' · ', '')}`, detailClass: 'text-rose-600 dark:text-rose-400' };
            }
            if (a.action === 'http_called' || a.action === 'http_failed' || a.action === 'http_skipped') {
                const ok = a.action === 'http_called';
                return { title: `HTTP Request: ${node.name}`, tag: ok ? 'called' : (a.action === 'http_failed' ? 'failed' : 'skipped'), tagClass: ok ? 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/40 dark:text-cyan-300' : 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300', icon: 'fa-solid fa-globe text-white', dot: 'bg-cyan-500', detail: a.comment ? `${a.comment}${when}` : when.replace(' · ', ''), detailClass: 'text-gray-400' };
            }
            if (a.action === 'rejected') {
                return { title: `${node.name} — rejected`, icon: 'fa-solid fa-xmark text-white', dot: 'bg-red-500', detail: `by ${a.user}${when}${a.comment ? ` — "${a.comment}"` : ''}`, detailClass: 'text-red-600 dark:text-red-400' };
            }
            return { title: `${node.name} — approved`, icon: 'fa-solid fa-check text-white', dot: 'bg-green-500', detail: `by ${a.user}${when}${a.comment ? ` — "${a.comment}"` : ''}`, detailClass: 'text-green-600 dark:text-green-400' };
        },

        load() {
            axios.get(`/admin/forms/${this.form_id}/responses/${this.response_id}/approval`)
                .then(res => {
                    this.status = res.data.status;
                    this.nodes = res.data.nodes || [];
                    this.pendingWith = res.data.pending_with || [];
                    this.loaded = true;
                })
                .catch(() => { this.loaded = true; });
        },
    },
};
</script>
