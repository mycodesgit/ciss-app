{{-- Edit modals of Configure Admission. form[data-edit="{section}"] is PUT to that tab's record URL by config.js --}}

<div class="modal fade" id="editProgramModal" tabindex="-1" aria-labelledby="editProgramModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProgramModalLabel">Edit Program</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form data-edit="programs">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label fw-semibold">Program Code: <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="code" oninput="this.value = this.value.toUpperCase()" required>
                    </div>
                    <div class="form-group mt-3">
                        <label class="form-label fw-semibold">Program Name: <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="program" oninput="this.value = this.value.toUpperCase()" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editStrandModal" tabindex="-1" aria-labelledby="editStrandModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editStrandModalLabel">Edit Strand</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form data-edit="strands">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label fw-semibold">Strand Code: <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="code" oninput="this.value = this.value.toUpperCase()" required>
                    </div>
                    <div class="form-group mt-3">
                        <label class="form-label fw-semibold">Strand Name: <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="strand" oninput="this.value = this.value.toUpperCase()" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editDateModal" tabindex="-1" aria-labelledby="editDateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editDateModalLabel">Edit Admission Date</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form data-edit="dates">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label fw-semibold">Admission Date: <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="date" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editDateTimeModal" tabindex="-1" aria-labelledby="editDateTimeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editDateTimeModalLabel">Edit Time Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form data-edit="times">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label fw-semibold">Admission Date: <span class="text-danger">*</span></label>
                        <select class="form-select js-date-options" name="date" required></select>
                    </div>
                    <div class="form-group mt-3">
                        <label class="form-label fw-semibold">Admission Time: <span class="text-danger">*</span></label>
                        <input type="time" class="form-control" name="time" required>
                    </div>
                    <div class="form-group mt-3">
                        <label class="form-label fw-semibold">Admission Slot: <span class="text-danger">*</span></label>
                        <input type="number" min="1" class="form-control" name="slots" required>
                    </div>
                    <small class="text-muted d-block mt-2">When applicants are already booked, only the slots can be changed.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editVenueModal" tabindex="-1" aria-labelledby="editVenueModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editVenueModalLabel">Edit Venue</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form data-edit="venues">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label fw-semibold">Admission Venue: <span class="text-danger">*</span></label>
                        <input type="text" class="form-control js-capitalize" name="venue" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editYearModal" tabindex="-1" aria-labelledby="editYearModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editYearModalLabel">Edit Admission Year</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form data-edit="years">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label fw-semibold">Admission Year: <span class="text-danger">*</span></label>
                        <input type="number" min="2000" max="2100" class="form-control" name="adyear" required>
                    </div>
                    <div class="form-group mt-3">
                        <label class="form-label fw-semibold">Admission Year Status: <span class="text-danger">*</span></label>
                        <select class="form-select" name="status">
                            <option value="On">On</option>
                            <option value="Off">Off</option>
                        </select>
                        <small class="text-muted">Switching a year On switches every other year Off.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
