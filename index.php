<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Library Management — MC-CLASE2-2026</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- ══ SIDEBAR ═══════════════════════════════════════════════ -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <h1>📖 LibraryDB</h1>
    <span>MC-CLASE2-2026</span>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section-label">Collections</div>

    <button class="nav-item active" data-panel="books">
      <span class="nav-icon">📚</span>
      Books
    </button>

    <button class="nav-item" data-panel="authors">
      <span class="nav-icon">👤</span>
      Authors
    </button>
  </nav>

  <div class="sidebar-footer">
    UDEN · Database Class 2 · 2026
  </div>
</aside>

<!-- ══ MAIN ══════════════════════════════════════════════════ -->
<div class="main">

  <!-- Top Bar -->
  <header class="topbar">
    <button class="btn btn-ghost" onclick="toggleSidebar()" style="display:none" id="menu-toggle">☰</button>
    <span id="topbar-title" class="topbar-title">Book List</span>
    <div class="topbar-actions">
      <!-- Contextual Add button rendered via JS, or static here -->
    </div>
  </header>

  <!-- Content -->
  <main class="content">

    <!-- ── BOOKS PANEL ──────────────────────────────────── -->
    <section class="panel active" id="panel-books">
      <div class="controls-bar">
        <div class="search-wrap">
          <span class="search-icon">🔍</span>
          <input
            class="search-input"
            id="books-search"
            type="search"
            placeholder="Search by code, title or author…"
          >
        </div>
        <button class="btn btn-primary" onclick="openAddBookModal()">
          + Add Book
        </button>
      </div>

      <div class="table-card">
        <div class="table-scroll">
          <table id="books-table">
            <thead>
              <tr>
                <th data-col="id_libro">ID <span class="sort-icon"></span></th>
                <th data-col="libro">Title <span class="sort-icon"></span></th>
                <th data-col="autor">Author <span class="sort-icon"></span></th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="books-tbody">
              <tr><td colspan="4" style="text-align:center;padding:40px;color:#aaa;">Loading…</td></tr>
            </tbody>
          </table>
        </div>
        <div class="table-footer">
          <span class="table-count" id="books-count">— records</span>
        </div>
      </div>
    </section>

    <!-- ── AUTHORS PANEL ────────────────────────────────── -->
    <section class="panel" id="panel-authors">
      <div class="controls-bar">
        <div class="search-wrap">
          <span class="search-icon">🔍</span>
          <input
            class="search-input"
            id="authors-search"
            type="search"
            placeholder="Search by ID or name…"
          >
        </div>
        <button class="btn btn-primary" onclick="openAddAuthorModal()">
          + Add Author
        </button>
      </div>

      <div class="table-card">
        <div class="table-scroll">
          <table id="authors-table">
            <thead>
              <tr>
                <th data-col="id_autor">ID <span class="sort-icon"></span></th>
                <th data-col="autor">Author Name <span class="sort-icon"></span></th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="authors-tbody">
              <tr><td colspan="3" style="text-align:center;padding:40px;color:#aaa;">Loading…</td></tr>
            </tbody>
          </table>
        </div>
        <div class="table-footer">
          <span class="table-count" id="authors-count">— records</span>
        </div>
      </div>
    </section>

  </main>
</div><!-- /.main -->

<!-- ══ TOAST CONTAINER ═══════════════════════════════════════ -->
<div id="toast-container"></div>

<!-- ══ EDIT BOOK MODAL ═══════════════════════════════════════ -->
<div class="modal-overlay" id="edit-modal">
  <div class="modal" role="dialog" aria-modal="true">
    <div class="modal-header">
      <h3 id="edit-modal-title">Edit Book</h3>
      <button class="modal-close" onclick="closeModal('edit-modal')" aria-label="Close">✕</button>
    </div>
    <div class="modal-body">
      <div id="book-edit-form">
        <input type="hidden" id="edit-book-id">
        <div class="form-group">
          <label class="form-label" for="edit-book-title">Title</label>
          <input class="form-control" id="edit-book-title" type="text" placeholder="Book title…" autocomplete="off">
        </div>
        <div class="form-group">
          <label class="form-label" for="edit-book-author">Author</label>
          <select class="form-control" id="edit-book-author">
            <option value="">— Select author —</option>
          </select>
        </div>
      </div>
      <div id="author-edit-form" style="display:none;"></div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('edit-modal')">Cancel</button>
      <button class="btn btn-primary" id="save-book-btn" onclick="saveBook()">Save</button>
    </div>
  </div>
</div>

<!-- ══ EDIT AUTHOR MODAL ═════════════════════════════════════ -->
<div class="modal-overlay" id="author-modal">
  <div class="modal" role="dialog" aria-modal="true">
    <div class="modal-header">
      <h3 id="edit-author-modal-title">Edit Author</h3>
      <button class="modal-close" onclick="closeModal('author-modal')" aria-label="Close">✕</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="edit-author-id">
      <div class="form-group">
        <label class="form-label" for="edit-author-name">Author Name</label>
        <input class="form-control" id="edit-author-name" type="text" placeholder="Full name…" autocomplete="off">
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('author-modal')">Cancel</button>
      <button class="btn btn-primary" id="save-author-btn" onclick="saveAuthor()">Save</button>
    </div>
  </div>
</div>

<!-- ══ DELETE CONFIRM MODAL ══════════════════════════════════ -->
<div class="modal-overlay" id="delete-modal">
  <div class="modal" role="dialog" aria-modal="true">
    <div class="modal-header">
      <h3>Confirm Deletion</h3>
      <button class="modal-close" onclick="closeModal('delete-modal')" aria-label="Close">✕</button>
    </div>
    <div class="modal-body">
      <div class="delete-icon">🗑️</div>
      <p class="delete-message">
        Are you sure you want to permanently delete:
        <strong class="delete-record-name" id="delete-record-label"></strong>
        This action cannot be undone.
      </p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('delete-modal')">Cancel</button>
      <button class="btn btn-danger" id="confirm-delete-btn" onclick="confirmDelete()">Delete</button>
    </div>
  </div>
</div>

<script src="js/app.js"></script>
</body>
</html>
