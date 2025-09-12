<div class="modal fade" id="recruitModal" tabindex="-1" aria-labelledby="recruitModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="recruitModalLabel">Recruit Soldiers</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="soldiersCount" class="form-label">Number of Soldiers</label>
                    <input type="number" class="form-control" id="soldiersCount" min="1" max="1000" value="10">
                    <div class="form-text">Cost: 10 grain per soldier</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="recruitSoldiers()">Recruit</button>
            </div>
        </div>
    </div>
</div>