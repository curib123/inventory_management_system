UPDATE products product
JOIN suppliers duplicate ON duplicate.id = product.supplier_id
JOIN suppliers keeper
  ON keeper.supplier_name = duplicate.supplier_name
 AND keeper.id < duplicate.id
SET product.supplier_id = keeper.id;

UPDATE stock_transactions transaction_record
JOIN suppliers duplicate ON duplicate.id = transaction_record.supplier_id
JOIN suppliers keeper
  ON keeper.supplier_name = duplicate.supplier_name
 AND keeper.id < duplicate.id
SET transaction_record.supplier_id = keeper.id;

DELETE duplicate
FROM suppliers duplicate
JOIN suppliers keeper
  ON keeper.supplier_name = duplicate.supplier_name
 AND keeper.id < duplicate.id;

ALTER TABLE suppliers
    DROP INDEX idx_suppliers_name,
    ADD UNIQUE KEY uq_suppliers_name (supplier_name);
