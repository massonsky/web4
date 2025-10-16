<?php
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    echo '<div class="alert alert-danger">Доступ запрещен</div>';
    exit;
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">Панель администратора</h2>
        </div>
    </div>

    <!-- Статистические карточки -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title" id="total-orders">0</h4>
                            <p class="card-text">Всего заказов</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-shopping-cart fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title" id="total-clients">0</h4>
                            <p class="card-text">Всего клиентов</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title" id="total-employees">0</h4>
                            <p class="card-text">Сотрудников</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-tie fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title" id="monthly-income">0 ₽</h4>
                            <p class="card-text">Доход за месяц</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-ruble-sign fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Графики и аналитика -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Статистика заказов по месяцам</h5>
                </div>
                <div class="card-body">
                    <canvas id="ordersChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Статусы заказов</h5>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" width="200" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Последние заказы -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Последние заказы</h5>
                    <button class="btn btn-primary btn-sm" onclick="loadPage('admin_orders')">
                        Все заказы
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Клиент</th>
                                    <th>Дата</th>
                                    <th>Статус</th>
                                    <th>Сумма</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody id="recent-orders">
                                <tr>
                                    <td colspan="6" class="text-center">Загрузка...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    loadDashboardData();
    loadRecentOrders();
});

function loadDashboardData() {
    $.ajax({
        url: 'backend/dashboard_api.php',
        method: 'POST',
        data: { action: 'get_admin_stats' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#total-orders').text(response.data.total_orders);
                $('#total-clients').text(response.data.total_clients);
                $('#total-employees').text(response.data.total_employees);
                $('#monthly-income').text(response.data.monthly_income + ' ₽');
                
                // Создаем графики
                createOrdersChart(response.data.monthly_orders);
                createStatusChart(response.data.order_statuses);
            }
        },
        error: function() {
            showAlert('Ошибка загрузки данных', 'danger');
        }
    });
}

function loadRecentOrders() {
    $.ajax({
        url: 'backend/dashboard_api.php',
        method: 'POST',
        data: { action: 'get_recent_orders', limit: 10 },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let html = '';
                response.data.forEach(function(order) {
                    let statusClass = getStatusClass(order.status);
                    html += `
                        <tr>
                            <td>#${order.id}</td>
                            <td>${order.client_name}</td>
                            <td>${formatDate(order.order_date)}</td>
                            <td><span class="badge ${statusClass}">${order.status}</span></td>
                            <td>${order.total_amount} ₽</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="viewOrder(${order.id})">
                                    Просмотр
                                </button>
                            </td>
                        </tr>
                    `;
                });
                $('#recent-orders').html(html);
            }
        },
        error: function() {
            $('#recent-orders').html('<tr><td colspan="6" class="text-center text-danger">Ошибка загрузки</td></tr>');
        }
    });
}

function createOrdersChart(data) {
    const ctx = document.getElementById('ordersChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(item => item.month),
            datasets: [{
                label: 'Количество заказов',
                data: data.map(item => item.count),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function createStatusChart(data) {
    const ctx = document.getElementById('statusChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.map(item => item.status),
            datasets: [{
                data: data.map(item => item.count),
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

function getStatusClass(status) {
    switch(status) {
        case 'новый': return 'badge-primary';
        case 'в работе': return 'badge-warning';
        case 'выполнен': return 'badge-success';
        case 'отменен': return 'badge-danger';
        default: return 'badge-secondary';
    }
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('ru-RU');
}

function viewOrder(orderId) {
    loadPage('admin_orders');
    // После загрузки страницы откроем модальное окно с деталями заказа
    setTimeout(() => {
        if (typeof viewOrderDetails === 'function') {
            viewOrderDetails(orderId);
        }
    }, 500);
}
</script>