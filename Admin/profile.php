<?php
$profileUser = $user ?? currentUser();
$profileName = $profileUser['name'] ?? 'LFT User';
$profileRole = ucfirst($profileUser['role'] ?? 'staff');
?>
<div class="admin-profile">
	<i class="fa-regular fa-bell"></i>
	<span class="admin-avatar"><?= e(strtoupper(substr($profileName, 0, 1))) ?></span>
	<strong><?= e($profileRole) ?></strong>
	<i class="fa-solid fa-chevron-down"></i>
</div>
