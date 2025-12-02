import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import App from './App.vue'
import './style.css'

import Home from './views/Home.vue'
import ForgotPasswordSuccess from './views/ForgotPasswordSuccess.vue'
import ForgotPasswordNotFound from './views/ForgotPasswordNotFound.vue'
import ResetPassword from './views/ResetPassword.vue'
import RecipeCreate from './views/RecipeCreate.vue'
import RecipeEdit from './views/RecipeEdit.vue'

const routes = [
  { path: '/', name: 'Home', component: Home },
  { path: '/forgot-password/success', name: 'ForgotPasswordSuccess', component: ForgotPasswordSuccess },
  { path: '/forgot-password/not-found', name: 'ForgotPasswordNotFound', component: ForgotPasswordNotFound },
  { path: '/reset-password', name: 'ResetPassword', component: ResetPassword },
  { path: '/recipes/create', name: 'RecipeCreate', component: RecipeCreate },
  { path: '/recipes/:id/edit', name: 'RecipeEdit', component: RecipeEdit }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

const app = createApp(App)
app.use(router)
app.mount('#app')


