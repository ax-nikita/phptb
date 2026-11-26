'use strict';

const form = document.getElementById('form');
const table = document.getElementById('data');
const status = document.getElementById('status');
const user = document.getElementById('user');
const year = document.getElementById('year');
const rows = table.querySelectorAll('tr[data-id]');
let pendingRequest;

form.addEventListener('submit', async (event) => {
    event.preventDefault();

    if (pendingRequest) {
        pendingRequest.abort();
    }

    const request = new AbortController();
    pendingRequest = request;

    const userName = user.selectedOptions[0].textContent;
    const reportYear = year.value;
    const url = new URL(form.action);
    url.search = new URLSearchParams(new FormData(form)).toString();

    table.hidden = true;
    status.textContent = 'Loading…';

    try {
        const response = await fetch(url, {
            signal: request.signal,
            headers: { Accept: 'application/json' },
        });

        if (!response.ok) {
            throw new Error('HTTP ' + response.status);
        }

        const data = await response.json();

        if (pendingRequest !== request) {
            return;
        }

        if (!Array.isArray(data)) {
            throw new Error('Invalid response');
        }

        const months = new Map(data.map((item) => [item.month, item]));

        rows.forEach((row) => {
            const month = months.get(row.dataset.id);
            row.querySelector('[data-field="amount"]').textContent =
                month ? Number(month.amount).toFixed(2) : '0.00';
            row.querySelector('[data-field="count"]').textContent =
                month ? String(month.count) : '0';
        });

        document.getElementById('user-name').textContent = userName;
        document.getElementById('report-year').textContent = reportYear;
        status.textContent = '';
        table.hidden = false;
    } catch (error) {
        if (pendingRequest === request && error.name !== 'AbortError') {
            status.textContent = 'Unable to load transactions. Please try again.';
        }
    } finally {
        if (pendingRequest === request) {
            pendingRequest = null;
        }
    }
});
