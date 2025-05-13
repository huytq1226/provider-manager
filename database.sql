-- Create database
CREATE DATABASE IF NOT EXISTS provider_management;
USE provider_management;

-- Create tables
CREATE TABLE IF NOT EXISTS Providers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    taxCode VARCHAR(15),
    vat VARCHAR(20),
    status VARCHAR(50),
    address VARCHAR(200),
    email VARCHAR(50),
    phone VARCHAR(15),
    createDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    updateDate DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    website VARCHAR(255),
    reputation INT DEFAULT 0
);

CREATE TABLE IF NOT EXISTS Services (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    des VARCHAR(255),
    status VARCHAR(50),
    createDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    updateDate DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS ProvideService (
    serviceId INT,
    providerId INT,
    providePrice DECIMAL(18,2),
    currency VARCHAR(15),
    unit VARCHAR(45),
    PRIMARY KEY (serviceId, providerId),
    FOREIGN KEY (serviceId) REFERENCES Services(id),
    FOREIGN KEY (providerId) REFERENCES Providers(id)
);

CREATE TABLE IF NOT EXISTS Contracts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    status VARCHAR(50),
    price DECIMAL(18,2),
    currency VARCHAR(15),
    unit VARCHAR(45),
    signedDate DATETIME,
    expiredDate DATETIME,
    nameA VARCHAR(50),
    phoneA VARCHAR(15),
    nameB VARCHAR(50),
    phoneB VARCHAR(15),
    contractUrl VARCHAR(255),
    serviceId INT,
    providerId INT,
    FOREIGN KEY (serviceId) REFERENCES Services(id),
    FOREIGN KEY (providerId) REFERENCES Providers(id)
);

CREATE TABLE IF NOT EXISTS Bills (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    des VARCHAR(255),
    status VARCHAR(50),
    quantity INT,
    createdDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    paidDate DATE,
    vat FLOAT,
    refContractId VARCHAR(255)
);

-- Insert sample data
INSERT INTO Providers (name, taxCode, vat, status, address, email, phone, website, reputation) VALUES
('ABC Company', 'TAX001', 'VAT001', 'Active', '123 Main St', 'abc@example.com', '0123456789', 'www.abc.com', 4),
('XYZ Corp', 'TAX002', 'VAT002', 'Active', '456 Oak St', 'xyz@example.com', '0987654321', 'www.xyz.com', 5),
('Tech Solutions', 'TAX003', 'VAT003', 'Inactive', '789 Pine St', 'tech@example.com', '0123456780', 'www.tech.com', 3),
('Global Services', 'TAX004', 'VAT004', 'Active', '321 Elm St', 'global@example.com', '0987654320', 'www.global.com', 4),
('Local Business', 'TAX005', 'VAT005', 'Active', '654 Maple St', 'local@example.com', '0123456781', 'www.local.com', 5);

INSERT INTO Services (name, des, status) VALUES
('Web Development', 'Website design and development services', 'Active'),
('Cloud Services', 'Cloud computing and storage solutions', 'Active'),
('IT Support', 'Technical support and maintenance', 'Active'),
('Network Security', 'Security solutions and consulting', 'Active'),
('Data Analytics', 'Business intelligence and analytics', 'Active');

INSERT INTO ProvideService (serviceId, providerId, providePrice, currency, unit) VALUES
(1, 1, 1000.00, 'USD', 'project'),
(2, 2, 500.00, 'USD', 'month'),
(3, 3, 200.00, 'USD', 'hour'),
(4, 4, 1500.00, 'USD', 'project'),
(5, 5, 800.00, 'USD', 'month');

INSERT INTO Contracts (name, status, price, currency, unit, signedDate, expiredDate, nameA, phoneA, nameB, phoneB, serviceId, providerId) VALUES
('Web Development Contract', 'Active', 5000.00, 'USD', 'project', '2024-01-01', '2024-12-31', 'John Doe', '0123456789', 'Jane Smith', '0987654321', 1, 1),
('Cloud Services Agreement', 'Active', 2000.00, 'USD', 'month', '2024-02-01', '2025-01-31', 'Mike Johnson', '0123456780', 'Sarah Brown', '0987654320', 2, 2),
('IT Support Contract', 'Active', 1000.00, 'USD', 'month', '2024-03-01', '2025-02-28', 'David Wilson', '0123456781', 'Lisa Davis', '0987654322', 3, 3),
('Security Services', 'Active', 3000.00, 'USD', 'project', '2024-04-01', '2025-03-31', 'Tom Harris', '0123456782', 'Mary Miller', '0987654323', 4, 4),
('Analytics Project', 'Active', 4000.00, 'USD', 'project', '2024-05-01', '2025-04-30', 'Chris Lee', '0123456783', 'Pat Taylor', '0987654324', 5, 5);

INSERT INTO Bills (name, des, status, quantity, paidDate, vat, refContractId) VALUES
('Web Dev Invoice', 'Payment for website development', 'Paid', 1, '2024-01-15', 10.0, 'CONTRACT001'),
('Cloud Services Bill', 'Monthly cloud services', 'Paid', 1, '2024-02-15', 10.0, 'CONTRACT002'),
('IT Support Invoice', 'Monthly IT support', 'Pending', 1, NULL, 10.0, 'CONTRACT003'),
('Security Services Bill', 'Security project payment', 'Paid', 1, '2024-04-15', 10.0, 'CONTRACT004'),
('Analytics Invoice', 'Data analytics project', 'Pending', 1, NULL, 10.0, 'CONTRACT005'); 