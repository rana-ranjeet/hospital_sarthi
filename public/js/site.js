document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('#hospital-search-form');
    const input = document.querySelector('#hospital-search');
    const results = document.querySelector('#search-results');
    const serviceSelect = document.querySelector('#service-search');

    if (!form || !input || !results || !serviceSelect) return;

    let requestNumber = 0;
    const searchHospitals = async (query) => {
        const currentRequest = ++requestNumber;
        results.hidden = false;
        results.replaceChildren();
        if (!query.trim()) {
            results.hidden = true;
            return;
        }

        const loading = document.createElement('p');
        loading.className = 'search-empty';
        loading.textContent = 'Looking for hospitals...';
        results.append(loading);

        try {
            const response = await fetch(`/api/hospitals?q=${encodeURIComponent(query.trim())}`, { headers: { Accept: 'application/json' } });
            if (!response.ok) throw new Error('Search is unavailable');
            const hospitals = await response.json();
            if (currentRequest !== requestNumber) return;
            results.replaceChildren();
            if (!hospitals.length) {
                const empty = document.createElement('p');
                empty.className = 'search-empty';
                empty.textContent = 'No listed hospitals match yet. Suggest yours and we’ll work on it.';
                results.append(empty);
                return;
            }

            hospitals.forEach((hospital) => {
                const item = document.createElement('div');
                item.className = 'search-result';
                const details = document.createElement('span');
                const name = document.createElement('strong');
                const city = document.createElement('small');
                name.textContent = hospital.name;
                city.textContent = `${hospital.city}${hospital.address ? ` · ${hospital.address}` : ''}`;
                details.append(name, city);
                const link = document.createElement('a');
                link.href = `/hospitals/${encodeURIComponent(hospital.id)}/guides?service=${encodeURIComponent(serviceSelect.value)}`;
                link.textContent = 'View guides →';
                item.append(details, link);
                results.append(item);
            });
        } catch (error) {
            if (currentRequest !== requestNumber) return;
            const message = document.createElement('p');
            message.className = 'search-empty';
            message.textContent = 'Hospital search is temporarily unavailable. Please try again.';
            results.replaceChildren(message);
        }
    };

    let debounceTimer;
    input.addEventListener('input', () => {
        window.clearTimeout(debounceTimer);
        debounceTimer = window.setTimeout(() => searchHospitals(input.value), 250);
    });
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        window.clearTimeout(debounceTimer);
        searchHospitals(input.value);
    });
    document.addEventListener('click', (event) => {
        if (!form.contains(event.target) && !results.contains(event.target)) results.hidden = true;
    });
    input.addEventListener('focus', () => {
        if (input.value.trim()) results.hidden = false;
    });
});
