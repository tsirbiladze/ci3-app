<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<h1 class="h3 mb-4">Users</h1>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="muted">Manage application users</div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">Add user</button>
</div>
<div class="card p-3">
    <div class="table-responsive">
        <table id="usersTable" class="table table-striped table-hover align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Created</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Add -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add user</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addUserForm">
                    <input type="hidden" name="<?= html_escape($this->security->get_csrf_token_name()); ?>" value="<?= html_escape($this->security->get_csrf_hash()); ?>">
                    <div class="mb-3"><label class="form-label">Full name</label><input class="form-control" name="name" required></div>
                    <div class="mb-3"><label class="form-label">Email</label><input class="form-control" type="email" name="email" required></div>
                    <div class="mb-3"><label class="form-label">Phone</label><input class="form-control" name="phone" required></div>
                    <p class="muted">A temporary password will be emailed.</p>
                </form>
            </div>
            <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" id="saveUserBtn">Save</button></div>
        </div>
    </div>
</div>

<!-- Edit -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit user</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    <input type="hidden" id="edit_user_id" name="user_id">
                    <input type="hidden" name="<?= html_escape($this->security->get_csrf_token_name()); ?>" value="<?= html_escape($this->security->get_csrf_hash()); ?>">
                    <div class="mb-3"><label class="form-label">Full name</label><input class="form-control" id="edit_name" name="name" required></div>
                    <div class="mb-3"><label class="form-label">Email</label><input class="form-control" type="email" id="edit_email" name="email" required></div>
                    <div class="mb-3"><label class="form-label">Phone</label><input class="form-control" id="edit_phone" name="phone" required></div>
                </form>
            </div>
            <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" id="updateUserBtn">Update</button></div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" />
<script>
    let dt;
    var CURRENT_USER_ID = <?= (int) ($auth['id'] ?? 0); ?>;

    function fmtDate(s) {
        if (!s) return '';
        const d = new Date((s + '').replace(' ', 'T'));
        return isNaN(d) ? s : d.toLocaleDateString();
    }

    function initUsersPage() {
        dt = $('#usersTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '<?= site_url('users/index'); ?>',
                dataSrc: 'data'
            },
            columns: [{
                    data: 'id'
                },
                {
                    data: 'name'
                },
                {
                    data: 'email'
                },
                {
                    data: 'phone'
                },
                {
                    data: 'created_at',
                    render: fmtDate
                },
                {
                    data: null,
                    orderable: false,
                    render: r => {
                        const canDelete = Number(r.id) !== Number(CURRENT_USER_ID);
                        const delBtn = canDelete ?
                            `<button class="btn btn-outline-danger delete" data-id="${r.id}">Delete</button>` :
                            `<button class="btn btn-outline-danger" disabled title="Cannot delete your own account">Delete</button>`;
                        return `<div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-secondary edit" data-id="${r.id}">Edit</button>
                            ${delBtn}
                        </div>`;
                    }
                }
            ]
        });

        $('#saveUserBtn').on('click', function() {
            const $b = $(this).prop('disabled', true).text('Saving…');
            $.post('<?= site_url('users/create'); ?>', $('#addUserForm').serialize())
                .done(r => {
                    r && r.success ? (toast('User created', 'success'), $('#addUserModal').modal('hide'), dt.ajax.reload()) : toast((r && r.message) || 'Failed', 'danger');
                })
                .fail(xhr => {
                    let m = 'Network error';
                    try {
                        var j = JSON.parse(xhr.responseText || '{}');
                        if (j.message) m = j.message;
                    } catch (e) {}
                    toast(m, 'danger');
                })
                .always(() => $b.prop('disabled', false).text('Save'));
        });

        // Ensure Add modal form is cleared after close and before open
        $('#addUserModal').on('hidden.bs.modal show.bs.modal', function() {
            const f = document.getElementById('addUserForm');
            if (f) {
                f.reset();
            }
        });

        $(document).on('click', '.edit', function() {
            $.get('<?= site_url('users/get'); ?>/' + $(this).data('id')).done(r => {
                if (!r.success) return toast(r.message || 'Not found', 'danger');
                const u = r.data;
                $('#edit_user_id').val(u.id);
                $('#edit_name').val(u.name);
                $('#edit_email').val(u.email);
                $('#edit_phone').val(u.phone);
                new bootstrap.Modal(document.getElementById('editUserModal')).show();
            });
        });

        $('#updateUserBtn').on('click', function() {
            const id = $('#edit_user_id').val();
            const $b = $(this).prop('disabled', true).text('Updating…');
            $.post('<?= site_url('users/edit'); ?>/' + id, $('#editUserForm').serialize())
                .done(r => {
                    r && r.success ? (toast('User updated', 'success'), $('#editUserModal').modal('hide'), dt.ajax.reload()) : toast((r && r.message) || 'Failed', 'danger');
                })
                .fail(xhr => {
                    let m = 'Network error';
                    try {
                        var j = JSON.parse(xhr.responseText || '{}');
                        if (j.message) m = j.message;
                    } catch (e) {}
                    toast(m, 'danger');
                })
                .always(() => $b.prop('disabled', false).text('Update'));
        });

        // Clear Edit modal form on close to avoid stale data on next open
        $('#editUserModal').on('hidden.bs.modal', function() {
            const f = document.getElementById('editUserForm');
            if (f) {
                f.reset();
            }
        });

        $(document).on('click', '.delete', function() {
            const id = $(this).data('id');
            confirmModal({
                    title: 'Delete user',
                    message: 'Are you sure you want to delete this user?',
                    confirmText: 'Delete',
                    variant: 'danger'
                })
                .then(function(ok) {
                    if (!ok) return;
                    $.post('<?= site_url('users/delete'); ?>/' + id, {})
                        .done(r => {
                            r && r.success ? (toast('User deleted', 'success'), dt.ajax.reload()) : toast((r && r.message) || 'Failed', 'danger');
                        })
                        .fail(xhr => {
                            let m = 'Network error';
                            try {
                                var j = JSON.parse(xhr.responseText || '{}');
                                if (j.message) m = j.message;
                            } catch (e) {}
                            toast(m, 'danger');
                        });
                });
        });
    }

    (function waitForjQuery() {
        if (!window.jQuery) {
            return setTimeout(waitForjQuery, 30);
        }
        // Load DataTables after jQuery is ready
        $.getScript('https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js')
            .done(function() {
                $.getScript('https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js')
                    .done(function() {
                        initUsersPage();
                    })
                    .fail(function() {
                        toast('Failed to load DataTables (bootstrap5).', 'danger');
                    });
            })
            .fail(function() {
                toast('Failed to load DataTables.', 'danger');
            });
    })();
</script>