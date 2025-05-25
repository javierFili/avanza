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
                        @lang('admin::app.goals.index.user_goals')
                    </p>
                </div>

                <!-- User Cards Container -->
                <div class="flex w-full max-w-full flex-col gap-6 px-4 pt-4" v-if="userGroups && Object.keys(userGroups).length">
                    <!-- Card por cada usuario -->
                    <div
                        v-for="(userCharts, userName) in userGroups"
                        :key="'user-' + userName"
                        class="border border-gray-200 rounded-lg p-6 bg-white dark:bg-gray-800 dark:border-gray-700"
                    >
                        <!-- Header del usuario -->
                        <div class="mb-6">
                            <h3 class="text-xl font-bold text-center dark:text-gray-300">
                                @{{ userName }}
                            </h3>
                        </div>

                        <!-- Grid de gráficos del usuario -->
                        <div :class="userCharts.length > 1 ? 'grid grid-cols-1 md:grid-cols-2 gap-6' : 'flex justify-center'">
                            <div
                                v-for="(chart, chartIndex) in userCharts"
                                :key="'chart-' + userName + '-' + chartIndex"
                                class="flex flex-col items-center"
                            >
                                <!-- Contenedor del gráfico -->
                                <div
                                    :id="'chart-container-' + sanitizeId(userName) + '-' + chartIndex"
                                    class="w-full max-w-sm h-80 mb-4"
                                >
                                    <!-- El gráfico se renderizará aquí -->
                                </div>

                                <!-- Información del gráfico -->
                                <div class="w-full max-w-sm">
                                    <!-- Información en grid horizontal -->
                                    <div class="grid grid-cols-3 gap-4 text-center mb-4">
                                        <!-- Completado -->
                                        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3">
                                            <div class="flex items-center justify-center mb-1">
                                                <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                                                <p class="text-xs font-medium text-green-700 dark:text-green-400">@lang('admin::app.goals.index.completed')</p>
                                            </div>
                                            <p class="text-lg font-bold text-green-800 dark:text-green-300">
                                                $@{{ formatNumber(chart.values.leads_won_value_sum) }}
                                            </p>
                                        </div>

                                        <!-- Meta -->
                                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3">
                                            <p class="text-xs font-medium text-blue-700 dark:text-blue-400 mb-1">  @lang('admin::app.goals.index.title')</p>
                                            <p class="text-lg font-bold text-blue-800 dark:text-blue-300">
                                                $@{{ formatNumber(chart.values.value_goal) }}
                                            </p>
                                            <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                                                @{{ chart.values.date_goal }}
                                            </p>
                                        </div>

                                        <!-- Faltante -->
                                        <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-3">
                                            <div class="flex items-center justify-center mb-1">
                                                <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                                                <p v-if="chart.values.value_goal - chart.values.leads_won_value_sum > 0"  class="text-xs font-medium text-red-700 dark:text-red-400"> @lang('admin::app.goals.index.missing')</p>
                                                <p v-else  class="text-xs font-medium text-green-700 dark:text-green-400"> @lang('admin::app.goals.index.completed')</p>
                                            </div>
                                            <p v-if="chart.values.value_goal - chart.values.leads_won_value_sum > 0" class="text-lg font-bold text-red-800 dark:text-red-300">
                                                $@{{ formatNumber(chart.values.value_goal - chart.values.leads_won_value_sum) }}
                                            </p>
                                            <p v-else class="text-lg font-bold text-green-800 dark:text-red-300">
                                                $@{{ formatNumber(chart.values.leads_won_value_sum - chart.values.value_goal) }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Porcentaje de completado -->
                                    <div class="text-center">
                                        <div class="bg-gray-200 dark:bg-gray-700 rounded-full h-3 mb-2">
                                            <div
                                                class="bg-gradient-to-r from-green-500 to-green-600 h-3 rounded-full transition-all duration-500 ease-out"
                                                :style="{ width: Math.min(chart.values.percentage_achieved, 100) + '%' }"
                                            ></div>
                                        </div>
                                        <p class="text-sm font-semibold dark:text-gray-300">
                                            @{{ Math.round(chart.values.percentage_achieved) }}% completado
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div class="flex flex-col gap-8 p-4" v-else>
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
                    charts: [],
                    chartConfigs: [],
                    userGroups: {}
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
                if (!this.isLoading && this.userGroups && Object.keys(this.userGroups).length) {
                    this.$nextTick(() => {
                        this.renderAllCharts();
                    });
                }
            },

            beforeUnmount() {
                this.destroyAllCharts();
                this.$emitter.off('reporting-filter-updated', this.getStats);
            },

            methods: {
                sanitizeId(str) {
                    return str.replace(/[^a-zA-Z0-9]/g, '');
                },

                formatNumber(value) {
                    if (!value) return '0';
                    return new Intl.NumberFormat('es-ES', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    }).format(value);
                },

                getStats(filters) {
                    this.isLoading = true;
                    this.destroyAllCharts();
                    this.chartConfigs = [];
                    this.userGroups = {};

                    var filters = Object.assign({}, filters);
                    filters.type = 'user-proccess-states';
                    const url = "{{ route('admin.dashboard.stats') }}";

                    this.$axios.get(url, {
                        params: filters
                    }).then(response => {
                        this.report = response.data;
                        console.log(this.report);

                        if (!Array.isArray(this.report.statistics)) {
                            this.report.statistics = Object.values(this.report.statistics.original?.data || {});
                        }

                        this.extendColors(this.report.statistics.length);
                        this.prepareChartConfigs(this.report.statistics);
                        this.groupChartsByUser();
                        this.isLoading = false;

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

                prepareChartConfigs(statistics) {
                    if (!statistics || !statistics.length) {
                        return;
                    }
                    this.chartConfigs = [];
                    statistics.forEach(item => {
                        const graphics = item.original.statistics;
                        graphics.forEach(stats => {
                            this.chartConfigs.push({
                                title: stats.userFullName,
                                type: "donut",
                                series: [stats.percentage_achieved, stats.missing_percentage],
                                labels: ['Completado', 'Faltante'],
                                colors: ['#10B981', '#EF4444'],
                                values: {
                                    leads_won_value_sum: stats.leads_won_value_sum,
                                    missing_percentage: stats.missing_percentage,
                                    name: stats.name,
                                    percentage_achieved: stats.percentage_achieved,
                                    userFullName: stats.userFullName,
                                    value_goal: stats.value_goal,
                                    date_goal: stats.date_goal
                                }
                            });
                        });
                    });
                    console.log('Chart configs:', this.chartConfigs);
                },

                groupChartsByUser() {
                    this.userGroups = {};
                    this.chartConfigs.forEach(chart => {
                        const userName = chart.values.userFullName;
                        if (!this.userGroups[userName]) {
                            this.userGroups[userName] = [];
                        }
                        this.userGroups[userName].push(chart);
                    });
                    console.log('User groups:', this.userGroups);
                },

                renderAllCharts() {
                    this.destroyAllCharts();

                    Object.keys(this.userGroups).forEach(userName => {
                        this.userGroups[userName].forEach((chart, chartIndex) => {
                            const containerId = `chart-container-${this.sanitizeId(userName)}-${chartIndex}`;
                            const container = document.getElementById(containerId);

                            if (container) {
                                const options = this.getChartOptions(chart);
                                try {
                                    const apexChart = new ApexCharts(container, options);
                                    apexChart.render();
                                    this.charts.push(apexChart);
                                } catch (error) {
                                    console.log(`Error al renderizar gráfico ${userName}-${chartIndex}:`, error);
                                }
                            } else {
                                console.warn(`Container ${containerId} no encontrado`);
                            }
                        });
                    });
                },

                getChartOptions(config) {
                    return {
                        series: config.series,
                        chart: {
                            type: config.type,
                            height: 280,
                            width: '100%',
                            fontFamily: 'inherit',
                            toolbar: {
                                show: false
                            },
                            animations: {
                                enabled: true,
                                easing: 'easeinout',
                                speed: 800
                            }
                        },
                        colors: config.colors,
                        labels: config.labels,
                        legend: {
                            position: 'bottom',
                            horizontalAlign: 'center',
                            fontSize: '.75rem',
                            markers: {
                                width: 8,
                                height: 8
                            }
                        },
                        dataLabels: {
                            enabled: true,
                            formatter: function (val) {
                                return Math.round(val) + "%";
                            },
                            style: {
                                fontSize: '.875rem',
                                fontWeight: 'bold',
                                colors: ['#fff']
                            }
                        },
                        plotOptions: {
                            pie: {
                                startAngle: -90,
                                endAngle: 90,
                                offsetY: 10,
                                donut: {
                                    size: '60%',
                                    labels: {
                                        show: true,
                                        name: {
                                            show: true,
                                            fontSize: '.875rem',
                                            fontWeight: 'bold'
                                        },
                                        value: {
                                            show: true,
                                            fontSize: '1rem',
                                            fontWeight: 'bold',
                                            formatter: function (val) {
                                                return Math.round(val) + '%';
                                            }
                                        },
                                        total: {
                                            show: true,
                                            label: 'Goal',
                                            fontSize: '.75rem',
                                            fontWeight: 'bold',
                                            formatter: function (w) {
                                                const completedPercentage = w.globals.seriesTotals[0];
                                                return Math.round(completedPercentage) + '%';
                                            }
                                        }
                                    }
                                }
                            }
                        },
                        responsive: [{
                            breakpoint: 480,
                            options: {
                                chart: {
                                    height: 220
                                },
                                legend: {
                                    position: 'bottom'
                                }
                            }
                        }],
                        stroke: {
                            width: 2,
                            colors: ['#fff']
                        },
                        grid: {
                            padding: {
                                bottom: -80
                            }
                        }
                    };
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
        /* Estilos base para el grid */
        .grid {
            display: grid;
        }

        .grid-cols-1 {
            grid-template-columns: repeat(1, minmax(0, 1fr));
        }

        .grid-cols-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        @media (min-width: 48rem) {
            .md\:grid-cols-2 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        /* Efectos de hover y transiciones */
        .transition-all {
            transition-property: all;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 150ms;
        }

        .duration-500 {
            transition-duration: 500ms;
        }

        .ease-out {
            transition-timing-function: cubic-bezier(0, 0, 0.2, 1);
        }

        /* Gradientes */
        .bg-gradient-to-r {
            background-image: linear-gradient(to right, var(--tw-gradient-stops));
        }

        .from-green-500 {
            --tw-gradient-from: #10B981;
            --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgba(16, 185, 129, 0));
        }

        .to-green-600 {
            --tw-gradient-to: #059669;
        }

        /* Espaciado y gaps */
        .gap-4 { gap: 16px; }
        .gap-6 { gap: 24px; }

        /* Max widths */
        .max-w-sm { max-width: 384px; }
    </style>
@endPushOnce
