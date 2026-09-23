<h2>Dashboard</h2>

<div class="stats">
    <div class="card">
        <h3>Total Products</h3>
        <div class="value"><?php echo $total_products; ?></div>
    </div>

    <div class="card">
        <h3>Total Stock</h3>
        <div class="value"><?php echo $total_stock; ?></div>
    </div>

    <div class="card">
        <h3>Low Stock Items</h3>
        <div class="value">0</div>
    </div>
</div>

<p>Welcome, <strong><?php echo $this->session->userdata('username'); ?></strong>.</p>
<p>This dashboard is ready for charts, stock summaries, and daily movement reports.</p>
