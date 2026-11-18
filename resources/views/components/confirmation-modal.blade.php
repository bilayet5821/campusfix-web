@props(['id' => 'confirmationModal', 'title' => 'Confirm changes', 'confirmLabel' => 'Confirm', 'formId' => null, 'disabled' => false])

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="{{ $id }}Label">{{ $title }}</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">{{ $slot }}</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" @if($formId) form="{{ $formId }}" @endif @disabled($disabled)>{{ $confirmLabel }}</button>
            </div>
        </div>
    </div>
</div>
