
<?php
$pageTitle = "Buy Products";
include("../includes/franchisee_header.php");
include("../includes/franchisee_navbar.php");

if (session_status() === PHP_SESSION_NONE) session_start();

// normalize: if login.php set user_id, treat it as franchisee_id
if (!isset($_SESSION['franchisee_id']) && isset($_SESSION['user_id'])) {
    $_SESSION['franchisee_id'] = $_SESSION['user_id'];
}

// make sure the franchisee is logged in
$currentFranchiseeId = $_SESSION['franchisee_id'] ?? null;
if (!$currentFranchiseeId) {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/../../db/config.php'; // gives $conn
$pdo = $conn;

$fr = ['franchisee_id'=>'','point_of_contact'=>'','email'=>'','phone'=>''];

if (!empty($_SESSION['franchisee_id'])) {
  $id = (int)$_SESSION['franchisee_id'];

  if (isset($pdo) && $pdo instanceof PDO) {             // PDO path
    $st = $pdo->prepare(
      "SELECT franchisee_id, point_of_contact, email, phone
         FROM franchisees
        WHERE franchisee_id = :id
        LIMIT 1"
    );
    $st->execute([':id'=>$id]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    if ($row) $fr = $row;

  } elseif (isset($conn) && $conn instanceof mysqli) {  // mysqli path
    $res = $conn->query(
      "SELECT franchisee_id, point_of_contact, email, phone
         FROM franchisees
        WHERE franchisee_id = $id
        LIMIT 1"
    );
    if ($res && $res->num_rows) $fr = $res->fetch_assoc();
  }
}

// Helpers (safe for HTML attributes)
$FR_ID   = htmlspecialchars((string)$fr['franchisee_id'] ?? '');
$FR_NAME = htmlspecialchars((string)$fr['point_of_contact'] ?? '');
$FR_MAIL = htmlspecialchars((string)$fr['email'] ?? '');
$FR_PHONE= htmlspecialchars((string)$fr['phone'] ?? '');

// fetch franchisee details
$stmt = $pdo->prepare("SELECT business_name, email FROM franchisees WHERE franchisee_id = :fid LIMIT 1");
$stmt->execute([':fid' => $currentFranchiseeId]);
$fr = $stmt->fetch();

$imgBase     = '../assets/images/products/';
$fallbackImg = '../assets/images/logo.png';

// ================== BUILD PRODUCT LIST ==================
$sql = "SELECT product_id, name, description, image_path, price, stock_quantity, category
        FROM products
        ORDER BY name ASC";
$rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];

$allProducts = array_map(function ($r) use ($imgBase, $fallbackImg) {
    $img = trim($r['image_path'] ?? '');
    return [
        'id'       => (int)($r['product_id'] ?? 0),
        'name'     => (string)($r['name'] ?? ''),
        'price'    => isset($r['price']) ? (float)$r['price'] : 0.00,
        'stock'    => (int)($r['stock_quantity'] ?? 0),
        'category' => (string)($r['category'] ?? ''),
        'desc'     => (string)($r['description'] ?? ''),
        'img'      => $img !== '' ? $imgBase . ltrim($img, '/') : $fallbackImg,
    ];
}, $rows);

if (!is_array($allProducts)) { $allProducts = []; }

// ================== READ FILTERS ==================
$filter    = $_GET['filter']   ?? 'All';   // stock filter
$q         = trim($_GET['q'] ?? '');       // search
$catFilter = $_GET['category'] ?? 'All';   // category filter

// ================== BUILD CATEGORY LIST (ONCE) ==================
try {
    $catRows = $pdo->query(
        "SELECT DISTINCT category
           FROM products
          WHERE category IS NOT NULL AND category <> ''
          ORDER BY category ASC"
    )->fetchAll(PDO::FETCH_COLUMN) ?: [];
} catch (Throwable $e) {
    $catRows = [];
}
$cats = array_merge(['All'], $catRows ?: [
    // fallback if DB returns none
    'equipment','safety gear','uniforms','consumables',
    'Cleaning Supplies','Cleaning Tools','Cleaning Equipment'
]);

// ================== APPLY FILTERS ==================
$filtered = array_values(array_filter($allProducts, function ($p) use ($filter, $catFilter, $q) {
    // search
    $hay = (string)($p['name'] ?? '') . (string)($p['desc'] ?? '');
    $okQ = ($q === '' || stripos($hay, $q) !== false);

    // stock bucket
    $stock   = (int)($p['stock'] ?? 0);
    $okStock = true;
    if     ($filter === 'In Stock')      $okStock = ($stock > 30);
    elseif ($filter === 'Low Stock')     $okStock = ($stock >= 1 && $stock <= 30);
    elseif ($filter === 'Out of Stock')  $okStock = ($stock === 0);

    // category match
    $catVal = (string)($p['category'] ?? '');
    $okCat  = ($catFilter === 'All' || strcasecmp($catVal, $catFilter) === 0);

    return $okQ && $okStock && $okCat;
}));


// Pagination (6 per page)
$perPage = 6;
$page    = max(1, (int)($_GET['page'] ?? 1));
$total   = count($filtered);
$totalPages = max(1, (int)ceil($total/$perPage));
$page    = min($page, $totalPages);
$offset  = ($page-1)*$perPage;
$products = array_slice($filtered, $offset, $perPage);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo htmlspecialchars($pageTitle); ?> | JaniKing</title>

  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

  <!-- Page-specific CSS -->
  <link rel="stylesheet" href="../assets/css/franchisee_products.css">
</head>

<body>
    <!-- Content -->
    <main id="fr-prod-main" class="main-content">
      <div class="container-fluid">
        <!-- Filters/search + Cart dropdown -->
        <div class="card border-0 shadow-sm mb-3">
          <div class="card-body">
            <div class="d-flex align-items-center gap-2 flex-md-nowrap flex-wrap">
  <!-- LEFT: search + filters + apply -->
  <form class="d-flex align-items-center gap-2 flex-md-nowrap flex-wrap me-2" method="get">
    <!-- search -->
    <div class="input-group input-group-sm" style="width:280px;">
      <span class="input-group-text"><i class="fas fa-search"></i></span>
      <input type="text" class="form-control" name="q" placeholder="Search products..."
             value="<?php echo htmlspecialchars($q); ?>">
    </div>

    <!-- stock filter -->
    <select name="filter" class="form-select form-select-sm" style="width:160px;">
      <option value="All"        <?php if($filter=="All") echo "selected"; ?>>All Stock</option>
      <option value="In Stock"   <?php if($filter=="In Stock") echo "selected"; ?>>In Stock</option>
      <option value="Low Stock"  <?php if($filter=="Low Stock") echo "selected"; ?>>Low Stock</option>
      <option value="Out of Stock" <?php if($filter=="Out of Stock") echo "selected"; ?>>Out of Stock</option>
    </select>

    <!-- category filter -->
    <select name="category" class="form-select form-select-sm" style="width:200px;">
      <?php foreach ($cats as $c): 
        $sel = ($catFilter === $c) ? 'selected' : '';
        $label = ($c==='All') ? 'All Categories' : $c; ?>
        <option value="<?php echo htmlspecialchars($c); ?>" <?php echo $sel; ?>>
          <?php echo htmlspecialchars($label); ?>
        </option>
      <?php endforeach; ?>
    </select>

    <!-- apply -->
    <button type="submit" class="btn btn-sm btn-primary" style="background-color:#004990;border-color:#004990;color:#fff;">
      <i class="fas fa-filter"></i> Apply
    </button>
    <input type="hidden" name="page" value="1">
  </form>

  <!-- RIGHT: cart dropdown -->
  <div class="dropdown ms-md-auto">
    <button class="btn btn-outline-primary position-relative"
            style="border-color:#004990;"
            data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
      <i class="fa-solid fa-cart-shopping"></i>
      <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="cart-count">0</span>
    </button>

    <div class="dropdown-menu dropdown-menu-end p-3 dropdown-menu-cart">
      <h6 class="mb-2">Your Cart</h6>
      <div id="cart-items" class="vstack gap-3 small"><!-- items injected by JS --></div>
      <hr class="my-2">
      <div class="d-flex justify-content-between"><span>Subtotal</span><strong id="subtotal">$0.00</strong></div>
      <div class="d-flex justify-content-between"><span>Tax (8%)</span><strong id="tax">$0.00</strong></div>
      <div class="d-flex justify-content-between fw-bold"><span>Total</span><strong id="total">$0.00</strong></div>
      <button id="checkoutBtn" class="btn btn-primary w-100 mt-3"
              style="background-color:#004990;border-color:#004990;"
              data-bs-toggle="modal" data-bs-target="#checkoutModal" disabled>
        Proceed to Checkout
      </button>
    </div>
  </div>
</div>
</div>
</div>

        <!-- Products grid -->
        <div class="row g-3">
          <?php foreach($products as $p): ?>
          <div class="col-12 col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100 product-card">
              <img src="<?php echo htmlspecialchars($p['img']); ?>" alt="">
              <div class="card-body d-flex flex-column">
                <h6 class="mb-1"><?php echo htmlspecialchars($p['name']); ?></h6>
                <div class="small text-muted mb-2"><?php echo htmlspecialchars($p['desc']); ?></div>
                <div class="mt-auto d-flex justify-content-between align-items-center">
                <div class="price">$<?php echo number_format((float)$p['price'], 2); ?></div>
            <?php
        // define status variables inside the foreach, per product
        $stock = isset($p['stock']) ? (int)$p['stock'] : 0;
        if ($stock === 0) {
        $statusText  = 'Out of stock';
        $statusClass = 'text-danger';
        } elseif ($stock <= 30) {
        $statusText  = 'Low stock';
        $statusClass = 'text-warning';
        } else {
        $statusText  = 'In stock';
        $statusClass = 'text-success';
        }
        ?>
         <small class="<?php echo $statusClass; ?>">
         <?php echo $statusText; ?>
         <?php if ($stock > 0 && $stock <= 30) echo ' • Only ' . $stock . ' left'; ?>
         </small>
         </div>
        </div>
              <div class="card-footer bg-white">
                <button
                  class="btn btn-primary w-100 add-to-cart"
                   style="
                  --bs-btn-bg:#004990; --bs-btn-border-color:#004990;
                  --bs-btn-hover-bg:#003a73; --bs-btn-hover-border-color:#003a73;
                  --bs-btn-active-bg:#002f5d; --bs-btn-active-border-color:#002f5d;
                  --bs-btn-focus-shadow-rgb:0,73,144;
                  transition:filter .15s, transform .02s;"
                  data-id="<?php echo $p['id']; ?>"
                  data-name="<?php echo htmlspecialchars($p['name']); ?>"
                  data-price="<?php echo $p['price']; ?>"
                  data-img="<?php echo htmlspecialchars($p['img']); ?>"
                  <?php echo $p['stock']>0?'':'disabled'; ?>
                  onmousedown="this.style.transform='translateY(1px)'"
                  onmouseup="this.style.transform=''"
                  onmouseleave="this.style.transform=''"
                >
                  <i class="fa-solid fa-cart-plus me-1"></i>Add to Cart
                </button>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-3">
          <div class="text-muted small">Showing <?php echo count($products); ?> of <?php echo $total; ?> products</div>
          <ul class="pagination pagination-sm mb-0" style="--bs-pagination-active-bg:#004990;
           --bs-pagination-active-border-color:#004990;
           --bs-pagination-active-color:#fff;
           --bs-pagination-hover-color:#004990;
           --bs-pagination-focus-box-shadow:0 0 0 .25rem rgba(0,73,144,.25);">

            <li class="page-item <?php echo $page<=1?'disabled':''; ?>">
              <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET,['page'=>max(1,$page-1)])); ?>">Previous</a>
            </li>
            <?php for($i=1;$i<=$totalPages;$i++): ?>
              <li class="page-item <?php echo $i==$page?'active':''; ?>">
                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET,['page'=>$i])); ?>"><?php echo $i; ?></a>
              </li>
            <?php endfor; ?>
            <li class="page-item <?php echo $page>=$totalPages?'disabled':''; ?>">
              <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET,['page'=>min($totalPages,$page+1)])); ?>">Next</a>
            </li>
          </ul>
        </div>
      </div>
    </main>
  </div>
</div>

<!-- Checkout Modal -->
<div class="modal fade" id="checkoutModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Checkout</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- form is only for your billing fields; PayPal handles payment -->
      <form id="checkoutForm" onsubmit="return false;">
        <div class="modal-body">
          <div class="row g-3">
            <!-- Left: Billing -->
            <div class="col-md-6">
              <h6>Billing Information</h6>

              <div class="mb-2">
                <label class="form-label small">Full Name</label>
                <input class="form-control" name="full_name" required
                       value="<?= htmlspecialchars($FR_NAME) ?>">
              </div>

              <div class="mb-2">
                <label class="form-label small">Franchise ID</label>
                <input class="form-control" name="franchise_id" required readonly
                       value="<?= htmlspecialchars($FR_ID) ?>">
              </div>

              <div class="mb-2">
                <label class="form-label small">Email Address</label>
                <input type="email" class="form-control" name="email" required
                       value="<?= htmlspecialchars($FR_MAIL) ?>">
              </div>

              <div class="mb-2">
                <label class="form-label small">Phone Number</label>
                <input class="form-control" name="phone" required
                       value="<?= htmlspecialchars($FR_PHONE) ?>">
              </div>
            </div>

            <!-- Right: PayPal -->
            <div class="col-md-6">
              <h6>Payment Method</h6>

              <div class="card p-3">
                <div class="d-flex justify-content-between align-items-center">
                  <span class="fw-semibold">Order Total:</span>
                  <strong id="modalTotal">$0.00</strong>
                </div>

                <!-- PayPal buttons render here -->
                <div id="paypal-buttons" class="mt-3"></div>
                <div id="paypal-error" class="text-danger small mt-2" style="display:none;"></div>
              </div>
            </div>
          </div>
        </div>
      </form><!-- /#checkoutForm -->
    </div>
  </div>
</div>



<!-- Backdrop & scripts -->
<div id="backdrop" class="jk-backdrop"></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://www.paypal.com/sdk/js?client-id=Acp6QvF9LOn6dyxTr1ab4MOVSOPiq1Y4WwIUQMnE2Xhpi90ehQNjVrYGlpV1ACWbMz5Wnll4DjbGbTO2&currency=AUD&intent=capture"></script>

<!-- Page-specific JS (externalized) -->
<script src="../assets/js/franchisee_products.js"></script>
</body>
</html>
