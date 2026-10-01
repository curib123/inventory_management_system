ALTER TABLE stock_adjustments
    ADD COLUMN transaction_id INT DEFAULT NULL AFTER id,
    ADD UNIQUE KEY uq_stock_adjustments_transaction (transaction_id),
    ADD CONSTRAINT fk_stock_adjustments_transaction
        FOREIGN KEY (transaction_id) REFERENCES stock_transactions(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT;

-- Existing adjustment rows are intentionally left unlinked unless an operator can
-- identify their transaction unambiguously. New writes must always set transaction_id.
