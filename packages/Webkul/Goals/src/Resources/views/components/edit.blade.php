<!-- Modal de confirmación para eliminar -->
  <div v-if="showDeleteModal"
            class="fixed inset-0 z-[10002] bg-gray-500 bg-opacity-50 transition-opacity flex items-center justify-center">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md dark:bg-gray-900">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Confirmar eliminación</h3>
                <p class="text-gray-600 dark:text-gray-300 mb-6">¿Estás seguro de que deseas eliminar este usuario?</p>
                <div class="flex justify-end space-x-3">
                    <button @click="showDeleteModal = false"
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        Cancelar
                    </button>
                    <button @click="deleteUser"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
