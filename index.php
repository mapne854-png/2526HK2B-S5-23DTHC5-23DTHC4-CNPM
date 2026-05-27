<?php
require_once __DIR__ . '/includes/config.php';
$selected_symptoms = isset($_GET['symptoms']) && is_array($_GET['symptoms']) ? array_map('intval', $_GET['symptoms']) : [];
$suggested_diseases = [];
$suggested_medicines = [];
try {
    $symptoms_stmt = $conn->query("SELECT * FROM symptoms ORDER BY name ASC");
    $all_symptoms = $symptoms_stmt->fetchAll();
} catch (PDOException $e) {
    die("Lỗi cơ sở dữ liệu: " . $e->getMessage());
}
if (!empty($selected_symptoms)) {
    try {
        $placeholders = implode(',', array_fill(0, count($selected_symptoms), '?'));
        
        $disease_query = "
            SELECT d.*, COUNT(sd.symptom_id) as match_count 
            FROM diseases d
            JOIN symptom_disease sd ON d.id = sd.disease_id
            WHERE sd.symptom_id IN ($placeholders)
            GROUP BY d.id
            ORDER BY match_count DESC, d.name ASC
        ";
        
        $disease_stmt = $conn->prepare($disease_query);
        $disease_stmt->execute($selected_symptoms);
        $suggested_diseases = $disease_stmt->fetchAll();
        
        if (!empty($suggested_diseases)) {
            $disease_ids = array_column($suggested_diseases, 'id');
            $disease_placeholders = implode(',', array_fill(0, count($disease_ids), '?'));
            
            $medicine_query = "
                SELECT m.*, d.name as disease_name, d.id as disease_id
                FROM medicines m
                JOIN disease_medicine dm ON m.id = dm.medicine_id
                JOIN diseases d ON dm.disease_id = d.id
                WHERE dm.disease_id IN ($disease_placeholders)
                ORDER BY d.name ASC, m.name ASC
            ";
            
            $medicine_stmt = $conn->prepare($medicine_query);
            $medicine_stmt->execute($disease_ids);
            
            while ($row = $medicine_stmt->fetch()) {
                $suggested_medicines[$row['disease_id']][] = $row;
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Đã xảy ra lỗi khi tìm kiếm gợi ý thuốc: " . $e->getMessage();
    }
}
require_once __DIR__ . '/includes/header.php'
?>
<div class="hero">
    <h1>Hệ Thống Gợi Ý Thuốc <span>Theo Triệu Chứng</span></h1>
    <p>Giải pháp tra cứu triệu chứng thông minh, dự đoán nguy cơ bệnh lý và gợi ý giải pháp điều trị an toàn cho nhà thuốc.</p>
</div>
<div class="card" style="margin-bottom: 40px;">
    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px; border-bottom: 2px solid var(--border-color); padding-bottom: 12px;">
        <i class="fa-solid fa-hand-holding-medical" style="font-size: 2rem; color: var(--primary);"></i>
        <h2 style="font-size: 1.6rem; font-weight: 800;">Chẩn Đoán & Tra Cứu Triệu Chứng</h2>
    </div>
    
    <p style="color: var(--text-secondary); margin-bottom: 24px; font-weight: 500;">
        Hãy chọn các triệu chứng mà bạn hoặc khách hàng đang gặp phải dưới đây:
    </p>
    
    <form action="index.php" method="GET">
        <div class="symptom-grid">
            <?php foreach ($all_symptoms as $symptom): ?>
                <label class="symptom-checkbox-card">
                    <input type="checkbox" name="symptoms[]" value="<?php echo $symptom['id']; ?>" 
                        <?php echo in_array($symptom['id'], $selected_symptoms) ? 'checked' : ''; ?>>
                    <span><?php echo htmlspecialchars($symptom['name']); ?></span>
                </label>
            <?php endforeach; ?>
        </div>
        
        <div style="display: flex; gap: 12px; justify-content: center; margin-top: 30px;">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-microscope"></i> Phân Tích & Gợi Ý Thuốc
            </button>
            <?php if (!empty($selected_symptoms)): ?>
                <a href="index.php" class="btn btn-light">
                    <i class="fa-solid fa-arrows-rotate"></i> Làm Mới
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>
<?php if (!empty($selected_symptoms)): ?>
    <div class="results-section">
        
        <div>
            <div class="card" style="height: 100%;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
                    <i class="fa-solid fa-stethoscope" style="color: var(--primary); font-size: 1.5rem;"></i>
                    <h3 style="font-size: 1.3rem; font-weight: 800;">Bệnh lý liên quan</h3>
                </div>
                
                <?php if (!empty($suggested_diseases)): ?>
                    <p style="font-size: 0.95rem; color: var(--text-secondary); margin-bottom: 20px;">
                        Dựa trên <strong style="color: var(--primary);"><?php echo count($selected_symptoms); ?> triệu chứng</strong> bạn chọn, hệ thống nhận diện các bệnh lý sau:
                    </p>
                    
                    <?php foreach ($suggested_diseases as $disease): ?>
                        <div class="disease-card">
                            <h3><?php echo htmlspecialchars($disease['name']); ?></h3>
                            <p style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 10px;">
                                <?php echo htmlspecialchars($disease['description']); ?>
                            </p>
                            <span class="role-badge customer" style="font-size: 0.75rem; font-weight: 700; padding: 4px 10px;">
                                Độ khớp triệu chứng: <?php echo $disease['match_count']; ?>/<?php echo count($selected_symptoms); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px 20px;">
                        <i class="fa-regular fa-face-frown" style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 15px;"></i>
                        <p style="color: var(--text-secondary); font-weight: 600;">Không tìm thấy bệnh lý nào khớp hoàn toàn với triệu chứng bạn chọn.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div>
            <div class="card" style="height: 100%;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
                    <i class="fa-solid fa-pills" style="color: var(--secondary); font-size: 1.5rem;"></i>
                    <h3 style="font-size: 1.3rem; font-weight: 800;">Thuốc gợi ý khuyên dùng</h3>
                </div>
                
                <?php if (!empty($suggested_medicines)): ?>
                    <p style="font-size: 0.95rem; color: var(--text-secondary); margin-bottom: 20px;">
                        <i class="fa-solid fa-triangle-exclamation" style="color: var(--warning);"></i> 
                        <strong>Lưu ý:</strong> Gợi ý dưới đây mang tính chất tham khảo. Cần tham khảo ý kiến của dược sĩ/bác sĩ trước khi dùng.
                    </p>
                    
                    <?php foreach ($suggested_diseases as $disease): ?>
                        <?php if (isset($suggested_medicines[$disease['id']])): ?>
                            <div style="margin-bottom: 30px;">
                                <div style="background: rgba(16, 185, 129, 0.08); padding: 8px 16px; border-radius: var(--radius-sm); border-left: 4px solid var(--secondary); margin-bottom: 16px;">
                                    <h4 style="color: var(--secondary-hover); font-weight: 700; font-size: 1.05rem;">
                                        Thuốc trị bệnh: <?php echo htmlspecialchars($disease['name']); ?>
                                    </h4>
                                </div>
                                
                                <?php foreach ($suggested_medicines[$disease['id']] as $med): ?>
                                    <div class="medicine-card">
                                        <div class="medicine-header">
                                            <span class="medicine-name"><?php echo htmlspecialchars($med['name']); ?></span>
                                            <span class="medicine-price"><?php echo number_format($med['price'], 0, ',', '.'); ?> đ/viên</span>
                                        </div>
                                        <div class="medicine-body">
                                            <div class="med-info-row">
                                                <strong><i class="fa-solid fa-flask" style="color: var(--primary); width: 20px;"></i> Hoạt chất chính:</strong>
                                                <span><?php echo htmlspecialchars($med['active_ingredient']); ?></span>
                                            </div>
                                            <div class="med-info-row">
                                                <strong><i class="fa-solid fa-prescription-bottle" style="color: var(--primary); width: 20px;"></i> Liều dùng:</strong>
                                                <span><?php echo htmlspecialchars($med['dosage']); ?></span>
                                            </div>
                                            <div class="med-info-row">
                                                <strong><i class="fa-solid fa-list-check" style="color: var(--primary); width: 20px;"></i> Hướng dẫn:</strong>
                                                <span><?php echo htmlspecialchars($med['usage_instruction']); ?></span>
                                            </div>
                                            <?php if (!empty($med['side_effects'])): ?>
                                                <div class="med-info-row">
                                                    <strong><i class="fa-solid fa-triangle-exclamation" style="color: var(--danger); width: 20px;"></i> Tác dụng phụ:</strong>
                                                    <span style="color: #b91c1c;"><?php echo htmlspecialchars($med['side_effects']); ?></span>
                                                </div>
                                            <?php endif; ?>
                                            <div class="med-info-row" style="margin-top: 8px; font-size: 0.85rem; display: flex; justify-content: space-between;">
                                                <span>Tình trạng kho: 
                                                    <strong style="color: <?php echo $med['stock_quantity'] > 0 ? 'var(--success)' : 'var(--danger)'; ?>">
                                                        <?php echo $med['stock_quantity'] > 0 ? $med['stock_quantity'] . ' viên' : 'Hết hàng'; ?>
                                                    </strong>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px 20px;">
                        <i class="fa-solid fa-prescription-bottle" style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 15px;"></i>
                        <p style="color: var(--text-secondary); font-weight: 600;">Chưa cấu hình gợi ý thuốc cho các bệnh lý liên quan này.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
    </div>
<?php endif; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>