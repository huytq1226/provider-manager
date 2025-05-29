-- Create notifications table
CREATE TABLE IF NOT EXISTS Notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    type VARCHAR(50) NOT NULL, -- 'bill_due', 'contract_expiring', etc.
    severity VARCHAR(20) NOT NULL, -- 'urgent', 'normal', 'info'
    title VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    relatedId INT, -- ID of the related entity (bill ID, contract ID, etc.)
    relatedTable VARCHAR(50), -- 'Bills', 'Contracts', etc.
    isRead BOOLEAN DEFAULT FALSE,
    createdDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    readDate DATETIME NULL,
    userId INT NULL, -- If notification is for a specific user
    expiryDate DATETIME NULL -- When the notification should expire/be automatically dismissed
);

-- Create index for better performance
CREATE INDEX idx_notifications_isread ON Notifications(isRead);
CREATE INDEX idx_notifications_type ON Notifications(type);
CREATE INDEX idx_notifications_user ON Notifications(userId);

-- Create stored procedure to generate notifications for bills due soon
DELIMITER //
CREATE PROCEDURE GenerateBillDueNotifications()
BEGIN
    -- Delete old bill notifications that are no longer relevant
    DELETE FROM Notifications 
    WHERE type = 'bill_due' 
    AND relatedTable = 'Bills'
    AND (
        -- Bill has been paid
        (SELECT status FROM Bills WHERE id = relatedId) = 'Paid'
        OR
        -- Bill due date has passed by more than 7 days
        (SELECT DATE_ADD(createdDate, INTERVAL 30 DAY) FROM Bills WHERE id = relatedId) < NOW() - INTERVAL 7 DAY
    );
    
    -- Insert notifications for bills due within the next 7 days (that don't already have notifications)
    INSERT INTO Notifications (type, severity, title, message, relatedId, relatedTable, expiryDate)
    SELECT 
        'bill_due', 
        CASE 
            WHEN DATE_ADD(b.createdDate, INTERVAL 30 DAY) <= NOW() + INTERVAL 2 DAY THEN 'urgent'
            WHEN DATE_ADD(b.createdDate, INTERVAL 30 DAY) <= NOW() + INTERVAL 5 DAY THEN 'warning'
            ELSE 'info'
        END,
        CONCAT('Hóa đơn sắp đến hạn: ', b.name),
        CONCAT('Hóa đơn "', b.name, '" sẽ đến hạn vào ngày ', 
               DATE_FORMAT(DATE_ADD(b.createdDate, INTERVAL 30 DAY), '%d/%m/%Y'), '.'),
        b.id,
        'Bills',
        DATE_ADD(b.createdDate, INTERVAL 37 DAY) -- Expire 7 days after due date
    FROM Bills b
    WHERE 
        b.status = 'Pending'
        AND DATE_ADD(b.createdDate, INTERVAL 30 DAY) BETWEEN NOW() AND NOW() + INTERVAL 7 DAY
        AND NOT EXISTS (
            SELECT 1 FROM Notifications 
            WHERE type = 'bill_due' AND relatedId = b.id AND relatedTable = 'Bills'
        );
END //
DELIMITER ;

-- Create stored procedure to generate notifications for contracts expiring soon
DELIMITER //
CREATE PROCEDURE GenerateContractExpiringNotifications()
BEGIN
    -- Delete old contract expiration notifications that are no longer relevant
    DELETE FROM Notifications 
    WHERE type = 'contract_expiring' 
    AND relatedTable = 'Contracts'
    AND (
        -- Contract is no longer active
        (SELECT status FROM Contracts WHERE id = relatedId) <> 'Active'
        OR
        -- Contract expiry date has passed by more than 7 days
        (SELECT expiredDate FROM Contracts WHERE id = relatedId) < NOW() - INTERVAL 7 DAY
    );
    
    -- Insert notifications for contracts expiring within the next 30 days (that don't already have notifications)
    INSERT INTO Notifications (type, severity, title, message, relatedId, relatedTable, expiryDate)
    SELECT 
        'contract_expiring', 
        CASE 
            WHEN c.expiredDate <= NOW() + INTERVAL 7 DAY THEN 'urgent'
            WHEN c.expiredDate <= NOW() + INTERVAL 15 DAY THEN 'warning'
            ELSE 'info'
        END,
        CONCAT('Hợp đồng sắp hết hạn: ', c.name),
        CONCAT('Hợp đồng "', c.name, '" với nhà cung cấp "', 
               (SELECT name FROM Providers WHERE id = c.providerId), 
               '" sẽ hết hạn vào ngày ', 
               DATE_FORMAT(c.expiredDate, '%d/%m/%Y'), '.'),
        c.id,
        'Contracts',
        DATE_ADD(c.expiredDate, INTERVAL 7 DAY) -- Expire 7 days after contract expires
    FROM Contracts c
    WHERE 
        c.status = 'Active'
        AND c.expiredDate BETWEEN NOW() AND NOW() + INTERVAL 30 DAY
        AND NOT EXISTS (
            SELECT 1 FROM Notifications 
            WHERE type = 'contract_expiring' AND relatedId = c.id AND relatedTable = 'Contracts'
        );
END //
DELIMITER ;

-- Create event to run notification generation procedures daily
DELIMITER //
CREATE EVENT IF NOT EXISTS generate_notifications_daily
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    CALL GenerateBillDueNotifications();
    CALL GenerateContractExpiringNotifications();
END //
DELIMITER ;

-- Make sure event scheduler is enabled
SET GLOBAL event_scheduler = ON; 

-- Insert sample notifications
-- Sample urgent bill notification
INSERT INTO Notifications (type, severity, title, message, relatedId, relatedTable, createdDate, expiryDate)
VALUES (
    'bill_due',
    'urgent',
    'Hóa đơn sắp đến hạn: Internet T7/2023',
    'Hóa đơn "Internet T7/2023" sẽ đến hạn vào ngày 25/07/2023.',
    1,
    'Bills',
    NOW(),
    DATE_ADD(NOW(), INTERVAL 7 DAY)
);

-- Sample warning contract notification
INSERT INTO Notifications (type, severity, title, message, relatedId, relatedTable, createdDate, expiryDate)
VALUES (
    'contract_expiring',
    'warning',
    'Hợp đồng sắp hết hạn: Hosting dịch vụ web',
    'Hợp đồng "Hosting dịch vụ web" với nhà cung cấp "Viettel IDC" sẽ hết hạn vào ngày 15/08/2023.',
    2,
    'Contracts',
    NOW(),
    DATE_ADD(NOW(), INTERVAL 14 DAY)
);

-- Sample info notification
INSERT INTO Notifications (type, severity, title, message, createdDate, expiryDate)
VALUES (
    'system',
    'info',
    'Bảo trì hệ thống',
    'Hệ thống sẽ được bảo trì vào ngày 30/07/2023 từ 22:00 - 24:00.',
    NOW(),
    DATE_ADD(NOW(), INTERVAL 10 DAY)
);

-- Sample read notification
INSERT INTO Notifications (type, severity, title, message, relatedId, relatedTable, isRead, readDate, createdDate, expiryDate)
VALUES (
    'bill_due',
    'info',
    'Hóa đơn sắp đến hạn: Điện thoại Q2/2023',
    'Hóa đơn "Điện thoại Q2/2023" sẽ đến hạn vào ngày 05/08/2023.',
    3,
    'Bills',
    1,
    DATE_SUB(NOW(), INTERVAL 1 DAY),
    DATE_SUB(NOW(), INTERVAL 2 DAY),
    DATE_ADD(NOW(), INTERVAL 5 DAY)
);

-- Sample urgent service notification
INSERT INTO Notifications (type, severity, title, message, relatedId, relatedTable, createdDate, expiryDate)
VALUES (
    'service_issue',
    'urgent',
    'Sự cố dịch vụ: Email Server',
    'Dịch vụ Email Server đang gặp sự cố. Đội kỹ thuật đang khắc phục.',
    4,
    'Services',
    NOW(),
    DATE_ADD(NOW(), INTERVAL 1 DAY)
);

-- Sample provider notification
INSERT INTO Notifications (type, severity, title, message, relatedId, relatedTable, createdDate, expiryDate)
VALUES (
    'provider_update',
    'info',
    'Cập nhật thông tin nhà cung cấp: FPT Telecom',
    'Nhà cung cấp FPT Telecom đã cập nhật thông tin liên hệ mới.',
    5,
    'Providers',
    NOW(),
    DATE_ADD(NOW(), INTERVAL 30 DAY)
); 