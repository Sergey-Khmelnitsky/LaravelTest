<template>
  <div class="reset-password-container">
    <div class="reset-password-card">
      <h2>Reset Password</h2>
      
      <div v-if="success" class="success-message">
        <p>{{ successMessage }}</p>
        <router-link to="/" class="btn btn-primary">Go to Login</router-link>
      </div>

      <form v-else @submit.prevent="handleReset" class="reset-password-form">
        <div class="form-group">
          <label for="email">Email</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            required
            :disabled="loading"
            class="form-control"
          />
        </div>

        <div class="form-group">
          <label for="password">New Password</label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            required
            :disabled="loading"
            class="form-control"
          />
        </div>

        <div class="form-group">
          <label for="password_confirmation">Confirm Password</label>
          <input
            id="password_confirmation"
            v-model="form.password_confirmation"
            type="password"
            required
            :disabled="loading"
            class="form-control"
          />
        </div>

        <div v-if="error" class="error-message">
          <p>{{ error }}</p>
        </div>

        <button type="submit" :disabled="loading" class="btn btn-primary">
          {{ loading ? 'Resetting...' : 'Reset Password' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'

const route = useRoute()
const router = useRouter()

const form = ref({
  email: '',
  password: '',
  password_confirmation: '',
  token: ''
})

const loading = ref(false)
const error = ref('')
const success = ref(false)
const successMessage = ref('')

onMounted(() => {
  // Get token and email from URL query parameters
  form.value.token = route.query.token || ''
  form.value.email = route.query.email || ''
  
  if (!form.value.token || !form.value.email) {
    error.value = 'Invalid reset link. Please request a new password reset.'
  }
})

const handleReset = async () => {
  error.value = ''
  loading.value = true

  // Validate passwords match
  if (form.value.password !== form.value.password_confirmation) {
    error.value = 'Passwords do not match.'
    loading.value = false
    return
  }

  // Validate password length
  if (form.value.password.length < 8) {
    error.value = 'Password must be at least 8 characters long.'
    loading.value = false
    return
  }

  try {
    const response = await axios.post('/api/password/reset', {
      token: form.value.token,
      email: form.value.email,
      password: form.value.password,
      password_confirmation: form.value.password_confirmation
    })

    if (response.data.message) {
      success.value = true
      successMessage.value = response.data.message
    }
  } catch (err) {
    if (err.response && err.response.data) {
      if (err.response.data.errors) {
        const errors = err.response.data.errors
        if (errors.email) {
          error.value = errors.email[0]
        } else if (errors.password) {
          error.value = errors.password[0]
        } else if (err.response.data.message) {
          error.value = err.response.data.message
        } else {
          error.value = 'An error occurred. Please try again.'
        }
      } else if (err.response.data.message) {
        error.value = err.response.data.message
      } else {
        error.value = 'Failed to reset password. Please try again.'
      }
    } else {
      error.value = 'Network error. Please check your connection.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.reset-password-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  padding: 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.reset-password-card {
  background: white;
  border-radius: 10px;
  padding: 40px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 400px;
}

.reset-password-card h2 {
  margin-bottom: 30px;
  text-align: center;
  color: #333;
}

.reset-password-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group label {
  font-weight: 500;
  color: #555;
}

.form-control {
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 5px;
  font-size: 16px;
  transition: border-color 0.3s;
}

.form-control:focus {
  outline: none;
  border-color: #667eea;
}

.form-control:disabled {
  background-color: #f5f5f5;
  cursor: not-allowed;
}

.btn {
  padding: 12px 24px;
  border: none;
  border-radius: 5px;
  font-size: 16px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.3s;
}

.btn-primary {
  background-color: #667eea;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background-color: #5568d3;
}

.btn-primary:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

.error-message {
  background-color: #fee;
  border: 1px solid #fcc;
  color: #c33;
  padding: 12px;
  border-radius: 5px;
  text-align: center;
}

.success-message {
  text-align: center;
  padding: 20px;
}

.success-message p {
  color: #28a745;
  font-size: 18px;
  margin-bottom: 20px;
}
</style>

