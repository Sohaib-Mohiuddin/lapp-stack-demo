CREATE TABLE IF NOT EXISTS products (
  product_no INTEGER PRIMARY KEY,
  name TEXT NOT NULL,
  price NUMERIC(10,2) NOT NULL
);

INSERT INTO products (product_no, name, price)
VALUES (100, 'Demo Product', 12.50)
ON CONFLICT (product_no) DO NOTHING;
