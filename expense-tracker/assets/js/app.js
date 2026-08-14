const API = {
    expenses: 'api/expenses.php',
    categories: 'api/categories.php',
    summary: 'api/summary.php',
};

let categoryChart = null;
let editModal = null;

document.addEventListener('DOMContentLoaded', () => {
    const page = document.body.querySelector('main');
    if (!page) {
        return;
    }

    if (document.getElementById('stat-month-total')) {
        loadDashboardData();
    }

    if (document.getElementById('add-expense-form')) {
        loadCategories('category_id');
        setDefaultDate('date');
        document.getElementById('add-expense-form').addEventListener('submit', submitExpenseForm);
    }

    if (document.getElementById('history-body')) {
        const modalEl = document.getElementById('editExpenseModal');
        if (modalEl && window.bootstrap) {
            editModal = new bootstrap.Modal(modalEl);
        }
        initHistoryPage();
    }
});

function formatCurrency(amount) {
    const value = Number(amount) || 0;
    return '₱' + value.toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function monthLabel(year, month) {
    return new Date(year, month - 1, 1).toLocaleDateString('en-PH', {
        month: 'long',
        year: 'numeric',
    });
}

function todayIso() {
    const now = new Date();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    return `${now.getFullYear()}-${month}-${day}`;
}

function setDefaultDate(inputId) {
    const input = document.getElementById(inputId);
    if (input && !input.value) {
        input.value = todayIso();
    }
}

function showAlert(message, type = 'danger') {
    const banner = document.getElementById('alert-banner');
    if (!banner) {
        window.alert(message);
        return;
    }
    banner.className = `alert alert-${type}`;
    banner.textContent = message;
    banner.classList.remove('d-none');
}

function hideAlert() {
    const banner = document.getElementById('alert-banner');
    if (banner) {
        banner.classList.add('d-none');
        banner.textContent = '';
    }
}

function categoryBadge(expense) {
    const color = expense.category_color || expense.color || '#4a4a4a';
    const icon = expense.category_icon || expense.icon || '';
    const name = expense.category_name || expense.name || '';
    const extraClass = name.toLowerCase() === 'savings' ? ' text-savings' : '';
    return `<span class="category-badge${extraClass}">
        <span class="category-dot" style="background:${color}"></span>
        <span>${icon} ${escapeHtml(name)}</span>
    </span>`;
}

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

async function apiRequest(url, options = {}) {
    const response = await fetch(url, {
        headers: {
            'Accept': 'application/json',
            ...(options.body ? { 'Content-Type': 'application/json' } : {}),
            ...(options.headers || {}),
        },
        ...options,
    });

    let payload;
    try {
        payload = await response.json();
    } catch (err) {
        throw new Error('The server returned an unexpected response.');
    }

    if (!response.ok || !payload.success) {
        throw new Error(payload.message || 'Request failed.');
    }

    return payload;
}

async function loadDashboardData() {
    hideAlert();
    try {
        const [summaryRes, expensesRes] = await Promise.all([
            apiRequest(API.summary),
            apiRequest(`${API.expenses}?limit=5`),
        ]);

        const summary = summaryRes.data;
        document.getElementById('dashboard-month-label').textContent = monthLabel(summary.year, summary.month);
        document.getElementById('stat-month-total').textContent = formatCurrency(summary.monthly_total);
        document.getElementById('stat-count').textContent = summary.transaction_count;
        document.getElementById('stat-avg-day').textContent = formatCurrency(summary.avg_per_day);

        const top = summary.top_category;
        document.getElementById('stat-top-category').innerHTML = top
            ? categoryBadge(top)
            : '—';

        renderRecentExpenses(expensesRes.data || []);
        renderCategoryChart(summary.category_breakdown || []);
    } catch (error) {
        showAlert(error.message || 'Unable to load dashboard data.');
    }
}

function renderRecentExpenses(expenses) {
    const body = document.getElementById('recent-expenses-body');
    if (!body) {
        return;
    }

    if (!expenses.length) {
        body.innerHTML = '<tr><td colspan="4" class="text-muted">No expenses yet.</td></tr>';
        return;
    }

    body.innerHTML = expenses.map((expense) => {
        const amountClass = (expense.category_name || '').toLowerCase() === 'savings'
            ? 'text-savings'
            : 'text-expense';
        return `<tr>
            <td>${escapeHtml(expense.date)}</td>
            <td>${categoryBadge(expense)}</td>
            <td>${escapeHtml(expense.description)}</td>
            <td class="text-end ${amountClass}">${formatCurrency(expense.amount)}</td>
        </tr>`;
    }).join('');
}

function renderCategoryChart(breakdown) {
    const canvas = document.getElementById('category-chart');
    const empty = document.getElementById('chart-empty');
    if (!canvas) {
        return;
    }

    const withTotals = breakdown.filter((row) => Number(row.total) > 0);

    if (categoryChart) {
        categoryChart.destroy();
        categoryChart = null;
    }

    if (!withTotals.length) {
        canvas.classList.add('d-none');
        if (empty) {
            empty.classList.remove('d-none');
        }
        return;
    }

    canvas.classList.remove('d-none');
    if (empty) {
        empty.classList.add('d-none');
    }

    const labels = withTotals.map((row) => `${row.icon} ${row.name}`);
    const values = withTotals.map((row) => Number(row.total));
    const colors = withTotals.map((row) => {
        if ((row.name || '').toLowerCase() === 'savings') {
            return '#1a7f37';
        }
        return row.color || '#4a4a4a';
    });

    categoryChart = new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: colors,
                borderColor: '#ffffff',
                borderWidth: 2,
            }],
        },
        options: {
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#111111',
                        boxWidth: 12,
                    },
                },
            },
        },
    });
}

async function loadCategories(selectId, selectedValue = '') {
    const select = document.getElementById(selectId);
    if (!select) {
        return [];
    }

    try {
        const result = await apiRequest(API.categories);
        const categories = result.data || [];
        const isFilter = selectId === 'filter-category';
        const placeholder = isFilter
            ? '<option value="">All categories</option>'
            : '<option value="">Select a category</option>';

        select.innerHTML = placeholder + categories.map((category) => {
            const selected = String(selectedValue) === String(category.id) ? ' selected' : '';
            return `<option value="${category.id}"${selected}>${category.icon} ${escapeHtml(category.name)}</option>`;
        }).join('');

        return categories;
    } catch (error) {
        showAlert(error.message || 'Unable to load categories.');
        select.innerHTML = '<option value="">Unable to load categories</option>';
        return [];
    }
}

async function submitExpenseForm(event) {
    event.preventDefault();
    hideAlert();

    const amountInput = document.getElementById('amount');
    const amount = Number(amountInput.value);
    const amountError = document.getElementById('amount-error');

    amountInput.classList.remove('is-invalid');
    if (!Number.isFinite(amount) || amount <= 0) {
        amountInput.classList.add('is-invalid');
        if (amountError) {
            amountError.classList.add('d-block');
        }
        return;
    }

    const payload = {
        amount,
        category_id: Number(document.getElementById('category_id').value),
        description: document.getElementById('description').value.trim(),
        date: document.getElementById('date').value,
    };

    if (!payload.category_id || !payload.description || !payload.date) {
        showAlert('Please complete all fields before saving.');
        return;
    }

    const button = document.getElementById('submit-expense-btn');
    button.disabled = true;

    try {
        await apiRequest(API.expenses, {
            method: 'POST',
            body: JSON.stringify(payload),
        });
        showAlert('Expense saved. Returning to the dashboard…', 'success');
        setTimeout(() => {
            window.location.href = 'index.php';
        }, 800);
    } catch (error) {
        showAlert(error.message || 'Unable to save this expense.');
        button.disabled = false;
    }
}

async function initHistoryPage() {
    await loadCategories('filter-category');
    await loadCategories('edit-category');
    await loadHistoryTable();

    document.getElementById('history-filter-form').addEventListener('submit', (event) => {
        event.preventDefault();
        filterHistory();
    });
    document.getElementById('clear-filters').addEventListener('click', () => {
        document.getElementById('filter-category').value = '';
        document.getElementById('filter-date-from').value = '';
        document.getElementById('filter-date-to').value = '';
        filterHistory();
    });
    document.getElementById('edit-expense-form').addEventListener('submit', updateExpense);
}

function buildHistoryQuery() {
    const params = new URLSearchParams();
    const categoryId = document.getElementById('filter-category').value;
    const dateFrom = document.getElementById('filter-date-from').value;
    const dateTo = document.getElementById('filter-date-to').value;

    if (categoryId) {
        params.set('category_id', categoryId);
    }
    if (dateFrom) {
        params.set('date_from', dateFrom);
    }
    if (dateTo) {
        params.set('date_to', dateTo);
    }

    const query = params.toString();
    return query ? `${API.expenses}?${query}` : API.expenses;
}

async function filterHistory() {
    await loadHistoryTable();
}

async function loadHistoryTable() {
    hideAlert();
    const body = document.getElementById('history-body');

    try {
        const result = await apiRequest(buildHistoryQuery());
        const expenses = result.data || [];

        if (!expenses.length) {
            body.innerHTML = '<tr><td colspan="5" class="text-muted">No expenses match these filters.</td></tr>';
            return;
        }

        body.innerHTML = expenses.map((expense) => {
            const amountClass = (expense.category_name || '').toLowerCase() === 'savings'
                ? 'text-savings'
                : 'text-expense';
            return `<tr>
                <td>${escapeHtml(expense.date)}</td>
                <td>${categoryBadge(expense)}</td>
                <td>${escapeHtml(expense.description)}</td>
                <td class="text-end ${amountClass}">${formatCurrency(expense.amount)}</td>
                <td class="text-end">
                    <button type="button" class="btn btn-sm btn-outline-dark me-1"
                        data-expense="${encodeURIComponent(JSON.stringify(expense))}"
                        onclick="editExpense(this)">Edit</button>
                    <button type="button" class="btn btn-sm btn-outline-dark"
                        onclick="deleteExpense(${Number(expense.id)})">Delete</button>
                </td>
            </tr>`;
        }).join('');
    } catch (error) {
        body.innerHTML = '<tr><td colspan="5" class="text-muted">Unable to load expenses.</td></tr>';
        showAlert(error.message || 'Unable to load history.');
    }
}

function editExpense(button) {
    const expense = JSON.parse(decodeURIComponent(button.getAttribute('data-expense')));
    document.getElementById('edit-id').value = expense.id;
    document.getElementById('edit-amount').value = expense.amount;
    document.getElementById('edit-category').value = expense.category_id;
    document.getElementById('edit-description').value = expense.description;
    document.getElementById('edit-date').value = expense.date;
    if (editModal) {
        editModal.show();
    }
}

async function updateExpense(event) {
    event.preventDefault();
    hideAlert();

    const amount = Number(document.getElementById('edit-amount').value);
    if (!Number.isFinite(amount) || amount <= 0) {
        showAlert('Amount must be a positive number.');
        return;
    }

    const payload = {
        id: Number(document.getElementById('edit-id').value),
        amount,
        category_id: Number(document.getElementById('edit-category').value),
        description: document.getElementById('edit-description').value.trim(),
        date: document.getElementById('edit-date').value,
    };

    try {
        await apiRequest(API.expenses, {
            method: 'PUT',
            body: JSON.stringify(payload),
        });
        if (editModal) {
            editModal.hide();
        }
        showAlert('Expense updated.', 'success');
        await loadHistoryTable();
    } catch (error) {
        showAlert(error.message || 'Unable to update this expense.');
    }
}

async function deleteExpense(id) {
    const confirmed = window.confirm('Delete this expense? This cannot be undone.');
    if (!confirmed) {
        return;
    }

    hideAlert();
    try {
        await apiRequest(`${API.expenses}?id=${encodeURIComponent(id)}`, {
            method: 'DELETE',
        });
        showAlert('Expense deleted.', 'success');
        await loadHistoryTable();
    } catch (error) {
        showAlert(error.message || 'Unable to delete this expense.');
    }
}
