<div class="form-group">
    <label class="form-label">{{ __('ui.admin.name_ar') }}</label>
    <input type="text" name="name_ar" class="form-control" value="{{ old('name_ar', $office->name_ar ?? '') }}" dir="rtl">
    @error('name_ar') <div class="form-error">{{ $message }}</div> @enderror
</div>
<div class="form-group">
    <label class="form-label">{{ __('ui.admin.municipality_ar') }}</label>
    <input type="text" name="municipality_ar" class="form-control" value="{{ old('municipality_ar', $office->municipality_ar ?? '') }}" dir="rtl">
    @error('municipality_ar') <div class="form-error">{{ $message }}</div> @enderror
</div>
<div class="form-group">
    <label class="form-label">{{ __('ui.admin.address_ar') }}</label>
    <input type="text" name="address_ar" class="form-control" value="{{ old('address_ar', $office->address_ar ?? '') }}" dir="rtl">
    @error('address_ar') <div class="form-error">{{ $message }}</div> @enderror
</div>
