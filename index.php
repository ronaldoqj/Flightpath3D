<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Simple User Management System</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous" />
</head>
<body>
  <div class="container my-5">
    <h1 class="mb-4">Registered Users</h1>

    <!-- Form to add a new user -->
    <form id="form-create" class="row gy-2 gx-3 mb-4">
      <div class="col-sm-4">
        <label for="name" class="form-label">Name</label>
        <input type="text" id="name" name="name" class="form-control" placeholder="Name" required />
      </div>
      <div class="col-sm-4">
        <label for="email" class="form-label">Email</label>
        <input type="email" id="email" name="email" class="form-control" placeholder="email@example.com" required />
      </div>
      <div class="col-sm-4 d-flex align-items-end">
        <button type="submit" class="btn btn-success me-2">Add</button>
        <button type="button" id="btn-reload" class="btn btn-outline-secondary">Reload</button>
      </div>
    </form>

    <!-- Tabela de usuários -->
    <table class="table table-striped table-hover align-middle">
      <thead class="table-dark">
        <tr>
          <th>#</th>
          <th>Name</th>
          <th>Email</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="users-tbody"></tbody>
    </table>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

  <script>
    const API = 'Main.php';

    async function api(action, payload = {}, method = 'POST') {
      let url = API;
      const init = { method };
      if (method === 'GET') {
        const params = new URLSearchParams({ action, ...payload }).toString();
        url += `?${params}`;
      } else {
        init.headers = { 'Content-Type': 'application/json' };
        init.body = JSON.stringify({ action, ...payload });
      }
      const res = await fetch(url, init);
      const json = await res.json();
      if (!json.ok) throw new Error(json.error || 'Request failed');
      return json.data;
    }

    async function loadUsers() {
      const tbody = document.getElementById('users-tbody');
      tbody.innerHTML = '<tr><td colspan="4">Loading...</td></tr>';
      try {
        const users = await api('list', {}, 'GET');
        if (users.length === 0) {
          tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No users</td></tr>';
          return;
        }
        tbody.innerHTML = users.map(u => `
          <tr data-id="${u.id}">
            <td>${u.id}</td>
            <td>${u.name}</td>
            <td>${u.email}</td>
            <td>
              <button class="btn btn-sm btn-primary me-1" onclick="updateUser(this)">Update</button>
              <button class="btn btn-sm btn-danger" onclick="deleteUser(this)">Delete</button>
            </td>
          </tr>
        `).join('');
      } catch (e) {
        tbody.innerHTML = `<tr><td colspan="4" class="text-danger">${e.message}</td></tr>`;
      }
    }

    async function createUser(ev) {
      ev.preventDefault();
      const form = ev.target;
      const name = form.name.value.trim();
      const email = form.email.value.trim();
      if (!name || !email) return;
      try {
        await api('create', { name, email });
        form.reset();
        await loadUsers();
      } catch (e) {
        alert(e.message);
      }
    }

    async function updateUser(button) {
      const row = button.closest('tr');
      const id = row.getAttribute('data-id');
      const currentName = row.children[1].textContent;
      const currentEmail = row.children[2].textContent;

      const newName = prompt('New name:', currentName);
      const newEmail = prompt('New email:', currentEmail);

      const payload = { action: 'update', id };
      if (newName && newName.trim() && newName.trim() !== currentName) payload.name = newName.trim();
      if (newEmail && newEmail.trim() && newEmail.trim() !== currentEmail) payload.email = newEmail.trim();

      if (!payload.name && !payload.email) return; // nothing to update

      try {
        await api('update', payload);
        await loadUsers();
      } catch (e) {
        alert(e.message);
      }
    }

    async function deleteUser(button) {
      const row = button.closest('tr');
      const id = row.getAttribute('data-id');
      if (!confirm('Are you sure you want to delete this user?')) return;
      try {
        await api('delete', { id });
        await loadUsers();
      } catch (e) {
        alert(e.message);
      }
    }

    document.addEventListener('DOMContentLoaded', () => {
      document.getElementById('form-create').addEventListener('submit', createUser);
      document.getElementById('btn-reload').addEventListener('click', loadUsers);
      loadUsers();
    });
  </script>
</body>
</html>
