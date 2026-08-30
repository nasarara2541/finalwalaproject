document.addEventListener('DOMContentLoaded', () => {
    const tbody = document.getElementById('users-tbody');
    const btnNewUser = document.getElementById('btn-new-user');
    const toastMsg = document.getElementById('toast-msg');

    let usersOffset = 0;
    let usersHasMore = false;
    let usersLoading = false;
    let searchTimer = null;
    let allRoles = [];          // every role that exists, from get_roles
    let checkedRoles = new Set(); // currently checked roles - all checked by default

    function showToast(message, isError = false) {
        toastMsg.textContent = message;
        toastMsg.className = `fixed bottom-4 right-4 px-4 py-2 rounded shadow-lg transition-all duration-300 z-50 font-medium ${isError ? 'bg-red-600 text-white' : 'bg-slate-800 text-white'}`;
        toastMsg.style.opacity = '1';
        toastMsg.style.transform = 'translateY(0)';
        setTimeout(() => {
            toastMsg.style.opacity = '0';
            toastMsg.style.transform = 'translateY(10px)';
        }, 3000);
    }

    function buildQuery(offset) {
        const params = new URLSearchParams();
        const name = document.getElementById('search-name').value.trim();
        const id = document.getElementById('search-id').value.trim();
        if (name) params.set('name', name);
        if (id) params.set('id', id);
        if (checkedRoles.size === 0) {
            params.set('roles', '__none__'); // sentinel: no role checked, show nothing
        } else if (checkedRoles.size < allRoles.length) {
            params.set('roles', Array.from(checkedRoles).join(','));
        }
        params.set('offset', offset);
        return params.toString();
    }

    function loadUsers(replace) {
        if (usersLoading) return;
        usersLoading = true;
        fetch('api/users.php?' + buildQuery(usersOffset))
            .then(res => res.json())
            .then(data => {
                usersLoading = false;
                const results = data.results || [];
                usersHasMore = !!data.hasMore;
                if (replace) tbody.innerHTML = '';
                if (replace && results.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" class="p-8 text-center text-slate-400">No users found.</td></tr>';
                    document.getElementById('users-load-more').style.display = 'none';
                    return;
                }
                results.forEach(user => {
                    const statusClass = user.status === 'Active' ? 'text-green-600 bg-green-50 px-2 py-1 rounded' : 'text-red-600 bg-red-50 px-2 py-1 rounded';
                    const statusText = user.status;

                    const actionBtn = user.status === 'Active'
                        ? `<button class="text-red-600 hover:text-red-800" onclick="window.toggleUserStatus('${user.id}', 'N')"><i class="fa-solid fa-ban"></i> Disable</button>`
                        : `<button class="text-green-600 hover:text-green-800" onclick="window.toggleUserStatus('${user.id}', 'Y')"><i class="fa-solid fa-check"></i> Enable</button>`;

                    const tr = document.createElement('tr');
                    tr.className = 'border-b border-slate-200 hover:bg-slate-50 transition-colors';
                    tr.innerHTML = `
                        <td class="p-3 text-slate-800 font-medium">${user.name || '-'}</td>
                        <td class="p-3 text-slate-600">${user.role || '-'}</td>
                        <td class="p-3"><span class="${statusClass} text-xs font-bold">${statusText}</span></td>
                        <td class="p-3 text-right">
                            <button class="text-blue-600 hover:text-blue-800 mr-3" onclick="window.openUserModal(false, '${user.id}', '${user.name}', '${user.role}')"><i class="fa-solid fa-pen-to-square"></i> Edit</button>
                            ${actionBtn}
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
                document.getElementById('users-load-more').style.display = usersHasMore ? 'block' : 'none';
            })
            .catch(err => {
                usersLoading = false;
                console.error(err);
                if (replace) tbody.innerHTML = '<tr><td colspan="4" class="p-8 text-center text-red-500">Failed to load users.</td></tr>';
            });
    }

    window.loadMoreUsers = function() {
        if (!usersHasMore || usersLoading) return;
        usersOffset += 100;
        loadUsers(false);
    };

    window.onUsersScroll = function() {
        const box = document.getElementById('users-scrollbox');
        if (usersHasMore && !usersLoading && (box.scrollTop + box.clientHeight >= box.scrollHeight - 40)) {
            window.loadMoreUsers();
        }
    };

    window.debouncedUserSearch = function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            usersOffset = 0;
            loadUsers(true);
        }, 250);
    };

    // ---- Role filter dropdown ----
    function renderRoleFilterList() {
        const list = document.getElementById('role-filter-list');
        list.innerHTML = allRoles.map(role => `
            <label style="display:flex;align-items:center;gap:6px;padding:3px 0;cursor:pointer;">
                <input type="checkbox" class="role-checkbox" value="${role}" ${checkedRoles.has(role) ? 'checked' : ''} onchange="onRoleCheckboxChange()">
                <span>${role}</span>
            </label>
        `).join('');
    }

    window.onRoleCheckboxChange = function() {
        checkedRoles = new Set(
            Array.from(document.querySelectorAll('.role-checkbox:checked')).map(cb => cb.value)
        );
        usersOffset = 0;
        loadUsers(true);
    };

    window.setAllRoles = function(checkAll) {
        checkedRoles = checkAll ? new Set(allRoles) : new Set();
        renderRoleFilterList();
        usersOffset = 0;
        loadUsers(true);
    };

    window.toggleRoleFilter = function() {
        document.getElementById('role-filter-dropdown').classList.toggle('hidden');
    };

    document.addEventListener('click', function(e) {
        const dropdown = document.getElementById('role-filter-dropdown');
        const btn = document.getElementById('btn-role-filter');
        if (!dropdown.contains(e.target) && e.target !== btn && !btn.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });

    function loadRolesThenUsers() {
        fetch('api/users.php?action=get_roles')
            .then(res => res.json())
            .then(roles => {
                allRoles = Array.isArray(roles) ? roles : [];
                checkedRoles = new Set(allRoles); // all checked by default
                renderRoleFilterList();
                loadUsers(true);
            })
            .catch(err => {
                console.error(err);
                loadUsers(true); // still try to load users even if roles fail
            });
    }

    // ---- Status toggle ----
    window.toggleUserStatus = function(userId, newStatus) {
        if (!confirm(`Are you sure you want to ${newStatus === 'Y' ? 'enable' : 'disable'} this user?`)) return;

        fetch('api/users.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'toggle_status', id: userId, status: newStatus })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast(`User ${newStatus === 'Y' ? 'enabled' : 'disabled'} successfully.`);
                usersOffset = 0;
                loadUsers(true);
            } else {
                showToast(data.error || 'Error updating status', true);
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Network error', true);
        });
    };

    btnNewUser.addEventListener('click', () => {
        window.openUserModal(true);
    });

    const modal = document.getElementById('user-modal');
    const form = document.getElementById('user-form');

    window.openUserModal = function(isNew, id = '', name = '', role = '') {
        document.getElementById('modal-title').textContent = isNew ? 'Add New User' : 'Edit User';
        document.getElementById('is-new-user').value = isNew ? 'true' : 'false';

        const idInput = document.getElementById('user-id');
        idInput.value = id;
        idInput.readOnly = !isNew;
        idInput.className = !isNew ? 'w-full border border-slate-300 rounded px-3 py-2 text-sm bg-slate-100 text-slate-500 cursor-not-allowed' : 'w-full border border-slate-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-teal-600';

        document.getElementById('user-name').value = name;
        document.getElementById('user-role').value = role;
        document.getElementById('user-password').value = '';

        document.getElementById('password-help').textContent = isNew ? 'Required for new users.' : 'Leave blank to keep current password.';
        document.getElementById('user-password').required = isNew;

        modal.classList.remove('hidden');
    };

    window.closeUserModal = function() {
        modal.classList.add('hidden');
        form.reset();
    };

    form.addEventListener('submit', (e) => {
        e.preventDefault();

        const payload = {
            is_new: document.getElementById('is-new-user').value === 'true',
            action: 'save_user',
            user_id: document.getElementById('user-id').value.trim(),
            user_name: document.getElementById('user-name').value.trim(),
            role: document.getElementById('user-role').value.trim(),
            password: document.getElementById('user-password').value
        };

        fetch('api/users.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast(payload.is_new ? 'User created successfully' : 'User updated successfully');
                closeUserModal();
                usersOffset = 0;
                loadUsers(true);
            } else {
                showToast(data.error || 'Failed to save user', true);
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Network error while saving user', true);
        });
    });

    loadRolesThenUsers();
});