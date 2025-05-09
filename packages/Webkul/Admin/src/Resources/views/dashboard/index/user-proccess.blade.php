{!! view_render_event('admin.dashboard.index.revenue_by_types.before') !!}

<!-- Total Leads Vue Component -->
<v-dashboard-revenue-by-user-goals>
    <!-- Shimmer -->
    <x-admin::shimmer.dashboard.index.revenue-by-types />
</v-dashboard-revenue-by-user-goals>
{!! view_render_event('admin.dashboard.index.revenue_by_types.after') !!}

@pushOnce('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue-apexcharts@1.7.0/dist/vue-apexcharts.min.js"></script>

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
                        Objetivos del usuario
                    </p>
                </div>

                <!-- Doughnut Chart -->
                <div class="flex w-full max-w-full flex-col gap-4 px-8 pt-8" v-if="report.statistics && report.statistics.length">
                    <!-- Gráficos con contenedor pequeño y opciones personalizadas -->
                    <div class="flex flex-wrap justify-center gap-5">
                        <!-- Múltiples gráficos ApexCharts -->
                       <div
                            v-for="(chart, chartIndex) in chartConfigs"
                            :key="'chart-' + chartIndex"
                            class="w-[350px] h-[300px]"
                        >
                        <div class="text-center dark:text-gray-300">
                            @{{ chart.values.name }}
                        </div>
                            <div :id="'chart-container-' + chartIndex" class="text-xs dark:text-gray-300"></div>
                            <!-- Alineación de los 3 elementos: izquierda, centro y derecha -->
                            <div class="flex justify-between items-center" >
                                <!-- Izquierda -->
                                <div class="text-start" style="margin-top:-60%; margin-left:3em;">
                                    <p class="text-xs dark:text-gray-300">
                                        @{{ chart.values.leads_won_value_sum }}
                                    </p>
                                </div>

                                <!-- Centro -->
                                <div class="text-center" style="margin-top:-80%;">
                                    <p class="text-xxs dark:text-gray-300">
                                        @{{ chart.values.value_goal }}
                                    </p>
                                </div>

                                <!-- Derecha -->
                                <div class="text-end" style="margin-top:-60%; margin-right:3em">
                                    <p class="text-xs dark:text-gray-300">
                                        @{{ chart.values.value_goal - chart.values.leads_won_value_sum }}
                                    </p>
                                </div>
                            </div>
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
        // Register ApexCharts component globally
        app.component('apexchart', VueApexCharts);

        app.component('v-dashboard-revenue-by-user-goals', {
            template: '#v-dashboard-revenue-by-user-goals-template',

            data() {
                return {
                    report: {
                        statistics: []
                    },
                    colors: [
                        '#8979FF',
                        '#111827',
                        '#d82323',
                        '#1fcb23',
                        '#FF9800',
                        '#03A9F4'
                    ],
                    isLoading: true,
                    charts: [], // Array para almacenar instancias de gráficos
                    chartConfigs: [] // Array para configuraciones de gráficos
                }
            },

            computed: {
                chartLabels() {
                    return ["Faltante", "Completado"];
                },
            },

            mounted() {
                this.getStats({});
                this.$emitter.on('reporting-filter-updated', this.getStats);
            },

            updated() {
                // Inicializar gráficos cuando los datos estén disponibles
                if (!this.isLoading && this.report.statistics && this.report.statistics.length) {
                    this.$nextTick(() => {
                        // Solo renderizar gráficos si hay configuraciones
                        if (this.chartConfigs.length > 0) {
                            this.renderAllCharts();
                        }
                    });
                }
            },

            beforeUnmount() {
                // Limpiar todas las instancias de gráficos
                this.destroyAllCharts();
                this.$emitter.off('reporting-filter-updated', this.getStats);
            },

            methods: {
                getStats(filters) {
                    this.isLoading = true;

                    // Destruir gráficos existentes antes de cargar nuevos datos
                    this.destroyAllCharts();
                    // Reiniciar configuraciones de gráficos
                    this.chartConfigs = [];

                    var filters = Object.assign({}, filters);
                    filters.type = 'user-proccess-states';

                    const url = "{{ route('admin.dashboard.stats') }}";

                    this.$axios.get(url, {
                        params: filters
                    }).then(response => {
                        this.report = response.data;

                        if (!Array.isArray(this.report.statistics)) {
                            this.report.statistics = Object.values(this.report.statistics.original?.data ||
                            {});
                        }

                        this.extendColors(this.report.statistics.length);
                        // Preparar configuraciones de gráficos basadas en los datos
                        this.prepareChartConfigs(this.report.statistics);

                        this.isLoading = false;

                        // Renderizar gráficos en el siguiente tick
                        this.$nextTick(() => {
                            this.renderAllCharts();
                        });
                    }).catch(error => {
                        console.error('Error fetching stats:', error);
                        this.isLoading = false;
                    });
                },

                extendColors(length) {
                    while (this.colors.length < length) {
                        const hue = Math.floor(Math.random() * 360);
                        const newColor = `hsl(${hue}, 70%, 60%)`;
                        this.colors.push(newColor);
                    }
                },

                // Preparar configuraciones de gráficos basadas en los datos
                prepareChartConfigs(statistics) {
                    if (!statistics || !statistics.length) {
                        return;
                    }

                    // Limpiar configuraciones anteriores
                    this.chartConfigs = [];

                    // Crear configuraciones para cada usuario
                    statistics.forEach(item => {
                        const stats = item.original.statistics;
                        this.chartConfigs.push({
                            title: stats.userFullName,
                            type: "donut",
                            series: [stats.percentage_achieved, stats.missing_percentage],
                            labels: ['Completado', 'Faltante'],
                            colors: ['#33D970', '#EF4444'],
                            values: {
                                leads_won_value_sum: stats.leads_won_value_sum,
                                missing_percentage: stats.missing_percentage,
                                name: stats.name,
                                percentage_achieved: stats.percentage_achieved,
                                userFullName: stats.userFullName,
                                value_goal: stats.value_goal,
                            }
                        });
                    });
                },

                renderAllCharts() {
                    this.destroyAllCharts();
                    this.chartConfigs.forEach((config, index) => {
                        const containerId = `chart-container-${index}`;
                        const container = document.getElementById(containerId);
                        if (container) {
                            const options = this.getChartOptions(config);
                            try {
                                const chart = new ApexCharts(container, options);
                                chart.render();
                                this.charts.push(chart);
                            } catch (error) {
                                console.log(`Error al renderizar gráfico ${index}:`, error);
                            }
                        }
                    });
                },

                getChartOptions(config) {
                    const baseOptions = {
                        series: config.series,
                        chart: {
                            type: config.type,
                            height: 280,
                            fontFamily: 'inherit',
                            toolbar: {
                                show: false
                            }
                        },
                        colors: config.colors,
                        labels: config.labels,
                        legend: {
                            position: 'bottom'
                        },
                        responsive: [{
                            breakpoint: 480,
                            options: {
                                chart: {
                                    width: 250
                                },
                                legend: {
                                    position: 'bottom',
                                    offsetY: 10
                                }
                            }
                        }],
                        plotOptions: {
                            pie: {
                                startAngle: -90,
                                endAngle: 90,
                                offsetY: 10
                            }
                        },
                        grid: {
                            padding: {
                                bottom: -80
                            }
                        }
                    };

                    return baseOptions;
                },

                destroyAllCharts() {
                    if (this.charts && this.charts.length) {
                        this.charts.forEach(chart => {
                            if (chart) {
                                try {
                                    chart.destroy();
                                } catch (error) {
                                    console.error("Error al destruir gráfico:", error);
                                }
                            }
                        });
                        this.charts = [];
                    }
                }
            }
        });
    </script>

    <style>
        .chart-container {
            margin-bottom: 20px;
        }
    </style>
@endPushOnce
