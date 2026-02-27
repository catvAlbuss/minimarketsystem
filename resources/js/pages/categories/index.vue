<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { Plus, Edit, Trash2, X, Tag } from 'lucide-vue-next'

// --- CAMBIO IMPORTANTE ---
// Eliminamos el import manual de 'ziggy-js'. 
// La función route() ya está disponible globalmente si usas el plugin de Ziggy.
// Si usas TypeScript y te da error visual, declaramos la global:
declare function route(name?: string, params?: any): string;

type Category = {
  id: number
  name: string
  description: string | null // Permitimos null para evitar errores
}

type Props = {
  categories: Category[]
}

// Definimos props con un valor por defecto para evitar pantallas negras por datos nulos
const props = withDefaults(defineProps<Props>(), {
  categories: () => []
})

const categories = computed(() => props.categories)
const showModal = ref(false)
const editingCategory = ref<Category | null>(null)

const form = useForm({
  name: '',
  description: '',
})

function abrirModalCrear() {
  editingCategory.value = null
  form.reset()
  form.clearErrors()
  showModal.value = true
}

function abrirModalEditar(category: Category) {
  editingCategory.value = category
  form.name = category.name
  form.description = category.description || ''
  showModal.value = true
}

function submit() {
  if (editingCategory.value) {
    form.put(route('categories.update', editingCategory.value.id), {
      onSuccess: () => cerrarModal(),
    })
  } else {
    form.post(route('categories.store'), {
      onSuccess: () => cerrarModal(),
    })
  }
}

function cerrarModal() {
  showModal.value = false
  form.reset()
}

function destroy(id: number) {
  if (confirm('¿Estás seguro de eliminar esta categoría?')) {
    router.delete(route('categories.destroy', id))
  }
}

// Movemos los breadcrumbs aquí para asegurar que route() esté disponible
const breadcrumbs = [
  { title: 'Categorías', href: '#', icon: Tag },
]
</script>

<template>
  <Head title="Categorías" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-6 bg-gray-100 min-h-screen">
      
      <div class="flex justify-between items-center bg-white p-4 rounded-xl shadow-sm">
        <h1 class="text-2xl font-bold text-gray-800">Categorías</h1>
        <button 
          @click="abrirModalCrear"
          class="flex items-center gap-2 bg-[#2b4485] hover:bg-[#1a3366] text-white px-4 py-2 rounded-lg transition"
        >
          <Plus :size="18" /> Nueva Categoría
        </button>
      </div>

      <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full">
          <thead class="bg-gray-50 text-gray-700 border-b">
            <tr>
              <th class="p-4 text-left font-semibold">ID</th>
              <th class="p-4 text-left font-semibold">Nombre</th>
              <th class="p-4 text-left font-semibold">Descripción</th>
              <th class="p-4 text-right font-semibold">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="cat in categories" :key="cat.id" class="hover:bg-gray-50 transition">
              <td class="p-4 text-gray-600">#{{ cat.id }}</td>
              <td class="p-4 font-medium text-gray-900">{{ cat.name }}</td>
              <td class="p-4 text-gray-500">{{ cat.description || 'Sin descripción' }}</td>
              <td class="p-4 flex justify-end gap-2">
                <button @click="abrirModalEditar(cat)" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition">
                  <Edit :size="18" />
                </button>
                <button @click="destroy(cat.id)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition">
                  <Trash2 :size="18" />
                </button>
              </td>
            </tr>
            <tr v-if="categories.length === 0">
              <td colspan="4" class="p-10 text-center text-gray-400">
                No se encontraron categorías disponibles.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="showModal" 
        class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        @click.self="showModal = false"
      >
        <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
          <div class="p-6 border-b flex justify-between items-center bg-gray-50">
            <h3 class="text-xl font-bold text-gray-900">
              {{ editingCategory ? 'Editar' : 'Nueva' }} Categoría
            </h3>
            <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 transition">
              <X :size="24" />
            </button>
          </div>

          <form @submit.prevent="submit" class="p-6 space-y-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1">Nombre de la Categoría</label>
              <input 
                v-model="form.name" 
                type="text"
                placeholder="Ej. Limpieza"
                class="w-full border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-[#2b4485] focus:border-[#2b4485] outline-none transition" 
                required
              >
              <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1">Descripción</label>
              <textarea 
                v-model="form.description" 
                rows="3"
                placeholder="Opcional..."
                class="w-full border-gray-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-[#2b4485] focus:border-[#2b4485] outline-none transition"
              ></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4">
              <button 
                type="button" 
                @click="showModal = false"
                class="px-6 py-2.5 text-gray-600 font-medium hover:bg-gray-100 rounded-xl transition"
              >
                Cancelar
              </button>
              <button 
                type="submit"
                :disabled="form.processing"
                class="px-6 py-2.5 bg-[#2b4485] hover:bg-[#1a3366] text-white font-bold rounded-xl shadow-lg transition disabled:opacity-50"
              >
                {{ form.processing ? 'Guardando...' : 'Guardar Categoría' }}
              </button>
            </div>
          </form>
        </div>
      </div>

    </div>
  </AppLayout>
</template>