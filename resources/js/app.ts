import '../css/app.css';
import { createInertiaApp } from '@inertiajs/vue3';
import { createApp, h, type DefineComponent } from 'vue';

const pages = import.meta.glob<{ default: DefineComponent }>('./pages/**/*.vue');

createInertiaApp({
    progress: {
        color: '#078B3E',
        showSpinner: true,
    },
    resolve: (name) => {
        const page = pages[`./pages/${name}.vue`];

        if (!page) {
            throw new Error(`Page Inertia introuvable : ${name}`);
        }

        return page().then((module) => module.default);
    },
    setup({ App, el, plugin, props }) {
        createApp({ render: () => h(App, props) }).use(plugin).mount(el);
    },
});
