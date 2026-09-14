<div class="space-y-5">
    <!-- Stat Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <!-- Questions Card -->
        <x-stat-card 
            label="Questions" 
            :value="number_format($questionCount)" 
            icon="fas fa-circle-question" 
            tone="brand" 
            :href="route('questions')"
        />

        <!-- Subjects Card -->
        <x-stat-card 
            label="Subjects" 
            :value="number_format($subjectCount)" 
            icon="fas fa-book-bookmark" 
            tone="sage" 
            :href="route('subject')"
        />

        <!-- Users Card -->
        <x-stat-card 
            label="Total Users" 
            :value="number_format($userCount)" 
            icon="fas fa-users" 
            tone="ink" 
            :href="route('users')"
        />

        <!-- Subscriptions Card -->
        <x-stat-card 
            label="Subscriptions" 
            :value="number_format($subscriptionCount)" 
            icon="fas fa-credit-card" 
            tone="khaki" 
            :href="route('subscription')"
        />
    </div>

    <!-- Charts & Shortcuts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        <!-- Subscriptions Overview Chart -->
        <div class="lg:col-span-8">
            <div class="card bg-white border border-slate-200/70 rounded-2xl shadow-sm overflow-hidden h-full">
                <div class="card-header border-b border-slate-100 px-5 py-4 flex items-center justify-between">
                    <div>
                        <h6 class="font-bold text-slate-800 text-sm mb-0.5">Subscriptions Overview</h6>
                        <p class="text-xs text-slate-400 mb-0">Monthly user engagement and growth trajectory</p>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-[#58706D]/10 text-[#58706D]">
                        Yearly Analytics
                    </span>
                </div>
                <div class="card-body p-5">
                    <div class="chart h-72">
                        <canvas id="chart-line" class="w-full h-full"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Platform Status -->
        <div class="lg:col-span-4 flex flex-col gap-4">
            <!-- Quick Actions Card -->
            <div class="card bg-white border border-slate-200/70 rounded-2xl shadow-sm p-4">
                <h6 class="font-bold text-slate-800 text-sm mb-3">Quick Actions</h6>
                <div class="grid grid-cols-2 gap-2.5">
                    <a href="{{ route('questions') }}" wire:navigate class="flex flex-col items-center justify-center p-3 rounded-xl bg-slate-50 hover:bg-[#58706D]/10 text-slate-700 hover:text-[#58706D] transition-all border border-slate-100 text-decoration-none group">
                        <div class="w-9 h-9 rounded-lg bg-white shadow-xs flex items-center justify-center mb-1.5 text-[#58706D] group-hover:scale-105 transition-transform">
                            <i class="fas fa-plus text-xs"></i>
                        </div>
                        <span class="text-xs font-semibold">New Question</span>
                    </a>

                    <a href="{{ route('contests.create') }}" class="flex flex-col items-center justify-center p-3 rounded-xl bg-slate-50 hover:bg-[#58706D]/10 text-slate-700 hover:text-[#58706D] transition-all border border-slate-100 text-decoration-none group">
                        <div class="w-9 h-9 rounded-lg bg-white shadow-xs flex items-center justify-center mb-1.5 text-[#58706D] group-hover:scale-105 transition-transform">
                            <i class="fas fa-trophy text-xs"></i>
                        </div>
                        <span class="text-xs font-semibold">New Contest</span>
                    </a>

                    <a href="{{ route('subject') }}" wire:navigate class="flex flex-col items-center justify-center p-3 rounded-xl bg-slate-50 hover:bg-[#58706D]/10 text-slate-700 hover:text-[#58706D] transition-all border border-slate-100 text-decoration-none group">
                        <div class="w-9 h-9 rounded-lg bg-white shadow-xs flex items-center justify-center mb-1.5 text-[#58706D] group-hover:scale-105 transition-transform">
                            <i class="fas fa-book-bookmark text-xs"></i>
                        </div>
                        <span class="text-xs font-semibold">Add Subject</span>
                    </a>

                    <a href="{{ route('videos') }}" wire:navigate class="flex flex-col items-center justify-center p-3 rounded-xl bg-slate-50 hover:bg-[#58706D]/10 text-slate-700 hover:text-[#58706D] transition-all border border-slate-100 text-decoration-none group">
                        <div class="w-9 h-9 rounded-lg bg-white shadow-xs flex items-center justify-center mb-1.5 text-[#58706D] group-hover:scale-105 transition-transform">
                            <i class="fas fa-video text-xs"></i>
                        </div>
                        <span class="text-xs font-semibold">Upload Video</span>
                    </a>
                </div>
            </div>

            <!-- Platform Health Card -->
            <div class="card bg-white border border-slate-200/70 rounded-2xl shadow-sm p-4">
                <h6 class="font-bold text-slate-800 text-sm mb-3">Platform Overview</h6>
                <div class="space-y-2.5">
                    <div class="flex items-center justify-between text-xs py-1 border-b border-slate-100">
                        <span class="text-slate-500 flex items-center gap-1.5">
                            <i class="fas fa-shield-halved text-[#58706D]"></i> Access Mode
                        </span>
                        <span class="font-semibold text-slate-700">Admin Console</span>
                    </div>
                    <div class="flex items-center justify-between text-xs py-1 border-b border-slate-100">
                        <span class="text-slate-500 flex items-center gap-1.5">
                            <i class="fas fa-layer-group text-[#7C8A6E]"></i> Curriculum
                        </span>
                        <span class="font-semibold text-slate-700">{{ $subjectCount }} Subjects Registered</span>
                    </div>
                    <div class="flex items-center justify-between text-xs py-1">
                        <span class="text-slate-500 flex items-center gap-1.5">
                            <i class="fas fa-database text-[#4B5757]"></i> Content Bank
                        </span>
                        <span class="font-semibold text-slate-700">{{ $questionCount }} Questions Active</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Scripts -->
    <script src="/assets/js/plugins/chartjs.min.js"></script>
    <script src="/assets/js/plugins/Chart.extension.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var chartElement = document.getElementById("chart-line");
            if (!chartElement) return;

            var ctx2 = chartElement.getContext("2d");

            var gradientStroke = ctx2.createLinearGradient(0, 230, 0, 50);
            gradientStroke.addColorStop(1, 'rgba(88, 112, 109, 0.35)');
            gradientStroke.addColorStop(0.2, 'rgba(88, 112, 109, 0.05)');
            gradientStroke.addColorStop(0, 'rgba(88, 112, 109, 0.0)');

            const orderedCounts = @json($orderedCounts);

            new Chart(ctx2, {
                type: "line",
                data: {
                    labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                    datasets: [{
                        label: "Active Users",
                        tension: 0.35,
                        pointRadius: 3,
                        pointBackgroundColor: "#58706D",
                        pointBorderColor: "#ffffff",
                        pointBorderWidth: 2,
                        pointHoverRadius: 5,
                        borderColor: "#58706D",
                        borderWidth: 2.5,
                        backgroundColor: gradientStroke,
                        data: Array.isArray(orderedCounts) ? orderedCounts : [12, 19, 15, 25, 22, 30, 28, 35, 42, 38, 45, 50],
                        fill: true,
                        maxBarThickness: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: { display: false },
                    tooltips: {
                        enabled: true,
                        mode: "index",
                        intersect: false,
                        backgroundColor: "#1e293b",
                        titleFontFamily: "'Plus Jakarta Sans', sans-serif",
                        bodyFontFamily: "'Plus Jakarta Sans', sans-serif",
                        cornerRadius: 8,
                        xPadding: 10,
                        yPadding: 10
                    },
                    scales: {
                        yAxes: [{
                            gridLines: {
                                borderDash: [3, 3],
                                color: '#f1f5f9',
                                zeroLineColor: '#f1f5f9',
                                drawBorder: false
                            },
                            ticks: {
                                suggestedMin: 0,
                                suggestedMax: 60,
                                beginAtZero: true,
                                padding: 10,
                                fontSize: 11,
                                fontColor: '#94a3b8',
                                fontFamily: "'Plus Jakarta Sans', sans-serif"
                            }
                        }],
                        xAxes: [{
                            gridLines: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                padding: 10,
                                fontSize: 11,
                                fontColor: '#94a3b8',
                                fontFamily: "'Plus Jakarta Sans', sans-serif"
                            }
                        }]
                    }
                }
            });
        });
    </script>
</div>