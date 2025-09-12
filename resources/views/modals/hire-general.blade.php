<div class="modal fade" id="hireGeneralModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Нанять генерала</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="hireGeneralForm">
                    @csrf
                    <div class="mb-3">
                        <label for="generalName" class="form-label">Имя генерала</label>
                        <input type="text" class="form-control" id="generalName" name="name" 
                               value="Генерал {{ rand(1, 100) }}" required>
                    </div>
                    <div class="mb-3">
                        <p class="text-muted">Стоимость: 1000 денег</p>
                        <p class="text-muted">Генерал будет нанят со случайными характеристиками</p>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" onclick="hireGeneral()">Нанять</button>
            </div>
        </div>
    </div>
</div>