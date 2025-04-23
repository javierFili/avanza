<!-- Tabla -->
<div class="flex">
    <div class="w-full">
        <table
            class="table-responsive box-shadow rounded-t-0 w-full overflow-hidden border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            <!-- Encabezados -->
            <thead class="hidden bg-gray-50 text-black dark:bg-gray-900 dark:text-gray-300 max-lg:block">
                <tr>
                    <th colspan="7" class="px-4 py-2.5 text-left">Lista de Metas</th>
                </tr>
            </thead>

            <thead
                class="hidden min-h-[47px] items-center gap-2.5 border-b bg-gray-50 px-4 py-2.5 text-black dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 max-lg:hidden"
                style="display: table-header-group;">
                <tr style="display: grid; grid-template-columns: repeat(7, minmax(0px, 1fr));">
                    {{-- <th class="px-2 py-2 text-center" hidden>
                        <label for="mass_action_select_all_records">
                            <input type="checkbox" v-model="selectAll" @change="toggleSelectAll()" class="peer hidden">
                            <span
                                class="icon-checkbox-outline cursor-pointer rounded-md text-2xl text-gray-500 peer-checked:text-brandColor"></span>
                        </label>
                    </th> --}}
                    <th class="px-2 py-2 text-center">
                        <div class="flex items-center gap-1.5 break-words cursor-pointer select-none hover:text-gray-800 dark:hover:text-white"
                            @click="sortBy('id')">
                            ID
                            {{-- <!-- <span v-if="sortColumn === 'id'" class="text-sm">{{ sortDirection === 'asc' ? '↑' : '↓' }}</span> --> --}}
                        </div>
                    </th>
                    <th class="px-2 py-2 text-center">
                        <div class="flex items-center gap-1.5 break-words cursor-pointer select-none hover:text-gray-800 dark:hover:text-white"
                            @click="sortBy('name')">
                            Nombre
                            {{-- <!-- <span v-if="sortColumn === 'name'" class="text-sm">{{ sortDirection === 'asc' ? '↑' : '↓' }}</span> --> --}}
                        </div>
                    </th>
                    <th class="px-2 py-2 text-center">
                        <div class="flex items-center gap-1.5 break-words cursor-pointer select-none hover:text-gray-800 dark:hover:text-white"
                            @click="sortBy('email')">
                            Pipeline
                            {{-- <!-- <span v-if="sortColumn === 'email'" class="text-sm">{{ sortDirection === 'asc' ? '↑' : '↓' }}</span> --> --}}
                        </div>
                    </th>
                    <th class="px-2 py-2 text-center">
                        <div class="flex items-center gap-1.5 break-words cursor-pointer select-none hover:text-gray-800 dark:hover:text-white"
                            @click="sortBy('status')">
                            Fecha inicio
                            {{-- <!-- <span v-if="sortColumn === 'status'" class="text-sm">{{ sortDirection === 'asc' ? '↑' : '↓' }}</span> --> --}}
                        </div>
                    </th>
                    <th class="px-2 py-2 text-center">
                        <div class="flex items-center gap-1.5 break-words cursor-pointer select-none hover:text-gray-800 dark:hover:text-white"
                            @click="sortBy('status')">
                            Monto objetivo
                            {{-- <!-- <span v-if="sortColumn === 'status'" class="text-sm">{{ sortDirection === 'asc' ? '↑' : '↓' }}</span> --> --}}
                        </div>
                    </th>
                    <th class="px-2 py-2 text-center">
                        <div class="flex items-center gap-1.5 break-words cursor-pointer select-none hover:text-gray-800 dark:hover:text-white"
                            @click="sortBy('created_at')">
                            Fecha fin
                            {{-- <!-- <span v-if="sortColumn === 'created_at'" class="text-sm">{{ sortDirection === 'asc' ? '↑' : '↓' }}</span> --> --}}
                        </div>
                    </th>
                    <th class="px-2 py-2 text-center">
                        <span>Acciones</span>
                    </th>
                </tr>
            </thead>

            <!-- Cuerpo de la tabla -->
            <tbody>
                @foreach ($goals as $goal)
                    <tr class="grid items-center gap-2.5 border-b px-4 py-4 text-gray-600 transition-all hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-gray-950 max-lg:hidden"
                        style="grid-template-columns: repeat(7, minmax(0px, 1fr));">
                        {{-- <td class="flex select-none items-center gap-16" hidden>
                            <input type="checkbox" v-model="selectedGoals" :id="{{ $goal->id }}"
                                value="{{ $goal->id }}" class="peer hidden" @change="openModalDeleted">
                            <label
                                class="icon-checkbox-outline peer-checked:icon-checkbox-select cursor-pointer rounded-md text-2xl text-gray-600 peer-checked:text-brandColor dark:text-gray-300"
                                for="{{ $goal->id }}" hidden></label >
                        </td> --}}
                        <td>{{ $goal->id }}</td>
                        <td>
                            <div class="flex items-center gap-2.5">
                                <div class="text-sm">{{ $goal->user->name }}</div>
                            </div>
                        </td>
                        <td class="truncate">{{ $goal->pipeline->name }}</td>
                        <td>
                            <span class="label-{{ strtolower($goal->start_date) }}">
                                {{ $goal->start_date }}
                            </span>
                        </td>
                        <td>
                            <span class="label-{{ strtolower($goal->start_date) }}">
                                <p class="truncate">{{ $goal->target_value }}</p>
                            </span>
                        </td>
                        <td>
                            <span class="label-{{ strtolower($goal->start_date) }}">
                                <p class="truncate">{{ $goal->end_date }}</p>
                            </span>
                        </td>
                        <td class="flex justify-center">
                            <a @click.prevent="editModal('{{ route('admin.goals.show', [$goal->id]) }}')"
                                class="p-1.5">
                                <span
                                    class="icon-edit cursor-pointer text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800"></span>
                            </a>
                            <a @click.prevent="confirmDelete({{ $goal->id }})" class="p-1.5">
                                <span
                                    class="icon-delete cursor-pointer text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800"></span>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
{{-- <div>
    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-sm mx-4">
            <!-- Encabezado del modal -->
            <div class="border-b px-4 py-3 flex justify-between items-center">
                <h3 class="text-base font-semibold text-gray-800">Confirmar eliminación</h3>
                <button @click="showModal = false" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Cuerpo del modal -->
            <div class="p-4">
                <p class="text-gray-700 mb-3 text-sm">¿Estás seguro que deseas eliminar los elementos seleccionados?</p>
                <p class="text-xs text-gray-500">Se eliminarán @{{ selectedGoals.length }} elementos.</p>
            </div>

            <!-- Pie del modal -->
            <div class="border-t px-4 py-3 flex justify-end space-x-2">
                <button @click="showModal = false"
                    class="px-3 py-1 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 text-sm">
                    Cancelar
                </button>
                <button @click="confirmDelete"
                    class="px-3 py-1 bg-red-500 text-white rounded-md hover:bg-red-600 text-sm">
                    Eliminar
                </button>
            </div>
        </div>
    </div>

</div> --}}
