<?php
// Create a fixed SQL file content directly
$fixedContent = <<<SQL
-- Rating criteria table
CREATE TABLE IF NOT EXISTS RatingCriteria (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255),
    weight DECIMAL(3,2) DEFAULT 1.00, -- Weight for calculating overall score
    status VARCHAR(20) DEFAULT 'Active',
    createDate DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Provider ratings table
CREATE TABLE IF NOT EXISTS ProviderRatings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    providerId INT NOT NULL,
    userId INT DEFAULT NULL, -- Can be null for anonymous ratings
    contractId INT DEFAULT NULL, -- Optional link to a specific contract
    comment TEXT,
    overall DECIMAL(3,1) DEFAULT 0, -- Overall calculated score
    createDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (providerId) REFERENCES Providers(id) ON DELETE CASCADE,
    FOREIGN KEY (contractId) REFERENCES Contracts(id) ON DELETE SET NULL
);

-- Individual criteria scores
CREATE TABLE IF NOT EXISTS RatingScores (
    ratingId INT NOT NULL,
    criteriaId INT NOT NULL,
    score INT NOT NULL CHECK (score >= 1 AND score <= 5),
    PRIMARY KEY (ratingId, criteriaId),
    FOREIGN KEY (ratingId) REFERENCES ProviderRatings(id) ON DELETE CASCADE,
    FOREIGN KEY (criteriaId) REFERENCES RatingCriteria(id) ON DELETE CASCADE
);

-- Insert default rating criteria
INSERT INTO RatingCriteria (name, description, weight) VALUES
('Chất lượng dịch vụ', 'Đánh giá chất lượng dịch vụ được cung cấp', 1.00),
('Thời gian giao hàng', 'Đánh giá về việc giao hàng đúng hạn', 0.90),
('Thái độ phục vụ', 'Đánh giá thái độ phục vụ của nhà cung cấp', 0.85),
('Tính chuyên nghiệp', 'Đánh giá tính chuyên nghiệp trong quá trình làm việc', 0.80),
('Giá cả hợp lý', 'Đánh giá về tính hợp lý của giá so với chất lượng', 0.70);

-- Create a trigger to update the overall rating when a new score is added
DELIMITER //
CREATE TRIGGER calculate_overall_rating AFTER INSERT ON RatingScores
FOR EACH ROW
BEGIN
    DECLARE weighted_sum DECIMAL(10,2);
    DECLARE total_weight DECIMAL(10,2);
    
    SELECT SUM(rs.score * rc.weight), SUM(rc.weight)
    INTO weighted_sum, total_weight
    FROM RatingScores rs
    JOIN RatingCriteria rc ON rs.criteriaId = rc.id
    WHERE rs.ratingId = NEW.ratingId;
    
    UPDATE ProviderRatings 
    SET overall = ROUND(weighted_sum / total_weight, 1)
    WHERE id = NEW.ratingId;
END //
DELIMITER ;

-- Create a trigger to update provider reputation based on ratings
DELIMITER //
CREATE TRIGGER update_provider_reputation AFTER INSERT ON ProviderRatings
FOR EACH ROW
BEGIN
    DECLARE avg_rating DECIMAL(10,2);
    
    SELECT AVG(overall) * 20 INTO avg_rating
    FROM ProviderRatings
    WHERE providerId = NEW.providerId;
    
    UPDATE Providers
    SET reputation = ROUND(avg_rating)
    WHERE id = NEW.providerId;
END //
DELIMITER ;

-- Create a trigger to update provider reputation when rating is updated
DELIMITER //
CREATE TRIGGER update_provider_reputation_on_change AFTER UPDATE ON ProviderRatings
FOR EACH ROW
BEGIN
    DECLARE avg_rating DECIMAL(10,2);
    
    SELECT AVG(overall) * 20 INTO avg_rating
    FROM ProviderRatings
    WHERE providerId = NEW.providerId;
    
    UPDATE Providers
    SET reputation = ROUND(avg_rating)
    WHERE id = NEW.providerId;
END //
DELIMITER ;
SQL;

// Write fixed content to file
file_put_contents('add_rating_tables.sql', $fixedContent);

echo "SQL file has been fixed.";
echo "<p><a href='setup_ratings.php'>Continue to setup ratings</a></p>";
?> 