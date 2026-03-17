/**
 * MC-CLASE2-2026 | Library Management System
 * Main Application JavaScript
 */

/* ── State ────────────────────────────────────────────────── */
const state = {
  books:   [],
  authors: [],
  sortBooks:   { col: 'id_libro', dir: 'asc' },
  sortAuthors: { col: 'id_autor', dir: 'asc' },
  filterBooks:   '',
  filterAuthors: '',
  editTarget:   null,  // { type, record }
  deleteTarget: null,  // { type, record }
};

/* ── API Helpers ──────────────────────────────────────────── */
const API = {
  books:   'api/books.php',
  authors: 'api/authors.php',

  async request(url, options = {}) {
    const res = await fetch(url, {
      headers: { 'Content-Type': 'application/json' },
      ...options,
    });

    const raw = await res.text();

    let json;
    try {
      json = JSON.parse(raw);
    } catch (_) {
      // PHP returned HTML (fatal error, warning, etc.) — show first 200 chars
      const preview = raw.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim().slice(0, 200);
      throw new Error('PHP error: ' + preview);
    }

    if (!json.success) throw new Error(json.message || 'Unknown API error');
    return json;
  },

  getBooks:    ()         => API.request(API.books),
  getAuthors:  ()         => API.request(API.authors),
  createBook:  (data)     => API.request(API.books,   { method: 'POST', body: JSON.stringify(data) }),
  updateBook:  (data)     => API.request(API.books,   { method: 'PUT',  body: JSON.stringify(data) }),
  deleteBook:  (id)       => API.request(`${API.books}?id=${id}`,   { method: 'DELETE' }),
  createAuthor:(data)     => API.request(API.authors, { method: 'POST', body: JSON.stringify(data) }),
  updateAuthor:(data)     => API.request(API.authors, { method: 'PUT',  body: JSON.stringify(data) }),
  deleteAuthor:(id)       => API.request(`${API.authors}?id=${id}`, { method: 'DELETE' }),
};

/* ── Toast ────────────────────────────────────────────────── */
function toast(message, type = 'success') {
  const icons = { success: '✔', error: '✖', info: 'ℹ' };
  const container = document.getElementById('toast-container');
  const el = document.createElement('div');
  el.className = `toast toast-${type}`;
  el.innerHTML = `<span>${icons[type] ?? '●'}</span> ${escHtml(message)}`;
  container.appendChild(el);
  setTimeout(() => {
    el.style.animation = 'toastOut .3s ease forwards';
    el.addEventListener('animationend', () => el.remove());
  }, 3400);
}

/* ── Utility ──────────────────────────────────────────────── */
function escHtml(str) {
  return String(str).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

function sortData(arr, col, dir) {
  return [...arr].sort((a, b) => {
    const va = String(a[col] ?? '').toLowerCase();
    const vb = String(b[col] ?? '').toLowerCase();
    const cmp = va.localeCompare(vb, undefined, { numeric: true });
    return dir === 'asc' ? cmp : -cmp;
  });
}

function filterData(arr, query, fields) {
  if (!query.trim()) return arr;
  const q = query.toLowerCase();
  return arr.filter(row => fields.some(f => String(row[f] ?? '').toLowerCase().includes(q)));
}

/* ── Navigation ───────────────────────────────────────────── */
function navigate(panel) {
  document.querySelectorAll('.nav-item').forEach(el => {
    el.classList.toggle('active', el.dataset.panel === panel);
  });
  document.querySelectorAll('.panel').forEach(el => {
    el.classList.toggle('active', el.id === `panel-${panel}`);
  });
  const titles = { books: 'Book List', authors: 'Author List' };
  document.getElementById('topbar-title').textContent = titles[panel] ?? 'Dashboard';
}

/* ── Books Rendering ──────────────────────────────────────── */
function renderBooks() {
  let data = filterData(state.books, state.filterBooks, ['id_libro','libro','autor']);
  data = sortData(data, state.sortBooks.col, state.sortBooks.dir);

  const tbody = document.getElementById('books-tbody');
  const count = document.getElementById('books-count');
  count.textContent = `${data.length} record${data.length !== 1 ? 's' : ''}`;

  if (!data.length) {
    tbody.innerHTML = `<tr><td colspan="5"><div class="empty-state">
      <div class="empty-icon">📚</div>
      <p>No books found</p>
    </div></td></tr>`;
    return;
  }

  tbody.innerHTML = data.map(b => `
    <tr>
      <td class="td-id">${escHtml(b.id_libro)}</td>
      <td class="td-title">${escHtml(b.libro)}</td>
      <td class="td-author">${escHtml(b.autor ?? '—')}</td>
      <td class="td-actions">
        <div class="actions-group">
          <button class="btn-icon" title="Edit" onclick="openEditModal('book', ${b.id_libro})">
            <img src="img/lapiz.png" alt="Edit">
          </button>
          <button class="btn-icon" title="Delete" onclick="openDeleteModal('book', ${b.id_libro})">
            <img src="img/bote.png" alt="Delete">
          </button>
        </div>
      </td>
    </tr>`).join('');

  updateSortHeaders('books-table', state.sortBooks);
}

/* ── Authors Rendering ────────────────────────────────────── */
function renderAuthors() {
  let data = filterData(state.authors, state.filterAuthors, ['id_autor','autor']);
  data = sortData(data, state.sortAuthors.col, state.sortAuthors.dir);

  const tbody = document.getElementById('authors-tbody');
  const count = document.getElementById('authors-count');
  count.textContent = `${data.length} record${data.length !== 1 ? 's' : ''}`;

  if (!data.length) {
    tbody.innerHTML = `<tr><td colspan="4"><div class="empty-state">
      <div class="empty-icon">👤</div>
      <p>No authors found</p>
    </div></td></tr>`;
    return;
  }

  tbody.innerHTML = data.map(a => `
    <tr>
      <td class="td-id">${escHtml(a.id_autor)}</td>
      <td class="td-title">${escHtml(a.autor)}</td>
      <td class="td-actions">
        <div class="actions-group">
          <button class="btn-icon" title="Edit" onclick="openEditModal('author', ${a.id_autor})">
            <img src="img/lapiz.png" alt="Edit">
          </button>
          <button class="btn-icon" title="Delete" onclick="openDeleteModal('author', ${a.id_autor})">
            <img src="img/bote.png" alt="Delete">
          </button>
        </div>
      </td>
    </tr>`).join('');

  updateSortHeaders('authors-table', state.sortAuthors);
}

/* ── Sort Columns ─────────────────────────────────────────── */
function updateSortHeaders(tableId, sortState) {
  document.querySelectorAll(`#${tableId} thead th[data-col]`).forEach(th => {
    th.classList.remove('sorted-asc', 'sorted-desc');
    if (th.dataset.col === sortState.col) {
      th.classList.add(sortState.dir === 'asc' ? 'sorted-asc' : 'sorted-desc');
    }
  });
}

function handleSortClick(type, col) {
  const s = type === 'books' ? state.sortBooks : state.sortAuthors;
  if (s.col === col) {
    s.dir = s.dir === 'asc' ? 'desc' : 'asc';
  } else {
    s.col = col;
    s.dir = 'asc';
  }
  type === 'books' ? renderBooks() : renderAuthors();
}

/* ── Load Data ────────────────────────────────────────────── */
async function loadBooks() {
  try {
    const res = await API.getBooks();
    state.books = res.data ?? [];
    renderBooks();
  } catch (e) {
    toast(e.message, 'error');
  }
}

async function loadAuthors() {
  try {
    const res = await API.getAuthors();
    state.authors = res.data ?? [];
    renderAuthors();
    populateAuthorSelect();
  } catch (e) {
    toast(e.message, 'error');
  }
}

function populateAuthorSelect(selectedId = null) {
  const sel = document.getElementById('edit-book-author');
  if (!sel) return;
  sel.innerHTML = '<option value="">— Select author —</option>' +
    state.authors.map(a =>
      `<option value="${a.id_autor}" ${String(a.id_autor) === String(selectedId) ? 'selected' : ''}>${escHtml(a.autor)}</option>`
    ).join('');
}

/* ── Add Modal ────────────────────────────────────────────── */
function openAddBookModal() {
  document.getElementById('edit-modal-title').textContent = 'Add Book';
  document.getElementById('edit-book-id').value   = '';
  document.getElementById('edit-book-title').value = '';
  populateAuthorSelect();
  document.getElementById('book-edit-form').style.display  = 'block';
  document.getElementById('author-edit-form').style.display = 'none';
  document.getElementById('edit-modal').classList.add('open');
}

function openAddAuthorModal() {
  document.getElementById('edit-author-modal-title').textContent = 'Add Author';
  document.getElementById('edit-author-id').value   = '';
  document.getElementById('edit-author-name').value = '';
  document.getElementById('author-modal').classList.add('open');
}

/* ── Edit Modal ───────────────────────────────────────────── */
function openEditModal(type, id) {
  if (type === 'book') {
    const book = state.books.find(b => b.id_libro == id);
    if (!book) return;
    document.getElementById('edit-modal-title').textContent = 'Edit Book';
    document.getElementById('edit-book-id').value    = book.id_libro;
    document.getElementById('edit-book-title').value = book.libro;
    populateAuthorSelect(book.id_autor);
    document.getElementById('book-edit-form').style.display   = 'block';
    document.getElementById('author-edit-form').style.display = 'none';
    document.getElementById('edit-modal').classList.add('open');
  } else {
    const author = state.authors.find(a => a.id_autor == id);
    if (!author) return;
    document.getElementById('edit-author-modal-title').textContent = 'Edit Author';
    document.getElementById('edit-author-id').value   = author.id_autor;
    document.getElementById('edit-author-name').value = author.autor;
    document.getElementById('author-modal').classList.add('open');
  }
}

/* ── Delete Modal ─────────────────────────────────────────── */
function openDeleteModal(type, id) {
  let name, recordId;
  if (type === 'book') {
    const book = state.books.find(b => b.id_libro == id);
    if (!book) return;
    name = book.libro;
    recordId = book.id_libro;
  } else {
    const author = state.authors.find(a => a.id_autor == id);
    if (!author) return;
    name = author.autor;
    recordId = author.id_autor;
  }
  state.deleteTarget = { type, id: recordId };
  document.getElementById('delete-record-label').textContent = name;
  document.getElementById('delete-modal').classList.add('open');
}

/* ── Close Modals ─────────────────────────────────────────── */
function closeModal(id) {
  document.getElementById(id).classList.remove('open');
}

/* ── Save Book ────────────────────────────────────────────── */
async function saveBook() {
  const id       = document.getElementById('edit-book-id').value;
  const titulo   = document.getElementById('edit-book-title').value.trim();
  const authorId = document.getElementById('edit-book-author').value;

  if (!titulo) { toast('Book title is required.', 'error'); return; }
  if (!authorId) { toast('Please select an author.', 'error'); return; }

  const btn = document.getElementById('save-book-btn');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner"></span> Saving…';

  try {
    if (id) {
      await API.updateBook({ id_libro: id, libro: titulo, id_autor: authorId });
      toast('Book updated successfully.', 'success');
    } else {
      await API.createBook({ libro: titulo, id_autor: authorId });
      toast('Book added successfully.', 'success');
    }
    closeModal('edit-modal');
    await loadBooks();
  } catch (e) {
    toast(e.message, 'error');
  } finally {
    btn.disabled = false;
    btn.innerHTML = 'Save';
  }
}

/* ── Save Author ──────────────────────────────────────────── */
async function saveAuthor() {
  const id   = document.getElementById('edit-author-id').value;
  const name = document.getElementById('edit-author-name').value.trim();

  if (!name) { toast('Author name is required.', 'error'); return; }

  const btn = document.getElementById('save-author-btn');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner"></span> Saving…';

  try {
    if (id) {
      await API.updateAuthor({ id_autor: id, autor: name });
      toast('Author updated successfully.', 'success');
    } else {
      await API.createAuthor({ autor: name });
      toast('Author added successfully.', 'success');
    }
    closeModal('author-modal');
    await loadAuthors();
    renderBooks(); // refresh author names in books table
  } catch (e) {
    toast(e.message, 'error');
  } finally {
    btn.disabled = false;
    btn.innerHTML = 'Save';
  }
}

/* ── Confirm Delete ───────────────────────────────────────── */
async function confirmDelete() {
  const { type, id } = state.deleteTarget ?? {};
  if (!id) return;

  const btn = document.getElementById('confirm-delete-btn');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner"></span> Deleting…';

  try {
    if (type === 'book') {
      await API.deleteBook(id);
      toast('Book deleted successfully.', 'success');
      await loadBooks();
    } else {
      await API.deleteAuthor(id);
      toast('Author deleted successfully.', 'success');
      await loadAuthors();
    }
    closeModal('delete-modal');
  } catch (e) {
    toast(e.message, 'error');
    closeModal('delete-modal');
  } finally {
    btn.disabled = false;
    btn.innerHTML = 'Delete';
    state.deleteTarget = null;
  }
}

/* ── Sidebar Toggle (mobile) ──────────────────────────────── */
function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('open');
}

/* ── Init ─────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', async () => {
  // Navigation clicks
  document.querySelectorAll('.nav-item').forEach(item => {
    item.addEventListener('click', () => navigate(item.dataset.panel));
  });

  // Search inputs
  document.getElementById('books-search').addEventListener('input', e => {
    state.filterBooks = e.target.value;
    renderBooks();
  });
  document.getElementById('authors-search').addEventListener('input', e => {
    state.filterAuthors = e.target.value;
    renderAuthors();
  });

  // Sort headers — Books
  document.querySelectorAll('#books-table thead th[data-col]').forEach(th => {
    th.addEventListener('click', () => handleSortClick('books', th.dataset.col));
  });

  // Sort headers — Authors
  document.querySelectorAll('#authors-table thead th[data-col]').forEach(th => {
    th.addEventListener('click', () => handleSortClick('authors', th.dataset.col));
  });

  // Close modals on overlay click
  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => {
      if (e.target === overlay) overlay.classList.remove('open');
    });
  });

  // Initial load
  navigate('books');
  await Promise.all([loadAuthors(), loadBooks()]);
});
