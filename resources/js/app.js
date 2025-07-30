import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/inertia-vue3'
import { InertiaProgress } from '@inertiajs/progress'
import VueApexCharts from 'vue3-apexcharts'

import Helper from './Helper/Helper' // Adjust the path if needed

createInertiaApp({
  resolve: name => require(`./Pages/${name}`),
  setup({ el, App, props, plugin }) {
    const app = createApp({ render: () => h(App, props) })

    // Add global methods
    app.mixin({
      methods: {
        hasAnyPermission(permissions) {
          const allPermissions = this.$page.props.auth.permissions || {};
          return permissions.some(item => allPermissions[item]);
        }
      }
    })

    // Register global properties
    app.config.globalProperties.$helper = Helper

    app
      .use(plugin)
      .use(VueApexCharts)
      .mount(el)
  },
})

InertiaProgress.init({})
