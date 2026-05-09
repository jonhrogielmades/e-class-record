<div id="global-confirm-modal" class="modal-overlay" style="display: none;">
    <div class="glass-modal">
        <div class="modal-header">
            <h3>Confirm Action</h3>
            <button type="button" class="modal-close" onclick="closeConfirmModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p id="confirm-modal-message">Are you sure you want to proceed?</p>
        </div>
        <div class="modal-footer button-row">
            <button type="button" class="btn btn-outline btn-fit" onclick="closeConfirmModal()">Cancel</button>
            <button type="button" id="confirm-modal-submit" class="btn btn-danger-outline btn-fit">Confirm</button>
        </div>
    </div>
</div>
