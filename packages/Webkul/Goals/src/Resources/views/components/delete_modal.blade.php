<!-- Agrega esto en tu template HTML (users-settings-template) -->
<div v-if="showDeleteModal" class="fixed inset-0 z-[998] bg-black bg-opacity-50"></div>
<div v-if="showDeleteModal"
    class="box-shadow absolute left-1/2 top-1/2 z-[999] w-full max-w-[400px] -translate-x-1/2 -translate-y-1/2 rounded-lg bg-white dark:bg-gray-900 max-md:w-[90%]">
    <div
        class="flex items-center justify-between gap-2.5 border-b px-4 py-3 text-lg font-bold text-gray-800 dark:border-gray-800 dark:text-white">
        ¿Estás seguro?
    </div>
    <div class="px-4 py-3 text-left text-gray-600 dark:text-gray-300">
        ¿Estás seguro de que quieres eliminar este objetivo? Esta acción no se puede deshacer.
    </div>
    <div class="flex justify-end gap-2.5 px-4 py-2.5">
        <button type="button" @click="showDeleteModal = false" class="transparent-button">
            Cancelar
        </button>
        <button type="button" @click="deleteGoal" class="primary-button">
            Eliminar
        </button>
    </div>
</div>
