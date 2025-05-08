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
                        <!-- Gráficos existentes de doughnut -->
                        {{-- <div
                        class="w-[300px] h-[250px]"
                        v-for="(start, index) in report.statistics"
                        :key="'doughnut-' + index"
                        >
                            <x-admin::charts.doughnut
                                ::labels="chartLabels"
                                ::datasets="[{
                                                data: [start.original.statistics.missing_percentage, start.original.statistics.percentage_achieved],
                                                backgroundColor: [
                                                '#d82323','#1fcb23'],
                                                borderWidth: 1
                                            }]"
                                ::options="{
                                            rotation: -Math.PI,
                                            circumference: Math.PI,
                                            responsive: true,
                                            maintainAspectRatio: false,
                                            plugins: {
                                                legend: { display: false }
                                            },
                                            cutout: '65%',
                                            animation: {
                                                animateRotate: true,
                                                animateScale: true
                                            }
                                        }"
                            />
                            <label class="text-center block mt-2">
                                @{{ start.original.statistics.userFullName }}
                            </label>
                        </div> --}}

                        <!-- Múltiples gráficos ApexCharts -->
                        <div
                            v-for="(chart, chartIndex) in chartConfigs"
                            :key="'chart-' + chartIndex"
                            class="w-[350px] h-[300px] relative"
                        >
                            <div :id="'chart-container-' + chartIndex"></div>
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
                            colors: ['#1fcb23', '#d82323']
                        });
                    });

                    console.log("Configuraciones de gráficos preparadas:", this.chartConfigs);
                },

                // Renderizar todos los gráficos
                renderAllCharts() {
                    // Destruir gráficos existentes primero
                    this.destroyAllCharts();

                    // Crear nuevas instancias de gráficos
                    this.chartConfigs.forEach((config, index) => {
                        const containerId = `chart-container-${index}`;
                        const container = document.getElementById(containerId);

                        if (container) {
                            console.log(`Renderizando gráfico ${index} en contenedor ${containerId}`);
                            const options = this.getChartOptions(config);

                            try {
                                const chart = new ApexCharts(container, options);
                                chart.render();
                                this.charts.push(chart);
                                console.log(`Gráfico ${index} renderizado con éxito`);
                            } catch (error) {
                                console.error(`Error al renderizar gráfico ${index}:`, error);
                            }
                        } else {
                            console.warn(
                                `Contenedor ${containerId} no encontrado para el gráfico ${index}`);
                        }
                    });
                },

                // Obtener opciones específicas para cada tipo de gráfico
                getChartOptions(config) {
                    const baseOptions = {
                        series: config.series,
                        title: {
                            text: config.title,
                            align: 'center',
                            margin: 10,
                            offsetX: 0,
                            offsetY: 0,
                            floating: false,
                            style: {
                                fontSize: '14px',
                                fontWeight: 'bold',
                                fontFamily: undefined,
                                color: '#263238'
                            },
                        },
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
                                offsetY: 10,
                                donut: {
                                    size: '65%',
                                    label: "total",
                                    formatter: function(w) {
                                        const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                        return total;
                                    }
                                },
                                value:{
                                    show:true,
                                    formatter:function (val){
                                        return val;
                                    },
                                }
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

                // Destruir todos los gráficos para limpiar la memoria
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
