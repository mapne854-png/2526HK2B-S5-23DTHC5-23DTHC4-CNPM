<?php
require_once 'config.php';
checkAccess(['admin', 'pharmacist']);
$total_medicines = 0;
$total_symptoms = 0;
$total_diseases = 0;
$out_of_stock = 0;
try {
    $stmt1 = $conn->query("SELECT COUNT(*) FROM medicines");
    $total_medicines = $stmt1->fetchColumn();
    $stmt2 = $conn->query("SELECT COUNT(*) FROM symptoms");
    $total_symptoms = $stmt2->fetchColumn();
    $stmt3 = $conn->query("SELECT COUNT(*) FROM diseases");
    $total_diseases = $stmt3->fetchColumn();
    $stmt4 = $conn->query("SELECT COUNT(*) FROM medicines WHERE stock_quantity = 0");
    $out_of_stock = $stmt4->fetchColumn();
    $recent_meds_stmt = $conn->query("SELECT * FROM medicines ORDER BY id DESC LIMIT 5");
    $recent_meds = $recent_meds_stmt->fetchAll();
    $recent_diseases_stmt = $conn->query("SELECT * FROM diseases ORDER BY id DESC LIMIT 5");
    $recent_diseases = $recent_diseases_stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Lỗi tải dữ liệu thống kê: " . $e->getMessage();
}
include 'header.php';
?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h1 style="font-size: 2.2rem; font-weight: 800; color: var(--dark);">
            <i class="fa-solid fa-chart-pie" style="color: var(--primary);"></i> Bảng Điều Khiển
        </h1>
        <p style="color: var(--text-secondary); margin-top: 4px;">
            Chào mừng quay trở lại, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>! Dưới đây là tình trạng nhà thuốc hôm nay.
        </p>
    </div>
    <div style="display: flex; gap: 12px;">
        <a href="manage_medicines.php" class="btn btn-primary btn-sm"><i class="fa-solid fa-pills"></i> Quản lý thuốc</a>
        <a href="manage_symptoms.php" class="btn btn-secondary btn-sm"><i class="fa-solid fa-notes-medical"></i> Quản lý Triệu chứng</a>
    </div>
</div>
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="fa-solid fa-capsules"></i>
        </div>
        <div class="stat-info">
            <h4>Tổng Số Thuốc</h4>
            <p><?php echo $total_medicines; ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon secondary">
            <i class="fa-solid fa-head-side-cough"></i>
        </div>
        <div class="stat-info">
            <h4>Tổng Triệu Chứng</h4>
            <p><?php echo $total_symptoms; ?></p>
        </div>
    </div>
    
    <div class="stat-card font-semibold">
        <div class="stat-icon" style="background: rgba(168, 85, 247, 0.15); color: #a855f7;">
            <i class="fa-solid fa-virus"></i>
        </div>
        <div class="stat-info">
            <h4>Tổng Bệnh Lý</h4>
            <p><?php echo $total_diseases; ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon warning">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <div class="stat-info">
            <h4>Thuốc Hết Hàng</h4>
            <p style="color: <?php echo $out_of_stock > 0 ? 'var(--danger)' : 'var(--dark)'; ?>">
                <?php echo $out_of_stock; ?>
            </p>
        </div>
    </div>
</div>
<div style="display: grid; grid-template-columns: 1fr; gap: 32px; margin-top: 32px;" class="results-section">
    
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 12px; margin-bottom: 20px;">
            <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--dark);">
                <i class="fa-solid fa-clock-rotate-left" style="color: var(--primary);"></i> Thuốc mới thêm
            </h3>
            <a href="manage_medicines.php" style="font-size: 0.85rem; font-weight: 700; color: var(--primary); text-decoration: none;">Xem tất cả &rarr;</a>
        </div>
        
        <div class="table-responsive" style="margin-top: 0; border: none;">
            <table>
                <thead>
                    <tr>
                        <th>Tên Thuốc</th>
                        <th>Hoạt Chất</th>
                        <th>Giá bán</th>
                        <th>Kho</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recent_meds)): ?>
                        <?php foreach ($recent_meds as $med): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($med['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($med['active_ingredient']); ?></td>
                                <td><?php echo number_format($med['price'], 0, ',', '.'); ?> đ</td>
                                <td>
                                    <span style="color: <?php echo $med['stock_quantity'] > 0 ? 'var(--success)' : 'var(--danger)'; ?>; font-weight: 700;">
                                        <?php echo $med['stock_quantity']; ?> viên
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-secondary);">Chưa có dữ liệu thuốc.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 12px; margin-bottom: 20px;">
            <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--dark);">
                <i class="fa-solid fa-notes-medical" style="color: var(--secondary);"></i> Bệnh lý mới cập nhật
            </h3>
            <a href="manage_symptoms.php" style="font-size: 0.85rem; font-weight: 700; color: var(--secondary); text-decoration: none;">Cấu hình gợi ý &rarr;</a>
        </div>
        
        <div class="table-responsive" style="margin-top: 0; border: none;">
            <table>
                <thead>
                    <tr>
                        <th>Tên Bệnh Lý</th>
                        <th>Mô tả</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recent_diseases)): ?>
                        <?php foreach ($recent_diseases as $disease): ?>
                            <tr>
                                <td style="width: 30%;"><strong><?php echo htmlspecialchars($disease['name']); ?></strong></td>
                                <td style="color: var(--text-secondary); font-size: 0.9rem;">
                                    <?php echo htmlspecialchars($disease['description']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2" style="text-align: center; color: var(--text-secondary);">Chưa có dữ liệu bệnh lý.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
</div>
<?php include 'footer.php'; ?>