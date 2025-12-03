<template>
  <div class="recipe-edit">
    <div class="container">
      <h1>Edit Recipe</h1>

      <div v-if="loadingRecipe" class="loading-message">
        Loading recipe...
      </div>

      <div v-else-if="recipeError" class="error-message">
        {{ recipeError }}
      </div>

      <form v-else @submit.prevent="handleSubmit" class="recipe-form">
        <!-- Basic Information -->
        <div class="form-section">
          <h2>Basic Information</h2>
          
          <div class="form-group">
            <label for="title">Recipe Title *</label>
            <input
              id="title"
              type="text"
              v-model="form.title"
              required
              placeholder="e.g., Borscht"
            />
          </div>

          <div class="form-group">
            <label for="cuisine">Cuisine Type *</label>
            <div class="select-with-create">
              <select
                id="cuisine"
                v-model="form.cuisine_id"
                required
                @change="selectedCuisineId = form.cuisine_id"
              >
                <option value="">Select cuisine type</option>
                <option v-for="cuisine in cuisines" :key="cuisine.id" :value="cuisine.id">
                  {{ cuisine.name }}
                </option>
              </select>
              <button type="button" @click="showCreateCuisine = true" class="btn-create">
                + Create New
              </button>
            </div>
          </div>

          <div class="form-group">
            <label for="description">Description</label>
            <textarea
              id="description"
              v-model="form.description"
              rows="4"
              placeholder="Brief recipe description"
            ></textarea>
          </div>

          <div class="form-group-inline">
            <div class="form-group">
              <label for="prep_time">Prep Time (min)</label>
              <input
                id="prep_time"
                type="number"
                v-model.number="form.prep_time"
                min="0"
                placeholder="e.g., 15"
              />
            </div>
            <div class="form-group">
              <label for="cook_time">Cook Time (min)</label>
              <input
                id="cook_time"
                type="number"
                v-model.number="form.cook_time"
                min="0"
                placeholder="e.g., 30"
              />
            </div>
            <div class="form-group">
              <label for="servings">Servings</label>
              <input
                id="servings"
                type="number"
                v-model.number="form.servings"
                min="1"
                placeholder="4"
              />
            </div>
          </div>
        </div>

        <!-- Ingredients (quick creation) -->
        <div class="form-section">
          <h2>Ingredients</h2>
          <p class="section-description">You can create new ingredients that will be available when adding to steps</p>
          <button type="button" @click="showCreateIngredient = true" class="btn-create">
            + Create New Ingredient
          </button>
        </div>

        <!-- Cooking Steps -->
        <div class="form-section">
          <h2>Cooking Steps *</h2>
          
          <div v-for="(step, index) in form.steps" :key="index" class="step-item">
            <div class="step-header">
              <h3>Step {{ step.step_number }}</h3>
              <button
                type="button"
                @click="removeStep(index)"
                v-if="form.steps.length > 1"
                class="btn-remove-step"
              >
                Delete
              </button>
            </div>

            <div class="form-group">
              <label>Step Description *</label>
              <textarea
                v-model="step.description"
                required
                rows="3"
                placeholder="Describe what needs to be done in this step"
              ></textarea>
            </div>

            <div class="form-group">
              <label>Ingredients for this step</label>
              <div
                v-for="(ingredient, ingIndex) in step.ingredients"
                :key="ingIndex"
                class="ingredient-row"
              >
                <select
                  v-model="ingredient.ingredient_id"
                  required
                  class="ingredient-select"
                >
                  <option value="">Select ingredient</option>
                  <option
                    v-for="ing in ingredients"
                    :key="ing.id"
                    :value="ing.id"
                  >
                    {{ ing.name }}
                  </option>
                </select>
                <input
                  type="number"
                  v-model.number="ingredient.amount"
                  min="0"
                  step="0.01"
                  placeholder="Amount"
                  class="amount-input"
                />
                <input
                  type="text"
                  v-model="ingredient.unit"
                  placeholder="unit (g, ml, pcs)"
                  class="unit-input"
                />
                <button
                  type="button"
                  @click="removeIngredientFromStep(index, ingIndex)"
                  class="btn-remove-small"
                >
                  ×
                </button>
              </div>
              <button
                type="button"
                @click="addIngredientToStep(index)"
                class="btn-add"
              >
                + Add Ingredient
              </button>
            </div>
          </div>

          <button type="button" @click="addStep" class="btn-add-step">
            + Add Step
          </button>
        </div>

        <!-- Images -->
        <div class="form-section">
          <h2>Images</h2>
          <div class="form-group">
            <label>Upload Images</label>
            <div class="file-input-wrapper">
              <input
                type="file"
                @change="handleImageUpload"
                multiple
                accept="image/*"
                id="file-input-edit"
                class="file-input-hidden"
              />
              <label for="file-input-edit" class="file-input-label">
                Choose Files
              </label>
            </div>
            <div v-if="uploadedImages.length > 0" class="images-preview">
              <div
                v-for="(image, index) in uploadedImages"
                :key="image.id || index"
                class="image-preview-item"
              >
                <img :src="image.url" :alt="image.name" />
                <button
                  type="button"
                  @click="removeImage(index)"
                  class="btn-remove-image"
                >
                  ×
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Errors and buttons -->
        <div v-if="error" class="error-message">
          {{ error }}
        </div>

        <div class="form-actions">
          <button type="submit" :disabled="loading" class="btn btn-primary">
            {{ loading ? 'Saving...' : 'Save Changes' }}
          </button>
          <router-link to="/" class="btn btn-secondary">Cancel</router-link>
        </div>
      </form>

      <!-- Cuisine creation modal -->
      <div v-if="showCreateCuisine" class="modal-overlay" @click="showCreateCuisine = false">
        <div class="modal" @click.stop>
          <h3>Create New Cuisine Type</h3>
          <form @submit.prevent="createCuisine">
            <div class="form-group">
              <label>Name *</label>
              <input
                type="text"
                v-model="newCuisine.name"
                required
                placeholder="e.g., Canadian"
              />
            </div>
            <div v-if="cuisineError" class="error-message">{{ cuisineError }}</div>
            <div class="modal-actions">
              <button type="submit" :disabled="creatingCuisine" class="btn btn-primary">
                {{ creatingCuisine ? 'Creating...' : 'Create' }}
              </button>
              <button
                type="button"
                @click="showCreateCuisine = false"
                class="btn btn-secondary"
              >
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Ingredient creation modal -->
      <div v-if="showCreateIngredient" class="modal-overlay" @click="showCreateIngredient = false">
        <div class="modal" @click.stop>
          <h3>Create New Ingredient</h3>
          <form @submit.prevent="createIngredient">
            <div class="form-group">
              <label>Name *</label>
              <input
                type="text"
                v-model="newIngredient.name"
                required
                placeholder="e.g., Carrot"
              />
            </div>
            <div v-if="ingredientError" class="error-message">{{ ingredientError }}</div>
            <div class="modal-actions">
              <button type="submit" :disabled="creatingIngredient" class="btn btn-primary">
                {{ creatingIngredient ? 'Creating...' : 'Create' }}
              </button>
              <button
                type="button"
                @click="showCreateIngredient = false"
                class="btn btn-secondary"
              >
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import axios from 'axios'

export default {
  name: 'RecipeEdit',
  setup() {
    const router = useRouter()
    const route = useRoute()
    const apiUrl = import.meta.env.VITE_API_URL || 'http://localhost/api'

    axios.defaults.withCredentials = true

    const recipeId = route.params.id
    const loadingRecipe = ref(true)
    const recipeError = ref('')
    const form = ref({
      title: '',
      cuisine_id: '',
      description: '',
      prep_time: null,
      cook_time: null,
      servings: null,
      steps: [],
      images: []
    })

    const cuisines = ref([])
    const ingredients = ref([])
    const uploadedImages = ref([])
    const loading = ref(false)
    const error = ref('')
    const showCreateCuisine = ref(false)
    const newCuisine = ref({ name: '' })
    const creatingCuisine = ref(false)
    const cuisineError = ref('')
    const selectedCuisineId = ref('')
    
    const showCreateIngredient = ref(false)
    const newIngredient = ref({ name: '' })
    const creatingIngredient = ref(false)
    const ingredientError = ref('')

    const loadRecipe = async () => {
      loadingRecipe.value = true
      recipeError.value = ''

      try {
        const response = await axios.get(`${apiUrl}/recipes/${recipeId}`, {
          withCredentials: true,
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
        const recipe = response.data

        // Populate form with recipe data
        form.value = {
          title: recipe.title || '',
          cuisine_id: recipe.cuisine_id || '',
          description: recipe.description || '',
          prep_time: recipe.prep_time || null,
          cook_time: recipe.cook_time || null,
          servings: recipe.servings || null,
          steps: recipe.steps ? recipe.steps.map((step, index) => ({
            step_number: step.step_number || index + 1,
            description: step.description || '',
            order: step.order || index,
            ingredients: step.ingredients ? step.ingredients.map(ing => ({
              ingredient_id: ing.id || ing.ingredient_id || ing.pivot?.ingredient_id,
              amount: ing.pivot?.amount || ing.amount || null,
              unit: ing.pivot?.unit || ing.unit || ''
            })) : []
          })) : [],
          images: []
        }

        // Load existing images
        if (recipe.attachment && Array.isArray(recipe.attachment) && recipe.attachment.length > 0) {
          uploadedImages.value = recipe.attachment.map(att => {
            // Build image URL
            let imageUrl = att.url
            if (!imageUrl && att.path) {
              // If URL is missing but path exists, build URL
              imageUrl = att.path.startsWith('http') ? att.path : `/storage/${att.path}`
            }
            return {
              id: att.id,
              url: imageUrl || '/storage/' + att.path,
              name: att.original_name || att.name || 'Image'
            }
          })
          form.value.images = recipe.attachment.map(att => att.id)
        } else if (recipe.attachment && !Array.isArray(recipe.attachment)) {
          // If attachment is a single object, not an array
          const att = recipe.attachment
          let imageUrl = att.url
          if (!imageUrl && att.path) {
            imageUrl = att.path.startsWith('http') ? att.path : `/storage/${att.path}`
          }
          uploadedImages.value = [{
            id: att.id,
            url: imageUrl || '/storage/' + att.path,
            name: att.original_name || att.name || 'Image'
          }]
          form.value.images = [att.id]
        }
      } catch (e) {
        console.error('Error loading recipe:', e)
        if (e.response?.status === 401) {
          recipeError.value = 'Session expired. Please refresh the page.'
        } else if (e.response?.status === 404) {
          recipeError.value = 'Recipe not found.'
        } else {
          recipeError.value = e.response?.data?.message || 'Error loading recipe'
        }
      } finally {
        loadingRecipe.value = false
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
        cuisines.value = response.data
      } catch (e) {
        console.error('Error loading cuisines:', e)
        if (e.response?.status === 401) {
          error.value = 'Session expired. Please refresh the page.'
        }
      }
    }

    const loadIngredients = async () => {
      try {
        const response = await axios.get(`${apiUrl}/ingredients`, {
          withCredentials: true,
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
        ingredients.value = response.data
      } catch (e) {
        console.error('Error loading ingredients:', e)
        if (e.response?.status === 401) {
          if (!error.value) {
            error.value = 'Session expired. Please refresh the page.'
          }
        }
      }
    }

    const createCuisine = async () => {
      creatingCuisine.value = true
      cuisineError.value = ''

      try {
        const response = await axios.post(`${apiUrl}/cuisines`, {
          name: newCuisine.value.name
        }, {
          withCredentials: true,
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
        const cuisine = response.data.cuisine || response.data
        cuisines.value.push(cuisine)
        form.value.cuisine_id = cuisine.id
        showCreateCuisine.value = false
        newCuisine.value = { name: '' }
      } catch (e) {
        console.error('Error creating cuisine:', e)
        if (e.response?.status === 401) {
          cuisineError.value = 'Session expired. Please refresh the page and log in again.'
        } else if (e.response?.data?.errors) {
          cuisineError.value = Object.values(e.response.data.errors).flat().join(', ')
        } else {
          cuisineError.value = e.response?.data?.message || 'Error creating cuisine'
        }
      } finally {
        creatingCuisine.value = false
      }
    }

    const createIngredient = async () => {
      creatingIngredient.value = true
      ingredientError.value = ''

      try {
        const response = await axios.post(`${apiUrl}/ingredients`, {
          name: newIngredient.value.name
        }, {
          withCredentials: true,
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
        const ingredient = response.data.ingredient || response.data
        ingredients.value.push(ingredient)
        showCreateIngredient.value = false
        newIngredient.value = { name: '' }
      } catch (e) {
        console.error('Error creating ingredient:', e)
        if (e.response?.status === 401) {
          ingredientError.value = 'Session expired. Please refresh the page and log in again.'
        } else if (e.response?.data?.errors) {
          ingredientError.value = Object.values(e.response.data.errors).flat().join(', ')
        } else {
          ingredientError.value = e.response?.data?.message || 'Error creating ingredient'
        }
      } finally {
        creatingIngredient.value = false
      }
    }

    const addStep = () => {
      const nextStepNumber = form.value.steps.length + 1
      form.value.steps.push({
        step_number: nextStepNumber,
        description: '',
        order: nextStepNumber,
        ingredients: []
      })
    }

    const removeStep = (index) => {
      form.value.steps.splice(index, 1)
      // Renumber steps after deletion
      form.value.steps.forEach((step, i) => {
        step.step_number = i + 1
        step.order = i + 1
      })
    }

    const addIngredientToStep = (stepIndex) => {
      form.value.steps[stepIndex].ingredients.push({
        ingredient_id: '',
        amount: null,
        unit: ''
      })
    }

    const removeIngredientFromStep = (stepIndex, ingIndex) => {
      form.value.steps[stepIndex].ingredients.splice(ingIndex, 1)
    }

    const handleImageUpload = async (event) => {
      const files = Array.from(event.target.files)
      
      for (const file of files) {
        const formData = new FormData()
        formData.append('file', file)

        try {
          // Upload file via API
          const response = await axios.post(`${apiUrl}/attachments`, formData, {
            headers: {
              'Content-Type': 'multipart/form-data'
            }
          })

          // Use URL from server response or create temporary URL
          const imageUrl = response.data.url || URL.createObjectURL(file)

          uploadedImages.value.push({
            id: response.data.id,
            url: imageUrl,
            name: response.data.original_name || file.name
          })

          form.value.images.push(response.data.id)
        } catch (e) {
          console.error('Error uploading image:', e)
          error.value = 'Error uploading image: ' + (e.response?.data?.message || e.message)
        }
      }
    }

    const removeImage = (index) => {
      uploadedImages.value.splice(index, 1)
      form.value.images.splice(index, 1)
    }

    const handleSubmit = async () => {
      loading.value = true
      error.value = ''

      try {
        // Remove empty ingredients from steps
        const stepsToSend = form.value.steps.map(step => ({
          step_number: step.step_number,
          description: step.description,
          order: step.order,
          ingredients: step.ingredients.filter(ing => ing.ingredient_id)
        }))

        const response = await axios.put(`${apiUrl}/recipes/${recipeId}`, {
          ...form.value,
          steps: stepsToSend
        }, {
          withCredentials: true,
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        })

        // Redirect to home page
        router.push('/')
      } catch (e) {
        if (e.response?.data?.errors) {
          error.value = Object.values(e.response.data.errors).flat().join(', ')
        } else {
          error.value = e.response?.data?.message || 'Error updating recipe'
        }
      } finally {
        loading.value = false
      }
    }

    onMounted(() => {
      loadRecipe()
      loadCuisines()
      loadIngredients()
    })

    return {
      form,
      cuisines,
      ingredients,
      uploadedImages,
      loading,
      loadingRecipe,
      recipeError,
      error,
      showCreateCuisine,
      newCuisine,
      creatingCuisine,
      cuisineError,
      selectedCuisineId,
      showCreateIngredient,
      newIngredient,
      creatingIngredient,
      ingredientError,
      addStep,
      removeStep,
      addIngredientToStep,
      removeIngredientFromStep,
      handleImageUpload,
      removeImage,
      handleSubmit,
      createCuisine,
      createIngredient
    }
  }
}
</script>

<style scoped>
.recipe-edit {
  min-height: 100vh;
  background: #f5f5f5;
  padding: 2rem 0;
}

.container {
  max-width: 900px;
  margin: 0 auto;
  padding: 0 1rem;
}

.container h1 {
  font-size: 2rem;
  margin-bottom: 2rem;
  color: #333;
}

.loading-message {
  text-align: center;
  padding: 3rem;
  color: #666;
  font-size: 1.1rem;
}

.recipe-form {
  background: white;
  padding: 2rem;
  border-radius: 10px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.form-section {
  margin-bottom: 2rem;
  padding-bottom: 2rem;
  border-bottom: 2px solid #e9ecef;
}

.form-section:last-of-type {
  border-bottom: none;
}

.form-section h2 {
  margin: 0 0 1.5rem 0;
  font-size: 1.5rem;
  color: #555;
}

.section-description {
  color: #666;
  font-size: 0.9rem;
  margin-bottom: 1rem;
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
.form-group input[type="number"],
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 5px;
  font-size: 1rem;
  transition: border-color 0.3s;
  box-sizing: border-box;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
  outline: none;
  border-color: #667eea;
}

.form-group-inline {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 1rem;
}

.select-with-create {
  display: flex;
  gap: 0.5rem;
}

.select-with-create select {
  flex: 1;
}

.btn-create {
  padding: 0.75rem 1rem;
  background: #6c757d;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 0.9rem;
  white-space: nowrap;
  transition: background 0.3s;
}

.btn-create:hover {
  background: #5a6268;
}

.step-item {
  background: #f8f9fa;
  padding: 1.5rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
}

.step-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.step-header h3 {
  margin: 0;
  font-size: 1.2rem;
  color: #667eea;
}

.btn-remove-step {
  background: #dc3545;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  padding: 0.5rem 1rem;
  font-size: 0.9rem;
  transition: background 0.3s;
}

.btn-remove-step:hover {
  background: #c82333;
}

.ingredient-row {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr auto;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
  align-items: center;
}

.ingredient-select {
  padding: 0.5rem;
}

.amount-input,
.unit-input {
  padding: 0.5rem;
}

.btn-remove-small {
  background: #dc3545;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  padding: 0.25rem 0.5rem;
  font-size: 1.2rem;
  line-height: 1;
  transition: background 0.3s;
}

.btn-remove-small:hover {
  background: #c82333;
}

.btn-add,
.btn-add-step {
  background: #28a745;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  padding: 0.75rem 1.5rem;
  font-size: 1rem;
  transition: background 0.3s;
}

.btn-add {
  padding: 0.5rem 1rem;
  font-size: 0.9rem;
}

.btn-add:hover,
.btn-add-step:hover {
  background: #218838;
}

.images-preview {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 1rem;
  margin-top: 1rem;
}

.image-preview-item {
  position: relative;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.image-preview-item img {
  width: 100%;
  height: 150px;
  object-fit: cover;
  display: block;
}

.btn-remove-image {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  background: #dc3545;
  color: white;
  border: none;
  border-radius: 50%;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 1.2rem;
  line-height: 1;
  transition: background 0.3s;
}

.btn-remove-image:hover {
  background: #c82333;
}

.file-input-wrapper {
  position: relative;
  display: inline-block;
  width: 100%;
}

.file-input-hidden {
  position: absolute;
  width: 0.1px;
  height: 0.1px;
  opacity: 0;
  overflow: hidden;
  z-index: -1;
}

.file-input-label {
  display: inline-block;
  padding: 0.75rem 1.5rem;
  background: #6c757d;
  color: white;
  border-radius: 5px;
  cursor: pointer;
  font-size: 1rem;
  font-weight: 500;
  transition: background 0.3s;
  text-align: center;
}

.file-input-label:hover {
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

.form-actions {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
  flex-wrap: wrap;
}

.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 5px;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s;
  text-decoration: none;
  display: inline-block;
  text-align: center;
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

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal {
  background: white;
  padding: 2rem;
  border-radius: 10px;
  max-width: 500px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
}

.modal h3 {
  margin: 0 0 1.5rem 0;
  font-size: 1.5rem;
}

.modal-actions {
  display: flex;
  gap: 1rem;
  margin-top: 1.5rem;
  justify-content: flex-end;
}
</style>

