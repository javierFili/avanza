{!! view_render_event('admin.dashboard.index.revenue_by_types.before') !!}

<!-- Total Leads Vue Component -->
<v-dashboard-revenue-by-user-goals>
    <!-- Shimmer -->
    <x-admin::shimmer.dashboard.index.revenue-by-types />
</v-dashboard-revenue-by-user-goals>

{!! view_render_event('admin.dashboard.index.revenue_by_types.after') !!}

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-dashboard-revenue-by-user-goals-template"
    >
        <!-- Shimmer -->
        <template v-if="isLoading">
            <x-admin::shimmer.dashboard.index.revenue-by-types />
        </template>

        <!-- Total Sales Section -->
        <template v-else>
            <div class="grid gap-4 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="flex flex-col justify-between gap-1">
                    <p class="text-base font-semibold dark:text-gray-300">
                        {{-- @lang('admin::app.dashboard.index.revenue-by-types.title') --}}
                        Objetivos del usuario
                    </p>
                    <form>

                    </form>
                </div>

                <!-- Doughnut Chart -->
                <div class="flex w-full max-w-full flex-col gap-4 px-8 pt-8" v-if="report.statistics.length">
                    <!-- Gráfico con contenedor pequeño y opciones personalizadas -->
                   <div class="flex flex-wrap justify-center gap-5">
                        <div class="w-[300px] h-[250px]"> <!-- Contenedor con tamaño fijo -->
                            <x-admin::charts.doughnut
                                ::labels="chartLabels"
                                ::datasets="chartDatasets"
                                ::options="{
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: {
                                            display: false
                                        }
                                    },
                                    cutout: '65%'  <!-- Controla el grosor del anillo -->
                                }"
                            />
                        </div>
                    </div>
                    <!-- Leyenda -->
                    <div class="flex flex-wrap justify-center gap-5">
                        <div
                            class="flex items-center gap-2 whitespace-nowrap"
                            v-for="(stat, index) in report.statistics"
                        >
                            <span
                                class="h-3.5 w-3.5 rounded-sm"
                                :style="{ backgroundColor: colors[index] }"
                            ></span>
                            <p class="text-xs dark:text-gray-300">@{{ stat.name }}</p>
                        </div>
                    </div>
                </div>

                <!-- Empty Product Design -->
                <div
                    class="flex flex-col gap-8 p-4"
                    v-else
                >
                    <div class="grid justify-center justify-items-center gap-3.5 py-2.5">
                        <!-- Placeholder Image -->
                        <img
                            src="{{ vite()->asset('images/empty-placeholders/default.svg') }}"
                            class="dark:mix-blend-exclusion dark:invert"
                        >

                        <!-- Add Variants Information -->
                        <div class="flex flex-col items-center">
                            <p class="text-base font-semibold text-gray-400">
                                @lang('admin::app.dashboard.index.revenue-by-sources.empty-title')
                            </p>

                            <p class="text-gray-400">
                                @lang('admin::app.dashboard.index.revenue-by-sources.empty-info')
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </script>

    <script type="module">
        app.component('v-dashboard-revenue-by-user-goals', {
            template: '#v-dashboard-revenue-by-user-goals-template',

            data() {
                return {
                    report: [],

                    colors: [
                        '#8979FF',
                        '#111827',
                    ],

                    isLoading: true,
                }
            },

            computed: {
                chartLabels() {
                    return ["Faltante", "Completado"];
                },
                chartDatasets() {
                    const statistics = this.report.statistics;

                    return [{
                        data: [statistics[2], statistics[1]], // Solo 2 valores que sumen 100%
                        backgroundColor: [
                        '#111827','#4CAF50'], // Verde para completado, rojo para faltante
                        borderWidth: 0 // Elimina el borde si no lo necesitas
                    },];
                }
            },

            mounted() {
                const filters = {
                    userId: 1,
                    pipelineId: 1,
                    date_start: "2025-04-10",
                    date_end: "2025-05-08"
                };

                this.getStats(filters);

                this.$emitter.on('reporting-filter-updated', this.getStats);
            },

            methods: {
                getStats(filters) {
                    this.isLoading = true;
                    const url = "{{ route('admin.goals.user.statistics') }}";
                    const response = this.$axios.get(url, {
                        params: filters
                    }).then(response => {
                        console.log(response);

                        this.report = response.data;
                        if (!Array.isArray(this.report.statistics)) {
                            this.report.statistics = Object.values(this.report.statistics);
                        }
                        this.extendColors(this.report.statistics.length);
                        this.isLoading = false;
                    }).catch(error => {});
                },

                extendColors(length) {
                    while (this.colors.length < length) {
                        const hue = Math.floor(Math.random() * 360);
                        const newColor = `hsl(${hue}, 70%, 60%)`;
                        this.colors.push(newColor);
                    }
                },
            }
        });
    </script>
@endPushOnce
