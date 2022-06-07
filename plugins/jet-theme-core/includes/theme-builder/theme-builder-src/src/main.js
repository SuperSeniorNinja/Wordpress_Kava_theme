import { createApp } from 'vue'
import App from './App.vue'
import store from './store'
import VueClickAway from 'vue3-click-away';
import FloatingVue from 'floating-vue'
import 'floating-vue/dist/style.css'

const jetThemeBuilder = createApp(App);

// Use Vuex Store
jetThemeBuilder.use(store);

// Use Vue Click Away Module
jetThemeBuilder.use(VueClickAway);

// FloatingVue module(tooltip)
jetThemeBuilder.use(FloatingVue);

// Register CherryX Global Vue Components
window.cxVueUi.registerGlobalComponents( jetThemeBuilder );

// Register CherryX CXNotice ext
jetThemeBuilder.config.globalProperties.$CXNotice = window.cxVueUi.extensions.CXNotice;

jetThemeBuilder.mount('#jet-theme-builder');

window.jetThemeBuilder = jetThemeBuilder;