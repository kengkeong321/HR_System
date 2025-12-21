<div class="mb-3">
  {{-- Woo Keng Keong --}}
  <label class="form-label">Name</label>
  <input name="name" class="form-control" value="{{ old('name', $position->name ?? '') }}" maxlength="20" required />
</div>

<div class="mb-3">
  <label class="form-label">Status</label>
  <select name="status" class="form-select">
    <option value="Active" {{ old('status', $position->status ?? 'Active') === 'Active' ? 'selected' : '' }}>Active</option>
    <option value="Inactive" {{ old('status', $position->status ?? '') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
  </select>
</div>

@if($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">
      @foreach($errors->all() as $err)
        <li>{{ $err }}</li>
      @endforeach
    </ul>
  </div>
@endif
