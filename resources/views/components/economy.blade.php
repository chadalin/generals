<!-- Economy Tab -->
<div class="tab-pane fade" id="economy" role="tabpanel">
    <h4>Economic Overview</h4>
    <div class="row">
        <div class="col-md-6">
            <div class="card bg-dark mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Resource Production</h6>
                </div>
                <div class="card-body">
                    <p>💰 Money Production: {{ number_format($player->territory * 0.1) }} per turn</p>
                    <p>🌾 Grain Production: {{ number_format($player->peasants * 2) }} per turn</p>
                    <p>👨‍🌾 Peasants: {{ number_format($player->peasants) }}</p>
                    <p>📊 Efficiency: {{ number_format(($player->peasants / ($player->peasants + $player->scientists + $player->soldiers)) * 100, 1) }}%</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card bg-dark mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Buildings & Infrastructure</h6>
                </div>
                <div class="card-body">
                    <p>🏠 Farms: {{ number_format($player->peasants / 100) }}</p>
                    <p>🏭 Factories: 0</p>
                    <p>🏛️ Universities: {{ number_format($player->scientists / 50) }}</p>
                    <p>🛡️ Barracks: {{ number_format($player->soldiers / 100) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card bg-dark">
        <div class="card-header">
            <h6 class="mb-0">Economic Actions</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <button class="btn btn-outline-warning w-100 mb-2" onclick="buildFarm()">
                        🏠 Build Farm ($500)
                    </button>
                    <small class="text-muted">+10 grain production</small>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-outline-info w-100 mb-2" onclick="buildFactory()">
                        🏭 Build Factory ($1000)
                    </button>
                    <small class="text-muted">+20 money production</small>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-outline-success w-100 mb-2" onclick="buildMarket()">
                        🏪 Build Market ($800)
                    </button>
                    <small class="text-muted">+15% trade efficiency</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Research Tab -->
<div class="tab-pane fade" id="research" role="tabpanel">
    <h4>Research & Development</h4>
    <div class="row">
        <div class="col-md-4">
            <div class="card bg-dark mb-3">
                <div class="card-header bg-danger">
                    <h6 class="mb-0">Military Research</h6>
                </div>
                <div class="card-body">
                    <p>Level: {{ $player->research->military_level ?? 0 }}</p>
                    <p>Progress: {{ $player->research->military_progress ?? 0 }}%</p>
                    <div class="progress mb-2">
                        <div class="progress-bar bg-danger" style="width: {{ $player->research->military_progress ?? 0 }}%"></div>
                    </div>
                    <button class="btn btn-sm btn-outline-danger w-100" onclick="researchMilitary()">
                        🔫 Research Military
                    </button>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card bg-dark mb-3">
                <div class="card-header bg-warning">
                    <h6 class="mb-0">Economic Research</h6>
                </div>
                <div class="card-body">
                    <p>Level: {{ $player->research->economy_level ?? 0 }}</p>
                    <p>Progress: {{ $player->research->economy_progress ?? 0 }}%</p>
                    <div class="progress mb-2">
                        <div class="progress-bar bg-warning" style="width: {{ $player->research->economy_progress ?? 0 }}%"></div>
                    </div>
                    <button class="btn btn-sm btn-outline-warning w-100" onclick="researchEconomy()">
                        💰 Research Economy
                    </button>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card bg-dark mb-3">
                <div class="card-header bg-info">
                    <h6 class="mb-0">Science Research</h6>
                </div>
                <div class="card-body">
                    <p>Level: {{ $player->research->science_level ?? 0 }}</p>
                    <p>Progress: {{ $player->research->science_progress ?? 0 }}%</p>
                    <div class="progress mb-2">
                        <div class="progress-bar bg-info" style="width: {{ $player->research->science_progress ?? 0 }}%"></div>
                    </div>
                    <button class="btn btn-sm btn-outline-info w-100" onclick="researchScience()">
                        🔬 Research Science
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card bg-dark">
        <div class="card-header">
            <h6 class="mb-0">Available Technologies</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="technology-item mb-3">
                        <h6>🛡️ Improved Armor</h6>
                        <p class="text-muted small">+10% defense for all units</p>
                        <span class="badge bg-secondary">Military Lvl 2</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="technology-item mb-3">
                        <h6>🌾 Advanced Farming</h6>
                        <p class="text-muted small">+25% grain production</p>
                        <span class="badge bg-secondary">Economy Lvl 1</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="technology-item mb-3">
                        <h6>🔭 Scientific Method</h6>
                        <p class="text-muted small">+20% research speed</p>
                        <span class="badge bg-secondary">Science Lvl 3</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>