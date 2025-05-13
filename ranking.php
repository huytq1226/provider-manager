<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get providers ranked by reputation and number of contracts
$stmt = $conn->query("
    SELECT 
        p.id,
        p.name,
        p.reputation,
        p.email,
        COUNT(DISTINCT c.id) as contract_count,
        COUNT(DISTINCT b.id) as bill_count
    FROM Providers p
    LEFT JOIN Contracts c ON p.id = c.providerId
    LEFT JOIN Bills b ON c.id = b.refContractId
    GROUP BY p.id
    ORDER BY p.reputation DESC, contract_count DESC
");
$providers = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<!-- Main Content -->
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Provider Ranking</h2>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Provider Name</th>
                                    <th>Reputation</th>
                                    <th>Email</th>
                                    <th>Contracts</th>
                                    <th>Bills</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $rank = 1;
                                foreach ($providers as $provider): 
                                ?>
                                <tr>
                                    <td><?php echo $rank++; ?></td>
                                    <td><?php echo htmlspecialchars($provider['name']); ?></td>
                                    <td>
                                        <?php
                                        $stars = '';
                                        for ($i = 1; $i <= 5; $i++) {
                                            $stars .= $i <= $provider['reputation'] ? '★' : '☆';
                                        }
                                        echo $stars . ' (' . $provider['reputation'] . '/5)';
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($provider['email']); ?></td>
                                    <td><?php echo $provider['contract_count']; ?></td>
                                    <td><?php echo $provider['bill_count']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 