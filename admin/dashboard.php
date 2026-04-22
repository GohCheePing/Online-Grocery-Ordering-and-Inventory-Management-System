<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin_name = $_SESSION['admin_full_name'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:-apple-system, BlinkMacSystemFont, "SF Pro Display", "Segoe UI", sans-serif;
        }

        body{
            min-height:100vh;
            background:
                radial-gradient(circle at top left, rgba(255,255,255,0.35), transparent 25%),
                radial-gradient(circle at bottom right, rgba(173,216,255,0.22), transparent 30%),
                linear-gradient(135deg, #dfe9f3, #f7fbff, #d6e4f0);
            color:#1d1d1f;
            overflow-x:hidden;
        }

        body::before,
        body::after{
            content:"";
            position:fixed;
            border-radius:50%;
            filter:blur(80px);
            z-index:0;
            pointer-events:none;
        }

        body::before{
            width:280px;
            height:280px;
            background:rgba(255,255,255,0.35);
            top:30px;
            left:30px;
        }

        body::after{
            width:320px;
            height:320px;
            background:rgba(0,122,255,0.15);
            bottom:30px;
            right:30px;
        }

        .dashboard{
            position:relative;
            z-index:1;
            display:flex;
            min-height:100vh;
        }

        .sidebar{
            width:260px;
            padding:24px 18px;
            background:rgba(255,255,255,0.18);
            backdrop-filter:blur(22px) saturate(180%);
            -webkit-backdrop-filter:blur(22px) saturate(180%);
            border-right:1px solid rgba(255,255,255,0.3);
            box-shadow:inset -1px 0 0 rgba(255,255,255,0.15);
        }

        .brand{
            font-size:24px;
            font-weight:700;
            margin-bottom:30px;
            padding:14px 16px;
            border-radius:20px;
            background:rgba(255,255,255,0.22);
            border:1px solid rgba(255,255,255,0.25);
            box-shadow:
                inset 0 1px 0 rgba(255,255,255,0.35),
                0 8px 20px rgba(0,0,0,0.05);
        }

        .menu{
            display:flex;
            flex-direction:column;
            gap:12px;
        }

        .menu a{
            text-decoration:none;
            color:#1d1d1f;
            padding:14px 16px;
            border-radius:18px;
            font-size:15px;
            font-weight:600;
            background:rgba(255,255,255,0.16);
            border:1px solid rgba(255,255,255,0.18);
            transition:0.25s ease;
        }

        .menu a:hover,
        .menu a.active{
            background:rgba(255,255,255,0.35);
            transform:translateX(4px);
            box-shadow:0 6px 18px rgba(0,0,0,0.06);
        }

        .main{
            flex:1;
            padding:24px;
        }

        .topbar{
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:16px;
            margin-bottom:24px;
            padding:18px 22px;
            border-radius:24px;
            background:rgba(255,255,255,0.18);
            backdrop-filter:blur(20px) saturate(180%);
            -webkit-backdrop-filter:blur(20px) saturate(180%);
            border:1px solid rgba(255,255,255,0.28);
            box-shadow:
                0 8px 24px rgba(0,0,0,0.05),
                inset 0 1px 0 rgba(255,255,255,0.35);
        }

        .topbar h1{
            font-size:28px;
            font-weight:700;
        }

        .admin-badge{
            padding:12px 16px;
            border-radius:18px;
            background:rgba(255,255,255,0.28);
            border:1px solid rgba(255,255,255,0.25);
            font-size:14px;
            font-weight:600;
        }

        .hero-card{
            padding:26px;
            border-radius:28px;
            margin-bottom:24px;
            background:rgba(255,255,255,0.20);
            backdrop-filter:blur(20px) saturate(180%);
            -webkit-backdrop-filter:blur(20px) saturate(180%);
            border:1px solid rgba(255,255,255,0.30);
            box-shadow:
                0 12px 30px rgba(0,0,0,0.06),
                inset 0 1px 0 rgba(255,255,255,0.40);
        }

        .hero-card h2{
            font-size:30px;
            margin-bottom:8px;
        }

        .hero-card p{
            color:rgba(29,29,31,0.68);
            font-size:15px;
        }

        .stats{
            display:grid;
            grid-template-columns:repeat(4, 1fr);
            gap:18px;
            margin-bottom:24px;
        }

        .stat-card{
            padding:22px;
            border-radius:24px;
            background:rgba(255,255,255,0.18);
            backdrop-filter:blur(18px) saturate(180%);
            -webkit-backdrop-filter:blur(18px) saturate(180%);
            border:1px solid rgba(255,255,255,0.26);
            box-shadow:
                0 10px 24px rgba(0,0,0,0.05),
                inset 0 1px 0 rgba(255,255,255,0.35);
        }

        .stat-card h3{
            font-size:14px;
            color:rgba(29,29,31,0.65);
            margin-bottom:12px;
            font-weight:600;
        }

        .stat-card .value{
            font-size:30px;
            font-weight:700;
        }

        .content-grid{
            display:grid;
            grid-template-columns:2fr 1fr;
            gap:20px;
        }

        .glass-box{
            padding:22px;
            border-radius:26px;
            background:rgba(255,255,255,0.18);
            backdrop-filter:blur(18px) saturate(180%);
            -webkit-backdrop-filter:blur(18px) saturate(180%);
            border:1px solid rgba(255,255,255,0.26);
            box-shadow:
                0 10px 24px rgba(0,0,0,0.05),
                inset 0 1px 0 rgba(255,255,255,0.35);
        }

        .glass-box h3{
            margin-bottom:18px;
            font-size:20px;
        }

        table{
            width:100%;
            border-collapse:collapse;
        }

        table th,
        table td{
            text-align:left;
            padding:14px 10px;
            font-size:14px;
        }

        table th{
            color:rgba(29,29,31,0.65);
            border-bottom:1px solid rgba(255,255,255,0.25);
        }

        table td{
            border-bottom:1px solid rgba(255,255,255,0.15);
        }

        .status{
            display:inline-block;
            padding:8px 12px;
            border-radius:999px;
            font-size:12px;
            font-weight:600;
            background:rgba(52,199,89,0.15);
            color:#067647;
        }

        .quick-actions{
            display:flex;
            flex-direction:column;
            gap:14px;
        }

        .quick-actions a{
            text-decoration:none;
            text-align:center;
            padding:14px;
            border-radius:18px;
            font-weight:700;
            color:#1d1d1f;
            background:rgba(255,255,255,0.28);
            border:1px solid rgba(255,255,255,0.25);
            box-shadow:
                inset 0 1px 0 rgba(255,255,255,0.45),
                0 6px 16px rgba(0,0,0,0.05);
            transition:0.25s ease;
        }

        .quick-actions a:hover{
            transform:translateY(-2px);
            background:rgba(255,255,255,0.40);
        }

        @media (max-width: 1100px){
            .stats{
                grid-template-columns:repeat(2, 1fr);
            }

            .content-grid{
                grid-template-columns:1fr;
            }
        }

        @media (max-width: 768px){
            .dashboard{
                flex-direction:column;
            }

            .sidebar{
                width:100%;
                border-right:none;
                border-bottom:1px solid rgba(255,255,255,0.3);
            }

            .stats{
                grid-template-columns:1fr;
            }

            .topbar{
                flex-direction:column;
                align-items:flex-start;
            }

            .hero-card h2{
                font-size:24px;
            }
        }
    </style>
</head>
<body>

<div class="dashboard">
    <aside class="sidebar">
        <div class="brand">Freshmart Admin</div>

        <nav class="menu">
            <a href="admin_dashboard.php" class="active">Dashboard</a>
            <a href="manage_products.php">Manage Products</a>
            <a href="manage_orders.php">Manage Orders</a>
            <a href="manage_customers.php">Customers</a>
            <a href="reports.php">Reports</a>
            <a href="settings.php">Settings</a>
            <a href="logout.php">Logout</a>
        </nav>
    </aside>

    <main class="main">
        <div class="topbar">
            <h1>Admin Dashboard</h1>
            <div class="admin-badge">Welcome, <?php echo htmlspecialchars($admin_name); ?></div>
        </div>

        <div class="hero-card">
            <h2>Hello, <?php echo htmlspecialchars($admin_name); ?> 👋</h2>
            <p>Here is an overview of your grocery ordering and inventory management system.</p>
        </div>

        <section class="stats">
            <div class="stat-card">
                <h3>Total Products</h3>
                <div class="value">128</div>
            </div>

            <div class="stat-card">
                <h3>Total Orders</h3>
                <div class="value">54</div>
            </div>

            <div class="stat-card">
                <h3>Total Customers</h3>
                <div class="value">89</div>
            </div>

            <div class="stat-card">
                <h3>Revenue</h3>
                <div class="value">RM 4,820</div>
            </div>
        </section>

        <section class="content-grid">
            <div class="glass-box">
                <h3>Recent Orders</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#1001</td>
                            <td>John Tan</td>
                            <td>RM 58.00</td>
                            <td><span class="status">Completed</span></td>
                        </tr>
                        <tr>
                            <td>#1002</td>
                            <td>Amy Lee</td>
                            <td>RM 72.50</td>
                            <td><span class="status">Completed</span></td>
                        </tr>
                        <tr>
                            <td>#1003</td>
                            <td>Daniel Wong</td>
                            <td>RM 34.90</td>
                            <td><span class="status">Completed</span></td>
                        </tr>
                        <tr>
                            <td>#1004</td>
                            <td>Sarah Lim</td>
                            <td>RM 90.20</td>
                            <td><span class="status">Completed</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="glass-box">
                <h3>Quick Actions</h3>
                <div class="quick-actions">
                    <a href="add_product.php">+ Add Product</a>
                    <a href="manage_orders.php">View Orders</a>
                    <a href="manage_customers.php">Manage Customers</a>
                    <a href="reports.php">Open Reports</a>
                </div>
            </div>
        </section>
    </main>
</div>

</body>
</html>