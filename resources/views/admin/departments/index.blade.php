@extends('layouts.admin')

@section('title', 'Departments')

@section('content')
  <div class="d-flex justify-content-between mb-3">
    <h4>Departments</h4>
    <a href="{{ route('admin.departments.create') }}" class="btn btn-primary">Create Department</a>
  </div>

  <div id="departments-list" class="ajax-paginate" data-url="{{ route('admin.departments.page') }}">
    @include('admin.departments._list', ['departments' => $departments])
  </div>

  <!-- Modal to show assigned courses -->
  <div class="modal fade" id="deptAssignmentsModal" tabindex="-1" aria-labelledby="deptAssignmentsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="deptAssignmentsModalLabel">Courses</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div id="dept-assignments-body">
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
      const modalEl = document.getElementById('deptAssignmentsModal');
      const modal = new bootstrap.Modal(modalEl);
      const body = document.getElementById('dept-assignments-body');

      // Use event delegation so dynamically loaded pages still work
      const listContainer = document.getElementById('departments-list');
      listContainer.addEventListener('click', function(e) {
        const row = e.target.closest('.department-row');
        if (!row) return;
        // ignore clicks on links/buttons/inputs inside the row
        if (e.target.closest('a,button,input')) return;

        const id = row.dataset.departId;
        const name = row.dataset.departName;
        body.innerHTML = '<div class="text-center py-4">Loading…</div>';
        modalEl.querySelector('.modal-title').textContent = `Courses — ${name}`;

        fetch(`/admin/departments/${id}/assignments`)
          .then(r => r.json())
          .then(data => {
            const courses = data.courses;
            if (!courses || courses.length === 0) {
              body.innerHTML = '<div class="text-muted">No courses assigned to this department.</div>';
              modal.show();
              return;
            }

            let html = '<ul class="list-group">';
            courses.forEach(c => {
              html += `<li class="list-group-item d-flex justify-content-between align-items-center">${c.course_name}<span class="badge bg-secondary">${c.course_id}</span></li>`;
            });
            html += '</ul>';
            body.innerHTML = html;
            modal.show();
          })
          .catch(err => {
            body.innerHTML = `<div class="text-danger">Error loading assignments.</div>`;
            modal.show();
            console.error(err);
          });
      });
    });
  </script>
@endsection
