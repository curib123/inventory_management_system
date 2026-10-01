ALTER TABLE stock_transaction_items
    ADD COLUMN product_code_snapshot VARCHAR(50) DEFAULT NULL AFTER cost_price,
    ADD COLUMN product_name_snapshot VARCHAR(150) DEFAULT NULL AFTER product_code_snapshot,
    ADD COLUMN category_id_snapshot INT DEFAULT NULL AFTER product_name_snapshot,
    ADD COLUMN category_name_snapshot VARCHAR(100) DEFAULT NULL AFTER category_id_snapshot,
    ADD COLUMN unit_snapshot VARCHAR(50) DEFAULT NULL AFTER category_name_snapshot,
    ADD COLUMN metadata_snapshot_source ENUM('transaction', 'reconstructed') NOT NULL DEFAULT 'reconstructed' AFTER unit_snapshot,
    ADD KEY idx_items_category_snapshot (category_id_snapshot);

UPDATE stock_transaction_items i
JOIN products p ON p.id = i.product_id
JOIN categories c ON c.id = p.category_id
SET i.product_code_snapshot = p.product_code,
    i.product_name_snapshot = p.product_name,
    i.category_id_snapshot = p.category_id,
    i.category_name_snapshot = c.category_name,
    i.unit_snapshot = p.unit,
    i.metadata_snapshot_source = 'reconstructed'
WHERE i.product_code_snapshot IS NULL;

-- Backfilled metadata reflects the current catalog, not verified transaction-time values.
