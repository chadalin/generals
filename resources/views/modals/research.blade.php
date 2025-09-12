<div class="modal fade" id="researchModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Исследования</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card mb-3">
                            <div class="card-header bg-danger text-white">Военные исследования</div>
                            <div class="card-body">
                                <p>Уровень: {{ $player->research->military_level ?? 0 }}</p>
                                <p>Прогресс: {{ $player->research->military_progress ?? 0 }}%</p>
                                <button class="btn btn-sm btn-outline-danger">Исследовать</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card mb-3">
                            <div class="card-header bg-warning text-dark">Экономические исследования</div>
                            <div class="card-body">
                                <p>Уровень: {{ $player->research->economy_level ?? 0 }}</p>
                                <p>Прогресс: {{ $player->research->economy_progress ?? 0 }}%</p>
                                <button class="btn btn-sm btn-outline-warning">Исследовать</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card mb-3">
                            <div class="card-header bg-info text-white">Научные исследования</div>
                            <div class="card-body">
                                <p>Уровень: {{ $player->research->science_level ?? 0 }}</p>
                                <p>Прогресс: {{ $player->research->science_progress ?? 0 }}%</p>
                                <button class="btn btn-sm btn-outline-info">Исследовать</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>