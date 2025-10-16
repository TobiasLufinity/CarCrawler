<!doctype html>
<html lang="sv">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Kortlista med sök</title>
    <style>
        <?php include 'css/main.css'; ?>
    </style>
</head>
<body>
<main class="container" aria-labelledby="page-title">
    <header>
        <h1 id="page-title" class="title">Bilar</h1>
        <label class="search" aria-label="Sök">
            <span class="icon" aria-hidden="true">🔎</span>
            <input id="searchInput" type="search" placeholder="Sök på märke, modell, år eller bränsle..." autocomplete="off" />
            <button id="clearBtn" class="clear" type="button" title="Rensa sökning" aria-label="Rensa">✕</button>
        </label>
    </header>
    <div class="controls">
        <div class="meta"><span id="count">Visar 0 objekt</span></div>
        <div class="controls-pager">
            <label for="pageSize">Antal per sida:</label>
            <select id="pageSize">
                <option value=4>4</option>
                <option value="8" selected>8</option>
                <option value="12">12</option>
                <option value="18">18</option>
            </select>
        </div>
    </div>

    <section id="grid" class="grid" aria-live="polite" aria-busy="false">
    </section>
    <section id="pagination" class="pagination"></section>

</main>

<script>


    const grid = document.getElementById('grid');
    const searchInput = document.getElementById('searchInput');
    const clearBtn = document.getElementById('clearBtn');
    const count = document.getElementById('count');
    const pageSizeSelect = document.getElementById('pageSize')
    const pagination = document.getElementById('pagination');

    function render(data){
        grid.setAttribute('aria-busy','true');
        if (!data.data.length){
            grid.innerHTML = `<div class="empty">Inget matchade din sökning.</div>`;
        } else {
            grid.innerHTML = data.data.map(item => {
                return `
              <article class="card" tabindex="0">
                <h3>${escapeHTML(item.model)}</h3>
                <p>${escapeHTML(item.description.length > 100 ? item.description.substring(0, 100) + "..." : item.description)}</p>
                <div class="tags">
                  <span class="tag">${escapeHTML(item.year)}</span>
                  <span class="tag">${escapeHTML(item.mileage)}</span>
                  <span class="tag">${escapeHTML(item.fuel)}</span>
                </div>
              </article>
            `;
            }).join('');
        }
        count.textContent = `Visar ${data.data.length} as ${data.total} objekt`;
        grid.setAttribute('aria-busy','false');
        renderPagination(data)
    }

    function renderPagination(data) {
        const totalPages = Math.ceil(data.total / pageSizeSelect.value); //TODO: Get page size from selector
        if (totalPages <= 1) { pagination.innerHTML = ''; return; }

        let html = '';
        if (data.page > 1) {
            html += `<button data-page="${data.page - 1}">◀ Föregående</button>`;
        }
        html += `<span>Sida ${data.page} av ${totalPages}</span>`;
        if (data.page < totalPages) {
            html += `<button data-page="${data.page + 1}">Nästa ▶</button>`;
        }
        pagination.innerHTML = html;

        pagination.querySelectorAll('button').forEach(btn => {
            btn.addEventListener('click', e => {
                console.log("Clidck")
                console.log(e)
                fetchCars(searchInput.value, parseInt(e.target.dataset.page, 10));
            });
        });
    }

    // Debounce för bättre UX
    function debounce(fn, ms=500){
        let t; return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms); };
    }

    // HTML-escape
    function escapeHTML(str){
        return String(str)
            .replaceAll('&','&amp;')
            .replaceAll('<','&lt;')
            .replaceAll('>','&gt;')
            .replaceAll('"','&quot;')
            .replaceAll("'",'&#39;');
    }

    // Init
    searchInput.addEventListener('input', debounce(e => {
        fetchCars(e.target.value)
    }));

    function fetchCars(search = "", page = null) {
        const pageSizeSelected = pageSize.value;
        fetch('api/cars', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ search: search, limit: pageSizeSelected, page: page })
        })
            .then(response => {
                if (!response.ok) throw new Error('Unable to fetch cars.');
                return response.json();
            })
            .then(response => {
                render(response);
            })
            .catch(error => {
                console.error('Fetch error:', error);
            });
    }

    pageSizeSelect.addEventListener('change', e => {
        fetchCars(searchInput.value);
    });

    fetchCars()


    clearBtn.addEventListener('click', () => {
        searchInput.value = '';
        searchInput.dispatchEvent(new Event('input'))
        searchInput.focus();
    });

</script>
</body>
</html>
