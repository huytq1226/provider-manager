<?php include 'includes/header.php'; ?>

<!-- Dashboard Content -->
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-hover">
            <div class="card-body">
                <h1 class="card-title text-gradient">Welcome to Provider Management System</h1>
                <p class="card-text lead">This system helps you manage providers, services, contracts, and bills efficiently.</p>
                
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card mb-3 shadow-hover">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-building me-2"></i>
                                    Providers
                                </h5>
                                <p class="card-text">Manage your service providers, their details, and status.</p>
                                <a href="providers.php" class="btn btn-primary">
                                    <i class="fas fa-arrow-right me-2"></i>
                                    Go to Providers
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card mb-3 shadow-hover">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-cogs me-2"></i>
                                    Services
                                </h5>
                                <p class="card-text">View and manage available services and their details.</p>
                                <a href="services.php" class="btn btn-primary">
                                    <i class="fas fa-arrow-right me-2"></i>
                                    Go to Services
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card mb-3 shadow-hover">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-file-invoice-dollar me-2"></i>
                                    Bills
                                </h5>
                                <p class="card-text">Create and manage bills for services provided.</p>
                                <a href="bills.php" class="btn btn-primary">
                                    <i class="fas fa-arrow-right me-2"></i>
                                    Go to Bills
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Features -->
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card mb-3 shadow-hover">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-file-contract me-2"></i>
                                    Contracts
                                </h5>
                                <p class="card-text">Manage service contracts and agreements.</p>
                                <a href="contracts.php" class="btn btn-primary">
                                    <i class="fas fa-arrow-right me-2"></i>
                                    View Contracts
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card mb-3 shadow-hover">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-chart-bar me-2"></i>
                                    Statistics
                                </h5>
                                <p class="card-text">View detailed statistics and analytics.</p>
                                <a href="statistics.php" class="btn btn-primary">
                                    <i class="fas fa-arrow-right me-2"></i>
                                    View Statistics
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card mb-3 shadow-hover">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-trophy me-2"></i>
                                    Rankings
                                </h5>
                                <p class="card-text">Check provider rankings and performance.</p>
                                <a href="ranking.php" class="btn btn-primary">
                                    <i class="fas fa-arrow-right me-2"></i>
                                    View Rankings
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>