@extends('layouts.admin')

@section('title', 'Faculties')

@section('content')
  <div class="d-flex justify-content-between mb-3">
    <h4>Faculties</h4>
    <a href="{{ route('admin.faculties.create') }}" class="btn btn-primary">Create Faculty</a>
  </div>

  <div id="faculties-list" class="ajax-paginate" data-url="{{ route('admin.faculties.page') }}">
    @include('admin.faculties._list', ['faculties' => $faculties])
  </div>

  <!-- Modal to show active departments for a faculty -->
  <div class="modal fade" id="facultyDepartmentsModal" tabindex="-1" aria-labelledby="facultyDepartmentsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="facultyDepartmentsModalLabel">Departments</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div id="faculty-departments-body">
            <div class="text-center py-4">Loading…</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const modalEl = document.getElementById('facultyDepartmentsModal');
      const modal = new bootstrap.Modal(modalEl);
      const body = document.getElementById('faculty-departments-body');

      // Event delegation for dynamically loaded rows
      const listContainer = document.getElementById('faculties-list');
      listContainer.addEventListener('click', function(e) {
        const row = e.target.closest('.faculty-row');
        if (!row) return;
        if (e.target.closest('a,button,input')) return;

        const id = row.dataset.facultyId;
        const name = row.dataset.facultyName;
        body.innerHTML = '<div class="text-center py-4">Loading…</div>';
        modalEl.querySelector('.modal-title').textContent = `Departments — ${name}`;

        fetch(`/admin/faculties/${id}/departments`)
          .then(r => r.json())
          .then(data => {
            const departments = data.departments;
            if (!departments || departments.length === 0) {
              body.innerHTML = '<div class="text-muted">No active departments for this faculty.</div>';
              modal.show();
              return;
            }

            let html = '<ul class="list-group">';
            departments.forEach(d => {
              html += `<li class="list-group-item d-flex justify-content-between align-items-center">${d.depart_name}<span class="badge bg-secondary">${d.depart_id}</span></li>`;
            });
            html += '</ul>';
            body.innerHTML = html;
            modal.show();
          })
          .catch(err => {
            body.innerHTML = `<div class="text-danger">Error loading departments.</div>`;
            modal.show();
            console.error(err);
          });
      });
    });
  </script>
@endsection
