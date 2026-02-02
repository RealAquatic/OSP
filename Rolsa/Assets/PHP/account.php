<?php
session_start();
require_once __DIR__ . '/config.php';

$error = '';

if (isset($_GET['logout'])) {
	$_SESSION = [];
	if (ini_get('session.use_cookies')) {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
	}
	session_destroy();
	header('Location: /index.html');
	exit;
}

// If already logged in, show account area
if (!empty($_SESSION['user_email'])) {
	$loggedIn = true;
	$userEmail = $_SESSION['user_email'];
} else {
	$loggedIn = false;
}

	if ($loggedIn) {
		try {
			if ($userEmail === 'admin@rolsa.com' && !empty(
					$_SESSION['is_admin']
				)) {
				$userRow = ['full_name' => 'Admin', 'email' => 'admin@rolsa.com', 'phone' => ''];
			} else {
				$pdo = getPDO();
				$u = $pdo->prepare('SELECT full_name, email, phone FROM users WHERE email = :email LIMIT 1');
				$u->execute([':email' => $userEmail]);
				$userRow = $u->fetch();
			}
			$displayFullName = trim((string)($userRow['full_name'] ?? '')) ?: 'none';
			$displayPhone = trim((string)($userRow['phone'] ?? '')) ?: 'none';
		} catch (Exception $e) {
			$userRow = null;
		}
	}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Action']) && $_POST['Action'] === 'Login') {
	$email = trim((string)($_POST['Email'] ?? ''));
	$password = (string)($_POST['Password'] ?? '');

		if ($email === 'admin@rolsa.com' && $password === 'admin') {
		$_SESSION['user_email'] = 'admin@rolsa.com';
		$_SESSION['full_name'] = 'Admin';
		$_SESSION['is_admin'] = true;
		header('Location: /Assets/PHP/account.php');
		exit;
	}

	if ($email === '' || $password === '') {
		$error = 'Invalid email or password.';
	} elseif (strlen($password) <= 8 || !preg_match('/[0-9]/', $password) || !preg_match('/[!@#$%^&*()_+\-=[\]{};:\'"\\|,.<>\/?]/', $password)) {
		$error = 'Invalid email or password.';
	} else {
		try {
			$pdo = getPDO();
			$stmt = $pdo->prepare('SELECT user_id, full_name, email, password FROM users WHERE email = :email LIMIT 1');
			$stmt->execute([':email' => $email]);
			$user = $stmt->fetch();
			if (!$user || !password_verify($password, $user['password'])) {
				$error = 'Invalid email or password.';
			} else {
				$_SESSION['user_email'] = $user['email'];
				$_SESSION['full_name'] = $user['full_name'];
				header('Location: /Assets/PHP/account.php');
				exit;
			}
		} catch (Exception $e) {
			error_log('Login error: ' . $e->getMessage());
			$error = 'Server error. Please try again later.';
		}
	}
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Account - Rolsa Technologies</title>
	<link rel="stylesheet" href="/Assets/CSS/Header.css">
	<link rel="stylesheet" href="/Assets/CSS/Main.css">
	<link rel="stylesheet" href="/Assets/CSS/Footer.css">
	<style>
		.AccountBox { max-width:480px; margin:2.5rem auto; background:#fff; padding:1.25rem; border-radius:10px; box-shadow:0 3px 10px rgba(0,0,0,0.08); }
		.AccountBox h2 { text-align:center; margin-top:0; }
		.SmallLink { color:#1e6bd8; text-decoration:underline; }
		.LogoutBtn { background:#e74c3c; color:#fff; border:none; padding:0.6rem 1rem; border-radius:6px; cursor:pointer; }
		.AccountWelcome { text-align:center; padding:1rem; }

		.AccountGrid { display:flex; gap:1rem; flex-direction:column; align-items:stretch; }
		.AccountCol { box-sizing:border-box; width:100%; }
		.AccountCol.Left { }
		.AccountCol.Right { }
		@media (max-width:800px) { .AccountGrid { flex-direction:column; } }

		.status-badge { display:inline-block; padding:0.25rem 0.5rem; border-radius:6px; font-weight:600; font-size:0.95rem; }
		.status-pending { background:#e9ecef; color:#666; }
		.status-completed { background:#d4edda; color:#1b6b2e; }
		.status-cancelled { background:#f8d7da; color:#7a1b1b; }
		.HistoryScrollable { max-height:440px; overflow:auto; }
	</style>
</head>
<body>
	<div class="Header">
		<div class="Logo">
			<a href="/index.html"><img class="LogoImg" src="/Assets/Images/RolsaLogo.png" alt="Rolsa Logo"></a>
		</div>
		<nav class="Navigation">
			<a href="/index.html#home">Home</a>
			<a href="/index.html#aboutus">About us</a>
			<a href="/products.html">Products</a>
			<a href="/index.html#consultations">Consultations</a>
			<a href="/index.html#calculator">Calculator</a>
		</nav>
		<a class="Account" href="/Assets/PHP/account.php">
			<img class="AccountImg" src="/Assets/Images/YourAccountImage.png" alt="Your Account">
			<div class="AccountLabel">Your Account</div>
		</a>
	</div>

	<main class="Main" style="padding-top: calc(var(--header-height) + 1rem);">
		<section class="Section">
			<div class="SectionInner">
				<?php if ($loggedIn): ?>
				<?php if ($loggedIn && !empty($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
				<section class="Section">
					<div class="SectionInner">
						<h2>Admin — Manage Consultations</h2>
						<div id="AdminManage" style="width:100%; margin-top:1rem;"></div>
					</div>
				</section>
				<?php endif; ?>
						<div class="AccountCol Left">
							<h2 style="color:#33683E; text-align:left; margin-top:0.5rem;">Your Data</h2>
							<div style="margin-bottom:1rem;">
								<div><strong>Full name:</strong> <span id="DisplayFullName"><?php echo htmlspecialchars($displayFullName ?? ($userRow['full_name'] ?? 'none')); ?></span>
									<button id="EditNameBtn" class="PrimaryButton" style="margin-left:0.5rem; padding:0.25rem 0.5rem; font-size:0.9rem;">Edit</button>
								</div>
								<div style="margin-top:0.5rem;"><strong>Email:</strong> <?php echo htmlspecialchars($userRow['email'] ?? $userEmail); ?></div>
								<div style="margin-top:0.5rem;"><strong>Phone:</strong> <span id="DisplayPhone"><?php echo htmlspecialchars($displayPhone ?? ($userRow['phone'] ?? 'none')); ?></span>
									<button id="EditPhoneBtn" class="PrimaryButton" style="margin-left:0.5rem; padding:0.25rem 0.5rem; font-size:0.9rem;">Edit</button>
								</div>
								<div id="ProfileMessage" class="eMsg" style="margin-top:0.5rem;"></div>
								<div style="margin-top:0.75rem;"><a href="/index.html" class="PrimaryButton">Return to site</a>
									<form method="get" action="/Assets/PHP/account.php" style="display:inline-block;margin-left:8px;">
										<button type="submit" name="logout" value="1" class="LogoutBtn">Log out</button>
									</form>
								</div>
							</div>
							<div id="EditForms" style="display:none; margin-bottom:1rem;">
								<form id="EditNameForm">
									<div class="FormGroup">
										<label for="FullNameField">Full name</label>
										<input id="FullNameField" name="FullName" type="text" value="<?php echo htmlspecialchars($userRow['full_name'] ?? ''); ?>">
									</div>
									<div style="text-align:right; margin-top:0.5rem;"><button type="button" id="SaveNameBtn" class="PrimaryButton">Save</button> <button type="button" id="CancelNameBtn" class="PrimaryButton" style="background:#ccc;color:#000;">Cancel</button></div>
								</form>
								<form id="EditPhoneForm" style="margin-top:0.75rem;">
									<div class="FormGroup">
										<label for="PhoneField">Phone</label>
										<input id="PhoneField" name="Phone" type="text" value="<?php echo htmlspecialchars($userRow['phone'] ?? ''); ?>">
									</div>
									<div style="text-align:right; margin-top:0.5rem;"><button type="button" id="SavePhoneBtn" class="PrimaryButton">Save</button> <button type="button" id="CancelPhoneBtn" class="PrimaryButton" style="background:#ccc;color:#000;">Cancel</button></div>
								</form>
							</div>
						</div>
						<div class="AccountCol Right">
							<h2 style="color:#33683E; text-align:left; margin-top:0;">Your History</h2>
								<div id="HistoryContainer" style="background:#fff; padding:0.5rem; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.06);">
									<table style="width:100%; border-collapse:collapse; font-size:0.95rem;">
									<thead>
										<tr style="text-align:left; border-bottom:1px solid #eee;">
											<th style="padding:0.4rem;">Date</th>
											<th style="padding:0.4rem;">Type</th>
											<th style="padding:0.4rem;">Status</th>
											<th style="padding:0.4rem;">Action</th>
											</tr>
										</thead>
										<tbody id="YourHistoryBody">
											<tr><td colspan="4" style="padding:0.5rem; color:#666;">No consultations found.</td></tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>
				<?php else: ?>
					<div class="AccountBox">
						<h2>Sign in to your account</h2>
							<p id="ErrorMessage" class="eMsg"><?php if ($error) echo htmlspecialchars($error); ?></p>
						<form method="post" action="/Assets/PHP/account.php">
							<input type="hidden" name="Action" value="Login">
							<div class="FormGroup">
								<label for="Email">Email address</label>
								<input id="Email" name="Email" type="email" required>
							</div>
							<div class="FormGroup">
								<label for="Password">Password</label>
								<input id="Password" name="Password" type="password" required>
							</div>
							<div style="text-align:center; margin-top:0.75rem;">
								<button type="submit" class="PrimaryButton">Continue</button>
							</div>
						</form>
					</div>
					<p style="text-align:center; margin-top:0.75rem;">Don't have an account? <a href="/Assets/PHP/signup.php" class="SmallLink">Sign up here!</a></p>
				<?php endif; ?>
				</div>
		</section>
	</main>
	<script src="/Assets/JS/cookies.js"></script>
	<script src="/Assets/JS/account.js"></script>
	<?php if ($loggedIn):
		try {
			$pdo = getPDO();
			try {
				if (!empty($_SESSION['is_admin']) && $_SESSION['is_admin'] && $userEmail === 'admin@rolsa.com') {
					$stmt = $pdo->prepare('SELECT consultation_id, first_name, second_name, email, phone_number, form_type, postcode, address, reason, submitted_at, status FROM consultations ORDER BY submitted_at DESC');
					$stmt->execute();
					$rows = $stmt->fetchAll();
				} else {
					$stmt = $pdo->prepare('SELECT consultation_id, first_name, second_name, email, phone_number, form_type, postcode, address, reason, submitted_at, status FROM consultations WHERE email = :email ORDER BY submitted_at DESC');
					$stmt->execute([':email' => $userEmail]);
					$rows = $stmt->fetchAll();
				}
			} catch (Exception $e) {
				// Fallback for older schema without `status` column
				if (!empty($_SESSION['is_admin']) && $_SESSION['is_admin'] && $userEmail === 'admin@rolsa.com') {
					$stmt = $pdo->prepare('SELECT consultation_id, first_name, second_name, email, phone_number, form_type, postcode, address, reason, submitted_at FROM consultations ORDER BY submitted_at DESC');
					$stmt->execute();
					$rows = $stmt->fetchAll();
					foreach ($rows as &$r) { $r['status'] = 'pending'; }
				} else {
					$stmt = $pdo->prepare('SELECT consultation_id, first_name, second_name, email, phone_number, form_type, postcode, address, reason, submitted_at FROM consultations WHERE email = :email ORDER BY submitted_at DESC');
					$stmt->execute([':email' => $userEmail]);
					$rows = $stmt->fetchAll();
					foreach ($rows as &$r) { $r['status'] = 'pending'; }
				}
			}
		} catch (Exception $e) {
			$rows = [];
		}
	?>
	<script>
		window.consultationsData = <?php echo json_encode($rows, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP); ?>;
		window.isAdmin = <?php echo (!empty($_SESSION['is_admin']) && $_SESSION['is_admin']) ? 'true' : 'false'; ?>;
	</script>
	<script>
	(function(){
		if (!window.isAdmin) return;
		var container = document.getElementById('AdminManage');
		if (!container) return;
		var rows = window.consultationsData || [];

		function buildControls() {
			var ctrl = document.createElement('div');
			ctrl.style.display = 'flex';
			ctrl.style.gap = '0.5rem';
			ctrl.style.alignItems = 'center';
			ctrl.style.marginBottom = '0.5rem';

			var typeSel = document.createElement('select');
			typeSel.id = 'AdminFilterType';
			['all','consultation','installation'].forEach(function(v){
				var o = document.createElement('option'); o.value = v; o.textContent = v.charAt(0).toUpperCase()+v.slice(1); typeSel.appendChild(o);
			});

			var statusSel = document.createElement('select');
			statusSel.id = 'AdminFilterStatus';
			['all','pending','complete','cancelled'].forEach(function(v){
				var o = document.createElement('option'); o.value = v; o.textContent = v.charAt(0).toUpperCase()+v.slice(1); statusSel.appendChild(o);
			});

			var search = document.createElement('input');
			search.id = 'AdminFilterEmail';
			search.placeholder = 'Filter by email';
			search.style.flex = '1';
			search.style.padding = '0.4rem';

			var reset = document.createElement('button');
			reset.type = 'button'; reset.textContent = 'Reset'; reset.className = 'PrimaryButton';

			ctrl.appendChild(typeSel);
			ctrl.appendChild(statusSel);
			ctrl.appendChild(search);
			ctrl.appendChild(reset);
			return ctrl;
		}

		// create controls once and a persistent wrapper for results
		var controls = buildControls();
		container.appendChild(controls);
		var resultsWrapper = document.createElement('div');
		resultsWrapper.id = 'AdminManageWrapper';
		container.appendChild(resultsWrapper);

		var typeSel = document.getElementById('AdminFilterType');
		var statusSel = document.getElementById('AdminFilterStatus');
		var search = document.getElementById('AdminFilterEmail');
		var resetBtn = controls.querySelector('button');

		var inputDebounce = null;

		function renderTable() {
			var type = (typeSel && typeSel.value) || 'all';
			var status = (statusSel && statusSel.value) || 'all';
			var emailQ = (search && (search.value || '').toLowerCase()) || '';
			var filtered = rows.filter(function(r){
				if (type !== 'all' && (r.form_type||'').toLowerCase() !== type) return false;
				if (status !== 'all' && (r.status||'').toLowerCase() !== status) return false;
				if (emailQ && !(r.email||'').toLowerCase().includes(emailQ)) return false;
				return true;
			});

			// clear previous results but keep controls intact
			resultsWrapper.innerHTML = '';

			if (!filtered.length) {
				var msg = document.createElement('div');
				msg.style.background = '#fff';
				msg.style.padding = '1rem';
				msg.style.borderRadius = '8px';
				msg.textContent = 'No consultations found.';
				resultsWrapper.appendChild(msg);
				return;
			}

			var wrapper = document.createElement('div');
			wrapper.className = (filtered.length > 10) ? 'HistoryScrollable' : '';
			wrapper.style.background = '#fff';
			wrapper.style.padding = '1rem';
			wrapper.style.borderRadius = '8px';

			var table = document.createElement('table');
			table.style.width = '100%';
			table.style.borderCollapse = 'collapse';
			table.style.fontSize = '0.95rem';

			var thead = document.createElement('thead');
			thead.innerHTML = '<tr><th style="padding:0.4rem;text-align:left;">ID</th><th style="padding:0.4rem;text-align:left;">Date</th><th style="padding:0.4rem;text-align:left;">Email</th><th style="padding:0.4rem;text-align:left;">Type</th><th style="padding:0.4rem;text-align:left;">Status</th><th style="padding:0.4rem;text-align:left;">Action</th></tr>';
			table.appendChild(thead);

			var tbody = document.createElement('tbody');
			filtered.forEach(function(r){
				var tr = document.createElement('tr'); tr.setAttribute('data-id', r.consultation_id || '');
				tr.innerHTML = '<td style="padding:0.4rem;">'+(r.consultation_id||'')+'</td>'+
							   '<td style="padding:0.4rem;">'+(r.submitted_at||'')+'</td>'+
							   '<td style="padding:0.4rem;">'+(r.email||'')+'</td>'+
							   '<td style="padding:0.4rem;">'+(r.form_type||'')+'</td>'+
							   '<td style="padding:0.4rem;"><select class="statusSelect"><option value="pending">pending</option><option value="complete">complete</option><option value="cancelled">cancelled</option></select></td>'+
							   '<td style="padding:0.4rem;"><button class="updateBtn PrimaryButton">Update</button></td>';
				tbody.appendChild(tr);
			});
			table.appendChild(tbody);
			wrapper.appendChild(table);
			resultsWrapper.appendChild(wrapper);

			// wire up update buttons and set select values
			wrapper.querySelectorAll('tr[data-id]').forEach(function(tr){
				var id = tr.getAttribute('data-id');
				var r = rows.find(function(x){ return String(x.consultation_id) === String(id); });
				var sel = tr.querySelector('.statusSelect');
				if (sel && r) sel.value = r.status || 'pending';
				var btn = tr.querySelector('.updateBtn');
				btn.addEventListener('click', function(){
					var newStatus = sel.value;
					btn.disabled = true;
					fetch('/Assets/PHP/admin_update_status.php', {
						method: 'POST',
						headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
						body: 'consultation_id=' + encodeURIComponent(id) + '&status=' + encodeURIComponent(newStatus)
					}).then(function(res){ return res.json(); }).then(function(data){
						btn.disabled = false;
						if (data && data.success) {
							btn.textContent = 'Saved';
							setTimeout(function(){ btn.textContent = 'Update'; }, 1200);
							if (r) r.status = newStatus;
						} else {
							alert('Update failed');
						}
					}).catch(function(){ btn.disabled = false; alert('Update failed'); });
				});
			});
		}

		// wire control events (debounced for input)
		if (typeSel) typeSel.addEventListener('change', renderTable);
		if (statusSel) statusSel.addEventListener('change', renderTable);
		if (search) search.addEventListener('input', function(){ clearTimeout(inputDebounce); inputDebounce = setTimeout(renderTable, 150); });
		if (resetBtn) resetBtn.addEventListener('click', function(){ typeSel.value='all'; statusSel.value='all'; search.value=''; renderTable(); });

		// initial render
		renderTable();
	})();
	</script>
	<?php endif; ?>

	<div class="Footer">
		<div class="FooterInner">
			<div class="FooterLeft">
				<img class="FooterLogo" src="/Assets/Images/WhiteLogo.png" alt="Rolsa White Logo">
				<div class="FooterCopy">© 2026 Rolsa Technologies. All rights reserved.</div>
				<div class="FooterDetails">
					<div>Stamford Drift Road, Cambridgeshire </div>
					<div>support@rolsa.com</div>
					<div>+44 01777 666888</div>
				</div>
			</div>
			<div class="FooterRight">
				<div class="FooterBlock">
					<div class="FooterTitle">Navigation</div>
					<div class="FooterBar"></div>
					<ul class="FooterList">
						<li><a href="/index.html#home">Home</a></li>
						<li><a href="/index.html#aboutus">About Us</a></li>
						<li><a href="/products.html">Products</a></li>
						<li><a href="/index.html#consultations">Consultations</a></li>
						<li><a href="/index.html#calculator">Calculator</a></li>
					</ul>
				</div>
				<div class="FooterBlock">
					<div class="FooterTitle">Legal</div>
					<div class="FooterBar"></div>
					<ul class="FooterList">
						<li><a href="/privacy.html">Privacy policy</a></li>
						<li><a href="/terms.html">Terms &amp; Conditions</a></li>
						<li><a href="/cookies.html">Cookie policy</a></li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</body>
</html>

