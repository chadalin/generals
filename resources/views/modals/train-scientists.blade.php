<div class="modal fade" id="scientistsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Обучить ученых</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="scientistsForm">
                    @csrf
                    <div class="mb-3">
                        <label for="scientistsCount" class="form-label">Количество ученых</label>
                        <input type="number" class="form-control" id="scientistsCount" name="count" 
                               min="1" max="50" value="5" required>
                        <div class="form-text">Стоимость: 20 денег за ученого</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Общая стоимость: <span id="scientistsTotalCost">100</span> денег</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" onclick="trainScientists()">Обучить</button>
            </div>
        </div>
    </div>
</div>