<template>
  <div class="forgot-password-result">
    <div class="result-container">
      <div class="result-icon success">✓</div>
      <h1>Email Sent</h1>
      <p class="result-message">
        We have sent a password reset email to the specified email address.
      </p>
      <p class="result-hint">
        Please check your email and follow the instructions in the email.
      </p>
      <div class="result-actions">
        <router-link to="/" class="btn btn-primary">Back to Home</router-link>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ForgotPasswordSuccess'
}
</script>

<style scoped>
.forgot-password-result {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.result-container {
  background: white;
  border-radius: 10px;
  padding: 3rem;
  max-width: 500px;
  width: 100%;
  text-align: center;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.result-icon {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 3rem;
  margin: 0 auto 2rem;
  font-weight: bold;
}

.result-icon.success {
  background: #d4edda;
  color: #155724;
  border: 3px solid #c3e6cb;
}

.result-icon.error {
  background: #f8d7da;
  color: #721c24;
  border: 3px solid #f5c6cb;
}

.result-container h1 {
  margin: 0 0 1rem 0;
  font-size: 2rem;
  color: #333;
}

.result-message {
  font-size: 1.1rem;
  color: #666;
  margin: 0 0 1rem 0;
  line-height: 1.6;
}

.result-hint {
  font-size: 0.95rem;
  color: #999;
  margin: 0 0 2rem 0;
}

.result-actions {
  margin-top: 2rem;
}

.btn {
  display: inline-block;
  padding: 0.75rem 2rem;
  border: none;
  border-radius: 5px;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  text-decoration: none;
  transition: all 0.3s;
}

.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(102, 126, 234, 0.4);
}
</style>

