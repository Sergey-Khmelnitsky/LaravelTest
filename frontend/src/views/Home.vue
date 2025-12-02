<template>
  <div class="home">
    <div v-if="checkingAuth" class="loading-container">
      <div class="loading-spinner">Loading...</div>
    </div>
    <div v-else-if="!user" class="auth-container">
      <div class="auth-tabs">
        <button 
          @click="activeTab = 'login'" 
          :class="['tab', { active: activeTab === 'login' }]"
        >
          Login
        </button>
        <button 
          @click="activeTab = 'register'" 
          :class="['tab', { active: activeTab === 'register' }]"
        >
          Register
        </button>
        <button 
          @click="activeTab = 'forgot'" 
          :class="['tab', { active: activeTab === 'forgot' }]"
        >
          Forgot Password
        </button>
      </div>

      <!-- Login Form -->
      <div v-if="activeTab === 'login'" class="auth-form">
        <h2>Login</h2>
        <form @submit.prevent="handleLogin">
          <div class="form-group">
            <label for="login-email">Email</label>
            <input 
              id="login-email"
              type="email" 
              v-model="loginForm.email" 
              required
              placeholder="your@email.com"
            />
          </div>
          <div class="form-group">
            <label for="login-password">Password</label>
            <input 
              id="login-password"
              type="password" 
              v-model="loginForm.password" 
              required
              placeholder="••••••••"
            />
          </div>
          <div class="form-group">
            <label class="checkbox-label">
              <input type="checkbox" v-model="loginForm.remember" />
              Remember me
            </label>
          </div>
          <div class="form-group">
            <a href="#" @click.prevent="activeTab = 'forgot'" class="forgot-password-link">
              Forgot password?
            </a>
          </div>
          <div v-if="loginError" class="error-message">
            {{ loginError }}
          </div>
          <button type="submit" :disabled="loading" class="btn btn-primary">
            {{ loading ? 'Logging in...' : 'Login' }}
          </button>
        </form>
      </div>

      <!-- Password Reset Form -->
      <div v-if="activeTab === 'forgot'" class="auth-form">
        <h2>Password Reset</h2>
        <form @submit.prevent="handleForgotPassword">
          <div class="form-group">
            <label for="forgot-email">Email</label>
            <input 
              id="forgot-email"
              type="email" 
              v-model="forgotForm.email" 
              required
              placeholder="your@email.com"
            />
          </div>
          <div class="form-group">
            <div 
              id="recaptcha-container"
              class="g-recaptcha"
              :data-sitekey="recaptchaSiteKey"
            ></div>
            <div v-if="recaptchaError" class="error-message" style="margin-top: 0.5rem;">
              {{ recaptchaError }}
            </div>
          </div>
          <div v-if="forgotError" class="error-message">
            {{ forgotError }}
          </div>
          <div v-if="forgotSuccess" class="success-message">
            {{ forgotSuccess }}
          </div>
          <button type="submit" :disabled="loading || !recaptchaToken" class="btn btn-primary">
            {{ loading ? 'Sending...' : 'Send Link' }}
          </button>
          <div class="form-group mt-3">
            <a href="#" @click.prevent="activeTab = 'login'" class="back-to-login-link">
              ← Back to Login
            </a>
          </div>
        </form>
      </div>

      <!-- Registration Form -->
      <div v-if="activeTab === 'register'" class="auth-form">
        <h2>Register</h2>
        <form @submit.prevent="handleRegister">
          <div class="form-group">
            <label for="register-name">Name</label>
            <input 
              id="register-name"
              type="text" 
              v-model="registerForm.name" 
              required
              placeholder="Your name"
            />
          </div>
          <div class="form-group">
            <label for="register-email">Email</label>
            <input 
              id="register-email"
              type="email" 
              v-model="registerForm.email" 
              required
              placeholder="your@email.com"
            />
          </div>
          <div class="form-group">
            <label for="register-password">Password</label>
            <input 
              id="register-password"
              type="password" 
              v-model="registerForm.password" 
              required
              placeholder="••••••••"
            />
          </div>
          <div class="form-group">
            <label for="register-password-confirm">Confirm Password</label>
            <input 
              id="register-password-confirm"
              type="password" 
              v-model="registerForm.password_confirmation" 
              required
              placeholder="••••••••"
            />
          </div>
          <div v-if="registerError" class="error-message">
            {{ registerError }}
          </div>
          <button type="submit" :disabled="loading" class="btn btn-primary">
            {{ loading ? 'Registering...' : 'Register' }}
          </button>
        </form>
      </div>
    </div>

    <!-- Content for authenticated users -->
    <div v-else class="welcome-content">
      <div class="recipes-header">
        <h1>My Recipes</h1>
        <div class="header-actions">
          <router-link to="/recipes/create" class="btn btn-primary">
            + Add Recipe
          </router-link>
          <button @click="handleLogout" class="btn btn-secondary">
            Logout
          </button>
        </div>
      </div>

      <!-- Filters -->
      <div class="filters-section">
        <div class="filters-row">
          <div class="filter-group">
            <label for="filter-title">Search by Title</label>
            <input
              id="filter-title"
              type="text"
              v-model="filters.title"
              @input="debouncedLoadRecipes"
              placeholder="Enter recipe title..."
              class="filter-input"
            />
          </div>
          <div class="filter-group">
            <label for="filter-cuisine">Filter by Cuisine</label>
            <select
              id="filter-cuisine"
              v-model="filters.cuisine_id"
              @change="loadRecipes"
              class="filter-select"
            >
              <option value="">All Cuisines</option>
              <option v-for="cuisine in cuisines" :key="cuisine.id" :value="cuisine.id">
                {{ cuisine.name }}
              </option>
            </select>
          </div>
          <div class="filter-group">
            <button @click="clearFilters" class="btn btn-secondary btn-clear-filters">
              Clear Filters
            </button>
          </div>
        </div>
      </div>

      <div v-if="loadingRecipes" class="loading-message">
        Loading recipes...
      </div>

      <div v-else-if="recipesError" class="error-message">
        {{ recipesError }}
      </div>

      <div v-else-if="recipes.length === 0" class="empty-state">
        <p v-if="filters.title || filters.cuisine_id">
          No recipes found matching your filters.
        </p>
        <p v-else>
          You don't have any recipes yet. Create your first recipe!
        </p>
      </div>

      <div v-else class="recipes-table-container">
        <table class="recipes-table">
          <thead>
            <tr>
              <th>Title</th>
              <th>Cuisine</th>
              <th>Cooking Time</th>
              <th>Servings</th>
              <th>Created At</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="recipe in recipes" :key="recipe.id">
              <td>{{ recipe.title }}</td>
              <td>{{ recipe.cuisine?.name || '-' }}</td>
              <td>
                <span v-if="recipe.cook_time">{{ recipe.cook_time }} min</span>
                <span v-else>-</span>
              </td>
              <td>
                <span v-if="recipe.servings">{{ recipe.servings }}</span>
                <span v-else>-</span>
              </td>
              <td>{{ formatDate(recipe.created_at) }}</td>
              <td class="actions-cell">
                <button 
                  @click="editRecipe(recipe.id)" 
                  class="btn btn-edit"
                  title="Edit"
                >
                  ✏️ Edit
                </button>
                <button 
                  @click="deleteRecipe(recipe.id)" 
                  class="btn btn-delete"
                  title="Delete"
                >
                  🗑️ Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

export default {
  name: 'Home',
  setup() {
    const router = useRouter()
    const activeTab = ref('login')
    const loading = ref(false)
    const checkingAuth = ref(true)
    const user = ref(null)
    const loginError = ref('')
    const registerError = ref('')

    const loginForm = ref({
      email: '',
      password: '',
      remember: false
    })

    const registerForm = ref({
      name: '',
      email: '',
      password: '',
      password_confirmation: ''
    })

    const forgotForm = ref({
      email: '',
      recaptcha_token: ''
    })

    // Use test key if real one is not set
    const recaptchaSiteKey = ref(import.meta.env.VITE_RECAPTCHA_SITE_KEY || '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI')
    const recaptchaToken = ref('')
    const recaptchaError = ref('')
    const recaptchaWidgetId = ref(null)
    const forgotError = ref('')
    const forgotSuccess = ref('')

    const apiUrl = import.meta.env.VITE_API_URL || 'http://localhost/api'

    // Configure axios for session handling
    axios.defaults.withCredentials = true
    axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

    const recipes = ref([])
    const loadingRecipes = ref(false)
    const recipesError = ref('')
    const cuisines = ref([])
    const filters = ref({
      title: '',
      cuisine_id: ''
    })
    let debounceTimer = null

    const checkAuth = async () => {
      checkingAuth.value = true
      try {
        const response = await axios.get(`${apiUrl}/user`)
        user.value = response.data.user
        if (user.value) {
          await loadCuisines()
          await loadRecipes()
        }
      } catch (error) {
        user.value = null
      } finally {
        checkingAuth.value = false
      }
    }

    const loadCuisines = async () => {
      try {
        const response = await axios.get(`${apiUrl}/cuisines`, {
          withCredentials: true,
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
        cuisines.value = response.data || []
      } catch (error) {
        console.error('Error loading cuisines:', error)
      }
    }

    const loadRecipes = async () => {
      loadingRecipes.value = true
      recipesError.value = ''
      
      try {
        const params = {}
        if (filters.value.title) {
          params.title = filters.value.title
        }
        if (filters.value.cuisine_id) {
          params.cuisine_id = filters.value.cuisine_id
        }

        const response = await axios.get(`${apiUrl}/recipes`, {
          params,
          withCredentials: true,
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
        recipes.value = response.data.data || response.data || []
      } catch (error) {
        console.error('Error loading recipes:', error)
        if (error.response?.status === 401) {
          recipesError.value = 'Session expired. Please refresh the page.'
        } else {
          recipesError.value = error.response?.data?.message || 'Error loading recipes'
        }
      } finally {
        loadingRecipes.value = false
      }
    }

    const debouncedLoadRecipes = () => {
      if (debounceTimer) {
        clearTimeout(debounceTimer)
      }
      debounceTimer = setTimeout(() => {
        loadRecipes()
      }, 500) // Wait 500ms after user stops typing
    }

    const clearFilters = () => {
      filters.value = {
        title: '',
        cuisine_id: ''
      }
      loadRecipes()
    }

    const editRecipe = (recipeId) => {
      router.push(`/recipes/${recipeId}/edit`)
    }

    const deleteRecipe = async (recipeId) => {
      if (!confirm('Are you sure you want to delete this recipe?')) {
        return
      }

      try {
        await axios.delete(`${apiUrl}/recipes/${recipeId}`, {
          withCredentials: true,
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
        // Remove recipe from list
        recipes.value = recipes.value.filter(r => r.id !== recipeId)
      } catch (error) {
        console.error('Error deleting recipe:', error)
        alert(error.response?.data?.message || 'Error deleting recipe')
      }
    }

    const formatDate = (dateString) => {
      if (!dateString) return '-'
      const date = new Date(dateString)
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      })
    }

    const handleLogin = async () => {
      loading.value = true
      loginError.value = ''
      
      try {
        const response = await axios.post(`${apiUrl}/login`, loginForm.value)
        user.value = response.data.user
        activeTab.value = 'login'
        loginForm.value = { email: '', password: '', remember: false }
        // Load recipes after successful login
        if (user.value) {
          loadCuisines()
          loadRecipes()
        }
      } catch (error) {
        if (error.response?.data?.errors) {
          loginError.value = Object.values(error.response.data.errors).flat().join(', ')
        } else {
          loginError.value = error.response?.data?.message || 'Login error'
        }
      } finally {
        loading.value = false
      }
    }

    const handleRegister = async () => {
      loading.value = true
      registerError.value = ''
      
      try {
        const response = await axios.post(`${apiUrl}/register`, registerForm.value)
        user.value = response.data.user
        activeTab.value = 'register'
        registerForm.value = { name: '', email: '', password: '', password_confirmation: '' }
        // Load recipes after successful registration
        if (user.value) {
          loadCuisines()
          loadRecipes()
        }
      } catch (error) {
        if (error.response?.data?.errors) {
          registerError.value = Object.values(error.response.data.errors).flat().join(', ')
        } else {
          registerError.value = error.response?.data?.message || 'Registration error'
        }
      } finally {
        loading.value = false
      }
    }

    const handleLogout = async () => {
      try {
        await axios.post(`${apiUrl}/logout`)
        user.value = null
      } catch (error) {
        console.error('Error logging out:', error)
        user.value = null
      }
    }

    const loadRecaptcha = () => {
      // Check if grecaptcha is available
      if (!window.grecaptcha) {
        recaptchaError.value = 'reCAPTCHA not loaded. Please refresh the page.'
        return
      }

      // Use test key if not set
      if (!recaptchaSiteKey.value) {
        console.warn('VITE_RECAPTCHA_SITE_KEY not set, using test key')
        recaptchaSiteKey.value = '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI' // Google test key
      }

      // Clear previous widget if exists
      if (recaptchaWidgetId.value !== null) {
        try {
          window.grecaptcha.reset(recaptchaWidgetId.value)
        } catch (e) {
          console.error('Error resetting reCAPTCHA:', e)
        }
      }

      // Create new widget
      const container = document.getElementById('recaptcha-container')
      if (container) {
        try {
          recaptchaWidgetId.value = window.grecaptcha.render('recaptcha-container', {
            sitekey: recaptchaSiteKey.value,
            callback: (token) => {
              recaptchaToken.value = token
              forgotForm.value.recaptcha_token = token
              recaptchaError.value = ''
            },
            'expired-callback': () => {
              recaptchaToken.value = ''
              forgotForm.value.recaptcha_token = ''
              recaptchaError.value = 'reCAPTCHA expired. Please verify again.'
            },
            'error-callback': () => {
              recaptchaToken.value = ''
              forgotForm.value.recaptcha_token = ''
              recaptchaError.value = 'Error loading reCAPTCHA. Please refresh the page.'
            }
          })
        } catch (e) {
          console.error('Error rendering reCAPTCHA:', e)
          recaptchaError.value = 'Failed to load reCAPTCHA. Please refresh the page.'
        }
      }
    }

    const handleForgotPassword = async () => {
      if (!recaptchaToken.value) {
        recaptchaError.value = 'Please complete the reCAPTCHA verification.'
        return
      }

      loading.value = true
      forgotError.value = ''
      forgotSuccess.value = ''
      recaptchaError.value = ''
      
      try {
        const response = await axios.post(`${apiUrl}/password/email`, {
          email: forgotForm.value.email,
          recaptcha_token: forgotForm.value.recaptcha_token
        })
        
        // Redirect to appropriate page
        if (response.data.success && response.data.user_found) {
          // User found, email sent
          router.push('/forgot-password/success')
        } else if (!response.data.user_found) {
          // User not found
          router.push('/forgot-password/not-found')
        } else {
          // Unexpected response
          forgotSuccess.value = response.data.message
        }
      } catch (error) {
        if (error.response?.data?.errors) {
          const errors = error.response.data.errors
          if (errors.recaptcha) {
            recaptchaError.value = errors.recaptcha[0]
          } else {
            forgotError.value = Object.values(errors).flat().join(', ')
          }
        } else {
          forgotError.value = error.response?.data?.message || 'Error sending request'
        }
        // Reset reCAPTCHA on error
        if (recaptchaWidgetId.value !== null) {
          window.grecaptcha.reset(recaptchaWidgetId.value)
          recaptchaToken.value = ''
          forgotForm.value.recaptcha_token = ''
        }
      } finally {
        loading.value = false
      }
    }

    // Load reCAPTCHA when switching to "Forgot Password" tab
    watch(activeTab, (newTab) => {
      if (newTab === 'forgot') {
        // Wait for next tick so DOM updates
        setTimeout(() => {
          const initRecaptcha = () => {
            if (window.grecaptcha && window.grecaptcha.ready) {
              window.grecaptcha.ready(() => {
                loadRecaptcha()
              })
            } else {
              loadRecaptcha()
            }
          }

          if (window.grecaptcha) {
            initRecaptcha()
          } else {
            // If grecaptcha is not loaded yet, wait for it
            const checkRecaptcha = setInterval(() => {
              if (window.grecaptcha) {
                initRecaptcha()
                clearInterval(checkRecaptcha)
              }
            }, 100)
            // Stop checking after 10 seconds
            setTimeout(() => {
              clearInterval(checkRecaptcha)
              if (!window.grecaptcha) {
                recaptchaError.value = 'Failed to load reCAPTCHA. Check your internet connection.'
              }
            }, 10000)
          }
        }, 200)
      }
    })

    onMounted(() => {
      checkAuth()
      if (activeTab.value === 'forgot') {
        setTimeout(() => {
          if (window.grecaptcha) {
            if (window.grecaptcha.ready) {
              window.grecaptcha.ready(() => {
                loadRecaptcha()
              })
            } else {
              loadRecaptcha()
            }
          }
        }, 300)
      }
    })

    return {
      activeTab,
      loading,
      checkingAuth,
      user,
      loginForm,
      registerForm,
      forgotForm,
      recaptchaSiteKey,
      recaptchaToken,
      recaptchaError,
      loginError,
      registerError,
      forgotError,
      forgotSuccess,
      recipes,
      loadingRecipes,
      recipesError,
      cuisines,
      filters,
      handleLogin,
      handleRegister,
      handleLogout,
      handleForgotPassword,
      loadRecaptcha,
      loadRecipes,
      debouncedLoadRecipes,
      clearFilters,
      editRecipe,
      deleteRecipe,
      formatDate,
      router
    }
  }
}
</script>

<style scoped>
.home {
  animation: fadeIn 0.5s;
}

.auth-container {
  max-width: 500px;
  margin: 2rem auto;
  background: white;
  border-radius: 10px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.auth-tabs {
  display: flex;
  border-bottom: 2px solid #e9ecef;
}

.tab {
  flex: 1;
  padding: 1rem;
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1rem;
  font-weight: 500;
  color: #666;
  transition: all 0.3s;
}

.tab:hover {
  background: #f8f9fa;
}

.tab.active {
  color: #667eea;
  border-bottom: 2px solid #667eea;
  background: #f8f9fa;
}

.auth-form {
  padding: 2rem;
}

.auth-form h2 {
  margin: 0 0 1.5rem 0;
  font-size: 1.75rem;
  text-align: center;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #333;
}

.form-group input[type="text"],
.form-group input[type="email"],
.form-group input[type="password"] {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 5px;
  font-size: 1rem;
  transition: border-color 0.3s;
  box-sizing: border-box;
}

.form-group input:focus {
  outline: none;
  border-color: #667eea;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
  width: auto;
  cursor: pointer;
}

.btn {
  width: 100%;
  padding: 0.75rem;
  border: none;
  border-radius: 5px;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(102, 126, 234, 0.4);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-secondary {
  background: #6c757d;
  color: white;
}

.btn-secondary:hover {
  background: #5a6268;
}

.error-message {
  background: #f8d7da;
  color: #721c24;
  padding: 0.75rem;
  border-radius: 5px;
  margin-bottom: 1rem;
  border: 1px solid #f5c6cb;
}

.success-message {
  background: #d4edda;
  color: #155724;
  padding: 0.75rem;
  border-radius: 5px;
  margin-bottom: 1rem;
  border: 1px solid #c3e6cb;
}

.forgot-password-link,
.back-to-login-link {
  color: #667eea;
  text-decoration: none;
  font-size: 0.9rem;
  transition: color 0.3s;
}

.forgot-password-link:hover,
.back-to-login-link:hover {
  color: #764ba2;
  text-decoration: underline;
}

.g-recaptcha {
  margin: 1rem 0;
  display: flex;
  justify-content: center;
}

.loading-captcha {
  padding: 1rem;
  text-align: center;
  color: #666;
  font-style: italic;
}

.mt-3 {
  margin-top: 1rem;
}

.welcome-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
}

.recipes-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.filters-section {
  background: white;
  border-radius: 10px;
  padding: 1.5rem;
  margin-bottom: 2rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.filters-row {
  display: flex;
  gap: 1.5rem;
  flex-wrap: wrap;
  align-items: flex-end;
}

.filter-group {
  flex: 1;
  min-width: 200px;
}

.filter-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #333;
  font-size: 0.9rem;
}

.filter-input,
.filter-select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 5px;
  font-size: 1rem;
  transition: border-color 0.3s;
  box-sizing: border-box;
}

.filter-input:focus,
.filter-select:focus {
  outline: none;
  border-color: #667eea;
}

.btn-clear-filters {
  padding: 0.75rem 1.5rem;
  white-space: nowrap;
}

.recipes-header h1 {
  font-size: 2rem;
  margin: 0;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.header-actions {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.header-actions .btn {
  width: auto;
  padding: 0.75rem 1.5rem;
  white-space: nowrap;
}

.loading-message {
  text-align: center;
  padding: 3rem;
  color: #666;
  font-size: 1.1rem;
}

.empty-state {
  text-align: center;
  padding: 3rem;
  background: white;
  border-radius: 10px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.empty-state p {
  font-size: 1.1rem;
  color: #666;
  margin: 0;
}

.recipes-table-container {
  background: white;
  border-radius: 10px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  overflow-x: auto;
}

.recipes-table {
  width: 100%;
  border-collapse: collapse;
}

.recipes-table thead {
  background: #f8f9fa;
}

.recipes-table th {
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  color: #333;
  border-bottom: 2px solid #dee2e6;
}

.recipes-table td {
  padding: 1rem;
  border-bottom: 1px solid #dee2e6;
  color: #555;
}

.recipes-table tbody tr:hover {
  background: #f8f9fa;
}

.recipes-table tbody tr:last-child td {
  border-bottom: none;
}

.actions-cell {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.actions-cell .btn {
  width: auto;
  padding: 0.5rem 1rem;
  font-size: 0.9rem;
  white-space: nowrap;
}

.btn-edit {
  background: #28a745;
  color: white;
}

.btn-edit:hover {
  background: #218838;
}

.btn-delete {
  background: #dc3545;
  color: white;
}

.btn-delete:hover {
  background: #c82333;
}

.loading-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 50vh;
}

.loading-spinner {
  font-size: 1.2rem;
  color: #666;
  text-align: center;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
