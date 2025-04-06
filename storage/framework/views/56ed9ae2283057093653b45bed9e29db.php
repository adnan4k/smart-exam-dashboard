<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
  <div class="container-fluid py-4">
    <div class="row">
      <!-- Questions Card -->
      <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
        <div class="card">
          <div class="card-body p-3">
            <div class="row">
              <div class="col-8">
                <div class="numbers">
                  <p class="text-sm mb-0 text-capitalize font-weight-bold">Questions</p>
                  <h5 class="font-weight-bolder mb-0">
                    <?php echo e($questionCount); ?> 
                  </h5>
                </div>
              </div>
              <div class="col-4 text-end">
                <div style="background-color: #56C596;" class="icon icon-shape bg-[#56C596] text-white   shadow text-center border-radius-md">
                  <i class="fas fa-question-circle text-dark"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Subjects Card -->
      <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
        <div class="card">
          <div class="card-body p-3">
            <div class="row">
              <div class="col-8">
                <div class="numbers">
                  <p class="text-sm mb-0 text-capitalize font-weight-bold">Subjects</p>
                  <h5 class="font-weight-bolder mb-0">
                    <?php echo e($subjectCount); ?> 
                  </h5>
                </div>
              </div>
              <div class="col-4 text-end">
                <div style="background-color: #56C596;" class="icon icon-shape bg-[#56C596] text-white   shadow text-center border-radius-md">
                  <i class="fas fa-book text-dark"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Users Card -->
      <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
        <div class="card">
          <div class="card-body p-3">
            <div class="row">
              <div class="col-8">
                <div class="numbers">
                  <p class="text-sm mb-0 text-capitalize font-weight-bold">Users</p>
                  <h5 class="font-weight-bolder mb-0">
                    <?php echo e($userCount); ?> 
                  </h5>
                </div>
              </div>
              <div class="col-4 text-end">
                <div style="background-color: #56C596;" class="icon icon-shape bg-[#56C596] text-white   shadow text-center border-radius-md">
                  <i class="fas fa-users text-dark"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Subscriptions Card -->
      <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
        <div class="card">
          <div class="card-body p-3">
            <div class="row">
              <div class="col-8">
                <div class="numbers">
                  <p class="text-sm mb-0 text-capitalize font-weight-bold">Subscriptions</p>
                  <h5 class="font-weight-bolder mb-0">
                    <?php echo e($subscriptionCount); ?> 
                  </h5>
                </div>
              </div>
              <div class="col-4 text-end">
                <div style="background-color: #56C596;" class="icon icon-shape bg-[#56C596] text-white   shadow text-center border-radius-md">
                  <i class="fas fa-credit-card text-dark"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="grid grid-cols-5 gap-4 mt-4">
    <div class="col-span-3">
      <div class="card">
        <div class="card-header pb-0">
          <h6>Subscriptions overview</h6>
        </div>
        <div class="card-body p-3">
          <div class="chart">
            <canvas id="chart-line" class="chart-canvas" height="300px"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="/assets/js/plugins/chartjs.min.js"></script>
  <script src="/assets/js/plugins/Chart.extension.js"></script>
  <script>
    var ctx2 = document.getElementById("chart-line").getContext("2d");

    var gradientStroke1 = ctx2.createLinearGradient(0, 230, 0, 50);
    gradientStroke1.addColorStop(1, 'rgba(253,235,173,0.4)');
    gradientStroke1.addColorStop(0.2, 'rgba(245,57,57,0.0)');
    gradientStroke1.addColorStop(0, 'rgba(255,214,61,0)');

    var gradientStroke2 = ctx2.createLinearGradient(0, 230, 0, 50);
    gradientStroke2.addColorStop(1, 'rgba(20,23,39,0.4)');
    gradientStroke2.addColorStop(0.2, 'rgba(245,57,57,0.0)');
    gradientStroke2.addColorStop(0, 'rgba(255,214,61,0)');

    const orderedCounts = <?php echo json_encode($orderedCounts, 15, 512) ?>;
    console.log(orderedCounts, 'ordered counts here');

    new Chart(ctx2, {
      type: "line",
      data: {
        labels: ["Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
        datasets: [{
          label: "Users",
          tension: 0.4,
          borderWidth: 0,
          pointRadius: 0,
          borderColor: "#fbcf33",
          borderWidth: 3,
          backgroundColor: gradientStroke1,
          data: orderedCounts,
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
          intersect: false
        },
        scales: {
          yAxes: [{
            gridLines: {
              borderDash: [2],
              borderDashOffset: [2],
              color: '#dee2e6',
              zeroLineColor: '#dee2e6',
              zeroLineWidth: 1,
              zeroLineBorderDash: [2],
              drawBorder: false
            },
            ticks: {
              suggestedMin: 0,
              suggestedMax: 500,
              beginAtZero: true,
              padding: 10,
              fontSize: 11,
              fontColor: '#adb5bd',
              lineHeight: 3,
              fontStyle: 'normal',
              fontFamily: "Open Sans"
            }
          }],
          xAxes: [{
            gridLines: {
              zeroLineColor: 'rgba(0,0,0,0)',
              display: false
            },
            ticks: {
              padding: 10,
              fontSize: 11,
              fontColor: '#adb5bd',
              lineHeight: 3,
              fontStyle: 'normal',
              fontFamily: "Open Sans"
            }
          }]
        }
      }
    });
  </script>
</main><?php /**PATH C:\Users\Administrator\Desktop\apps\quiz\resources\views/livewire/dashboard.blade.php ENDPATH**/ ?>