<x-admin::layouts>
    <x-slot:title>
        {{-- @lang('admin::app.settings.goals.index.title') --}} Goals
    </x-slot>

    <div class="flex flex-col gap-4">
        <div
            class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                <!-- Breadcrumbs -->
                {{-- <x-admin::breadcrumbs name="admin.goals" /> --}}

                <div class="text-xl font-bold dark:text-white">
                    {{-- @lang('admin::app.settings.users.index.title') --}}
                    Metas
                </div>
            </div>

            <div class="flex items-center gap-x-2.5">
                {!! view_render_event('admin.settings.users.index.create_button.before') !!}

                <!-- Create button for User -->
                @if (bouncer()->hasPermission('settings.user.users.create'))
                    <div class="flex items-center gap-x-2.5">
                        <button type="button" class="primary-button" @click="$refs.userSettings.openModal()">
                            {{-- @lang('admin::app.settings.users.index.create-btn') --}}
                            Crear Meta
                        </button>
                    </div>
                @endif

                {!! view_render_event('admin.settings.users.index.create_button.after') !!}
            </div>
        </div>
        <v-users-settings ref="userSettings"></v-users-settings>


    </div>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="users-settings-template"
        >  <div>
            <!-- Toolbar Superior -->
            <div
                class="flex items-center justify-between gap-4 rounded-t-lg border border-b-0 border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 max-md:flex-wrap">
                <div class="toolbarLeft flex gap-x-1">
                    <div class="flex w-full items-center gap-x-1.5">
                        <!-- Buscador -->
                        <div class="flex max-w-[445px] items-center max-sm:w-full max-sm:max-w-full">
                            <div class="relative w-full">
                                <div
                                    class="icon-search absolute top-1.5 flex items-center text-2xl ltr:left-3 rtl:right-3">
                                </div>
                                <input  type="text"
                                    :value="searchQuery"
                                    @input="searchQuery = $event.target.value; searchUsers()"
                                    class="block w-full rounded-lg border bg-white py-1.5 leading-6 text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400 ltr:pl-10 ltr:pr-3 rtl:pl-3 rtl:pr-10"
                                    placeholder="Buscar" autocomplete="off">
                            </div>
                        </div>

                        <!-- Filtro -->
                        {{-- <div>
                            <div class="relative flex cursor-pointer items-center rounded-md bg-sky-100 px-4 py-[9px] font-semibold text-sky-600 dark:bg-brandColor dark:text-white"
                                onclick="searchUsers1()">
                                Filtro
                            </div>
                        </div> --}}
                    </div>
                </div>

                <!-- Paginación y elementos por página -->
                <div class="toolbarRight flex gap-x-4 hidden">
                    <div class="flex items-center gap-x-2">
                        <p class="whitespace-nowrap text-gray-600 dark:text-gray-300 max-sm:hidden">Por Página</p>
                        <div class="relative">
                            <select v-model="perPage" @change="updatePagination"
                                class="block appearance-none bg-gray-100 border border-gray-300 text-gray-700 py-2 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="30">30</option>
                                <option value="50">50</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            @include('goals::table')
            @include("goals::components.delete_modal")
        </div>

            <x-admin::form
                v-slot="{ meta, values, errors, handleSubmit }"
                as="div"
                ref="modalForm"
            >
                <form
                    @submit="handleSubmit($event, updateOrCreate)"
                    ref="userForm"
                >
                    {!! view_render_event('admin.settings.users.index.form_controls.before') !!}

                    <x-admin::modal ref="userUpdateAndCreateModal">
                        <!-- Modal Header -->
                        <x-slot:header>
                            <p class="text-lg font-bold text-gray-800 dark:text-white">
                                @{{
                                    selectedType == 'create'
                                    ? "@lang('admin::app.settings.users.index.create.title')"
                                    : "@lang('admin::app.settings.users.index.edit.title')"
                                }}
                            </p>
                        </x-slot>

                        <!-- Modal Content -->
                        <x-slot:content>
                             <x-admin::form.control-group.control
                                type="hidden"
                                name="id"
                                v-model="goal.id"
                            />

                            <div class="flex gap-4 ">
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label class="required">
                                        User Name
                                    </x-admin::form.control-group.label>
                                    <x-admin::form.control-group.control
                                        type="select"
                                        name="user_id"
                                        rules="required"
                                        v-model="goal.user_id"
                                        :label="trans('admin::app.settings.users.index.create.role')"
                                        @change="changePipeline()"
                                    >
                                        <option
                                            v-for="user in users"
                                            :key="user.id"
                                            :value="user.id"
                                        >
                                            @{{ user.name }}
                                        </option>

                                    </x-admin::form.control-group.control>
                                </x-admin::form.control-group>
                                {{-- amount --}}
                                <x-admin::form.control-group >
                                    <x-admin::form.control-group.label class="required">
                                        Amount
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="number"
                                        name="target_value"
                                        rules="required"
                                        v-model="goal.target_value"
                                        :label="trans('admin::app.settings.users.index.create.role')"
                                    >
                                    @{{ goal.target_value }}
                                    </x-admin::form.control-group.control>
                                </x-admin::form.control-group>
                            </div>
                            <!-- Pipeline-->
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label class="required">
                                    Pipeline
                                    </x-admin::form.control-group.label>

                                    <v-field
                                        name="pipeline_id"
                                        label="@lang('admin::app.settings.users.index.create.pipeline')"
                                        v-model="pipeline_id"
                                    >
                                        <select
                                        name="pipeline_id"
                                        class="flex min-h-[39px] w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                                        :class="[errors['pipeline_id'] ? 'border !border-red-600 hover:border-red-600' : '']"
                                        v-model="goal.pipeline_id"
                                    >
                                        <option v-if="pipelines[0] == undefined" value="" disabled selected >
                                            No hay pipelines disponibles para este usuario
                                        </option>
                                        <option
                                            v-for="pipeline in pipelines"
                                            :value="pipeline.id"
                                        >
                                            @{{ pipeline.name }}
                                        </option>
                                    </select>
                                    </v-field>

                                    <x-admin::form.control-group.error name="pipeline_id" />
                                </x-admin::form.control-group>
                            {{-- Pipeline end --}}
                            {{-- Dates start --}}
                                 <div class="flex gap-4">
                                    <x-admin::form.control-group>
                                        <x-admin::form.control-group.label class="required">
                                            Date Start
                                        </x-admin::form.control-group.label>
                                        <x-admin::form.control-group.control
                                            type="date"
                                            name="date_start"
                                            rules="required"
                                            v-model="goal.date_start"
                                            :label="trans('admin::app.settings.users.index.create.role')"
                                        >
                                        @{{ goal.date_start }}
                                        </x-admin::form.control-group.control>
                                    </x-admin::form.control-group>
                                    <x-admin::form.control-group>
                                        <x-admin::form.control-group.label class="required">
                                            Date End
                                        </x-admin::form.control-group.label>

                                        <x-admin::form.control-group.control
                                            type="date"
                                            name="date_end"
                                            rules="required"
                                            v-model="goal.date_end"
                                            :label="trans('admin::app.settings.users.index.create.role')"
                                        >
                                        @{{ goal.date_end }}
                                        </x-admin::form.control-group.control>
                                    </x-admin::form.control-group>
                                </div>
                            {{-- Dates end --}}
                        </x-slot>

                        <!-- Modal Footer -->
                        <x-slot:footer>
                            {!! view_render_event('admin.settings.users.index.modal.footer.save_button.before') !!}

                            <!-- Save Button -->
                            <x-admin::button
                                button-type="submit"
                                class="primary-button justify-center"
                                :title="trans('admin::app.settings.users.index.create.save-btn')"
                                ::loading="isProcessing"
                                ::disabled="isProcessing"
                            />

                            {!! view_render_event('admin.settings.users.index.modal.footer.save_button.after') !!}
                        </x-slot>
                    </x-admin::modal>

                    {!! view_render_event('admin.settings.users.index.form_controls.after') !!}
                </form>
            </x-admin::form>
        </script>

        <script type="module">
            app.component('v-users-settings', {
                template: '#users-settings-template',
                data() {
                    return {
                        isProcessing: false,
                        roles: @json($roles),
                        groups: @json($groups),
                        pipelines: @json($pipelines),
                        users: @json($users),
                        goal: {},
                        searchQuery: '',
                        goalToDelete: null,
                        showDeleteModal: false,
                        selectedGoals: [],
                        selectAll: false
                    };
                },

                computed: {
                    gridsCount() {
                        let count = this.$refs.datagrid.available.columns.length;

                        if (this.$refs.datagrid.available.actions.length) {
                            ++count;
                        }

                        if (this.$refs.datagrid.available.massActions.length) {
                            ++count;
                        }

                        return count;
                    },

                    selectedType() {
                        return this.goal.id ? 'edit' : 'create';
                    },
                },

                methods: {
                    openModal() {
                        this.goal = {
                            id: null,
                            user_id: null,
                            target_value: null,
                            pipeline_id: null,
                            date_start: null,
                            date_end: null,
                            groups: []
                        };
                        this.pipelines = [];

                        this.$refs.userUpdateAndCreateModal.toggle();
                    },

                    updateOrCreate(params, {
                        resetForm,
                        setErrors
                    }) {
                        const userForm = new FormData(this.$refs.userForm);

                        userForm.append('_method', params.id ? 'put' : 'post');

                        this.isProcessing = true;

                        this.$axios.post(params.id ? `{{ route('admin.goals.update', '') }}/${params.id}` :
                            "{{ route('admin.goals.store') }}", userForm).then(response => {
                            this.isProcessing = false;

                            this.$refs.userUpdateAndCreateModal.toggle();
                            if (response.data.success) {
                                this.$emitter.emit('add-flash', {
                                    type: 'success',
                                    message: response.data.message,
                                });
                                setTimeout(() => {
                                    window.location.reload();
                                }, 2000);
                            } else {
                                this.$emitter.emit('add-flash', {
                                    type: 'error',
                                    message: response.data.message
                                });
                            }

                        }).catch(error => {
                            this.isProcessing = false;

                            if (error.response.status === 422) {
                                setErrors(error.response.data.errors);
                            }
                        });
                    },

                    editModal(url) {
                        this.$axios.get(url)
                            .then(response => {
                                this.goal = {
                                    id: response.data.data.id,
                                    user_id: response.data.data.user_id,
                                    target_value: response.data.data.target_value,
                                    pipeline_id: response.data.data.pipeline_id,
                                    date_start: response.data.data.start_date,
                                    date_end: response.data.data.end_date,
                                    groups: response.data.data.groups?.map(group => group.id) || []
                                };
                                console.log(this.goal);
                                console.log(response.data.data);
                                this.$refs.userUpdateAndCreateModal.toggle();
                            })
                            .catch(error => {
                                console.error("Error loading goal data:", error);
                                this.$emitter.emit('add-flash', {
                                    type: 'error',
                                    message: 'Failed to load goal data'
                                });
                            });
                    },
                    searchUsers() {
                        console.log("Término de búsqueda:", this.searchQuery);
                    },
                    sortBy(parametro) {
                        console.log("Ordenar por:", this.sortColumn);
                    },
                    //metods for delete
                    confirmDelete(id) {
                        console.log(id);
                        this.goalToDelete = id;
                        this.showDeleteModal = true;
                    },
                    deleteGoal() {
                        this.isProcessing = true;
                        this.$axios.post(`{{ route('admin.goals.delete', '') }}/${this.goalToDelete}`)
                            .then(response => {
                                this.isProcessing = false;
                                this.showDeleteModal = false;

                                this.$emitter.emit('add-flash', {
                                    type: 'success',
                                    message: response.data.message
                                });
                                setTimeout(() => {
                                    window.location.reload();
                                }, 2000);
                            })
                            .catch(error => {
                                this.isProcessing = false;
                                this.showDeleteModal = false;

                                this.$emitter.emit('add-flash', {
                                    type: 'error',
                                    message: error.response?.data?.message ||
                                        'Error al eliminar el objetivo'
                                });
                            });
                    },
                    changePipeline() {
                        if (!this.goal.user_id) {
                            this.pipelines = [];
                            return;
                        }

                        this.isProcessing = true;

                        const params = {
                            params: {
                                userId: this.goal.user_id
                            }
                        };

                        this.$axios.get(`{{ route('admin.settings.pipelines.getPipelinesForUser') }}`, params)
                            .then(response => {
                                this.pipelines = response.data.pipelines || [];

                                if (this.pipelines.length > 0 && !this.pipelines.some(p => p.id === this.goal
                                        .pipeline_id)) {
                                    this.goal.pipeline_id = this.pipelines[0].id;
                                } else if (this.pipelines.length === 0) {
                                    this.goal.pipeline_id = null;
                                }
                            })
                            .catch(error => {
                                console.log("Error fetching pipelines:", error);
                                this.pipelines = [];
                            })
                            .finally(() => {
                                this.isProcessing = false;
                            });
                    },
                    toggleSelectAll() {
                        console.log("entra");
                        if (this.selectAll) {
                            // Seleccionar todos los IDs de las metas
                            this.selectedGoals = @json($goals->pluck('id'));
                        } else {
                            // Limpiar selección
                            this.selectedGoals = [];
                        }
                    },

                    toggleGoalSelection(goalId) {
                        const index = this.selectedGoals.indexOf(goalId);
                        if (index === -1) {
                            // Añadir a la selección
                            this.selectedGoals.push(goalId);
                        } else {
                            // Quitar de la selección
                            this.selectedGoals.splice(index, 1);
                        }

                        // Actualizar el estado de "seleccionar todos"
                        this.selectAll = this.selectedGoals.length === @json($goals->count());
                    },
                    openModalDeleted() {
                        console.log("entra desde el checkbox", this.selectedGoals);
                        this.showModal = true;
                    },
                    confirmDelete() {
                        // Aquí iría tu lógica para eliminar
                        console.log("Elementos a eliminar:", this.selectedGoals);
                        // Ejemplo:
                        // this.deleteSelectedGoals();
                        this.showModal = false;
                    }
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
