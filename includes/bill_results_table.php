<?php if (!empty($bills)): ?>
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Contract</th>
                <th>Status</th>
                <th>Quantity</th>
                <th>Created Date</th>
                <th>VAT</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($bills as $bill): ?>
            <tr>
                <td><?php echo htmlspecialchars($bill["id"]); ?></td>
                <td><?php echo htmlspecialchars($bill["name"]); ?></td>
                <td><?php echo htmlspecialchars($bill["des"]); ?></td>
                <td><?php echo htmlspecialchars($bill["contractName"] ?? "N/A"); ?></td>
                <td>
                    <span class="badge bg-<?php 
                        echo $bill["status"] === "Paid" ? "success" : 
                            ($bill["status"] === "Pending" ? "warning" : "danger"); 
                    ?>">
                        <?php echo htmlspecialchars($bill["status"]); ?>
                    </span>
                </td>
                <td><?php echo htmlspecialchars($bill["quantity"]); ?></td>
                <td><?php echo htmlspecialchars($bill["createdDate"]); ?></td>
                <td><?php echo htmlspecialchars($bill["vat"]); ?>%</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>